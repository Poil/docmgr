<?

//get our thumbnail and content functions
include("content.php");
include("thumbnail.php");

class fileObject {

	/***********************************************************************
	  Perform additional handling after the main object is created
	***********************************************************************/
	function runCreate($opt) {
	
	    extract($opt);

	    if (!$objDir) {
	    	if ($objectId) $objDir = returnObjPath($conn,$objectId);
	    	else {
	    		define("ERROR_MESSAGE","runCreate was not passed the object's data path");
	    		return false;
		}
	    }

	    $dataPath = DATA_DIR."/".$objDir;

	    //make sure we can write to the data directory
	    if (!is_writable($dataPath)) {
	      define("ERROR_MESSAGE","Data directory ".$dataPath." is not writable");
	      return false;
	    }

	    //filepath sanity check
	    if (!$filepath) {
	    	define("ERROR_MESSAGE","no filepath was passed to upload.  Maybe max_upload in php.ini needs to be increased?");
	    	return false;
	    }
	    if (!is_file(stripsan($filepath))) {
	    	define("ERROR_MESSAGE","can't seem to find the file you tried to upload.");
	    	return false;
	    }
	    
	    //pull the name from the filepath if we don't know it
	    if (!$name) {
	      $arr = explode("/",$filepath);
	      $name = array_pop($arr);
	    }

	    //run a virus scan on the file
	    if (defined("CLAMAV_SUPPORT")) {
	
	      $r = clamAvScan(stripsan($filepath));
	
	      if ($r===FALSE) {
	        define("ERROR_MESSAGE","There was an error scanning the file");
	        return false;
	      }
	      if ($r!="clean") {
	        define("ERROR_MESSAGE",$r);
	        return false;
	      }		
	
	      logEvent($conn,OBJ_VIRUS_PASS,$objectId);
	    }

	    //get the file size
	    $file_size = @filesize(stripsan($filepath));
	    if (!$file_size) $file_size = "0";

	    //set our variables if we are updaing the file
	    if (!$updateFile) $version = 1;

	    $option = null;
	    $option["object_id"] = $objectId;
	    $option["name"] = $name;
	    $option["size"] = $file_size;
	    $option["version"] = $version;
	    $option["modify"] = date("Y-m-d H:i:s");
	    $option["object_owner"] = $objectOwner;
	    $option["md5sum"] = md5_file(stripsan($filepath));
	    $option["notes"] = $notes;
	    $option["custom_version"] = $customVersion;
		
	    if (!$fileId = dbInsertQuery($conn,"dm_file_history",$option)) return false;

	    //update the object with the file size, and the new version if it's an update
	    $opt = null;
	    $opt["filesize"] = $file_size;
	    $opt["where"] = "id='$objectId'";
	    if ($updateFile) {
	    	$opt["name"] = $name;
	    	$opt["version"] = $version;
	    	$opt["status"] = "0";
	    	$opt["status_owner"] = $objectOwner;
	    	$opt["status_date"] = date("Y-m-d h:i:s");
	    }
	    dbUpdateQuery($conn,"dm_object",$opt);

	    //copy the actual file now
	    $fileDest = $dataPath."/".$fileId.".docmgr";	
	    if (!copy("$filepath", "$fileDest")) return false;

	    //do we delete the files we imported?
	    if ($delete_files=="yes") @unlink("$filepath");

	    //get out here if we are doing a file update
	    if ($updateFile) {

	    	//if there is a file revision limit, delete the previous file if necessary
	    	fileCommon::removeRevision($conn,$objectId,"earliest");

	    	return true;
	    }

	    //save our keywords.  This may get moved to createObject eventually so we can have
	    //keywords for differnt file types
	    $keyArr = returnKeywords();
	    $num = count($keyArr);
	
	    if ($num > 0) {
	
	      $option = null;
	      $option["object_id"] = $objectId;
	
	      //retrieve the keyword data from the post
	      foreach ($keyArr AS $keyword) {
	        $name = $keyword["name"];
	        $option[$name] = $_POST[$name];
	      }
	 
	      //insert the data
	      dbInsertQuery($conn,"dm_keyword",$option);
	
	    }

	    
	    return true;
	
	}

	/**********************************************************************
		runUpdate
	**********************************************************************/
	function runUpdate($opt) {

		return true;
	
	}
	
	/**********************************************************************
	  fileDelete
	  perform additional deletion when removing an object
	***********************************************************************/
	function runDelete($conn,$objId) {

		$sql = "SELECT id FROM dm_file_history WHERE object_id='$objId'";
		$info = total_result($conn,$sql);
	
		//get our directory path for this object
		$dirPath = returnObjPath($conn,$objId);
	
		//delete the thumbnail and preview
		@unlink(THUMB_DIR."/".$dirPath."/".$objId.".docmgr");
		@unlink(PREVIEW_DIR."/".$dirPath."/".$objId.".docmgr");
	
		//delete any physical files associated with our revisions
		if (is_array($info["id"])) 
			foreach ($info["id"] AS $id) @unlink(DATA_DIR."/".$dirPath."/".$id.".docmgr");
	
		$sql = "DELETE FROM dm_file_history WHERE object_id='$objId';";
		if (!db_query($conn,$sql)) return false;

		//delete our workflow and associated routes
		$sql = "SELECT id,status FROM dm_workflow WHERE object_id='$objId'";
		$list = total_result($conn,$sql);

		$sql = null;
		
		//there is a pending workflow, handle it's keys
		if ($list) {
	
			//make sure none of them are pending
			if (in_array("pending",$list["status"])) {
				
				//get the object's name
				$sql = "SELECT name FROM dm_object WHERE id='$objId';";
				$info = single_result($conn,$sql);
				define("ERROR_MESSAGE",_PENDING_WORKFLOW_ERROR." \"".$info["name"]."\"");
				return false;
			}
	
			$wsql = "SELECT id FROM dm_workflow_route WHERE workflow_id IN (".implode(",",$list["id"]).");";
			$tmp = total_result($conn,$wsql);
			
			if ($tmp) {
				$sql .= "DELETE FROM dm_task WHERE task_id IN (".implode(",",$tmp["id"]).");"; 	
				$sql .= "DELETE FROM dm_workflow_route WHERE id IN (".implode(",",$tmp["id"]).");"; 	
			}
			
		}
	
		$sql .= "DELETE FROM dm_workflow WHERE object_id='$objId';";
		if (!db_query($conn,$sql)) return false;
	
		//return true if we make it to here
		return true;
		
	
	}
	
	
	
	/***********************************************************************
	  Getting into system:
	  This function creates a thumbnail for the object when imported
	  into the system
	***********************************************************************/
	function thumbCreate($conn,$objId) {

		//make sure thumbnail support is enabled.  Also make sure imagemagick is enabled
		if (!defined("THUMB_SUPPORT")) return false;

		//get the id of the most reccent revision.  also snag the name for filetype checking
		$sql = "SELECT id,object_id,(SELECT name FROM dm_object WHERE dm_object.id=dm_file_history.object_id) AS name
			FROM dm_file_history WHERE object_id='$objId' ORDER BY version DESC LIMIT 1";
		$info = single_result($conn,$sql);

		$dirPath = returnObjPath($conn,$objId);

		$filename = $info["name"];
		$filepath = DATA_DIR."/".$dirPath."/".$info["id"].".docmgr";

		$fileinfo = return_file_info($filename,$filepath);
		$type = &$fileinfo["fileType"];
		$mime = &$fileinfo["mimeType"];

		$thumb = THUMB_DIR."/".$dirPath."/".$info["object_id"].".png";
		$finalThumb = str_replace(".png",".docmgr",$thumb);

		//init our thumbnail creator in thumb node
		$t = new fileThumbnail("thumb",$filepath,$thumb,$mime);

		//get the name of our processing method
		$method = "create".$type."thumb";

		//run our method if it exists for this file type
		if (method_exists($t,$method)) $t->$method();
                                
		//rename the file to a docmgr extension for security
		if (file_exists($thumb)) rename($thumb,$finalThumb);

	}

	/***********************************************************************
	  Getting into system:
	  This function creates a preview thumbnail for the object when imported
	  into the system
	***********************************************************************/
	function previewCreate($conn,$objId) {

		//make sure thumbnail support is enabled.  Also make sure imagemagick is enabled
		if (!defined("THUMB_SUPPORT")) return false;

		//get the id of the most reccent revision.  also snag the name for filetype checking
		$sql = "SELECT id,object_id,(SELECT name FROM dm_object WHERE dm_object.id=dm_file_history.object_id) AS name
			FROM dm_file_history WHERE object_id='$objId' ORDER BY version DESC LIMIT 1";
		$info = single_result($conn,$sql);

		$dirPath = returnObjPath($conn,$objId);

		$filename = $info["name"];
		$filepath = DATA_DIR."/".$dirPath."/".$info["id"].".docmgr";

		$fileinfo = return_file_info($filename,$filepath);
		$type = &$fileinfo["fileType"];
		$mime = &$fileinfo["mimeType"];

		$thumb = PREVIEW_DIR."/".$dirPath."/".$info["object_id"].".png";
		$finalThumb = str_replace(".png",".docmgr",$thumb);

		//init our thumbnail creator in thumb node
		$t = new fileThumbnail("preview",$filepath,$thumb,$mime);

		//get the name of our processing method
		$method = "create".$type."thumb";

		//run our method if it exists for this file type
		if (method_exists($t,$method)) $t->$method();

		//rename the file to a docmgr extension for security
		if (file_exists($thumb)) rename($thumb,$finalThumb);

	}


	
	/***********************************************************************
	  Displaying:
	  This function returns the link and the icon to be displayed
	  in the finder in list view
	  return $arr("link" => $link, "icon" => $icon);
	***********************************************************************/
	function listDisplay($info) {
	
	  //$extension = return_file_extension($info["name"]);
	  $type = return_file_type($info["name"]);

	  if (file_exists(THEME_PATH."/images/fileicons/".$type.".png"))
	    $arr["icon"] = THEME_PATH."/images/fileicons/".$type.".png";
	  else
	    $arr["icon"] = THEME_PATH."/images/fileicons/file.png";
	
	  $arr["link"] = "index.php?module=fileview&objectId=".$info["id"];
	
	  return $arr;

	}
	
	/**********************************************************************
	  Displaying:
	  This returns the icon or thumbnail to be displayed for the
	  object while in thumbnail view, and the link that is followed
	  when the object name or thumbnail is clicked
	  return $arr("link" => $link, "icon" => $icon);
	**********************************************************************/
	function thumbDisplay($info) {
	
	  $src = THUMB_DIR."/".$info["level1"]."/".$info["level2"]."/".$info["id"].".docmgr";
	  $sessId = session_id();

	  if (file_exists($src)) {
	    $arr["class"] = "previewImage";
	    $arr["icon"] = "app/showthumb.php?objectId=".$info["id"]."&objDir=".$info["level1"]."/".$info["level2"]."&sessId=".$sessId;
	  }
	  else {
	          
	    $type = $info["object_type"];
	             
	    if (file_exists(THEME_PATH."/images/thumbnails/".$type.".png"))
	      $arr["icon"] = THEME_PATH."/images/thumbnails/".$type.".png";
	    else
	      $arr["icon"] = THEME_PATH."/images/thumbnails/file.png";
	
	  }
	
	  $arr["link"] = "index.php?module=fileview&objectId=".$info["id"];

	  return $arr;                        
	
	}
	
	/***********************************************************************
	  Getting into system:
	  This function returns the contents of the object as a string
	  to be indexed by the indexObject function
	***********************************************************************/
	function runIndex($conn,$objId) {

		//get the id of the most reccent revision.  also snag the name for filetype checking
		$sql = "SELECT id,(SELECT name FROM dm_object WHERE dm_object.id=dm_file_history.object_id) AS name
			FROM dm_file_history WHERE object_id='$objId' ORDER BY version DESC LIMIT 1";
		$info = single_result($conn,$sql);

		$dirPath = returnObjPath($conn,$objId);

		$filename = $info["name"];
		$filepath = DATA_DIR."/".$dirPath."/".$info["id"].".docmgr";

		//make sure the user has not prevented indexing of this file
		if (!return_file_idxopt($filename,$filepath)) return false;

		/*********************************************
			filetype can be image, txt, markup,
			soffice, pdf, or other
		*********************************************/

		$fileType=return_file_type($filename,$filepath);

		$function = "get".$fileType."content";

		//extract content from the file by calling the associated
		//function for that file type
		return fileContent::$function($filepath,$fileType);
	  
	
	}
	
	
}

		
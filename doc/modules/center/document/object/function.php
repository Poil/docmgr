<?


class documentObject {

	
	function runCreate($opt) {

		extract($opt);
    
		//make sure we can write to the data directory
		if (!is_writable(FILE_DIR)) {
			define("ERROR_MESSAGE","File directory is not writable");
			return false;
		}

		$documentPath = FILE_DIR."/document/".returnObjPath($conn,$objectId);
		if (!$version) $version = "1";

		//create our document directory if it doesn't exist
		if (!is_dir($documentPath)) mkdir($documentPath);

		//this is a new document entry, so we will get a new unique id for it and store it in the filesystem
		$opt = null;
		$opt["object_id"] = $objectId;
		$opt["version"] = $version;
		$opt["modify"] = date("Y-m-d h:i:s");
		$opt["object_owner"] = USER_ID;
		$documentId = dbInsertQuery($conn,"dm_document",$opt);

		if (!$documentId) return false;
		
		$file = $documentPath."/".$documentId.".docmgr";

		//open a new file and write our contents to it
		if ($fp = fopen($file,"w")) {

			fwrite($fp,$documentContent);
			fclose($fp);	

			//update the filesize for this object
			$size = strlen($documentContent);
			$opt = null;
			$opt["filesize"] = $size;
			$opt["where"] = "id='$objectId'";
			dbUpdateQuery($conn,"dm_object",$opt);

			return true;	

		} else return false;		
    	
	}

	function runUpdate($opt) {

		extract($opt);

		//get the current version so we can increment
		$sql = "SELECT version FROM dm_object WHERE id='$objectId'";
		$info = single_result($conn,$sql);
		
		$newver = $info["version"] + 1;

		//update the object record w/ the new version number
		$sql = "UPDATE dm_object SET version='$newver' WHERE id='$objectId'";
		db_query($conn,$sql);
		
		$opt["version"] = $newver;

		//basically we run our insert query first to create a file with the new contents
		documentObject::runCreate($opt);

		//now we update our histories
		indexObject($conn,$objectId,USER_ID,null);
                //fileObject::thumbCreate($conn,$objectId);

                //if there is a file revision limit, delete the previous file if necessary
                documentCommon::removeRevision($conn,$objectId,"earliest");
                                                                                        
		//log the events
		logEvent($conn,OBJ_CHECKED_IN,$objectId);
		sendEventNotify($conn,$objectId,"OBJ_CHECKIN_ALERT");
    	
	}

	/***********************************************************************
	  Additional processing for removing a collection
	***********************************************************************/
	function runDelete($conn,$objId) {

		//get the ids of our dm_documents belonging to this object
		$sql = "SELECT id FROM dm_document WHERE object_id='$objId'";
		$info = total_result($conn,$sql);

		$objPath = returnObjPath($conn,$objId);
	
		//delete them
		$sql = "DELETE FROM dm_document WHERE object_id='$objId'";
		if (db_query($conn,$sql)) {

			//delete any physical files associated with our revisions
			foreach ($info["id"] AS $id) @unlink(DOC_DIR."/".$objPath."/".$id.".docmgr");

			return true;
		}
		else return false;
	
	}

	function runIndex($conn,$objId) {

		//get the current version so we can increment
		$sql = "SELECT version FROM dm_object WHERE id='$objId'";
		$info = single_result($conn,$sql);

		//find the id of our most recent entry
		$sql = "SELECT id FROM dm_document WHERE object_id='$objId' AND version='".$info["version"]."' ORDER BY version DESC LIMIT 1";
		$winfo = single_result($conn,$sql);
		
		$file = FILE_DIR."/document/".returnObjPath($conn,$objId)."/".$winfo["id"].".docmgr";
		
		//extract the contents of our file
		if (file_exists($file)) return removeTags(file_get_contents($file));
	
	
	}
	
	/***********************************************************************
	  Getting into system:
	  This function creates a thumbnail for the object when imported
	  into the system
	***********************************************************************/
	function thumbCreate() {
	
	
	
	}
	
	/***********************************************************************
	  Displaying:
	  This function returns the link and the icon to be displayed
	  in the finder in list view
	  return $arr("link" => $link, "icon" => $icon);
	***********************************************************************/
	function listDisplay($info) {
	
	  $arr["icon"] = THEME_PATH."/images/fileicons/document.png";
	  $arr["link"] = "index.php?module=docview&objectId=".$info["id"];
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
	
	  $arr["icon"] = THEME_PATH."/images/thumbnails/file.png";
	  $arr["link"] = "index.php?module=docview&objectId=".$info["id"];
	  return $arr;
	
	}
	
	
}
<?php

/**************************************************
	do not modify anything below here
**************************************************/

require_once "Server/Server.php";
require_once "Server/function.php";
require_once "Server/header.php";


/**
 * Filesystem access using WebDAV
 *
 * @access public
 */
class HTTP_WebDAV_Server_Filesystem extends HTTP_WebDAV_Server 
{

        function ServeRequest($base = false) 
        {
            // special treatment for litmus compliance test
            // reply on its identifier header
            // not needed for the test itself but eases debugging
            foreach(apache_request_headers() as $key => $value) {
                if(stristr($key,"litmus")) {
                    error_log("Litmus test $value");
                    header("X-Litmus-reply: ".$value);
                }
            }

            // set root directory, defaults to webserver document root if not set
            if ($base) {
            	$this->base = realpath($base); // TODO throw if not a directory
	    } else if(!$this->base) {
		$this->base = $_SERVER['DOCUMENT_ROOT'];
	    }

	    // let the base class do all the work
	    parent::ServeRequest();

	}

        /**
         * Process authenticaiton
         * @access private
         * @param  string  HTTP Authentication type (Basic, Digest, ...)
         * @param  string  Username
         * @param  string  Password
         * @return bool    true on successful authentication
         */
        function checkAuth($type, $user, $pass) 
        {

          $keys = array_keys($_SERVER);
          
          $conn = $_SESSION["conn"];

          if (defined("USE_LDAP")) include(WEBDAV_PATH."/auth/ldap.inc.php"); 
          else include(WEBDAV_PATH."/auth/db.inc.php");

          if ($accountInfo = password_check($conn,$user,$pass)) {

            $_SESSION["user_id"] = $accountInfo["id"];
            $_SESSION["user_login"] = $accountInfo["login"];
 
            define("USER_ID",$_SESSION["user_id"]);

            //set the bitset info for our permissions
            include(WEBDAV_PATH."/auth/function.inc.php");
            userPermSet($conn,$_SESSION["user_id"]);
            return true;
   
          }
          else return false;
        
        }

        
        function HEAD(&$options) {
        
        	$files = null;
        	$files["files"] = array();

        	$options["path"] = webdavfunc::fixPath($options["path"]);
        	$path = $options["path"];

		if (!$path) return false;

		//this needs to determine if we are looking at a file or not
		//return true if the object does not exist
		if (webdavfunc::checkObjExists($path)) return true;
		else return false;

	}

	function returnRootLevel() {

		// create result array
		$info = array();
		$info["path"]  = "/";    
		$info["props"] = array();

		$info["props"][] = $this->mkprop("displayname", "/");
            
		// creation and modification time
		$info["props"][] = $this->mkprop("creationdate",    0);
		$info["props"][] = $this->mkprop("getlastmodified", 0);

		// directory (WebDAV collection)
		$info["props"][] = $this->mkprop("resourcetype", "collection");
		$info["props"][] = $this->mkprop("getcontenttype", "httpd/unix-directory");             
		$info["props"][] = $this->mkprop("getcontentlength", 0);

		return $info;

	}


	function PROPFIND(&$options, &$files)  {

		$files = null;
		$files["files"] = array();


		//store information about the requested path itself
		$path = webdavfunc::fixPath($options["path"]);
		$depth = $options["depth"];

		webdavfunc::checkOutput("propfinding ".$path."\n");

		if ($path=="/") $files["files"][] = $this->returnRootLevel();
		else {

			if (!webdavfunc::checkObjExists($path)) return false;
			webdavfunc::checkOutput("this object exists\n");
			$arr = webdavfunc::getObjInfo($path);
			$files["files"][] = $this->fileinfo($path,$arr["objInfo"]);

		}

		if (!empty($options["depth"])) {

			//get the id of our current category            
			webdavfunc::checkOutput("getting id of ".$path."\n");
			$objId = webdavfunc::getCollectionId($path);
			webdavfunc::checkOutput("object's id is ".$objId."\n");
			//get all files and categories in this category
			$listArray = execWebdav($_SESSION["conn"],$objId);

			//loop through our objects and get the properties for our files
			for ($i=0;$i<$listArray["count"];$i++) {
				$files["files"][] = $this->fileinfo($path."/".$listArray[$i]["name"],$listArray[$i]);      
			}
		}
		// ok, all done
		return true;
          
	} 
        
        /**
         * Get properties for a single file/resource
         *
         * @param  string  resource path
         * @return array   resource properties
         */
        function fileinfo($fPath,$data) 
        {

            //get all our values
            $name = $data["name"];

            // create result array
            $info = array();
            $info["path"] = webdavfunc::fixURL($fPath);
            $info["props"] = array();

            // show special beautified displayname here ...
            $info["props"][] = $this->mkprop("displayname", $name);

            if (!$data["create_date"]) $data["create_date"] = date("Y-m-d H:i:s");
            if (!$data["status_date"]) $data["status_date"] = date("Y-m-d H:i:s");
            
            // creation and modification time
            $info["props"][] = $this->mkprop("creationdate",    strtotime($data["create_date"]));
            $info["props"][] = $this->mkprop("getlastmodified", strtotime($data["status_date"]));

            // type and size (caller already made sure that path exists)
            if ($data["object_type"]=="collection") {

		if (substr($info["path"],-1) != "/") {
			$info["path"] .= "/";
		}

                // directory (WebDAV collection)
                $info["props"][] = $this->mkprop("resourcetype", "collection");
                $info["props"][] = $this->mkprop("getcontenttype", "httpd/unix-directory");             
                $info["props"][] = $this->mkprop("getcontentlength", 0);

           } else {
           
               $size = $data['filesize'];
               $type = return_file_mime($name);
               
                // plain file (WebDAV resource)
                $info["props"][] = $this->mkprop("resourcetype", "");
                if ($type) {
                    $info["props"][] = $this->mkprop("getcontenttype", $type);
                } else {
                    $info["props"][] = $this->mkprop("getcontenttype", "application/x-non-readable");
                }               
                $info["props"][] = $this->mkprop("getcontentlength", $size);

            }
            
            if ($info) return $info;

        }


        function GET(&$options) {

		$path = webdavfunc::fixPath($options["path"]);
		$conn = $_SESSION["conn"];

		webdavfunc::checkOutput("Getting ".$path."\n");

		//get information for our current path;
		$arr = webdavfunc::getObjInfo($path);
		if (!$arr["objInfo"]) return false;
	
		$objectId = $arr["objInfo"]["id"];
		$version = $arr["objInfo"]["version"];
		$filename = $arr["objInfo"]["name"];
		$modDate = $arr["objInfo"]["status_date"];

		//check the permissions for this file to see if the user can view them
		if (!webdavfunc::checkWebdavPerms($objectId,OBJ_VIEW)) return "403 Forbidden";

		$sql = "SELECT id FROM dm_file_history WHERE object_id='$objectId' AND version='$version'";
		$info = single_result($conn,$sql);

		if (!$objectId || !$info) return "404 Not Found";

		$filePath = DATA_DIR."/".returnObjPath($conn,$objectId)."/".$info["id"].".docmgr";

		if (!file_exists($filePath)) return "404 Not Found";

		//verify the md5sum for the file
		if (!fileChecksum($conn,$info["id"],$filePath)) return "424 Failed Dependency";
                                                        
		//log the view
		logEvent($conn,OBJ_VIEWED,$objectId);
                                                        
		$fileSize = filesize($filePath);
		$fileType = return_file_mime($filename);

		$options["mimetype"] = $fileType;
		$options["mtime"] = $modDate;
		$options["size"] = $fileSize;
		$options["stream"] = fopen($filePath,"r");

		return true;

	}


	function PUT (&$options) {

		$conn = $_SESSION["conn"];
		webdavfunc::checkOutput("about to put ".$options["path"]."\n");
		$path = webdavfunc::fixPath($options["path"]);

		webdavfunc::checkOutput("putting ".$path."\n");

		//make sure this user is allowed to insert files
		if (!bitset_compare(BITSET,INSERT_OBJECTS,ADMIN)) return "403 Forbidden";

		//make sure the parent directory exists
		if (webdavfunc::getCatOwner($path)==NULL) return "405 Method not allowed";

		//get our information for this file
		$arr = webdavfunc::getObjInfo($path);

		if ($arr["objInfo"]) {

	  	       $filename = 	$arr["objInfo"]["name"];
	  	       $parentId =	$arr["objInfo"]["parent_id"];
		       $objectId = 	$arr["objInfo"]["id"];
		       $mode 	=	"update";
		       $name  = addslashes($arr["objInfo"]["name"]);

		       //if the existing object is not a file, call it off
		       if ($arr["objInfo"]["object_type"]!="file") return "405 Method not allowed";

		       //if the file is checked out, make sure the user has access to checkin the file,
		       //otherwise check to see if they have access to update the file
		       $sql = "SELECT status,version FROM dm_object WHERE id='$objectId';";
		       $info = single_result($conn,$sql);
		       $newVersion = $info["version"] + 1;

			// make sure this user allowed to overwrite file
			if (!webdavfunc::checkWebdavPerms($objectId, OBJ_EDIT)) return "403 Forbidden";
		
		        // if file is checked out && user has no OBJ_CHECKIN rights
			if ($info["status"] == 1 && !webdavfunc::checkWebdavPerms($objectId,OBJ_CHECKIN)) return "403 Forbidden";


		} else {

			$mode = "insert";
		     	$parentId = webdavfunc::getCatOwner($path);
		     	$tmp = explode("/",$path);
		     	$name = addslashes(array_pop($tmp));
		     	
		    	//make sure this directory does not already exist
		    	if (!checkobjName($conn,$name,$parentId)) return "405 Method not allowed";

		     	//make sure the user has write access to the parent category to create a new file
		     	if (!webdavfunc::checkWebdavPerms($parentId,OBJ_EDIT)) return "403 Forbidden";

		}

		if ($options["stream"] && defined("USER_ID")) {

			$filepath = TMP_DIR."/".rand();
			$fp = fopen($filepath,"w");
			while(!feof($options["stream"])) fwrite($fp,fread($options["stream"],4096));
			fclose($fp);

			webdavfunc::checkOutput("Creating ".$name."\n");
			
			$opt = null;
			$opt["conn"] = $conn;
			$opt["name"] = $name;
			$opt["filepath"] = $filepath;
			$opt["delete_files"] = "yes";
			$opt["objectType"] = "file";
			$opt["parentId"] = $parentId;
			$opt["objectOwner"] = USER_ID;

			if ($mode=="insert") {
				if (createObject($opt)) return "201 Created";
				else return "204 No Content";
			} else {

				$opt["objectId"] = $objectId;
				$opt["updateFile"] = 1;
				$opt["version"] = $newVersion;
				
				if (fileObject::runCreate($opt)) {

					//index and thumb
					indexObject($conn,$objectId,USER_ID,null);
					fileObject::thumbCreate($conn,$objectId);

					//log our events
					logEvent($conn,OBJ_CHECKED_IN,$objectId);
					sendEventNotify($conn,$objectId,"OBJ_CHECKIN_ALERT");

					return "201 Created";

				} else return "204 No Content";
			}

		} else return "403 Forbidden";
		
		return "204 No Content";

	}


	function MKCOL($options) {	   

	    $path = webdavfunc::fixPath($options["path"]);

	    webdavfunc::checkOutput("MKCOL on ".$path."\n");

	    //make sure the parent directories exist and is a directory
	    $parentId = webdavfunc::getCatOwner($path);
	    webdavfunc::checkOutput("parent id is ".$parentId."\n");
	    if ($parentId==NULL) return "409 Conflict";

	    //this gets the filename and leaves an array containing the parent
	    $arr = explode("/",$path);
	    $objName = addslashes(array_pop($arr));

	    $conn = $_SESSION["conn"];

	    //make sure we have permissions to insert and at least edit the collection parent
	    if (!bitset_compare(BITSET,INSERT_OBJECTS,ADMIN) ||
	    	!webdavfunc::checkWebdavPerms($parentId,OBJ_EDIT)) return "403 Forbidden";

	    if(!empty($_SERVER["CONTENT_LENGTH"])) { // no body parsing yet
		return "415 Unsupported media type";
	    }

	    //make sure this directory does not already exist
	    webdavfunc::checkOutput("checking to see if it exists\n");
	    if (!checkobjName($conn,$objName,$parentId)) return "405 Method not allowed";
	    webdavfunc::checkOutput("it doesn't\n");

	    //create our directory	
	    $option = null;
	    $option["conn"] = $conn;
	    $option["name"] = $objName;
	    $option["parentId"] = $parentId;
	    $option["objectType"] = "collection";
	    $option["objectOwner"] = USER_ID;
	    if (createObject($option)) {
	    	webdavfunc::checkOutput("Collection created successfully\n");
	    	return "201 Created";
	    }
	    else {
	    	return "204 No Content";
	    }
	}
	
	/**
	 * DELETE method handler
	 *
	 * @param  array  general parameter passing array
	 * @return bool   true on success
	 */
	function delete($options) 
	{

	    $conn = $_SESSION["conn"];
	    $path = webdavfunc::fixPath($options["path"]);

	    webdavfunc::checkOutput("deleting ".$path."\n");
	    
	    //get current object info
	    $arr = webdavfunc::getObjInfo($path);
	    $objectId = $arr["objInfo"]["id"];

	    webdavfunc::checkOutput("deleting ".$objectId."\n");

	    //does it exist
	    if (!$objectId) return "404 Not Found";

	    //check permissions
	    if (!webdavfunc::checkWebdavPerms($objectId,OBJ_MANAGE)) return "403 Forbidden";

	    //if it's locked, don't allow it to be deleted
	    if (is_array($this->checkLock($path))) return "403 Forbidden";

	    //delete the object
	    deleteObject($conn,$objectId);

	    return "204 No Content";	    

	}


	/**
	 * MOVE method handler
	 *
	 * @param  array  general parameter passing array
	 * @return bool   true on success
	 */
	function move($options) 
	{
	    return $this->copy($options, true);
	}

	/**
	 * COPY method handler
	 *
	 * @param  array  general parameter passing array
	 * @return bool   true on success
	 */
	function copy($options, $del=false) 
	{
	    // TODO Property updates still broken (Litmus should detect this?)
	    if(!empty($_SERVER["CONTENT_LENGTH"])) { // no body parsing yet
		return "415 Unsupported media type";
	    }

	    // no copying to different WebDAV Servers yet
	    if(isset($options["dest_url"])) {
		return "502 bad gateway";
	    }

	    $conn = $_SESSION["conn"];

	    $srcPath = webdavfunc::fixPath($options["path"]);
	    $destPath = webdavfunc::fixPath($options["dest"]);

	    $destCatId = webdavfunc::getCatOwner($destPath);
	    $srcCatId = webdavfunc::getCatOwner($srcPath);

	    webdavfunc::checkOutput("Copying ".$srcPath." to ".$destPath."\n");
	    webdavfunc::checkOutput("Copying ".$srcCatId." to ".$destCatId."\n");

	    $new = false;
	    $existing_col = null;

	    //make sure there is a source and destination directory
	    if ($destCatId==NULL) {
	    	webdavfunc::checkOutput("Couldn't find dest, bailing\n");
	    	return "409 Conflict";
	    }
	    if ($srcCatId==NULL) return "409 Conflict";

	    $srcArr = webdavfunc::getObjInfo($srcPath);
	    $objectId = $srcArr["objInfo"]["id"];
	    $srcInfo = $srcArr["objInfo"];
	    $mode = $srcInfo["object_type"];

	    $destArr = webdavfunc::getObjInfo($destPath);
	    $destInfo = $destArr["objInfo"];
	    
	    if ($destInfo) {
	    
	      //does this file exist?  Has the user prompted to overwrite?
	      if (!$options["overwrite"]) return "412 Precondition Failed";
	      $copyMode = "update";
	    
	    } else $copyMode = "insert";

	    //copy our file to the temp directory with it's real name, then insert it into the database as
	    //a new file
	    $arr = explode("/",$srcPath);
	    $filename = addslashes(array_pop($arr));
	    
	    $arr = explode("/",$destPath);
	    $newfilename = addslashes(array_pop($arr));
	    
	    //permissions check.  if inserting, we need add permissions.  if updating we need to check the object.
	    //otherwise we need to check the parent
	    if (!webdavfunc::checkWebdavPerms($objectId,OBJ_MANAGE) &&
	    	!webdavfunc::checkWebdavPerms($objectId,OBJ_EDIT)) return "403 Forbidden";

	    //if we are renaming the object, just do it and leave
	    if ($srcCatId==$destCatId && $del==true) {

		  //if the category already exists.  don't allow it to be overwritten
		  //if (!checkobjName($conn,$newfilename,$destCatId)) return "403 Forbidden";

		  $sql = "UPDATE dm_object SET name='".$newfilename."' WHERE id='$objectId';";
		  db_query($conn,$sql);

		  //reindex our object
		  indexObject($conn,$objectId,USER_ID,null);

		  return "201 Created";
		  
	    }	    
	    //if we are simply moving the object to another collection
	    else if ($srcCatId!=$destCatId && $del==true) {

		  //if the category already exists.  don't allow it to be overwritten
		  //if (!checkObjName($conn,$newfilename,$destCatId)) {
		  //	return "403 Forbidden";
		  //}

		  $sql = "DELETE FROM dm_object_parent WHERE object_id='$objectId' AND parent_id='$srcCatId';";
		  $sql .= "INSERT INTO dm_object_parent (object_id,parent_id) VALUES ('$objectId','$destCatId');";
		  db_query($conn,$sql);

		  return "201 Created";

	    }	    

	    //process copying a file
	    if ($mode=="file") {

	    	$sql = "SELECT id FROM dm_file_history WHERE object_id='$objectId' ORDER BY version DESC LIMIT 1";
	    	$info = single_result($conn,$sql);

		//create a path to our docmgr file		
	      	$src = DATA_DIR."/".returnObjPath($conn,$objectId)."/".$info["id"].".docmgr";

		$opt = null;
		$opt["conn"] = $conn;
		$opt["name"] = $newfilename;
		$opt["filepath"] = $src;
		$opt["delete_files"] = "no";
		$opt["objectType"] = "file";
		$opt["parentId"] = $destCatId;
		$opt["objectOwner"] = USER_ID;

		if ($copyMode=="insert") {
			if (createObject($opt)) return "201 Created";
			else return "204 No Content";
		} else {

			$opt["objectId"] = $objectId;
			$opt["updateFile"] = 1;
			$opt["version"] = $newVersion;
			
			if (fileObject::runCreate($opt)) {

				//index and thumb
				indexObject($conn,$objectId,USER_ID,null);
				fileObject::thumbCreate($conn,$objectId);

				//log our events
				logEvent($conn,OBJ_CHECKED_IN,$objectId);
				sendEventNotify($conn,$objectId,"OBJ_CHECKIN_ALERT");

				return "201 Created";

			} else return "204 No Content";
		}

	    //process copying a collection
	    } else {

	      	//does this file exist?  Has the user prompted to overwrite?
	      	//if (!$options["overwrite"]) return "412 Precondition Failed";

		//if the category already exists.  carry on like it was created
		if (!checkobjName($conn,$newfilename,$destCatId)) {
			return "201 Created";
		}
		
		webdavfunc::checkOutput("copying this collection\n");

		//create our directory	
		$option = null;
		$option["conn"] = $conn;
		$option["name"] = $newfilename;
		$option["parentId"] = $destCatId;
		$option["objectType"] = "collection";
		$option["objectOwner"] = USER_ID;
		if (createObject($option)) {

			//update our stored collections
			$sql = "SELECT * FROM dm_view_collections ORDER BY id";
			$_SESSION["catList"] = total_result($_SESSION["conn"],$sql);

			webdavfunc::checkOutput("collection created\n");

			//get all sub ojects of this collection and copy them also
			//dest, path, overwrite
			$opt = null;
			$opt["path"] = $Path;	//src
			$opt["dest"] = $dest;
			
			//get all objects belonging to this one
			$sql = "SELECT id,name FROM dm_view_objects WHERE object_type IN ('file','collection') AND parent_id='$objectId'";
			$children = list_result($conn,$sql);
			
			for ($i=0;$i<$children["count"];$i++) {
			
				$child = $children[$i];
				$name = $child["name"];
				
				//recall our copy function to copy the new object
				$opt = null;
				$opt["path"] = $srcPath."/".$name;
				$opt["dest"] = $destPath."/".$name;
				$opt["overwrite"] = $option["overwrite"];	//reuse the overwrite selection
				$this->copy($opt,false);
			
			}
			
			return "201 Created";
		}
		else {
			webdavfunc::checkOutput("collection creation failed\n");
			return "403 Forbidden";
		}
	    }

	}

	/**
	 * PROPPATCH method handler
	 *
	 * @param  array  general parameter passing array
	 * @return bool   true on success
	 */
	function proppatch(&$options) 
	{

		/*	
		//return "404 Not Found";
	    webdavfunc::checkOutput("proppatch path is ".$options["path"]."\n");

	    foreach($options["props"] as $key => $prop) {

	    	$keys = array_keys($prop);
	    	foreach ($keys AS $key) {
		    	webdavfunc::checkOutput("prop => ".$key.": ".$prop[$key]."\n");
		}
			    	
	    }

	    return true;
	    */
	    
	    $conn = $_SESSION["conn"];
	    global $prefs, $tab;

	    $msg = "";
	    
	    $path = $options["path"];
	    
	    $dir = dirname($path)."/";
	    $base = basename($path);
	    
	    foreach($options["props"] as $key => $prop) {
		if($ns == "DAV:") {
		    $options["props"][$key][$status] = "403 Forbidden";
		} else {
		    if(isset($prop["val"])) {
			$query = "REPLACE INTO dm_properties SET path = '$options[path]', name = '$prop[name]', ns= '$prop[ns]', value = '$prop[val]'";
		    } else {
			$query = "DELETE FROM dm_properties WHERE path = '$options[path]' AND name = '$prop[name]' AND ns = '$prop[ns]'";
		    }       
		    db_query($conn,$query);
		}
	    }
			
	    return "";
	}

	/**
	 * LOCK method handler
	 *
	 * @param  array  general parameter passing array
	 * @return bool   true on success
	 */
	function lock(&$options) 
	{

	  webdavfunc::checkOutput("locking\n");

	    $conn = $_SESSION["conn"];
	    $arr = webdavfunc::getObjInfo($options[path]);

	    if ($arr["objInfo"]) {
	    
		$opt = null;
		$opt["status"] = 1;
		$opt["status_owner"] = USER_ID;
		$opt["token"] = $options[locktoken];	
		$opt["where"] = "id='".$arr["objInfo"]["id"]."'";
		if (dbUpdateQuery($conn,"dm_object",$opt)) webdavfunc::checkOutput("file locked\n");
		else webdavfunc::checkOutput("file not locked\n");

		return "200 OK";
		
	    } else return "409 Conflict";

	}

	/**
	 * UNLOCK method handler
	 *
	 * @param  array  general parameter passing array
	 * @return bool   true on success
	 */
	function unlock(&$options) 
	{

	  webdavfunc::checkOutput("unlocking\n");

	    $conn = $_SESSION["conn"];
	    $arr = webdavfunc::getObjInfo($options[path]);

	    if ($arr["objInfo"]) {

		//only allow this if the user has manage permissions
		if (!webdavfunc::checkWebdavPerms($arr["objInfo"]["id"],OBJ_MANAGE)) {
			webdavfunc::checkOutput("Conflict unlocking file\n");
			return "409 Conflict";
		}

		$opt = null;
		$opt["status"] = "0";
		$opt["token"] = NULL;
		$opt["where"] = "id='".$arr["objInfo"]["id"]."'";
		dbUpdateQuery($conn,"dm_object",$opt);
		return "200 OK";

	    } else {
	    	return "409 Conflict";
	    }

	}

	/**
	 * checkLock() helper
	 *
	 * @param  string resource path to check for locks
	 * @return bool   true on success
	 */
	function checkLock($path) 
	{

	  webdavfunc::checkOutput("checking lock on ".$path."\n");
	  
	  $conn = $_SESSION["conn"];
	  
	  $arr = webdavfunc::getObjInfo($path);
	  $mode = $arr["objInfo"]["object_type"];
	  $objId = $arr["objInfo"]["id"];
	  $status = $arr["objInfo"]["status"];
	  $token = $arr["objInfo"]["token"];

	  webdavfunc::checkOutput(implode("-",$arr)."\n");
	  webdavfunc::checkOutput($mode."-".$objId."-".$status."-".$token."\n");

	  if ($mode!="file") return true;
	  else {
	 
	    if (!$objId) return true;

	    //get out if it's not checked out
	    if ($arr["objInfo"]["status"]=="0") return true;

	    $ai = returnAccountInfo($conn,$arr["objInfo"]["status_owner"]);

	    $res = array();
	    $res["type"] = "write";
	    $res["scope"] = "exclusive";
	    $res["owner"] = $ai["login"];
	    $res["depth"] = "0";
	    $res["token"] = $arr["objInfo"]["token"];
	    $res["expires"] = time() + 86400;	//just add a day to whatever the current time is
	  
	    webdavfunc::checkOutput("Returning as locked\n");  
	    return $res;
	    	    
	  }
	  
	}


}


?>
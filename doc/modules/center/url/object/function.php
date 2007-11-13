<?

class urlObject {

	/***********************************************************************
	  remove url-specific information
	***********************************************************************/
	function runDelete($conn,$objId) {
	
		//delete the database entry
		$sql = "DELETE FROM dm_url WHERE object_id='$objId';";
		if (!db_query($conn,$sql)) return false;
	
		//return true if we make it to here
		return true;
	
	}
	

	function runCreate($opt) {
	
	  extract($opt);

          $sql = "INSERT INTO dm_url (object_id,url) VALUES ('".$objectId."','".$url."');";
          if (db_query($conn,$sql)) return true;
          else return false;
	
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
	
	  $arr["icon"] = THEME_PATH."/images/fileicons/url.png";
	  $arr["link"] = "index.php?module=urlview&objectId=".$info["id"];
	  $arr["target"] = "_new";	//open in a new window
	
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
	
	  $arr["icon"] = THEME_PATH."/images/thumbnails/url.png";
	  $arr["link"] = "index.php?module=urlview&objectId=".$info["id"];
	  $arr["target"] = "_new";	//open in a new window
	
	  return $arr;
	
	}
	
	/***********************************************************************
	  Getting into system:
	  This function returns the contents of the object as a string
	  to be indexed by the indexObject function
	***********************************************************************/
	function runIndex($conn,$objId) {
	
		$sql = "SELECT url FROM dm_url WHERE object_id='$objId';";
		$info = single_result($conn,$sql);
		$url = $info["url"];
	
		//download the file
		$file = TMP_DIR."/".rand().".html";
		system(APP_WGET." \"".$file."\" \"".$url."\"");

		//return the contents of the file
		$str = file_get_contents($file);
		$str = removeTags($str);
		
		@unlink($file);
		
		return $str;
		
	}
   	  

}

<?


class collectionObject {

	/***********************************************************************
	  Additional processing for removing a collection
	***********************************************************************/
	function runDelete($conn,$objId) {
	
		$sql = "SELECT * FROM dm_object_parent WHERE parent_id='$objId';";
		$list = list_result($conn,$sql);
	
		//delete all objects under this parent.  we pass our own id as parent so 
		//objects with multiple parents aren't removed completely			
		for ($i=0;$i<$list["count"];$i++)
			if (!deleteObject($conn,$list[$i]["object_id"],$objId)) return false;

		//reset everyone's home directory if they were using this one
		$sql = "UPDATE auth_settings SET home_directory='0' WHERE home_directory='$objId'";
		if (!db_query($conn,$sql)) return false;
					
		//return true if we make it to here
		return true;	
	
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
	
	  $arr["icon"] = THEME_PATH."/images/fileicons/folder.png";
	  $arr["link"] = "javascript:browseCollection('".$info["id"]."');";
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
	
	  $arr["icon"] = THEME_PATH."/images/thumbnails/folder.png";
	  $arr["link"] = "javascript:browseCollection('".$info["id"]."');";
	  return $arr;
	
	}
	
	
}
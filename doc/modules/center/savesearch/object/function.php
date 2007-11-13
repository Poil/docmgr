<?

class savesearchObject {

	/**********************************************************************
		create the search
	**********************************************************************/
	function runCreate($opt) {
	
		extract($opt);
		
		$opt = null;
		$opt["date1"] = $_SESSION["date1"];
		$opt["date2"] = $_SESSION["date2"];
		$opt["search_string"] = $_SESSION["searchString"];
		$opt["show_objects"] = @implode(",",$_SESSION["showObjects"]);
		$opt["search_option"] = implode("|",$_SESSION["search_option"]);
		$opt["date_option"] = $_SESSION["date_option"];
		$opt["mod_option"] = $_SESSION["mod_option"];
		$opt["object_id"] = $objectId;
		$opt["meta_option"] = $_SESSION["metaOption"];
		$opt["col_filter"] = $_SESSION["colFilter"];
		$opt["col_filter_id"] = $_SESSION["colFilterId"];
		$opt["account_filter"] = $_SESSION["accountFilter"];
		$opt["account_filter_id"] = $_SESSION["accountFilterId"];
		$opt["search_type"] = $_SESSION["searchType"];

		if (dbInsertQuery($conn,"dm_savesearch",$opt)) return true;
		else return false;
	
	}


	/***********************************************************************
	  delete saved search specific information
	***********************************************************************/
	function runDelete($conn,$objId) {
	
		//delete the database entry
		$sql = "DELETE FROM dm_savesearch WHERE object_id='$objId';";
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
	
	  $arr["icon"] = THEME_PATH."/images/fileicons/search_folder.png";
	  $arr["link"] = "index.php?module=searchview&objectId=".$info["id"];
	
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
	
	  $arr["icon"] = THEME_PATH."/images/thumbnails/search_folder.png";
	  $arr["link"] = "index.php?module=searchview&objectId=".$info["id"];
	
	  return $arr;
	
	}
	
}

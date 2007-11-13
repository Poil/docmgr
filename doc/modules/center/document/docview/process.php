<?
/************************************************************************************************
	view.php

	Updated:
		01-26-2002:  Removed the header.inc.php include on line 15 which caused a file
		corruption when downloading text or pdf files
	Updated:
		03-27-2002:  Viewing files fixed.  The view now works without loading another 
		page as well.  

*************************************************************************************************/
$objectId = $_REQUEST["objectId"];

if ($objectId) {

	$sql = "SELECT name,version FROM dm_object WHERE id='$objectId'";
	$objInfo = single_result($conn,$sql);
	$realname=$objInfo["name"];
	$version=$objInfo["version"];

	$sql = "SELECT id FROM dm_document WHERE object_id='$objectId' AND version='$version'";
	$info = single_result($conn,$sql);
	$documentId = $info["id"];
	$file_action = "view";
	
	//we have to include path
	$current_date=date("Y-m-d H:i:s");

	// get the filename
	$filename = FILE_DIR."/document/".returnObjPath($conn,$objectId)."/".$documentId.".docmgr";

	//get the contents
	$xhtml = formatEditorStr(file_get_contents($filename));
	
	//log the view
	logEvent($conn,OBJ_VIEWED,$objectId);

	//get our basic file information
	$sql = "SELECT * FROM dm_view_objects WHERE id='$objectId';";
	$colInfo = single_result($conn,$sql);
	$objName = $colInfo["name"];
	$objParent = $colInfo["parent_id"];

}
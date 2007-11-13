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

	//log the view
	logEvent($conn,OBJ_VIEWED,$objectId);

	$sql = "SELECT url FROM dm_url WHERE object_id='$objectId'";
	$objInfo = single_result($conn,$sql);
	$url = &$objInfo["url"];

	if ($objInfo["url"]) header("Location: $url");
	

}

die;

?>

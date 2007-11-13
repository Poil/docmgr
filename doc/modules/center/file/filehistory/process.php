<?

$objectId = $_SESSION["objectId"];
if ($_SESSION["objectId"]==NULL) {
	$errorMessage = "No object is specified";
	return false;
}
                

if ($_POST["pageAction"]=="view") {

	//this variable comes from the calling module
	$realname=$fileInfo["name"];

	// get the filename
	$filename = DATA_DIR."/".returnObjPath($conn,$objectId)."/".$_POST["fileId"].".docmgr";

	$force_download = null;
	$type = null;

	//verify the md5sum for the file
	if (!fileChecksum($conn,$_POST["fileId"],$filename)) {
		$errorMessage = _INVALID_MD5SUM_WARNING;
		return false;
	}
                                                        
	//get our file type to pass to the browser
	if ($type = return_file_mime(strtolower($realname))) header ("Content-Type: $type");
	else $type="application/octet-stream";

	// send headers to browser to initiate file download
	header ("Content-Type: ".$type);
	header ("Content-Type: application/force-download");
	header ("Content-Length: ".filesize($filename));
	header ("Content-Disposition: attachment; filename=\"$realname\"");

	header ("Content-Transfer-Encoding:binary");
	header ("Cache-Control: must-revalidate, post-check=0, pre-check=0");
	header ("Pragma: public");

	readfile_chunked($filename);

	die;

}
elseif ($_POST["pageAction"]=="promote") {

	if (fileCommon::promote($conn,$objectId,$_POST["fileId"])) {

                $successMessage = _UPDATE_SUCCESS;
                indexObject($conn,$objectId,USER_ID,null);
		fileObject::thumbCreate($conn,$objectId);
		logEvent($conn,OBJ_VERSION_PROMOTE,$objectId);

	} else $errorMessage = _UPDATE_ERROR;

}	
else if ($_POST["pageAction"]=="delete") {

	if (fileCommon::removeRevision($conn,$objectId,$_POST["fileId"])) $successMessage = _OBJECT_REMOVE_SUCCESS;
	else $errorMessage = _OBJECT_REMOVE_ERROR;
}



/* get file history info */

$sql = "SELECT * FROM dm_file_history WHERE object_id='$objectId' ORDER BY version DESC";
$info = total_result($conn,$sql);


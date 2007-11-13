<?

$objectId = $_SESSION["objectId"];
if ($_SESSION["objectId"]==NULL) {
	$errorMessage = "No object is specified";
	return false;
}
                

if ($_REQUEST["pageAction"]=="view") {

	//this variable comes from the calling module
	$realname=$fileInfo["name"];

	// get the filename
	$filename = DOC_DIR."/".returnObjPath($conn,$objectId)."/".$_REQUEST["docId"].".docmgr";
	$str = formatEditorStr(file_get_contents($filename));
	echo $str;

	die;
}
elseif ($_POST["pageAction"]=="promote") {

	if (documentCommon::promote($conn,$objectId,$_POST["docId"])) {

		$successMessage = _UPDATE_SUCCESS;
		indexObject($conn,$objectId,USER_ID,null);
		logEvent($conn,OBJ_VERSION_PROMOTE,$objectId);

	} else $errorMessage = _UPDATE_ERROR;

}	
else if ($_POST["pageAction"]=="delete") {

	if (documentCommon::removeRevision($conn,$objectId,$_POST["docId"])) $successMessage = _OBJECT_REMOVE_SUCCESS;
        else $errorMessage = _OBJECT_REMOVE_ERROR;
        
}


/* get file history info */
$sql = "SELECT * FROM dm_document WHERE object_id='$objectId' ORDER BY version DESC";
$info = total_result($conn,$sql);


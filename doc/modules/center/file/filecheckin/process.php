<?
$pageAction = $_POST["pageAction"];
$objectId = $_SESSION["objectId"];

if ($_SESSION["objectId"]==NULL) {
  $errorMessage = "No object is specified";
  return false;
}

$page_name="upload";
$pageLoad="permShow();";
$form_name = "upload";

if ($_POST["pageAction"] == "upload") {
	
	if ($_FILES['userfile']['tmp_name']) {

		//figure out what version we are at and increment by one
		$sql = "SELECT version FROM dm_object WHERE id='".$objectId."';";
		$info = single_result($conn,$sql);
		$newVersion = $info["version"] + 1;

		//set all our options into the array with corresponding keys.  These will
		//be passed to the file_insert function, which handles inserting the file into the system
		$option = null;
		$option["conn"] = $conn;
		$option["name"] = smartslashes($_FILES['userfile']['name']);
		$option["filepath"] = $_FILES['userfile']['tmp_name'];
		$option["updateFile"] = 1;
		$option["objectId"] = $objectId;
		$option["version"] = $newVersion;
		$option["objectOwner"] = USER_ID;
		$option["notes"] = $_POST["notes"];
		$option["customVersion"] = $_POST["fileVersion"];

		if (fileObject::runCreate($option)) {
			$successMessage = _FILE_UPLOAD_SUCCESS;

			//index and thumb
			indexObject($conn,$objectId,USER_ID,null);
			fileObject::thumbCreate($conn,$objectId);

			//log our events
			logEvent($conn,OBJ_CHECKED_IN,$objectId);
			sendEventNotify($conn,$objectId,"OBJ_CHECKIN_ALERT");
		}
		else $errorMessage = _FILE_UPLOAD_ERROR;

                //tack on an error message from the function
		if (defined("ERROR_MESSAGE")) $errorMessage .= "<br>".ERROR_MESSAGE;
                                
	}
	else $errorMessage = _FILE_UPLOAD_SELECT_ERROR;

}


?>
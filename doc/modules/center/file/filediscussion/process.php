<?

$objectId = $_SESSION["objectId"];

if ($_SESSION["objectId"]==NULL) {
	$errorMessage = "No object is specified";
	return false;
}
                
$pageAction = $_POST["pageAction"];

$threadId = $_POST["threadId"];
$origThread = $_POST["origThread"];
$messageId = $_POST["messageId"];

if ($_POST["newPost"] || $_POST["changePost"] || $_POST["replyPost"]) {

	$option = null;
	$option["header"] = $_POST["header"];
	$option["content"] = $_POST["content"];
	$option["time_stamp"] = date("Y-m-d H:i:s");
	$option["object_id"] = $objectId;
	$option["account_id"] = USER_ID;

	if ($_POST["newPost"]) {

		if (!$_POST["header"]) $errorMessage = _NO_SUBJECT_ERROR;
		else {

			$option["owner"] = "0";

			if (dbInsertQuery($conn,"dm_discussion",$option)) $successMessage = _UPDATE_SUCCESS;
			else $errorMessage = _UPDATE_ERROR;	
		
			$pageAction = "list";

			//send a subscription alert
			if ($successMessage) sendEventNotify($conn,$objectId,"OBJ_COMMENT_POST_ALERT");

		}

	} else if ($_POST["replyPost"]) {			
		
		$option["owner"] = $threadId;

		if (dbInsertQuery($conn,"dm_discussion",$option)) $successMessage = _UPDATE_SUCCESS;
		else $errorMessage = _UPDATE_ERROR;	
		
		$pageAction = "viewThread";
		if ($origThread) $threadId = $origThread;		

		//send a subscription alert
		if ($successMessage) sendEventNotify($conn,$objectId,"OBJ_COMMENT_POST_ALERT");
                      

	} else if ($_POST["changePost"]) {

		$option["where"] = "id='$messageId'";
			
		if (dbUpdateQuery($conn,"dm_discussion",$option)) $successMessage = _UPDATE_SUCCESS;
		else $errorMessage = _UPDATE_ERROR;

		$pageAction = "viewThread";
		if ($origThread) $threadId = $origThread;
		

	}
	
}

if ($pageAction=="removePost") {

	$sql = "SELECT owner FROM dm_discussion WHERE id='$messageId';";
	$info = single_result($conn,$sql);

	$sql = "DELETE FROM dm_discussion WHERE id='$messageId';";

	//if this is a root message, delete them all
	if ($info["owner"]=="0") {

		$sql .= "DELETE FROM dm_discussion WHERE owner='$messageId';";
		$pageAction = null;

	} else {

		$pageAction = "viewThread";
		if ($origThread) $threadId = $origThread;

	}

	if (db_query($conn,$sql)) $successMessage = _DELETE_SUCCESS;
	else $errorMessage = _DELETE_ERROR;
			
}


//get the thread information
if ($threadId) {

	$sql = "SELECT * FROM dm_discussion WHERE id='$threadId'";
	$threadInfo = single_result($conn,$sql);
	
	if ($threadInfo["owner"]=="0") $origThread = $threadId;

	$sql = "SELECT id FROM dm_discussion WHERE owner='$threadId'";
	$totalResults = num_result($conn,$sql) + 1;

}


/************************ End Discussion Processing ****************************/


<?

/************************************************
  file delete module
************************************************/

$objectId = $_REQUEST["objectId"];

if ($_POST["bookmarkObject"]) {

  $sql = "INSERT INTO dm_bookmark (account_id,object_id,name) VALUES ('".USER_ID."','$objectId','".$_REQUEST["bookmarkName"]."');";
  if (db_query($conn,$sql)) $successMessage = _COLLECTION_BOOKMARK_SUCCESS;
  else $errorMessage = _COLLECTION_BOOKMARK_ERROR;

}

//get object name for later
$sql = "SELECT name FROM dm_object WHERE id='$objectId'";
$info = single_result($conn,$sql);
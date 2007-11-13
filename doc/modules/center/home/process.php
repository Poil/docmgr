<?

$objectId = $_REQUEST["objectId"];
$pageAction = $_REQUEST["pageAction"];

switch ($pageAction) {

  case "removeBookmark":
  
    $sql = "DELETE FROM dm_bookmark WHERE object_id='$objectId' AND account_id='".USER_ID."';";
    if (db_query($conn,$sql)) $successMessage = _OBJECT_REMOVE_SUCCESS;
    else $errorMessage = _OBJECT_REMOVE_ERROR;  
  
    break;

  case "clearAlert":
  
    $sql = "DELETE FROM dm_alert WHERE id='$objectId';";    
    if (db_query($conn,$sql)) $successMessage = _OBJECT_REMOVE_SUCCESS;
    else $errorMessage = _OBJECT_REMOVE_ERROR;  
    break;

  case "clearAllAlerts":
  
    $sql = "DELETE FROM dm_alert WHERE account_id='".USER_ID."'";
    if (db_query($conn,$sql)) $successMessage = _OBJECT_REMOVE_SUCCESS;
    else $errorMessage = _OBJECT_REMOVE_ERROR;  
    break;  
    
}
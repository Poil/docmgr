<?

$objectId = $_REQUEST["objectId"];
$pageAction = $_REQUEST["pageAction"];

//update the settings
if ($pageAction=="update") {

  //delete any current settings
  $sql = "DELETE FROM dm_subscribe WHERE account_id='".USER_ID."' AND object_id='$objectId';";
  
  //pull our settings from our checkbox
  for ($i=0;$i<count($_POST["type"]);$i++) {

    $opt = null;
    $opt["object_id"] = $objectId;
    $opt["account_id"] = USER_ID;
    $opt["send_email"] = $_POST["emailNotify"];
    $opt["event_type"] = $_POST["type"][$i];
    $opt["send_file"] = $_POST["sendFile"];
    $opt["query"] = 1;
    $sql .= dbInsertQuery($conn,"dm_subscribe",$opt);
                                    
  }
  
  //run our query
  if (db_query($conn,$sql)) $successMessage = _SETTING_UPDATE_SUCCESS;
  else $errorMessage = _SETTING_UPDATE_ERROR;

}

//get the file's settings
$sql = "SELECT * FROM dm_object WHERE id='$objectId';";
$fileInfo = single_result($conn,$sql);

//get the current user's settings
$sql = "SELECT * FROM dm_subscribe WHERE account_id='".USER_ID."' AND object_id='$objectId';";
$subInfo = total_result($conn,$sql);

$hideHeader = 1;

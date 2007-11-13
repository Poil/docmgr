<?
$entryId = $_REQUEST["entryId"];
$routeId = $_REQUEST["routeId"];
$templateId = $_REQUEST["templateId"];
$pageAction = $_REQUEST["pageAction"];

if ($pageAction=="update") {

    $arr = array();

    for ($row=0;$row<count($_REQUEST["accountId"]);$row++) {

      $option = null;
      $option["account_id"] = $_REQUEST["accountId"][$row];
      $option["task_type"] = $_REQUEST["taskType"];
      $option["task_notes"] = $_REQUEST["taskNotes"];
      $option["date_due"] = dateProcess($_REQUEST["dateDue"]);
      $option["status"] = "nodist";
      $option["workflow_id"] = $routeId;
      $option["sort_order"] = $_REQUEST["stage"];

      $sql = "SELECT workflow_id FROM dm_workflow_route WHERE workflow_id='$routeId' AND account_id='".$_REQUEST["accountId"][$row]."'
            AND sort_order='".$_REQUEST["stage"]."';";
      $num = num_result($conn,$sql);

      if ($num > 0) {
    
        $errorMessage = _ENTRY_STAGE_ALREADY_EXISTS." ".$_REQUEST["stage"];
        break;
      
      } else $arr[] = dbInsertQuery($conn,"dm_workflow_route",$option);

    }

    if (count($arr) > 0) { 	
      //get info for what we just entered to display later
      $sql = "SELECT * FROM dm_workflow_route WHERE id IN (".implode(",",$arr).")";
      $eList = list_result($conn,$sql);
    }


}
else if ($pageAction=="updateEntry") {

    $option = null;
    $option["task_type"] = $_REQUEST["taskType"];
    $option["task_notes"] = $_REQUEST["taskNotes"];
    $option["date_due"] = dateProcess($_REQUEST["dateDue"]);
    $option["where"] = "id='$entryId'";
    dbUpdateQuery($conn,"dm_workflow_route",$option);

    //get info for what we just entered to display later
    $sql = "SELECT * FROM dm_workflow_route WHERE workflow_id='$routeId'";
    $eList = list_result($conn,$sql);

}
else if ($pageAction=="delete") {

  //get the data for later
  $sql = "SELECT * FROM dm_workflow_route WHERE id='$entryId'";
  $eList = list_result($conn,$sql);

  $sql = "DELETE FROM dm_workflow_route WHERE id='$entryId'";
  db_query($conn,$sql);

  $entryId = null;

}
//modify email notification for a workflow owner
else if ($pageAction=="modnotify") {
  $sql = "UPDATE dm_workflow SET email_notify='".$_REQUEST["emailNotify"]."' WHERE id='$routeId'";
  db_query($conn,$sql);
}
else if ($pageAction=="loadtemplate") {

  //convert our template to a route
  templateToRoute($conn,$routeId,$templateId);

  //get our list to display
  $sql = "SELECT * FROM dm_workflow_route WHERE workflow_id='$routeId' ORDER BY sort_order";
  $eList = list_result($conn,$sql);

}
else if ($pageAction=="removetemplate") {

  //delete the template
  $sql = "DELETE FROM dm_saveroute WHERE id='$templateId';";
  $sql .= "DELETE FROM dm_saveroute_data WHERE save_id='$templateId';";
  db_query($conn,$sql);

}
else if ($pageAction=="view" || $pageAction=="editrecip") {

  if ($entryId) {
    $sql = "SELECT * FROM dm_workflow_route WHERE id='$entryId'";
    $eList = list_result($conn,$sql);
  } else if ($routeId) {
    $sql = "SELECT * FROM dm_workflow_route WHERE workflow_id='$routeId' ORDER BY sort_order";
    $eList = list_result($conn,$sql);
  }
}


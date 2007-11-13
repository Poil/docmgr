<?

//transfer all the current routes into a template
function transferWorkflowTemplate($conn,$templateId,$routeId) {

  $sql = "SELECT * FROM dm_workflow_route WHERE workflow_id='$routeId'";
  $list = list_result($conn,$sql);
  
  //delete any current template info
  $sql = "DELETE FROM dm_saveroute_data WHERE save_id='$templateId';";

  //get the time in seconds
  $today = time();
  
  for ($i=0;$i<$list["count"];$i++) {

    //get the date in seconds
    $sec = strtotime($list[$i]["date_due"]);
    
    $diff = $sec - $today;
    $days = intValue($diff/86400);
    if (!$days) $days = "0";
  
    $opt = null;
    $opt["save_id"] = $templateId;
    $opt["account_id"] = $list[$i]["account_id"];
    $opt["task_type"] = $list[$i]["task_type"];
    $opt["task_notes"] = addslashes($list[$i]["task_notes"]);
    $opt["sort_order"] = $list[$i]["sort_order"];
    $opt["date_due"] = $days;
    $opt["query"] = 1;
    $sql .= dbInsertQuery($conn,"dm_saveroute_data",$opt)."\n";
  
  }

  db_query($conn,$sql);

}
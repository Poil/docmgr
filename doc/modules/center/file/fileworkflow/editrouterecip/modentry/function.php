<?

function templateToRoute($conn,$routeId,$templateId) {

  //get our saved info
  $sql = "SELECT * FROM dm_saveroute_data WHERE save_id='$templateId'";
  $tList = list_result($conn,$sql);

  //first, clean out anything in this route
  $sql = "DELETE FROM dm_workflow_route WHERE workflow_id='$routeId';";
  
  //now, create new entries based on our template data
  for ($i=0;$i<$tList["count"];$i++) {

    //come up with a date due based on the difference
    $sec = $tList[$i]["date_due"] * 3600 * 24;
    $newtime = time() + $sec;
    $dateDue = date("Y-m-d",$newtime);
  
    $opt = null;
    $opt["account_id"] = $tList[$i]["account_id"];
    $opt["task_type"] = $tList[$i]["task_type"];
    $opt["task_notes"] = addslashes($tList[$i]["task_notes"]);
    $opt["date_due"] = $dateDue;
    $opt["sort_order"] = $tList[$i]["sort_order"];
    $opt["workflow_id"] = $routeId;
    $opt["status"] = "nodist";
    $opt["query"] = 1;
    $sql .= dbInsertQuery($conn,"dm_workflow_route",$opt);
  
  }

  db_query($conn,$sql);
  
}
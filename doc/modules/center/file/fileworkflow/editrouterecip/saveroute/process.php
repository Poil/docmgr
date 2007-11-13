<?

$routeId = $_REQUEST["routeId"];
$saveId = $_REQUEST["saveId"];

if ($_REQUEST["pageAction"]=="save") {

  //if there is a saveId, we have to delete it first
  $sql = "DELETE FROM dm_saveroute_data WHERE save_id='$saveId'";
  db_query($conn,$sql);

  //just convert all our routes to the template
  $sql = "SELECT * FROM dm_workflow_route WHERE workflow_id='$routeId'";
  $list = list_result($conn,$sql);
  
  for ($i=0;$i<$list["count"];$i++) {

    $datediff = "15";
  
    $opt = null;
    $opt["account_id"] = $list[$i]["account_id"];
    $opt["task_type"] = $list[$i]["task_type"];
    $opt["task_notes"] = addslashes($list[$i]["task_notes"]);
    $opt["date_due"] = $datediff;
    $opt["sort_order"] = $list[$i]["sort_order"];
    $opt["save_id"] = $saveId;
    dbInsertQuery($conn,"dm_saveroute_data",$opt);
      
  }  
  
}


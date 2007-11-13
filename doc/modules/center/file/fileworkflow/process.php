<?

include("app/workflow.inc.php");

$pageAction = $_POST["pageAction"];
$objectId = $_SESSION["objectId"];
$routeId = $_REQUEST["routeId"];

if ($_SESSION["objectId"]==NULL) {
  $errorMessage = "No object is specified";
  return false;
}
                
//create a new route for this object
if ($pageAction=="createRoute") {

  $opt = null;
  $opt["object_id"] = $objectId;
  $opt["status"] = "nodist";
  $opt["account_id"] = USER_ID;
  $opt["date_create"] = date("Y-m-d H:i:s");

  $routeId = dbInsertQuery($conn,"dm_workflow",$opt);

//begin routing distribution by push alerts out to first stage of users  
} else if ($pageAction=="beginDist") {

  //issue our alerts for the first stage
  beginWorkflowDist($conn,$routeId,"1");
  logEvent($conn,OBJ_WORKFLOW_BEGIN,$objectId,null, USER_ID);

} else if ($pageAction=="clearRoute") {

  $sql = "SELECT id FROM dm_workflow_route WHERE workflow_id='$routeId';";
  $list = total_result($conn,$sql);
  
  //delete the tasks
  $sql = "DELETE FROM dm_task WHERE task_id IN (".implode(",",$list["id"]).");";
  $sql .= "UPDATE dm_workflow_route SET status='forcecomplete' WHERE workflow_id='$routeId' AND status!='complete';";
  $sql .= "UPDATE dm_workflow SET status='forcecomplete',date_complete='".date("Y-m-d H:i:s")."' WHERE id='$routeId';";
  
  if (db_query($conn,$sql)) {
    $routeId = null;
    logEvent($conn,OBJ_WORKFLOW_CLEAR,$objectId,null, USER_ID);
  }
}



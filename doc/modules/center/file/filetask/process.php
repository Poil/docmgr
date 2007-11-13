<?

include("app/workflow.inc.php");

$pageAction = $_POST["pageAction"];
$objectId = $_SESSION["objectId"];
$taskId = $_POST["taskId"];

if ($_SESSION["objectId"]==NULL) {
  $errorMessage = "No object is specified";
  return false;
}

//user accepted the approval. process it
if ($pageAction=="taskComplete") {

  //figure out what kind of event this is so we can log an entry
  $sql = "SELECT alert_type FROM dm_task WHERE task_id='$taskId'";
  $tinfo = single_result($conn,$sql);
  $at = $tinfo["alert_type"];

  //update this route and delete the task from the alert
  $sql = "UPDATE dm_workflow_route SET status='complete',comment='".$_POST["fileComment"]."' WHERE id='$taskId';";
  $sql .= "DELETE FROM dm_task WHERE task_id='$taskId';";
  if (db_query($conn,$sql)) {

    //figure out how many other tasks are left at this level
    $sql = "SELECT workflow_id,sort_order FROM dm_workflow_route WHERE id='$taskId';";
    $info = single_result($conn,$sql);
    
    $sql = "SELECT * FROM dm_workflow_route WHERE workflow_id='".$info["workflow_id"]."' AND sort_order='".$info["sort_order"]."' AND status!='complete'";
    $list = list_result($conn,$sql);
    
    //what's our log type
    if ($at=="OBJ_APPROVAL_ALERT") $type = OBJ_WORKFLOW_APPROVE;
    elseif ($at=="OBJ_VIEW_ALERT") $type = OBJ_WORKFLOW_VIEW;
    elseif ($at=="OBJ_EDIT_ALERT") $type = OBJ_EDIT_ALERT;

    //log the event
    logEvent($conn,$type,$objectId,$_POST["fileComment"],USER_ID);

    //if there are some left at this level, do nothing.  If there are not, queue the approvers at the next stage
    if ($list["count"]=="0") {

      $nextOrder = $info["sort_order"] + 1;

      //queue the tasks for the next stage.  If this returns false, there are no objects left
      beginWorkflowDist($conn,$info["workflow_id"],$nextOrder);
      
    }

  }  

}
//user accepted the approval. process it
else if ($pageAction=="rejectApproval") {

    //figure out how many other tasks are left at this left
    $sql = "SELECT workflow_id,sort_order FROM dm_workflow_route WHERE id='$taskId';";
    $info = single_result($conn,$sql);

    //update this route and delete the task from the alert
    $sql = "UPDATE dm_workflow_route SET status='rejected',comment='".$_POST["fileComment"]."' WHERE id='$taskId';";
    $sql .= "UPDATE dm_workflow SET status='rejected',date_complete='".date("Y-m-d H:i:s")."' WHERE id='".$info["workflow_id"]."';";
    $sql .= "DELETE FROM dm_task WHERE task_id='$taskId';";
    if (db_query($conn,$sql)) {

      //figure out how many other tasks are left at this left
      $sql = "SELECT workflow_id,sort_order FROM dm_workflow_route WHERE id='$taskId';";
      $info = single_result($conn,$sql);
    
      $sql = "SELECT * FROM dm_workflow_route WHERE workflow_id='".$info["workflow_id"]."' AND sort_order='".$info["sort_order"]."' AND status!='complete'";
      $list = total_result($conn,$sql);
    
      //we've rejected the message.  delete any user alerts
      $sql = "DELETE FROM dm_task WHERE task_id IN (".implode(",",$list["id"]).");";
      db_query($conn,$sql);        

      //log the event
      logEvent($conn,OBJ_WORKFLOW_REJECT,$objectId,$_POST["fileComment"],USER_ID);
    
  }  

}


//get the current task for this user
$sql = "SELECT * FROM dm_task_view WHERE object_id='$objectId' AND account_id='".USER_ID."' LIMIT 1";
$taskInfo = single_result($conn,$sql);


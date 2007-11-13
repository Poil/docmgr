<?

//issue our alerts for a specific stage of a route
function beginWorkflowDist($conn,$routeId,$stage) {

  //get our list of routes assigned to this object
  $sql = "SELECT * FROM dm_workflow_route WHERE workflow_id='$routeId';";
  $list = total_result($conn,$sql);

  //stop here if there are no tasks assigned yet  
  if ($list["count"]=="0" || !$list) return false;

  //get the list of accounts for this stage
  $keys = array_keys($list["sort_order"],$stage);

  //if there are no accounts at this stage, the job is complete.  update our status and exit
  if (count($keys)=="0") {

    //see if we are supposed to notify the user this is complete
    sendWorkflowNotify($conn,$routeId);

    $sql = "UPDATE dm_workflow SET status='complete',date_complete='".date("Y-m-d H:i:s")."' WHERE id='$routeId';";
    if (db_query($conn,$sql)) {

      //log that it's completed
      $sql = "SELECT object_id FROM dm_workflow WHERE id='$routeId'";
      $info = single_result($conn,$sql);
      logEvent($conn,"OBJ_WORKFLOW_END",$info["object_id"],null,USER_ID);
      return true;
    }
    else return false;

  }

  //pull all accounts at this stage and their job types
  $accounts = arrayCombine($keys,$list["account_id"]);
  $ids = arrayCombine($keys,$list["id"]);
  $dates = arrayCombine($keys,$list["date_due"]);
  $types = arrayCombine($keys,$list["task_type"]);

  //get our object id
  $sql = "SELECT object_id FROM dm_workflow WHERE id='$routeId';";
  $info = single_result($conn,$sql);
  $objectId = $info["object_id"];

  //make sure our primary status is still set to pending since we are not done
  $sql = "Update dm_workflow SET status='pending' WHERE id='$routeId';";

  for ($i=0;$i<count($accounts);$i++) {

    if ($types[$i]=="view") $alertType = "OBJ_VIEW_ALERT";
    elseif ($types[$i]=="edit") $alertType = "OBJ_EDIT_ALERT";
    elseif ($types[$i]=="approve") $alertType = "OBJ_APPROVAL_ALERT";
    elseif ($types[$i]=="comment") $alertType = "OBJ_COMMENT_ALERT";

    $sql .= "INSERT INTO dm_task (account_id,task_id,alert_type,date_due) 
                                VALUES
                                ('".$accounts[$i]."','".$ids[$i]."','".$alertType."','".$dates[$i]."');";

    $sql .= "UPDATE dm_workflow_route SET status='pending' WHERE id='".$ids[$i]."';";
    
  }

  //run the query
  if (db_query($conn,$sql)) {

    //send email alerts if configured
    if (defined("EMAIL_SUPPORT")) {
    
      //send a task notify alert
      for ($i=0;$i<count($accounts);$i++) sendTaskNotify($conn,$objectId,$accounts[$i]);
  
    }
    
    return true;

  }
  else return false;
  
}


function sendWorkflowNotify($conn,$routeId) {

    //see if we are supposed to notify the user this is complete
    $sql = "SELECT object_id,account_id,email_notify FROM dm_workflow WHERE id='$routeId'";
    $info = single_result($conn,$sql);
    
    if ($info["email_notify"]=="t") {
    
      //get the user's email and send them a notification
      $ainfo = returnAccountInfo($conn,$info["account_id"],null);
      if ($ainfo["email"]) {

        //get the object's name
        $sql = "SELECT name FROM dm_object WHERE id='".$info["object_id"]."'";
        $objInfo = single_result($conn,$sql);
      
        $sub = _WORKFLOW_COMPLETE;
        $msg = $objInfo["name"].": "._WORKFLOW_COMPLETE_MSG;
      
        send_email($ainfo["email"],ADMIN_EMAIL,$sub,$msg,null);
      
      }
    
    }

}

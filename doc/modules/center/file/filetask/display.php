<?

if (!$taskInfo) $pageContent .= "<div class=\"errorMessage\">"._NO_PENDING_TASKS_ASSIGNED."</div>\n";
else {

  $alertText = returnAlertType($alertArr,$taskInfo["alert_type"]);

  //display the instructions for this task
  if ($taskInfo["alert_type"]=="OBJ_APPROVAL_ALERT") $inst = returnApprovalText();
  elseif ($taskInfo["alert_type"]=="OBJ_VIEW_ALERT") $inst = returnViewText();
  elseif ($taskInfo["alert_type"]=="OBJ_EDIT_ALERT") $inst = returnEditText();
  //elseif ($taskInfo["alert_type"]=="OBJ_COMME _ALERT") $inst = returnCommentText();

  $ts = strtotime($taskInfo["date_due"]);
  if ($ts < time()) $class = "class=\"errorMessage\"";
  else $class = null;

  $pageContent .= "

  <form method=post name=\"pageForm\">
  <input type=hidden name=\"pageAction\" id=\"pageAction\" value=\"upload\">
  <input type=hidden name=\"objectId\" id=\"objectId\" value=\"".$_REQUEST["objectId"]."\">
  <input type=hidden name=\"taskId\" id=\"taskId\" value=\"".$taskInfo["task_id"]."\">
  <input type=hidden name=\"alertType\" id=\"alertType\" value=\"".$taskInfo["alert_type"]."\">
  <input type=hidden name=\"includeModule\" id=\"includeModule\" value=\"filetask\">

  <div class=\"formHeader\">
  "._YOUR_PENDING_TASK."
  </div>
  ".$alertText."
  <br><br>
  <div class=\"formHeader\">
  "._TASK_NOTES."
  </div>
  ".$taskInfo["task_notes"]."
  <br><br>
  <div class=\"formHeader\">
  "._DUE_DATE."
  </div>
  <span ".$class.">
  ".date_view($taskInfo["date_due"])."
  </span>
  ".$inst."
  </form>
  ";

}


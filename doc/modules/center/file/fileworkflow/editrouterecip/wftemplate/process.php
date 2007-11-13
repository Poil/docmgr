<?

$pageAction = $_REQUEST["pageAction"];
$routeId = $_REQUEST["routeId"];
$templateId = $_REQUEST["templateId"];
$errorMessage = null;

if (!$pageAction) $pageAction = "templatelist";

if ($pageAction=="save") {

  //update if passed the id  
  if ($templateId) {
  
    //make sure this user has permission to update this
    $sql = "SELECT name,account_id FROM dm_saveroute WHERE id='$templateId'";
    $info = single_result($conn,$sql);
    
    if ($info["account_id"]!=USER_ID) $errorMessage = _TEMPLATE_EDIT_PERM_ERROR;
      
  } else {

    $opt = null;
    $opt["account_id"] = USER_ID;
    $opt["name"] = $_REQUEST["saveName"];
    $templateId = dbInsertQuery($conn,"dm_saveroute",$opt);

  }  

  //if we haven't gotten an error to here, transfer the workflow over to our template
  if (!$errorMessage) transferWorkflowTemplate($conn,$templateId,$routeId);

}

if ($pageAction=="templatelist" || $pageAction=="loadsavelist") {

  //get all saved templates for this user
  $sql = "SELECT * FROM dm_saveroute WHERE account_id='".USER_ID."'";
  $tempList = list_result($conn,$sql);
  
}

<?

function showTasks($conn) {

  $sql = "SELECT * FROM dm_task_view WHERE account_id='".USER_ID."';";
  $list = list_result($conn,$sql);

  if ($list["count"]=="0") 
    return "<div class=\"errorMessage\" style=\"padding-left:5px;padding-top:5px\">"._NO_FILES_DISPLAY."</div>";

  $alertArr = returnAlertList();

  $string = "<ul class=\"bulletedList\">\n";
  
  foreach ($list AS $task)  {

    if (!$task["name"]) continue;

    $alertMsg = returnAlertType($alertArr,$task["alert_type"]);;

    //highlight the date if it's past due
    $ts1 = strtotime($task["date_due"]);

    if ($ts1 <= time()) $class = "class=\"errorMessage\"";
    else $class = null;

    $date = date_view($task["date_due"]);
    
    $string .= "<li style=\"padding-bottom:3px\">";
    $string .= "<a href=\"index.php?module=file&includeModule=filetask&objectId=".$task["object_id"]."\">";
    $string .= $task["name"];
    $string .= "</a><br>\n ";
    $string .= "&nbsp;&nbsp;"._TASK.": ".$alertMsg."<br>\n";
    $string .= "&nbsp;&nbsp;"._DUE.": <span ".$class.">".$date."</span><br>\n";
    $string .= "</li>\n";

  }
  
  $string .= "</ul>\n";
  
  return $string;

}

//show the last ten files to be added to the system
function showRecent($conn) {

  if (bitset_compare(BITSET,ADMIN,null))
    $sql = "SELECT id,name,object_type FROM dm_object WHERE object_type!='collection' ORDER BY id DESC limit 10";
  else
    $sql = "SELECT DISTINCT id,name,object_type FROM dm_view_perm 
                    WHERE object_type!='collection' AND (".permString($conn).") ORDER BY id DESC LIMIT 10";
  $list = list_result($conn,$sql);

  if ($list["count"]=="0") 
    return "<div class=\"errorMessage\" style=\"padding-left:5px;padding-top:5px\">"._NO_FILES_DISPLAY."</div>";

  $string = "<table cellpadding=0 border=0 cellspacing=0 style=\"padding-left:5px\">";
  
  foreach ($list AS $file)  {
    if ($file["name"]) {

      $module = $file["object_type"];

        $extension = return_file_type($file["name"]);
        if (file_exists(THEME_PATH."/images/fileicons/".$extension.".png"))
          $icon = THEME_PATH."/images/fileicons/".$extension.".png";
        else
          $icon = THEME_PATH."/images/fileicons/file.png";
      
      $string .= "<tr><td width=10>";
      $string .= "<img src=\"".$icon."\" border=0>";
      $string .= "</td><td width=100% style=\"padding-left:3px;padding-bottom:2px\">\n";
      $string .= "<a href=\"index.php?module=".$module."&objectId=".$file["id"]."\">";
      $string .= $file["name"];
      $string .= "</a>";
      $string .= "</td></tr>\n";
    }
  }
  
  $string .= "</table>\n";
  
  return $string;
  
}

//show collections the user has marked as favorites
function showBookmark($conn) {

  $sql = "SELECT dm_bookmark.*,(SELECT object_type FROM dm_object WHERE id=object_id) AS object_type
          FROM dm_bookmark WHERE account_id='".USER_ID."' ORDER BY upper(name)";
  $list = list_result($conn,$sql);
  
  if ($list["count"]=="0") 
    return "<div class=\"errorMessage\" style=\"padding-left:5px;padding-top:5px\">"._NO_BOOKMARKS_DISPLAY."</div>";

  $string = "<ul class=\"bulletedList\">\n";
  
  foreach ($list AS $file)  {

    if ($file["name"]) {

      if ($file["object_type"]=="savesearch") {
        $icon = THEME_PATH."/images/fileicons/search_folder.png";
        $module = "searchview";
        $ref = "objectId";
      } else {
        $icon = THEME_PATH."/images/fileicons/folder.png";
        $module = "browse";
        $ref = "view_parent";
      }

      $string .= "<li>";
      $string .= "<img src=\"".$icon."\" border=0>&nbsp;";
      $string .= "<a href=\"index.php?module=".$module."&".$ref."=".$file["object_id"]."\">";
      $string .= $file["name"];
      $string .= "</a>";
      $string .= "&nbsp;";
      $string .= "<a href=\"javascript:removeBookmark('".$file["object_id"]."');\">["._REMOVE."]</a>";
      $string .= "</li>\n";
    }
  }
  
  $string .= "</ul>\n";
  
  return $string;


}

function showSubscription($conn) {

  $sql = "SELECT * FROM dm_view_alert WHERE account_id='".USER_ID."' ORDER BY status_date DESC;";
  $list = list_result($conn,$sql);

  if ($list["count"]=="0") 
    return "<div class=\"errorMessage\" style=\"padding-left:5px;padding-top:5px\">"._NO_ALERTS_DISPLAY."</div>";

  $alertArr = returnAlertList();

  $string = "<div style=\"float:right\">
              <a href=\"javascript:clearAllAlerts()\">["._CLEAR_ALL."]</a>
              </div>
              <ul class=\"bulletedList\">\n";
  
  foreach ($list AS $alert)  {

    if (!$alert["name"]) continue;

    $alertMsg = returnAlertType($alertArr,$alert["alert_type"]);
    $mod = $alert["object_type"];

    //the file properties module doesn't follow the naming scheme of the other objects
    if ($mod=="file") $modprop = "fileproperties";
    else if ($mod=="collection") $modprop = "colprop";
    else if ($mod=="document") $modprop = "docprop";
    else $modprop = $mod."prop";

    $string .= "<li style=\"list-style-type:none\">";
    $string .= "<input type=checkbox onClick=\"clearAlert('".$alert["alert_id"]."');\">&nbsp;";
    $string .= "<a href=\"index.php?module=".$mod."&includeModule=".$modprop."&objectId=".$alert["object_id"]."\">";
    $string .= $alert["name"].":&nbsp;&nbsp;";
    $string .= "</a>";
    $string .= $alertMsg;
    $string .= "</li>\n";

  }
  
  $string .= "</ul>\n";
  
  return $string;


}


function showCheckOut($conn) {

  $sql = "SELECT id,name FROM dm_object WHERE status='1' AND status_owner='".USER_ID."' ORDER BY upper(name)";
  $list = list_result($conn,$sql);
  
  if ($list["count"]=="0") 
    return "<div class=\"errorMessage\" style=\"padding-left:5px;padding-top:5px\">"._NO_FILES_DISPLAY."</div>";

  $string = "<table cellpadding=0 cellspacing=0 style=\"padding-left:10px;padding-top:5px;\">\n";
  
  foreach ($list AS $file)  {
    if ($file["name"]) {

        $module = "fileview";
        $action = "objectId";

        $extension = return_file_extension($file["name"]);
        if (file_exists(THEME_PATH."/images/fileicons/".$extension.".png"))
          $icon = THEME_PATH."/images/fileicons/".$extension.".png";
        else
          $icon = THEME_PATH."/images/fileicons/file.png";

      $string .= "<tr><td width=10>\n";
      $string .= "<img src=\"".$icon."\" border=0>";
      $string .= "</td><td width=100% style=\"padding-left:3px;padding-bottom:2px\">\n";
      $string .= "<a href=\"index.php?module=file&includeModule=filecheckin&objectId=".$file["id"]."\">";
      $string .= $file["name"];
      $string .= "</a>";
      $string .= "</td></tr>\n";
    }
  }
  
  $string .= "</table>\n";
  
  return $string;


}

//displays all active files that I routed to someone
//contributed by Martijn on 09/28/2006
function showRoutes($conn) { 

  $sql = "SELECT *,dm_workflow.status AS workflow_status FROM dm_workflow 
          LEFT JOIN dm_object ON dm_workflow.object_id = dm_object.id WHERE 
          ((account_id='".USER_ID."') AND 
          ((dm_workflow.status != 'complete') AND (dm_workflow.status != 'forcecomplete')));";  
  $list = list_result($conn,$sql);

  if ($list["count"]=="0")
    return "<div class=\"errorMessage\" style=\"padding-left:5px;padding-top:5px\">"._NO_FILES_DISPLAY."</div>";

  $alertArr = returnAlertList();
  $string = "<ul class=\"bulletedList\">\n";

  foreach ($list AS $file){
    if (!$file["object_id"]) continue;
    $alertMsg = returnAlertType($alertArr,$file["absolute_due"]);;
    //highlight the date if it's past due
    $class = null;
    if ($file["absolute_due"]) {
      $ts1 = strtotime($file["absolute_due"]);
      $route = $file["workflow_status"];
      if ($ts1 <= time()) $class = "class=\"errorMessage\"";
    }
    $date = date_view($file["date_create"]);

    $string .= "<li style=\"padding-bottom:3px\">";
    $string .= "<a href=\"index.php?module=file&includeModule=fileworkflow&objectId=".$file["object_id"]."\">";
    $string .= $file["name"];
    $string .= "</a><br>\n ";
    $string .= "&nbsp;&nbsp;"._STATUS.": ".$route."<br>\n";
    $string .= "&nbsp;&nbsp;"._DUE.": <span ".$class.">".$date."</span><br>\n";
    $string .= "</li>\n";
  }
  $string .= "</ul>\n";
  
  return $string;

}
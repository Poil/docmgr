<?

//generate our alert list
$alerts = returnAlertList();

$ad = null;

for ($row=0;$row<count($alerts["name"]);$row++) {
  
  //skip if the alert isn't related to a subscription
  if ($alerts["type"][$row]!="subscription") continue;

  //text for the checkbox
  $alertArr = returnAlertList();
  $text = returnAlertType($alertArr,$alerts["link_name"][$row]);
  
  //see if this has already been selected by the user
  if (is_array($subInfo["event_type"]) && in_array($alerts["link_name"][$row],$subInfo["event_type"])) $checked = " CHECKED ";
  else $checked = null;
  
  //output the form
  $ad .= "<li>";
  $ad .= "<input type=checkbox ".$checked." name=\"type[]\" id=\"type[]\" value=\"".$alerts["link_name"][$row]."\">\n";
  $ad .= $text;
  $ad .= "</li>\n";

}


$content = "
          <form name=\"pageForm\" method=post>
          <input type=hidden name=pageAction id=pageAction value=\"update\">
          <input type=hidden name=\"objectId\" id=\"objectId\" value=\"".$objectId."\">
          <br>
          <div class=\"formHeader\">
          "._NOTIFY_ME_EVENT."
          </div>
          <ul style=\"list-style-type:none;padding-left:10px;margin-top:3px\">
          ".$ad."
          </ul>
          ";

//allow email notification if supported          
if (defined("EMAIL_SUPPORT")) {

  //get the current user's email address
  $info = returnAccountInfo($conn,USER_ID,null);

  //see if the user wants email notification
  if ($subInfo["send_email"][0]=="t") {
    $sendFileStyle = "style=\"visibility:visible;position:static\"";
    if ($subInfo["send_file"][0]=="t") $yesFileCheck = " CHECKED ";
    else $noFileCheck = " CHECKED ";
    $yesCheck = " CHECKED ";
  } else {
    $sendFileStyle = "style=\"visibility:hidden;position:absolute\"";
    $noFileCheck = " CHECKED ";
    $noCheck = " CHECKED ";
  }  

  if (!$info["email"])
    $str = "<div class=\"errorMessage\">"._EMAIL_PROFILE_ERROR."</div>";
  else 
    $str = "<input type=radio name=\"emailNotify\" onClick=\"showObject('sendFileEmail');\" id=\"emailNotify\" value=\"t\" ".$yesCheck."> "._YES."
            &nbsp;&nbsp;&nbsp;&nbsp;
            <input type=radio name=\"emailNotify\" onClick=\"hideObject('sendFileEmail');\" id=\"emailNotify\" value=\"f\" ".$noCheck."> "._NO."
            <br><br>
            <div id=\"sendFileEmail\" ".$sendFileStyle.">
            <div class=\"formHeader\">
            "._SEND_FILE_ON_UPDATE."
            </div>
            <input type=radio name=\"sendFile\" id=\"sendFile\" value=\"t\" ".$yesFileCheck."> "._YES."
            &nbsp;&nbsp;&nbsp;&nbsp;
            <input type=radio name=\"sendFile\" id=\"sendFile\" value=\"f\" ".$noFileCheck."> "._NO."
            </div>
            ";

  $content .= "
          <br>
          <div class=\"formHeader\">
          "._SEND_EMAIL_NOTIFY."
          </div>
          ".$str."
          ";

} else {
  $content .= "<input type=hidden name=\"emailNotify\" id=\"emailNotify\" value=\"f\">\n";
}

$content .= "<br><br>
          <input type=submit value=\""._UPDATE_SETTINGS."\">
          </form>
          ";
          
$option = null;
$option["leftHeader"] = str_replace(" ","&nbsp;","\"".$fileInfo["name"]."\" "._SUBSCRIPTION." ");
$option["content"] = $content;
$siteContent = sectionDisplay($option);


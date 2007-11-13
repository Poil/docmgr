<?

//generate our alert list
$alertArr = returnAlertList();

$ad = null;

for ($row=0;$row<count($alertArr["name"]);$row++) {
  
  //skip if the alert isn't related to a subscription
  if ($alertArr["type"][$row]!="subscription" && $alertArr["type"][$row]!="colsubscription") continue;

  $text = returnAlertType($alertArr,$alertArr["link_name"][$row]);
  
  //see if this has already been selected by the user
  if (is_array($subInfo["event_type"]) && in_array($alertArr["link_name"][$row],$subInfo["event_type"])) $checked = " CHECKED ";
  else $checked = null;
  
  //output the form
  $ad .= "<li>";
  $ad .= "<input type=checkbox ".$checked." name=\"type[]\" id=\"type[]\" value=\"".$alertArr["link_name"][$row]."\">\n";
  $ad .= $text;
  $ad .= "</li>\n";

}


$content = "
          <form name=\"pageForm\" method=post>
          <input type=hidden name=pageAction id=pageAction value=\"update\">
          <input type=hidden name=objectId id=objectId value=\"".$objectId."\">
          <br>
          <div class=\"formHeader\">
          "._NOTIFY_ME_COLEVENT."
          </div>
          <ul style=\"list-style-type:none;padding-left:10px;margin-top:3px\">
          ".$ad."
          </ul>
          ";

//allow email notification if supported          
if (defined("EMAIL_SUPPORT")) {

  //see if the user wants email notification
  if ($subInfo["send_email"][0]=="t") {
    $sendFileStyle = "style=\"visibility:visible;position:static\"";
    $yesCheck = " CHECKED ";
    if ($subInfo["send_file"][0]=="t") $yesFileCheck = " CHECKED ";
    else $noFileCheck = " CHECKED ";
  } else {
    $sendFileStyle = "style=\"visibility:hidden;position:absolute\"";
    $noCheck = " CHECKED ";
    $noFileCheck = " CHECKED ";
  }
                    
  //get the current user's email address
  $info = returnAccountInfo($conn,USER_ID,null);

  if (!$info["email"])
    $str = "<div class=\"errorMessage\">"._EMAIL_PROFILE_ERROR."</div>";
  else 
    $str = "<input type=radio name=\"emailNotify\" id=\"emailNotify\" onClick=\"showObject('sendFileEmail');\" value=\"t\" ".$yesCheck."> "._YES."
            &nbsp;&nbsp;&nbsp;&nbsp;
            <input type=radio name=\"emailNotify\" id=\"emailNotify\" onClick=\"hideObject('sendFileEmail');\" value=\"f\" ".$noCheck."> "._NO."
            <br><br>
            <div id=\"sendFileEmail\" ".$sendFileStyle.">
            <div class=\"formHeader\">
            "._SEND_FILE_ON_CREATE_UPDATE."
            </div>
            <input type=radio name=\"sendFile\" id=\"sendFile\" value=\"t\" ".$yesFileCheck."> "._YES."
            &nbsp;&nbsp;&nbsp;&nbsp;
            <input type=radio name=\"sendFile\" id=\"sendFile\" value=\"f\" ".$noFileCheck."> "._NO."
            </div>
    ";

  $content .= "
          <div class=\"formHeader\">
          "._SEND_EMAIL_NOTIFY."
          </div>
          ".$str."
          <br>
          ";

} else {
  $content .= "<input type=hidden name=\"emailNotify\" id=\"emailNotify\" value=\"f\">\n";
}

$content .= "
          <input type=submit value=\""._UPDATE_SETTINGS."\">
          </form>
          ";
          
$option = null;
$option["leftHeader"] = str_replace(" ","&nbsp;","\"".$fileInfo["name"]."\" "._SUBSCRIPTION." ");
$option["content"] = $content;
$siteContent = sectionDisplay($option);


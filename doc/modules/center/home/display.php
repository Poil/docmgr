<?
//login information
$loginstat = "   <div>"._LAST_SUCCESSFUL_LOGIN.": ".dateView($_SESSION["last_login"]).".  ";
if ($_SESSION["failed_logins"] > 0) $loginstat .= "<span class=\"errorMessage\">".$_SESSION["failed_logins"]." "._FAILED_ATTEMPTS."</span>";
$loginstat .= "</div>\n";

$content = "

<form name=\"pageForm\" method=\"get\">
<input type=hidden name=\"pageAction\" id=\"pageAction\" value=\"\">
<input type=hidden name=\"objectId\" id=\"objectId\" value=\"\">
<input type=hidden name=\"module\" id=\"module\" value=\"home\">

<!--encapsulating with a table so the width is okay in konqueror/safari -->
<table width=100% border=0 cellpadding=0 cellspacing=0>
<tr><td width=100%>
<div class=\"welcomeHeader\">
  <div class=\"welcomeStat\">
  ".$loginstat."
  </div>
  <div class=\"welcomeContent\">
  "._WELCOME." ".USER_FN." ".$profile."
  </div>
</div>

<div class=\"leftColumn\">

<div class=\"dbCell\">
  <div class=\"dbHeader\">
  "._BOOKMARK_COLLECTION."
  </div>
  <div class=\"dbContent\">
  ".showBookmark($conn)."
  </div>
</div>
<div class=\"dbCell\">
  <div class=\"dbHeader\">
  "._RECENT_ADD_FILES."
  </div>
  <div class=\"dbContent\">
  ".showRecent($conn)."
  </div>
</div>

</div><div class=\"rightColumn\">

<div class=\"dbCell\">
  <div class=\"dbHeader\">
  <div class=\"dbNote\">
  ("._CLICK_MANAGE.")
  </div>
  "._MY_TASKS."
  </div>
  <div class=\"dbContent\">
  ".showTasks($conn)."
  </div>
</div>
<div class=\"dbCell\">
  <div class=\"dbHeader\">
  <div class=\"dbNote\">
  ("._CLICK_VIEW_PROP.".  "._CHECK_CLEAR_ALERT.")
  </div>
  "._MY_SUBSCRIPTIONS."
  </div>
  <div class=\"dbContent\">
  ".showSubscription($conn)."
  </div>
</div>

<div class=\"dbCell\">
  <div class=\"dbHeader\">
  <div class=\"dbNote\">
  ("._CLICK_CHECKIN.")
  </div>
  "._MY_CHECKED_OUT_FILES."
  </div>
  <div class=\"dbContent\">
  ".showCheckOut($conn)."
  </div>
</div>

<div class=\"dbCell\">
  <div class=\"dbHeader\">
    <div class=\"dbNote\">
    ("._CLICK_MANAGE.")
    </div>
    "._MY_ROUTED_DOCS."
  </div>
  <div class=\"dbContent\">
  ".showRoutes($conn)."
  </div>
</div>

</div>
<div class=\"cleaner\">&nbsp;</div>

</td></tr>
</table>

</form>
";

$option = null;
$option["hideHeader"] = 1;
$option["content"] = $content;
$siteContent .= sectionDisplay($option);

?>

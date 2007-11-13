<?

$sql = "SELECT home_directory FROM auth_settings WHERE account_id='$accountId'";
$info = single_result($conn,$sql);

$option = null;
$option["conn"] = $conn;
$option["mode"] = "radio";
$option["formName"] = "homeDir";
if ($info) $option["curValue"] = array($info["home_directory"]);
$option["divName"] = "home_div";

if (!$info["home_directory"]) $homeCheck = " CHECKED ";
else $homeCheck = null;

$content = "<br/>
            <form name=\"pageForm\" method=\"post\">
            <input type=hidden name=\"pageAction\" id=\"pageAction\" value=\"update\">
            <input type=hidden name=\"includeModule\" id=\"includeModule\" value=\"homedir\">
            <div class=\"pageHeader\">"._MT_HOMEDIR."</div>
            <input type=radio name=\"homeDir\" id=\"homeDir\" value=\"0\" ".$homeCheck."> "._HOME."
            <div id=\"home_div\"></div>
            <br>
            <input type=submit value=\""._UPDATE."\">
            </form>
            ".tree_view($option)."
            ";

$option = null;
$option["hideHeader"] = 1;
$option["content"] = $content;
$siteContent .= sectionDisplay($option);


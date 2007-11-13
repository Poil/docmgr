<?

includeJavascript("javascript/permissions.js");
includeStylesheet(THEME_PATH."/css/permissions.css");
$onPageLoad = "loadPermissions('permDiv');";


$pageContent .= "
            <form name=\"pageForm\" onSubmit=\"permissionSubmit();\" method=post>
            <input type=hidden name=\"pageAction\" id=\"pageAction\" value=\"update\">
            <input type=hidden name=\"includeModule\" id=\"includeModule\" value=\"savesearchperm\">
            <input type=hidden name=\"objectId\" id=\"objectId\" value=\"".$objectId."\">
            <div id=\"permDiv\"></div>
            <br><br>
            <input type=checkbox name=\"resetPerm\" id=\"resetPerm\" value=\"1\">
            "._RESET_CHILD_PERM."
            <br><br><br>
            <input type=submit name=\"submit\" value=\""._UPDATE."\">
            </form>
            ";


<?

includeJavascript("javascript/objtree.js");

$option = null;
$option["conn"] = $conn;
$option["mode"] = "checkbox";
$option["formName"] = "relateId[]";
$option["curForm"] = "curTreeValue";
$option["curValue"] = $relatedValue;
$option["divName"] = "objrelate";

$onPageLoad= "loadTreeHandlers();".loadObjTree($option);

$pageContent .= "
            <form name=\"pageForm\" method=\"post\">
            <input type=hidden name=\"curTreeVal\" id=\"curTreeVal\" value=\"".@implode(",",$relatedValue)."\">
            <input type=hidden name=\"pageAction\" id=\"pageAction\" value=\"update\">
            <input type=hidden name=\"includeModule\" id=\"includeModule\" value=\"filerelated\">
            <div id=\"objrelate\"></div>
            <br>
            <input type=checkbox name=\"reverseRelate\" id=\"reverseRelate\" value=\"1\"> "._REVERSE_RELATE."
            <br><br>
            <input type=submit class=\"submit\" value=\""._UPDATE."\">
            </form>
            ";
            

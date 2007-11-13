<?

$option = null;
$option["conn"] = $conn;
$option["mode"] = "checkbox";
$option["formName"] = "parentId[]";
$option["curValue"] = $parentValue;
$option["divName"] = "fileparent";

$pageContent .= "
            <form name=\"pageForm\" method=\"post\">
            <input type=hidden name=\"pageAction\" id=\"pageAction\" value=\"update\">
            <input type=hidden name=\"includeModule\" id=\"includeModule\" value=\"fileparent\">
            <div id=\"fileparent\"></div>
            <br>
            <input type=submit value=\""._UPDATE."\">
            </form>
            ".tree_view($option);


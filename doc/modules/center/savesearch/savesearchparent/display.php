<?

$option = null;
$option["conn"] = $conn;
$option["mode"] = "checkbox";
$option["formName"] = "parentId[]";
if ($parentValue) $option["curValue"] = $parentValue;
$option["divName"] = "savesearchparent";

$pageContent .= "
            <form name=\"pageForm\" method=\"post\">
            <input type=hidden name=\"pageAction\" id=\"pageAction\" value=\"update\">
            <input type=hidden name=\"includeModule\" id=\"includeModule\" value=\"savesearchparent\">
            <div id=\"savesearchparent\"></div>
            <br>
            <input type=submit value=\""._UPDATE."\">
            </form>
            ".tree_view($option)."
            ";


<?

$option = null;
$option["conn"] = $conn;
$option["mode"] = "radio";
$option["formName"] = "parentId";
if ($parentValue) $option["curValue"] = $parentValue;
$option["divName"] = "colparent";

$pageContent .= "
            <form name=\"pageForm\" method=\"post\">
            <input type=hidden name=\"pageAction\" id=\"pageAction\" value=\"update\">
            <input type=hidden name=\"includeModule\" id=\"includeModule\" value=\"colparent\">
            <div id=\"colparent\"></div>
            <br>
            <input type=submit value=\""._UPDATE."\">
            </form>
            ".tree_view($option)."
            ";


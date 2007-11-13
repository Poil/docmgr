<?
/***********************************************
  Category and questions content
***********************************************/
$option = null;
$option["conn"] = $conn;
$option["mode"] = "checkbox";
$option["formName"] = "parentId[]";
if ($_REQUEST["curVal"]) $option["curValue"] = explode(",",$_REQUEST["curVal"]);
$option["divName"] = "searchcol";

$content = "

<form name=\"pageForm\" method=post>

<div style=\"float:right;width:100%;text-align:right;padding-top:5px\">
<input type=\"button\" onClick=\"updateCol();\" value=\""._UPDATE_SELECTION."\">
</div>
<div id=\"searchcol\"></div>
</form>
".tree_view($option);

$opt = null;
$opt["leftHeader"] = "Select Collection";
$opt["content"] = $content;

$siteContent = sectionDisplay($opt);

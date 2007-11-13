<?

$toolbar = createBrowseToolbar($_SESSION["searchCount"],$_REQUEST["curPage"]);;
$function = createBrowseFunction($_SESSION["searchCount"],$view_parent);

if (defined("PAGE_BROWSE_RESULTS")) $nav = createFinderNav($_SESSION["searchCount"],$_REQUEST["curPage"]);
else $nav = null;

$content = "
<div class=\"toolBar\">
<div class=\"menuNav\">
<div class=\"menuEntry\"><b>"._LOCATION.": </b></div></div>".returnNavList($conn,$view_parent,null)."
</div>
<form name=\"pageForm\" method=\"get\">
<div  style=\"padding-bottom:5px\">
<table width=100% cellpadding=0 cellspacing=0>
<tr><td align=left class=\"browseFunctionLeft\">
&nbsp;".$function.$nav."
</td><td align=right class=\"browseFunctionRight\">
".createViewSwitch()."&nbsp;
</td></tr>
</table>
</div>
<input type=hidden name=\"advance_search\" id=\"advance_search\" value=\"1\">
<input type=hidden name=\"pageAction\" id=\"pageAction\" value=\"search\">
<input type=hidden name=\"view_parent\" id=\"view_parent\" value=\"".$view_parent."\">
<input type=hidden name=\"newCategory\" id=\"newCategory\" value=\"\">
<input type=hidden name=\"saveViewParent\" id=\"saveViewParent\" value=\"".$view_parent."\">
<input type=hidden name=\"bookmarkName\" id=\"bookmarkName\" value=\"\">
<input type=hidden name=\"beginLimit\" id=\"beginLimit\" value=\"".$beginLimit."\">
<input type=hidden name=\"curPage\" id=\"curPage\" value=\"".$_REQUEST["curPage"]."\">
<input type=hidden name=\"objectId\" id=\"objectId\" value=\"".$objectId."\">
<input type=hidden name=\"pageView\" id=\"pageView\" value=\"\">
<input type=hidden name=\"module\" id=\"module\" value=\"browse\">
<input type=hidden name=\"typeid\" id=\"typeid\" value=\"browsecollection\">
<input type=hidden name=\"sortField\" id=\"sortField\" value=\"".$sortField."\">
<input type=hidden name=\"sortDir\" id=\"sortDir\" value=\"".$sortDir."\">
".$display."
<div style=\"margin-top:5px\">".$nav["pageBar"]."</div>
</form>
";

$option = null;
$option["hideHeader"] = 1;
$option["content"] = $content;
$siteContent .= sectionDisplay($option);

?>

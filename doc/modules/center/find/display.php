<?
$content = "
<form name=\"pageForm\" method=\"get\">
<input type=hidden name=\"module\" id=\"module\" value=\"find\">
<input type=hidden name=\"advance_search\" id=\"advance_search\" value=\"1\">
<input type=hidden name=\"pageAction\" id=\"pageAction\" value=\"search\">
<input type=hidden name=\"pageView\" id=\"pageView\" value=\"\">
<input type=hidden name=\"newCategory\" id=\"newCategory\" value=\"\">
<input type=hidden name=\"saveViewParent\" id=\"saveViewParent\" value=\"".$view_parent."\">
<input type=hidden name=\"bookmarkName\" id=\"bookmarkName\" value=\"\">
<input type=hidden name=\"beginLimit\" id=\"beginLimit\" value=\"".$beginLimit."\">
<input type=hidden name=\"curPage\" id=\"curPage\" value=\"".$_REQUEST["curPage"]."\">
<input type=hidden name=\"objectId\" id=\"objectId\" value=\"".$objectId."\">
<input type=hidden name=\"sortField\" id=\"sortField\" value=\"".$sortField."\">
<input type=hidden name=\"sortDir\" id=\"sortDir\" value=\"".$sortDir."\">
";

if ($functionBar || $pageBar) {

//create our "Save This Search" Link
if (bitset_compare(BITSET,INSERT_OBJECTS,ADMIN)) 
  $ss = "  <span style=\"padding-left:5px;padding-right:5px\">|</span>
           <a href=\"javascript:saveQuery();\">"._SAVE_THIS_SEARCH."</a>&nbsp;
           ";
else $ss = null;

$content .= "
  <div style=\"padding-bottom:5px\">
  <table width=100% cellpadding=0 cellspacing=0>
  <tr><td align=left class=\"browseFunctionLeft\">
  ".$functionBar."
  ".$pageBar."
  </td><td align=right class=\"browseFunctionRight\">
  ".createViewSwitch()."&nbsp;
  ".$ss."
  </td></tr>
  </table>
  </div>
  ";

}

$content .= "
".$display."
<table width=100%>
<tr><td>
".$pageBar."
</td></tr>
</table>
</form>
";

$option = null;
$option["hideHeader"] = 1;
$option["leftHeader"] = $header;

$option["content"] = $content;
$siteContent .= sectionDisplay($option);

?>

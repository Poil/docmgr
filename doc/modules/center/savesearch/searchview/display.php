<?

$content = null;

if ($functionBar || $pageBar) {

  $content .= "
  <div class=\"toolBar\">
  <div class=\"menuNav\"><div class=\"menuEntry\"><b>"._LOCATION.": </b></div></div>
  ".returnNavList($conn,$sInfo["parent_id"],null,$sInfo["name"])."</div>
  <div style=\"padding-bottom:5px\">
  <table width=100% cellpadding=0 cellspacing=0>
  <tr><td align=left class=\"browseFunctionLeft\">
  ".$functionBar."
  ".$pageBar."
  </td><td align=right class=\"browseFunctionRight\">
  ".createViewSwitch()."&nbsp;
  </td></tr>
  </table>
  </div>
  ";

}

$content .= "
<form name=\"pageForm\" method=\"get\">
<input type=hidden name=\"module\" id=\"module\" value=\"searchview\">
<input type=hidden name=\"advance_search\" id=\"advance_search\" value=\"1\">
<input type=hidden name=\"pageAction\" id=\"pageAction\" value=\"search\">
<input type=hidden name=\"pageView\" id=\"pageView\" value=\"\">
<input type=hidden name=\"newCategory\" id=\"newCategory\" value=\"\">
<input type=hidden name=\"saveViewParent\" id=\"saveViewParent\" value=\"".$view_parent."\">
<input type=hidden name=\"bookmarkName\" id=\"bookmarkName\" value=\"\">
<input type=hidden name=\"beginLimit\" id=\"beginLimit\" value=\"".$beginLimit."\">
<input type=hidden name=\"curPage\" id=\"curPage\" value=\"".$curPage."\">
<input type=hidden name=\"objectId\" id=\"objectId\" value=\"".$objectId."\">
<input type=hidden name=\"sortField\" id=\"sortField\" value=\"".$sortField."\">
<input type=hidden name=\"sortDir\" id=\"sortDir\" value=\"".$sortDir."\">

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

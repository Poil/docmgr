<?
if ($successMessage) ajaxSelfClose();

$option = null;
$option["conn"] = $conn;
$option["mode"] = "radio";
$option["formName"] = "parentId";
if ($curValue) $option["curValue"] = $curValue;
$option["divName"] = "selectcol";

$content = "

<div style=\"width:100%;\">

<form name=pageForm method=post>
<input type=hidden name=\"objectAction\" id=\"objectAction\" value=\"\">

<table width=95% align=center>
<tr><td colspan=2>
	<div class=\"blankBigSectionHeader\">
		"._SELECT_NEW_COLLECTION.":
	</div>
</td></tr>
<tr><td>
		<br>
		<table cellpadding=0 cellspacing=0>
		<tr><td>
		<input type=radio name=\"parentId\" id=\"parentId\" value=\"0\">
		</td><td>		
		<b>"._HOME."</b>
		</td></tr>
		</table>
		<div id=\"selectcol\"></div>

</td><td valign=top align=center>
	<br>
	<input type=submit onClick=\"getObjIds()\" name=\"moveObject\" value=\""._MOVE_OBJECT."\">
</td></tr>
</table>
</form>
</div>
".tree_view($option);


$opt = null;
$opt["hideHeader"] = 1;
$opt["content"] = $content;

$siteContent .= sectionDisplay($opt);



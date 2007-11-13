<?

if ($successMessage) ajaxSelfClose();

selfFocus();

$content = "

<form name=pageForm method=post>

<input type=hidden name=\"pageAction\" id=\"pageAction\" value=\"update\">
<input type=hidden name=\"parentId\" id=\"parentId\" value=\"".$parentId."\">

		<br>
		<table>
		<tr><td colspan=2>
			"._NAME.":<br>
			<input type=text size=30 name=\"name\" id=\"name\" value=\"".stripsan($_POST["name"])."\"><br>
			<br><br>
			"._DESCRIPTION.":<br>
			<textarea rows=4 cols=25 name=\"summary\" id=\"summary\">".stripsan($_POST["summary"])."</textarea>
		</td></tr>
		<tr><td colspan=2>
			<br><br>
			<input type=\"submit\" onClick=\"return checkName();\"  value=\""._SUBMIT_CHANGES."\">
		</td></tr>
		</table>


</form>
<script language=\"javascript\">
document.pageForm.name.focus();
</script>

";

$option = null;
$option["leftHeader"] = _MT_NEWCOLLECTION;
$option["content"] = $content;
$siteContent .= sectionDisplay($option);




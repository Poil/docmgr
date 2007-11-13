<?

if ($successMessage) ajaxSelfClose();

selfFocus();

$content = "

<form name=pageForm method=post>

<input type=hidden name=\"pageAction\" id=\"pageAction\" value=\"update\">
<input type=hidden name=\"parentId\" id=\"parentId\" value=\"".$parentId."\">

		<br>
		<div class=\"wikiForm\">
			<div class=\"formHeader\">
			"._NAME."
			</div>
			<input type=text size=30 name=\"name\" id=\"name\" value=\"".stripsan($_POST["name"])."\"><br>
		</div>
		<div class=\"wikiForm\">
			<div class=\"formHeader\">
			"._DESCRIPTION."
			</div>
			<textarea rows=2 cols=37 name=\"summary\" id=\"summary\">".stripsan($_POST["summary"])."</textarea>
		</div>
		<div class=\"cleaner\">&nbsp;</div>
		<input type=\"submit\" onClick=\"return checkName();\"  value=\""._SUBMIT_CHANGES."\">

</form>
<script language=\"javascript\">
document.pageForm.name.focus();
</script>

";

$option = null;
$option["leftHeader"] = _MT_NEWDOC;
$option["content"] = $content;
$siteContent .= sectionDisplay($option);




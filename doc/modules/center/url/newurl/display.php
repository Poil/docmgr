<?

if ($successMessage) ajaxSelfClose();
selfFocus();

$content = "

<form name=pageForm method=post>

<input type=hidden name=\"pageAction\" id=\"pageAction\" value=\"update\">
<input type=hidden name=\"parentId\" id=\"parentId\" value=\"".$parentId."\">

		<br>
			<div class=\"formHeader\">
			"._NAME."
			</div>
			<input type=text size=30 name=\"name\" id=\"name\" value=\"".$_POST["name"]."\"><br>
			<br>
			<div class=\"formHeader\">
			"._URL_ADDR."
			</div>
			<input type=text size=30 name=\"url\" id=\"url\" value=\"".$_POST["url"]."\"><br>
			<br>
			<div class=\"formHeader\">
			"._DESCRIPTION."
			</div>
			<textarea rows=3 cols=30 name=\"summary\" id=\"summary\">".$_POST["summary"]."</textarea>
			<br><br><br>
			<input type=\"submit\" onClick=\"return checkName();\" value=\""._SUBMIT_CHANGES."\">


</form>

";

$option = null;
$option["leftHeader"] = _MT_NEWURL;
$option["content"] = $content;
$siteContent .= sectionDisplay($option);




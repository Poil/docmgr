<?

if ($successMessage) {

	echo "
	<script type=\"text/javascript\">
	var oldUrl = \"index.php?module=browse&view_parent=".$parentId."\";
	window.opener.location.href = oldUrl;
	";
	
	if (!$_POST["openwin"]) echo "self.close();\n";

	echo "</script>\n";	

}


selfFocus();

if (is_array($keyArr) && count($keyArr) > 0) {

	foreach ($keyArr AS $keyword) {

		$func = "keyword".$keyword["type"];

		$keyString .= "<div class=\"keywordForm\">\n";
		$keyString .= $func($keyword);
		$keyString .= "</div>\n";

	}	

	$keyString .= "<div class=\"cleaner\">&nbsp;</div>\n";

}



//check to see if we have done an upload and want to keep the window open
if ($_POST["openwin"]) $winCheck = " CHECKED ";
else $winCheck = null;

if ($_POST["modeStatus"]=="multiUpload") {
	$multiSelect = " SELECTED ";
	$multiClass = "visibility:visible;position:static;";
	$singleClass = "visibility:hidden;position:absolute;";
	$modeStatus = "multiUpload";
} else {
	$singleClass = "visibility:visible;position:static;";
	$multiClass = "visibility:hidden;position:absolute;";
	$multiSelect = null;
	$modeStatus = "singleUpload";
}	


$ctrl = "<select name=\"uploadMode\" id=\"uploadMode\" size=1 class=\"submitSmall\" onChange=\"switchMode()\">
	 <option value=\"singleUpload\">Single Upload
	 <option ".$multiSelect." value=\"multiUpload\">Multiple Upload
	 </select>
	 ";

$content = "

<FORM ENCTYPE=\"multipart/form-data\" METHOD=POST name=\"pageForm\">
<input type=hidden name=\"pageAction\" id=\"pageAction\" value=\"upload\">
<input type=hidden name=\"parentId\" id=\"parentId\" value=\"".$parentId."\">
<input type=hidden name=\"modeStatus\" id=\"modeStatus\" value=\"".$modeStatus."\">

	<div id=\"multiUpload\" style=\"".$multiClass."\">
		<br>
		<div class=\"formHeader\">Select Files To Upload</div>
		<br>
		<div id=\"uploadFileForm\">
		<input type=file size=20 class=\"textboxSmall\" onChange=\"addFile()\">
		</div>
		<div id=\"uploadFileText\"></div>
	</div>
	<div id=\"singleUpload\" style=\"".$singleClass."\">
		<br>
		<div class=\"textForm\">
			<div class=\"formHeader\">
			"._FILE."
			</div>
			<INPUT size=20 NAME=\"singleFile\" id=\"singleFile\" TYPE=\"file\">
		</div>
		<div class=\"textForm\">
			<div class=\"formHeader\">
			"._FILE_VERSION."
			</div>
			<input type=text name=\"fileVersion\" id=\"fileVersion\" value=\"\">
		</div>
		<div class=\"textForm\">
			<div class=\"formHeader\">
			"._SUMMARY."
			</div>
			<textarea name=\"summary\" id=\"summary\" rows=4 cols=60></textarea>
		</div>
		<div class=\"cleaner\">&nbsp;</div>
		".$keyString."
	</div>
	
	<br><input type=\"submit\" value=\""._UPLOAD."\">

	<div style=\"position:absolute;left:5px;top:400px;\">
	<div class=\"formHeader\">
	<br>
	<input type=\"checkbox\" name=\"openwin\" id=\"openwin\" ".$winCheck." value=\"1\">
	"._KEEP_WINDOW_OPEN."
	</div>
	</div>	

</form>
";

$option = null;
$option["leftHeader"] = "<div style=\"float:right\">".$ctrl."</div>File Upload";
$option["content"] = $content;
$siteContent .= sectionDisplay($option);


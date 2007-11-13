<?

if ($successMessage) {

	$pageContent .= "<br><div class=\"successMessage\">File Checkin Complete</div>\n";

} else {

	$pageContent .= "

		<FORM ENCTYPE=\"multipart/form-data\" METHOD=POST name=\"pageForm\">
		<input type=hidden name=\"pageAction\" id=\"pageAction\" value=\"upload\">
		<input type=hidden name=\"objectId\" id=\"objectId\" value=\"".$_REQUEST["objectId"]."\">
		<input type=hidden name=\"includeModule\" id=\"includeModule\" value=\"filecheckin\">

		<br>
		<div class=\"formHeader\">
		"._FILE.":
		</div>
		<INPUT NAME=\"userfile\" TYPE=\"file\">
		<br><br>
		<div class=\"formHeader\">
		"._FILE_VERSION."
		</div>
		<input type=text name=\"fileVersion\" id=\"fileVersion\" value=\"\">
		<br><br>
		<div class=\"formHeader\">		
		"._NOTES_FOR_REVISION.":
		</div>
		<textarea name=\"notes\" id=\"notes\" rows=4 cols=30></textarea>
		<br><br>
		<input type=\"submit\" value=\""._UPLOAD."\">

		</form>
		";

}


<?
//allow the user with object manage permissions to edit properties
if (bitset_compare(CUSTOM_BITSET,OBJ_MANAGE,OBJ_ADMIN))
	$readOnly = null;
else 
	$readOnly = " READONLY ";

if (!$file_notes) $file_notes = _NONE;

$size = displayFileSize($file_size);

//setup our keyword input fields
$keyString = null;
$keyArr = returnKeywords();
$num = count($keyArr);

if (is_array($keyArr) && $num > 0) {

	//get the values from the database
	$sql = "SELECT * FROM dm_keyword WHERE object_id='$objectId';";
	$keyInfo = single_result($conn,$sql);

	foreach ($keyArr AS $keyword) {

		$func = "keyword".$keyword["type"];
		$fname = $keyword["name"];

		$keyString .= "<div style=\"float:left;padding-right:15px;padding-bottom:10px;\">\n";
		$keyString .= $func($keyword,$keyInfo[$fname]);
		$keyString .= "</div>\n";

	}

	$keyString .= "<div class=\"cleaner\">&nbsp;</div>\n";

}


$pageContent .= "
	<form name=\"pageForm\" method=\"post\">
	<input type=hidden name=\"pageAction\" id=\"pageAction\" value=\"update\">
	<input type=hidden name=\"includeModule\" id=\"includeModule\" value=\"fileproperties\">
	<div class=\"formHeader\">
	"._FILE_NAME."
	</div>
	<input type=text size=30 name=\"fileName\" id=\"fileName\" value=\"".$file_name."\" ".$readOnly.">
	<br><br>
	<div class=\"formHeader\">
	"._SUMMARY."
	</div>
	<textarea rows=3 cols=40 name=\"fileSummary\" id=\"fileSummary\" ".$readOnly.">".$file_summary."</textarea>
	<br><br>
	".$keyString."
	<table cellpadding=0 width=275px cellspacing=0>
	<tr><td width=\"33%\">
		<div class=\"formHeader\">
		"._SIZE."
		</div>
		".$size."
	</td><td width=\"33%\">
		<div class=\"formHeader\">
		"._VERSION."
		</div>
		".$version."
	</td><td width=\"33%\" style=\"white-space:nowrap\">
		<div class=\"formHeader\">
		"._FILE_VERSION."
		</div>
		".$customVersion."
	</td></tr>
	</table>
	<br>
	<div class=\"formHeader\">
	"._CREATED."
	</div>
	".$creation_date[0]." "._AT." ".$creation_date[1]." "._BY." ".$file_owner."
	<br><br>
	<div class=\"formHeader\">
	"._LAST_MODIFIED."  
	</div>
	".$last_modify_date[0]." "._AT." ".$last_modify_date[1]." "._BY." ".$last_modify_user."
	<br><br>
	<div class=\"formHeader\">
	"._LATEST_REVISION_NOTES."
	</div>
	".$file_notes."
	<br><br>
	<div class=\"formHeader\">
	"._FILE_STATUS."
	</div>
	<font color=".$font_color."><b>".$check_status."</b></font>
	<br><br>
";

if ($status_num==1) { 

	$pageContent .= "

	<table cellpadding=0 cellspacing=0 width=350px>
	<tr><td width=40%>
	<div class=\"formHeader\">
	"._CHECKOUT_BY."
	</div>
	".$check_user."
	</td><td>
	<div class=\"formHeader\">
	"._CHECKOUT_ON."
	</div>
	".$check_date[0]." "._AT." ".$check_date[1]."
	</td></tr>
	</table>
	<br>
	";

	if (bitset_compare(BITSET,ADMIN,NULL) || $fileInfo["object_owner"]==USER_ID || $fileInfo["status_owner"]==USER_ID) {
		$pageContent .= "<a href=\"javascript:clearCheckout()\">
				["._CLEAR_CHECKOUT_STATUS."]
				</a><br><br>\n";
	}

}

if (!$readOnly) $pageContent .= "<br><input type=submit value=\""._UPDATE."\">\n";

$pageContent .= "
</td></tr>
</table>
</div>
";



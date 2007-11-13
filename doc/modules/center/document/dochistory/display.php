<?

$pageContent .= "
<form name=\"pageForm\" method=\"post\">
<input type=hidden name=\"pageAction\" id=\"pageAction\" value=\"\">
<input type=hidden name=\"includeModule\" id=\"includeModule\" value=\"dochistory\">
<input type=hidden name=\"docId\" id=\"docId\" value=\"\">
";

$objDir = returnObjPath($conn,$objectId);

for ($row=0;$row<$info["count"];$row++) {

	$temp = returnAccountInfo($conn,$info["object_owner"][$row],null);
	$login = $temp["login"];
	$modArr = date_time_view($info["modify"][$row]);
	$modDate = $modArr[0]." "._AT." ".$modArr[1];

	if (!$info["notes"][$row]) $info["notes"][$row] = _NONE;

	$pageContent .= "<div class=\"areaHeader\">"._VERSION." ".$info["version"][$row]."</div>\n";
	$pageContent .= "<div style=\"padding-left:10px;margin-bottom:10px;\">\n";
	if ($info["name"][$row]) $pageContent .= _NAME.": ".$info["name"][$row]."<br>\n";
	$pageContent .= _MODIFIED.": ".$modDate." "._BY." ".$login."<br>\n";
	if ($info["custom_version"][$row]) $pageContent .= _FILE_VERSION.": ".$info["custom_version"][$row]."<br>\n";
	$pageContent .= _REVISION_NOTES.": ".$info["notes"][$row]."<br>\n";
	$pageContent .= _SIZE.": ".displayFileSize(@filesize(DOC_DIR."/".$objDir."/".$info["id"][$row].".docmgr"))."<br>\n";

	$pageContent .= "<a href=\"javascript:viewFile('".$info["id"][$row]."');\" class=main>["._VIEW_THIS_VERSION."]</a>\n";

	//link for promoting the version
	if ($fileInfo["object_owner"]==USER_ID || bitset_compare(BITSET,ADMIN,null)) {

		$pageContent .= "&nbsp;";
		$pageContent .= "<a href=\"javascript:promoteFile('".$info["id"][$row]."');\" class=main>["._PROMOTE_LATEST_VERSION."]</a>\n";
		
		if (defined("FILE_REVISION_REMOVE") && FILE_REVISION_REMOVE=="yes") {
			$pageContent .= "&nbsp;";
			$pageContent .= "<a href=\"javascript:removeFile('".$info["id"][$row]."');\" class=main>["._REMOVE_THIS_VERSION."]</a>\n";
		}			
		
	} 
	$pageContent .= "</div>\n";

}

$pageContent .= "</form>";


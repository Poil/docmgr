<?
$logArr = returnLogList();
$filter = null;
$content = null;

$filter = " AND log_type IN ('OBJ_ANON_EMAILED','OBJ_ANON_VIEWED') AND account_id='".USER_ID."' ";
$sql = "SELECT name FROM dm_object WHERE id='$objectId'";
$info = single_result($conn,$sql);
$fileName = $info["name"];
$content = "<br>";

$sql = "SELECT * FROM dm_object_log WHERE object_id='$objectId' ".$filter." ORDER BY log_time DESC";
$list = total_result($conn,$sql);

if (!$list["count"]) $content = "<br><div class=\"errorMessage\">"._NO_RESULTS."</div>\n";
else {


	for ($row=0;$row<$list["count"];$row++) {

		$logText = returnLogType($logArr,$list["log_type"][$row]);

		if ($accountFilter=="share") {
			$login = $list["log_data"][$row];
			$dataText = _RECIPIENT;
		} else {
			$temp = returnAccountInfo($conn,$list["account_id"][$row],null);
			$login = $temp["login"];
			$dataText = _USER;
		}	
		$content .= "<div class=\"areaHeader\">".implode(" "._AT." ",date_time_view($list["log_time"][$row],null))."</div>\n";
		$content .= "<div style=\"padding-left:10px\">\n";	
		$content .= _ENTRY.": ".$logText."<br>\n";
		$content .= $dataText.": ".$login."<br>\n";
		$content .= "</div><br>\n";

	}

}

$option = null;
$option["leftHeader"] = "\"".$fileName."\" "._MT_FILELOGS;
$option["content"] = $content;
$siteContent .= sectionDisplay($option);




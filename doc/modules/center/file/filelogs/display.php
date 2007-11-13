<?

$logArr = returnLogList();
$limit = null;
$filter = null;

if ($_REQUEST["logFilter"]=="myentries") {
	$myCheck = " SELECTED ";
	$filter = "AND account_id='".USER_ID."'";	
} elseif ($_REQUEST["logFilter"]=="virus") {
	$virusCheck = " SELECTED ";
	$filter = "AND log_type IN ('OBJ_VIRUS_PASS','OBJ_VIRUS_FAIL','OBJ_VIRUS_ERROR')";	
} elseif ($_REQUEST["logFilter"]=="email") {
	$emailCheck = " SELECTED ";
	$filter = "AND log_type IN ('OBJ_ANON_EMAILED','OBJ_EMAILED')";
} elseif ($_REQUEST["logFilter"]=="view") {
	$viewCheck = " SELECTED ";
	$filter = "AND log_type IN ('OBJ_VIEWED','OBJ_ANON_VIEWED')";	
} elseif ($_REQUEST["logFilter"]=="checkin") {
	$checkinCheck = " SELECTED ";
	$filter = "AND log_type IN ('OBJ_CHECKED_OUT','OBJ_CHECKED_IN')";	
} elseif ($_REQUEST["logFilter"]=="all") {
	$allCheck = " SELECTED ";
	$filter = null;	
} else {
	$tenCheck = " SELECTED ";
	$filter = null;
	$limit = " limit 10";
}

//put together our filter dropdown
$filterdd = "	<select name=\"logFilter\" id=\"logFilter\" class=\"dropdownSmall\" onChange=\"submitForm()\">
		<option value=\"lastten\">"._LAST_TEN_ENTRIES."
		<option value=\"myentries\" ".$myCheck.">"._MY_ENTRIES."
		<option value=\"virus\" ".$virusCheck.">"._VIRUS_SCANS."
		<option value=\"email\" ".$emailCheck.">"._EMAILS."
		<option value=\"view\" ".$viewCheck.">"._FILE_VIEWS."
		<option value=\"checkin\" ".$checkinCheck.">"._CHECKINS_CHECKOUTS."
		<option value=\"all\" ".$allCheck.">"._ALL_ENTRIES."
		</select>\n";



$sql = "SELECT * FROM dm_object_log WHERE object_id='$objectId' ".$filter." ORDER BY log_time DESC ".$limit."";
$list = total_result($conn,$sql);

$pageContent .= "
<form name=\"pageForm\" method=\"post\">
<input type=hidden name=\"pageAction\" id=\"pageAction\" value=\"\">
<input type=hidden name=\"includeModule\" id=\"includeModule\" value=\"filelogs\">
      
<div style=\"float:right\">".$filterdd."</div><br>\n";

if (!$list["count"]) $pageContent .= "<br><div class=\"errorMessage\">"._NO_RESULTS."</div>\n";
else {

	for ($row=0;$row<$list["count"];$row++) {

		$logText = returnLogType($logArr,$list["log_type"][$row]);

		if ($list["account_id"][$row]) {
			$temp = returnAccountInfo($conn,$list["account_id"][$row],null);
			$login = $temp["login"];
		} else $login = _ANONYMOUS;

		$pageContent .= "<div class=\"areaHeader\">".implode(" "._AT." ",date_time_view($list["log_time"][$row],null))."</div>\n";
		$pageContent .= "<div style=\"padding-left:10px\">\n";	
		$pageContent .= _ENTRY.": ".$logText."<br>\n";
		$pageContent .= _USER.": ".$login."<br>\n";
		if ($list["log_data"][$row]) $pageContent .= _DATA.": ".$list["log_data"][$row]."<br>\n";
		$pageContent .= "</div><br>\n";

	}

}

$pageContent .= "</form>\n";


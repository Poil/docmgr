<?

//get a list of all accounts
$accountList = returnAccountList(array("conn"=>$conn));

//get a list of all threads for this forum (page)
$sql = "SELECT * FROM dm_discussion WHERE object_id='$objectId' ORDER BY id";
$messageList = total_result($conn,$sql);


if ($messageList) {

	//get the keys for those that have no owner
	$keys = array_keys($messageList["owner"],"0");

	for ($i=0;$i<count($keys);$i++) {

		$row = $keys[$i];

		//get all the messages belonging to this owner
		$listKeys = array_keys($messageList["owner"],$messageList["id"][$row]);

		$num = count($listKeys);

		if ($num>1) $lastRow = count($listKeys) - 1;
		else $lastRow = $row;
		
		//get the current account
		$accountKey = array_search($messageList["account_id"][$lastRow],$accountList["id"]);
		$accountLogin = $accountList["login"][$accountKey];		

		$replyTime = dateView($messageList["time_stamp"][$lastRow]);

		$replyCount = threadCount($messageList,$messageList["id"][$row],null);

		$temp = returnAccountInfo($conn,$messageList["account_id"][$row],null);
		$login = $temp["login"];

		$pageContent .= "<div class=\"areaHeader\">";
		$pageContent .= "<a href=\"javascript:viewThread('".$messageList["id"][$row]."');\">".$messageList["header"][$row]."</a>";
		$pageContent .= "</div>\n";
		$pageContent .= "<div style=\"padding-left:10px\">\n";
		$pageContent .= _REPLIES.": ".$replyCount."<br>\n";
		$pageContent .= _LAST_COMMENT.": ".$replyTime." "._BY." ".$accountLogin."<br>\n";
		$pageContent .= _STARTED_BY.": ".$login."<br>\n";
		$pageContent .= "</div><br>\n";

	}

} else {

	$pageContent .= "<div class=\"errorMessage\">"._NO_DISCUSS_MESSAGES."</div>\n";

}


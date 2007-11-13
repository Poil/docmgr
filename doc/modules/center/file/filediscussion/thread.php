<?


$sql = "SELECT header FROM dm_discussion WHERE id='$threadId'";
$info = single_result($conn,$sql);

$pageContent .= "<table width=100% cellpadding=0 cellspacing=0>
			<tr><td class=\"forumResultsTitle\" width=125>
				<div class=\"threadHeader\">"._AUTHOR."</div>
			</td><td class=\"forumResultsTitle\">
				<div class=\"threadHeader\">"._TOPIC.": ".$info["header"]."</div>
			</td></tr>
			</table>
			";

$offsetLimit = $curPage * 10 - 10;

//get the main info for the primary thread
$sql="SELECT * FROM dm_discussion WHERE (id='$threadId' OR owner='$threadId') ORDER BY id DESC LIMIT 10 OFFSET $offsetLimit";
$threadInfo = total_result($conn,$sql);

if ($threadInfo) {

	for ($row=0;$row<count($threadInfo["id"]);$row++) {

		$check = $row % 2;
	
		if ($check=="1") $threadInfo["class"] = "forumResultsEven";
		else $threadInfo["class"] = "forumResultsOdd";

		$threadInfo["auth_conn"] = $auth_conn;
		$threadInfo["row"] = $row;

		//display the table
		$pageContent .= threadTable($threadInfo);
	}

}


?>
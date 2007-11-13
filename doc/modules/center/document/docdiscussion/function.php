<?
function threadCount($infoArray,$id,$count) {

	$keys = array_keys($infoArray["owner"],$id);

	$count = $count + count($keys);

	for ($row=0;$row<count($keys);$row++) {

		$key = $keys[$row];
	
		$count = threadCount($infoArray,$infoArray["id"][$key],$count);

	}

	return $count;

}

function threadTable($infoArray) {

	$row = $infoArray["row"];
	$dtPost = dateView($infoArray["time_stamp"][$row]);

	if (bitset_compare(BITSET,ADMIN,null) || bitset_compare(PAGE_BITSET,PAGE_ADMIN,null)) {

		$rowspan="2";
		$adminString .= "	<tr><td align=right class=\"".$infoArray["class"]."\">
						<a href=\"javascript:editMessage('".$infoArray["id"][$row]."');\" class=main>["._EDIT_POST."]</a>
						&nbsp;
						<a href=\"javascript:void(0);\" onClick=\"return removeMessage('".$infoArray["id"][$row]."');\" class=main>["._DELETE_POST."]</a>
						</td></tr>
						";

	}
	else {

		$rowspan="1";
		$adminString = null;

	}

	$temp = returnAccountInfo($infoArray["auth_conn"],$infoArray["account_id"][$row],null);
	$login = $temp["login"];

	$string .= "	<table class=\"threadTable\" cellpadding=0 cellspacing=0 width=100%>
				<tr><td width=125 valign=top class=\"".$infoArray["class"]."\" rowspan=\"".$rowspan."\">
					".$login."
				</td><td valign=top class=\"".$infoArray["class"]."\">
					"._POSTED.":&nbsp;
					".$dtPost."
					<hr>
					".$infoArray["content"][$row]."
				<br>
				<div class=\"threadPad\">&nbsp;</div>
				</td></tr>
				";

	if ($adminString) $string .= $adminString;

	$string .= "	</table>";

	return $string;

}


?>

<?
includeJavascript("javascript/browse.js");

$searchOption = null;
$showObjects = null;
if ($curInfo["search_option"]) $searchOption = @explode("|",$curInfo["search_option"]);
if ($curInfo["show_objects"]) $showObjects = @explode(",",$curInfo["show_objects"]);

//set our options for the form
if (!$searchOption || in_array("file_contents",$searchOption)) $contentChecked = " CHECKED ";
if (!$searchOption || in_array("file_name",$searchOption)) $nameChecked = " CHECKED ";
if (!$searchOption || in_array("summary",$searchOption)) $summaryChecked = " CHECKED ";

//what types of objects will we display
if (!$showObjects || in_array("file",$showObjects)) $fileChecked = " CHECKED ";
if (!$showObjects || in_array("collection",$showObjects)) $colChecked = " CHECKED ";
if (!$showObjects || in_array("url",$showObjects)) $urlChecked = " CHECKED ";
if (!$showObjects || in_array("savesearch",$showObjects)) $ssChecked = " CHECKED ";

if ($curInfo["mod_option"]=="enter") $modEnterSelect = " SELECTED ";	

//which limit result dropdown is selected?
if (!$limitResults) $limitResults = RESULTS_PER_PAGE;

if ($limitResults=="10") $limit10 = " SELECTED ";
elseif ($limitResults=="25") $limit25 = " SELECTED ";
elseif ($limitResults=="50") $limit50 = " SELECTED ";

if ($curInfo["date_option"]=="before") $dateBeforeSelect = " SELECTED ";
if ($curInfo["date_option"]=="after") $dateAfterSelect = " SELECTED ";
if ($curInfo["date_option"]=="single") $dateSingleSelect = " SELECTED ";
if ($curInfo["date_option"]=="period") $datePeriodSelect = " SELECTED ";

//setup our visibility for normal or keyword searches
if ($curInfo["search_type"]=="keyword") {
	$kStyle = "visibility:visible;position:static";
	$nStyle = "visibility:hidden;position:absolute";
} else {
	$kStyle = "visibility:hidden;position:absolute";
	$nStyle = "visibility:visible;position:static";
}	

//setup keyword search
$keyArr = returnKeywords();
$num = count($keyArr);

if ($num > 0) {

	$metaRow = "<div id=\"keywordOption\" style=\"".$kStyle."\">
			<div class=\"formHeader\">
			"._SEARCH_IN."
			</div>
			<select name=\"metaOption\" id=\"metaOption\">
			";

	foreach ($keyArr AS $keyword) {

		$name = $keyword["name"];

		if ($metaOption==$name) $select = " SELECTED ";
		else $select = null;

		$metaRow .= "<option ".$select." value=\"".$name."\">".$keyword["title"]."\n";
	}

	$metaRow .= "</select></div>\n";

	if ($curInfo["search_type"]=="keyword") $keywordSelect = " SELECTED ";

	$searchTypeStr .= "	
				<br><br><div class=\"formHeader\">
				"._SEARCH_TYPE."
				</div>
				<select name=\"searchType\" id=\"searchType\" onChange=\"swapSearchType()\">
				<option value=\"normal\">"._NORMAL."
				<option value=\"keyword\" ".$keywordSelect.">"._KEYWORD."
				</select>
				";
}
else {

	$searchTypeStr = "&nbsp;<input type=hidden name=\"searchType\" id=\"searchType\" value=\"normal\">\n";

}


$pageContent .= "

		<form name=\"pageForm\" method=\"post\">
		<input type=hidden name=\"module\" id=\"module\" value=\"savesearch\">
		<input type=hidden name=\"includeModule\" id=\"includeModule\" value=\"savesearchcriteria\">
		<input type=hidden name=\"objectId\" id=\"objectId\" value=\"".$objectId."\">
		<input type=hidden name=\"colFilterId\" id=\"colFilterId\" value=\"".$curInfo["col_filter_id"]."\">
		<input type=hidden name=\"accountFilterId\" id=\"accountFilterId\" value=\"".$curInfo["account_filter_id"]."\">

			<div>
			<div class=\"formHeader\">
			"._SEARCH_FOR_WILDCARD.":
			</div>
			<input type=text name=\"searchString\" size=30 value=\"".$curInfo["search_string"]."\">
			".$searchTypeStr."
			<br><br>			
			<div id=\"normalOption\" style=\"".$nStyle."\">

				<div class=\"formHeader\">
				"._SEARCH_IN.":
				</div>
				<input type=checkbox name=\"search_option[]\" value=\"file_contents\" ".$contentChecked.">&nbsp;"._FILE_CONTENTS."
				&nbsp;&nbsp;&nbsp;&nbsp;
				<input type=checkbox name=\"search_option[]\" value=\"file_name\" ".$nameChecked.">&nbsp;"._FILE_NAME."
				&nbsp;&nbsp;&nbsp;&nbsp;
				<input type=checkbox name=\"search_option[]\" value=\"summary\" ".$summaryChecked.">&nbsp;"._FILE_SUMMARY."

			<br><br>

				<div class=\"formHeader\">
				"._SHOW_MATCHING.":
				</div>
				<span style=\"white-space:nowrap;padding-right:8px;\">
				<input type=checkbox name=\"showObjects[]\" id=\"showObjects[]\" value=\"file\" ".$fileChecked."> "._FILES."
				</span>
				<span style=\"white-space:nowrap;padding-right:8px;\">
				<input type=checkbox name=\"showObjects[]\" id=\"showObjects[]\" value=\"collection\" ".$colChecked.">&nbsp;"._COLLECTIONS."
				</span>
				<span style=\"white-space:nowrap;padding-right:8px;\">
				<input type=checkbox name=\"showObjects[]\" id=\"showObjects[]\" value=\"url\" ".$urlChecked.">&nbsp;"._URLS."
				</span>
				<span style=\"white-space:nowrap;padding-right:8px;\">
				<input type=checkbox name=\"showObjects[]\" id=\"showObjects[]\" value=\"savesearch\" ".$ssChecked.">&nbsp;"._SAVESEARCH."
				</span>
			
			</div>
			".$metaRow."
			<br>
			<table width=100% cellpadding=0 cellspacing=0>
			<tr><td width=50% style=\"white-space:nowrap\">
				<div class=\"formHeader\">
				"._IN_COLLECTION.":
				</div>
				<input READONLY type=text size=15 name=\"colFilter\" id=\"colFilter\" value=\"".$curInfo["col_filter"]."\">
				<input type=button value=\"...\" onClick=\"selectCollection();\">

			</td><td width=50% style=\"white-space:nowrap\">
				
				<div class=\"formHeader\">
				"._OWNED_BY_USER.":
				</div>
				<input READONLY type=text size=15 name=\"accountFilter\" id=\"accountFilter\" value=\"".$curInfo["account_filter"]."\">
				<input type=button value=\"...\" onClick=\"selectAccount();\">

			</td></tr>
			</table>
			<br>
				<table width=100% cellpadding=0 cellspacing=0>
				<tr><td width=50% valign=top>

					<div class=\"formHeader\">
					"._WHEN_FILE_WAS.":
					</div>
					<select name=\"mod_option\" id=\"mod_option\">
					<option value=\"last\">"._LAST_MOD."
					<option value=\"enter\" ".$modEnterSelect.">"._ENTER_INTO_SYSTEM."
					</select>	

					<br><br>
					<div class=\"formHeader\">
					"._DURING.":
					</div>
					<select name=\"date_option\" id=\"date_option\" onChange=\"swapDate();\">
					<option value=\"any\">"._ANY_DATE."
					<option value=\"before\" ".$dateBeforeSelect.">"._BEFORE."
					<option value=\"single\" ".$dateSingleSelect.">"._ON_DATE."
					<option value=\"after\" ".$dateAfterSelect.">"._AFTER."
					<option value=\"period\" ".$datePeriodSelect.">"._TIME_PERIOD."
					</select>
				
				</td><td width=50% valign=top>
	
					<div class=\"formHeader\" id=\"fromText\" style=\"top:0px;left:0px;visibility:hidden;position:absolute\">"._FROM." (".DATE_FORMAT."):</div>
					<div class=\"formHeader\" id=\"onText\" style=\"top:0px;left:0px;visibility:hidden;position:absolute\">"._ON." (".DATE_FORMAT."):</div>
					<div class=\"formHeader\" id=\"beforeText\" style=\"top:0px;left:0px;visibility:hidden;position:absolute\">"._BEFORE." (".DATE_FORMAT."):</div>
					<div class=\"formHeader\" id=\"afterText\" style=\"top:0px;left:0px;visibility:hidden;position:absolute\">"._AFTER." (".DATE_FORMAT."):</div>
					<div style=\"top:0px;left:0px;visibility:hidden;position:absolute\" id=\"date1Form\">
					".dateFormSelect(null,"date1",$curInfo["date1"],null)."
					</div>

					<br>
					
					<div class=\"formHeader\" id=\"toText\" style=\"top:0px;left:0px;visibility:hidden;position:absolute\">"._TO." (".DATE_FORMAT."):<br>
					".dateFormSelect(null,"date2",$curInfo["date2"],null)."
					</div>

				</td></tr>
				</table>
				<br>
				<input type=submit name=\"update\" id=\"update\" value=\""._UPDATE."\">
			</form>

			</div>

	<!-- create the javascript to show the date fields -->
	<script language=\"javascript\">\n
	swapDate('".$curInfo["date_option"]."');\n
	</script>\n

	";

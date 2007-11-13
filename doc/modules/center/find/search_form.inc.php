<?

//set our options for the form
if (!$search_option || in_array("file_contents",$search_option)) $contentChecked = " CHECKED ";
if (!$search_option || in_array("file_name",$search_option)) $nameChecked = " CHECKED ";
if (!$search_option || in_array("summary",$search_option)) $summaryChecked = " CHECKED ";

function genObjSelection($showObjects) {

	$num = OBJECT_NUMBER;
	$arr = $_SESSION["siteObjectList"];
	$str = null;
	
	for ($i=0;$i<$num;$i++) {
	
		$val = $arr["link_name"][$i];
		$name = constant(strtoupper("_MT_".$arr["link_name"][$i]));

		if (!$showObjects || in_array($val,$showObjects)) $checked = " CHECKED ";
		else $checked = null;
		
		$str .= "<div class=\"optionSelect\">
			<input type=checkbox ".$checked." name=\"showObjects[]\" id=\"showObjects[]\" value=\"".$val."\">
			".$name."
			</div>
			";						
	
	}

	return $str;
}

//which limit result dropdown is selected?
if (!$limitResults) $limitResults = RESULTS_PER_PAGE;

if ($limitResults=="10") $limit10 = " SELECTED ";
elseif ($limitResults=="25") $limit25 = " SELECTED ";
elseif ($limitResults=="50") $limit50 = " SELECTED ";

if ($mod_option=="enter") $modEnterSelect = " SELECTED ";

if ($date_option=="before") $dateBeforeSelect = " SELECTED ";
if ($date_option=="after") $dateAfterSelect = " SELECTED ";
if ($date_option=="single") $dateSingleSelect = " SELECTED ";
if ($date_option=="period") $datePeriodSelect = " SELECTED ";

//setup our visibility for normal or keyword searches
if ($searchType=="keyword") {
	$kStyle = "visibility:visible;position:static";
	$nStyle = "visibility:hidden;position:absolute";
} else {
	$kStyle = "visibility:hidden;position:absolute";
	$nStyle = "visibility:visible;position:static";
}	

//setup keyword search
$keyArr = returnKeywords();
$num = count($keyArr);

/*
				<span style=\"white-space:nowrap;padding-right:8px;\">
				<input type=checkbox name=\"showObjects[]\" id=\"showObjects[]\" value=\"2\" ".$fileChecked."> "._FILES."
				</span>
				<span style=\"white-space:nowrap;padding-right:8px;\">
				<input type=checkbox name=\"showObjects[]\" id=\"showObjects[]\" value=\"1\" ".$colChecked.">&nbsp;"._COLLECTIONS."
				</span>
				<span style=\"white-space:nowrap;padding-right:8px;\">
				<input type=checkbox name=\"showObjects[]\" id=\"showObjects[]\" value=\"3\" ".$urlChecked.">&nbsp;"._URLS."
				</span>
				<span style=\"white-space:nowrap;padding-right:8px;\">
				<input type=checkbox name=\"showObjects[]\" id=\"showObjects[]\" value=\"4\" ".$ssChecked.">&nbsp;"._SAVESEARCH."
				</span>
*/

if (is_array($keyArr) && $num > 0) {
	
	$metaRow = "<tr id=\"keywordOption\" style=\"".$kStyle."\">
			<td class=\"searchCriteria\" width=\"100%\" colspan=2>
			<div class=\"formHeader\">
			"._SEARCH_IN."
			</div>

			<select name=\"metaOption\" id=\"metaOption\">
			<option value=\"_allkeywords\">"._ALL_KEYWORDS."\n";

	foreach ($keyArr AS $keyword) {

		$name = $keyword["name"];

		if ($metaOption==$name) $select = " SELECTED ";
		else $select = null;
		
		$metaRow .= "<option ".$select." value=\"".$name."\">".$keyword["title"]."\n";
	}

	$metaRow .= "</select>
			</td></tr>\n";

	if ($searchType=="keyword") $keywordSelect = " SELECTED ";

	$searchTypeStr .= "	<div class=\"formHeader\">
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


$display .= "

		<form name=\"searchForm\" method=\"get\">
		<input type=hidden name=\"module\" id=\"module\" value=\"find\">
		<input type=hidden name=\"advance_search\" id=\"advance_search\" value=\"1\">
		<input type=hidden name=\"pageAction\" id=\"pageAction\" value=\"search\">
		<input type=hidden name=\"newCategory\" id=\"newCategory\" value=\"\">
		<input type=hidden name=\"saveViewParent\" id=\"saveViewParent\" value=\"".$view_parent."\">
		<input type=hidden name=\"beginLimit\" id=\"beginLimit\" value=\"".$beginLimit."\">
		<input type=hidden name=\"curPage\" id=\"curPage\" value=\"".$curPage."\">
		<input type=hidden name=\"objectId\" id=\"objectId\" value=\"".$objectId."\">
		<input type=hidden name=\"colFilterId\" id=\"colFilterId\" value=\"".$colFilterId."\">
		<input type=hidden name=\"accountFilterId\" id=\"accountFilterId\" value=\"".$accountFilterId."\">

		<div style=\"width:100%;\">
			<table width=100% cellpadding=0 cellspacing=0>
			<tr><td class=\"searchResultsLeftTitle\">
			&nbsp;&nbsp;"._ADVANCE_DOC_SEARCH."
			</td><td class=\"searchResultsRightTitle\">
			&nbsp;
			</td></tr></table>
	
		<table class=\"searchForm\" border=0 width=100%>

		<tr><td class=\"searchFormCell\">

			".$errorStr."

			<table width=100% cellpadding=0 cellspacing=0 border=0>
			<tr><td width=50% class=\"searchCriteria\">
				<div class=\"formHeader\">
				"._SEARCH_FOR_WILDCARD.":
				</div>
				<input type=text name=\"searchString\" value=\"".stripsan($searchString)."\" size=50>
			</td><td width=50% class=\"searchCriteria\">
				".$searchTypeStr."
			</td></tr>
			
			<tr id=\"normalOption\" style=\"".$nStyle."\">
			<td width=50% valign=top class=\"searchCriteria\">

				<div class=\"formHeader\">
				"._SEARCH_IN.":
				</div>
				<div class=\"optionSelect\">
				<input type=checkbox name=\"search_option[]\" value=\"file_contents\" ".$contentChecked.">&nbsp;"._FILE_CONTENTS."
				</div><div class=\"optionSelect\">
				<input type=checkbox name=\"search_option[]\" value=\"file_name\" ".$nameChecked.">&nbsp;"._FILE_NAME."
				</div><div class=\"optionSelect\">
				<input type=checkbox name=\"search_option[]\" value=\"summary\" ".$summaryChecked.">&nbsp;"._FILE_SUMMARY."
				</div>
				
			</td><td width=50% valign=top class=\"searchCriteria\">

				<div class=\"formHeader\">			
				"._SHOW_MATCHING.":
				</div>
				".genObjSelection($showObjects)."
				
			</td></tr>

			".$metaRow."
			
			<tr><td class=\"searchCriteria\">
				
				<div class=\"formHeader\">
				"._OWNED_BY_USER.":
				</div>
				<input READONLY type=text name=\"accountFilter\" id=\"accountFilter\" value=\"".$accountFilter."\">
				<input type=button value=\"...\" onClick=\"selectAccount();\">
				
			</td><td class=\"searchCriteria\">
			
				<div class=\"formHeader\">
				"._IN_COLLECTION.":
				</div>
				<input READONLY type=text name=\"colFilter\" id=\"colFilter\" value=\"".$colFilter."\">
				<input type=button value=\"...\" onClick=\"selectCollection();\">
				
			</td></tr>
			<tr><td colspan=2 class=\"searchCriteria\">

				<table width=100% cellpadding=0 cellspacing=0>
				<tr><td width=25%>

					<div class=\"formHeader\">
					"._WHEN_FILE_WAS.":
					</div>
					<select name=\"mod_option\" id=\"mod_option\">
					<option value=\"last\">"._LAST_MOD."
					<option value=\"enter\">"._ENTER_INTO_SYSTEM."
					</select>	

				</td><td width=25%>

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
					
				</td><td width=50%>
					<table cellpadding=0 cellspacing=0 width=100%>
					<tr><td width=50%>
						<div class=\"formHeader\" id=\"fromText\" style=\"top:0px;left:0px;visibility:hidden;position:absolute\">"._FROM." (".DATE_FORMAT."):</div>
						<div class=\"formHeader\" id=\"onText\" style=\"top:0px;left:0px;visibility:hidden;position:absolute\">"._ON." (".DATE_FORMAT."):</div>
						<div class=\"formHeader\" id=\"beforeText\" style=\"top:0px;left:0px;visibility:hidden;position:absolute\">"._BEFORE." (".DATE_FORMAT."):</div>
						<div class=\"formHeader\" id=\"afterText\" style=\"top:0px;left:0px;visibility:hidden;position:absolute\">"._AFTER." (".DATE_FORMAT."):</div>
						<div style=\"top:0px;left:0px;visibility:hidden;position:absolute\" id=\"date1Form\">
						".dateFormSelect(null,"date1",$date1,null)."
						</div>
					</td><td width=50%>
						<div class=\"formHeader\" id=\"toText\" style=\"top:0px;left:0px;visibility:hidden;position:absolute\">"._TO." (".DATE_FORMAT."):<br>
						".dateFormSelect(null,"date2",$date2,null)."
						</div>
					</td></tr>
					</table>
				</td></tr>
				</table>
			</td></tr>
			<tr><td class=\"searchCriteria\">

				<div class=\"formHeader\">
				"._LIMIT_TO.":<br>
				</div>
				<select name=\"limitResults\" id=\"limitResults\">
				<option value=\"10\" ".$limit10.">10 "._RESULT_PER_PAGE."
				<option value=\"25\" ".$limit25.">25 "._RESULT_PER_PAGE."
				<option value=\"50\" ".$limit50.">50 "._RESULT_PER_PAGE."
				</select>

			</td><td class=\"searchCriteria\">
			
				<input type=\"submit\" name=\"search\" value=\""._SEARCH_FILES."\">
				&nbsp;&nbsp;
				<input type=\"button\" onClick=\"location.href='index.php?module=find&clearForm=yes';\" name=\"reset\" value=\""._RESET_FORM."\">
	
			</td></tr>
			</table>
		
		</td></tr>				
		</table>
		</form>
	</td></tr>
	<!-- end date form -->
	</table>
	</div>
	<!-- create the javascript to show the date fields -->
	<script language=\"javascript\">\n
	swapDate('".$date_option."');\n
	</script>\n

	";


?>

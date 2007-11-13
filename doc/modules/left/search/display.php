<?

//if we are viewing a category, allow the user to restrict the search
//to all files in and below this collection
if ($_REQUEST["view_parent"]) {
	$colStyle = "visibility:visible;position:static;";
	$colVal = "1";
} else {
	$colStyle = "visibility:hidden;position:absolute;";
	$colVal = null;
}


$content = "

	<form name=search method=get>
	<input type=hidden name=\"module\" id=\"module\" value=\"find\">
	<input type=hidden name=\"pageAction\" id=\"pageAction\" value=\"search\">
	<input type=hidden name=\"clearForm\" id=\"clearForm\" value=\"1\">
	<input type=hidden name=\"limitResults\" id=\"limitResults\" value=\"".RESULTS_PER_PAGE."\">
	<input type=hidden name=\"limitColValue\" id=\"limitColValue\" value=\"".$_REQUEST["view_parent"]."\">

	<div style=\"margin-right:5px;margin-top:5px\">

	<div style=\"padding-left:2px;\">
	<input type=text name=searchString value=\"".stripsan($searchString)."\" size=23 style=\"font-size:10px;height:18px\">
	</div>
	<div style=\"".$colStyle."padding-top:2px;padding-bottom:2px;white-space:nowrap;\" id=\"searchCollection\">
	<input type=checkbox name=\"limitCol\" id=\"limitCol\" value=\"".$colVal."\">
	"._WITHIN_COLLECTION."
	</div>
	<div style=\"float:right\">
		<a href=\"index.php?module=find\" class=main>"._ADVANCED."</a>
	</div>
	<div>
		<input type=submit name=search value=\""._SEARCH."\" class=\"searchButton\">
	</div>
	<div class=\"cleaner\"></div>
	</div>
	</form>	

	";

$option = null;
$option["leftHeader"] = _SEARCH_FOR_FILES;
$option["content"] = $content;

$leftColumnContent .= leftColumnDisplay($option);
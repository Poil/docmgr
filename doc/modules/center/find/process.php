<?

include("app/search_function.inc.php");
include("app/finder.inc.php");

$pageAction = $_REQUEST["pageAction"];

$sessionKeys = array("date1","date2","searchString","metaString","metaOption",
					"showObjects","searchCount","search_option","mod_option",
					"date_option","limitResults","limitColValue","limitCol","showRank",
					"searchType","accountFilterId","accountFilter","colFilterId","colFilter",
					"metaOption");

//if we arrive from a link, clear everything
if ($_REQUEST["clearForm"] || (!$pageAction && !$_REQUEST["searchAgain"])) {

	//null out our sessions
	foreach ($sessionKeys AS $key) $_SESSION[$key] = null;

}


//setup our variables.  Form posts take precedence over sessions.  But, they are
//all saved in sessions for jumping from page result to page result.
if ($pageAction && !$saveSearch) {

	//pull our values from the url
	foreach ($sessionKeys AS $key) {
		if ($_GET[$key]!=NULL) $_SESSION[$key] = $_GET[$key];
	}

}

//pull our variables from the sessions for passing to our functions
foreach ($sessionKeys AS $key) $$key = &$_SESSION[$key];

//change our view if requested by the user, otherwise rely on the config file setting
if ($_REQUEST["pageView"]) $_SESSION["pageView"] = $_REQUEST["pageView"];
if (!$_SESSION["pageView"]) $_SESSION["pageView"] = DEFAULT_BROWSE_VIEW;

//our sorting defaults
if ($_REQUEST["sortField"]) $sortField = $_REQUEST["sortField"];
if ($_REQUEST["sortDir"]) $sortDir = $_REQUEST["sortDir"];
if ($_REQUEST["curPage"]) $curPage = $_REQUEST["curPage"];

//reset our search count
if (!$curPage && !$pageAction) $_SESSION["searchCount"] = null;

//default result limt per page
if (!$limitResults) $limitResults = RESULTS_PER_PAGE;

if ($pageAction=="search") {

	//user submitted a form with nothing in it
	if ($pageAction=="search" && !$_SESSION["searchString"]) {

		$num_results = "0";
		$display = "<div style=\"padding:5px\" class=\"errorMessage\">&nbsp;&nbsp;&nbsp;"._SEARCH_ERROR."!</div>";
		$pageAction = null;

	}
	else {

		//set default options for left column search
		if (!$search_option) $search_option = array("summary","file_contents","file_name");
		if (!$date_option) $date_option = "any";
		if (!$mod_option) $mod_option = "last";

		//fix the date if a user picks a date option but doesn't enter the date
		if (($date_option=="before" || $date_option=="single" || $date_option=="after") && !$date1) $date_option = "any";
		if ($date_option=="period" && (!$date1 || !$date2)) $date_option = "any";
		
		$pageAction = "search";
		$search_object = $searchString;

		$view_parent = null;

		//we need to save
		//limitResults, totalCount, curPage

		if (!$curPage) {

			$searchCount = null;
			$offset = "0";

		} else $offset = ($curPage-1) * $limitResults;

		//load our search options 
		$opt = null;
		$opt["conn"] = $conn;
		$opt["string"] = $searchString;
		$opt["search_option"] = $search_option;
		$opt["showObjects"] = $showObjects;
		$opt["mod_option"] = $mod_option;
		$opt["date_option"] = $date_option;
		$opt["filter_option"] = $filter_option;
		$opt["date1"] = $date1;
		$opt["date2"] = $date2;
		$opt["limit"] = $limitResults;
		$opt["offset"] = $offset;
		$opt["searchType"] = $searchType;
		$opt["metaOption"] = $metaOption;
		$opt["sortField"] = $sortField;
		$opt["sortDir"] = $sortDir;

		//restrict to files within current column
		if ($limitCol) $opt["limitColValue"] = $limitColValue;
		else if ($colFilterId) $opt["limitColValue"] = $colFilterId;

		//restrict to certain account owners
		if ($accountFilterId) $opt["limitAccountValue"] = $accountFilterId;

		//execute our search		
		$objectArray = execSearch($opt);
		
		//set a variable for our number of results
		$searchCount = $_SESSION["searchCount"];

		//create our results navigation and toolbar
		$functionBar = createFindFunction($_SESSION["searchCount"]);
		$toolBar = createFindToolbar($searchString,$_SESSION["searchCount"],$curPage,$objectArray["timeCount"]);
		$pageBar = createFinderNav($_SESSION["searchCount"],$curPage);

		$display = showObjects($conn,$objectArray,null,1);

	}

	
}


if (!$pageAction || !$_SESSION["searchCount"]) include("search_form.inc.php");

//save our url
if ($_SESSION["searchCount"] > 0) $_SESSION["saveUrl"] = $_SERVER["REQUEST_URI"];
else $_SESSION["saveUrl"] = null;

?>

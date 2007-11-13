<?

include("app/search_function.inc.php");

$pageAction = $_REQUEST["pageAction"];
$objectId = $_REQUEST["objectId"];

$sql = "SELECT * FROM dm_savesearch WHERE object_id='$objectId'";
$info = single_result($conn,$sql);

if ($info["show_objects"]) $_SESSION["showObjects"] = explode(",",$info["show_objects"]);

if ($info["search_option"]) $_SESSION["search_option"] = explode("|",$info["search_option"]);
else $_SESSION["search_option"] = null;


$_SESSION["date1"] = $info["date1"];
$_SESSION["date2"] = $info["date2"];
$_SESSION["searchString"] = sanitize($info["search_string"]);
$_SESSION["date_option"] = $info["date_option"];
$_SESSION["mod_option"] = $info["mod_option"];
$_SESSION["metaOption"] = $info["meta_option"];
$_SESSION["searchType"] = $info["search_type"];
$_SESSION["accountFilter"] = $info["account_filter"];
$_SESSION["accountFilterId"] = $info["account_filter_id"];
$_SESSION["colFilter"] = $info["col_filter"];
$_SESSION["colFilterId"] = $info["col_filter_id"];


//pull our variables from the sessions for passing to our functions
$date1 			= 	&$_SESSION["date1"];
$date2 			= 	&$_SESSION["date2"];
$searchString 		= 	&$_SESSION["searchString"];
$metaString 		= 	&$_SESSION["metaString"];
$metaOption 		= 	&$_SESSION["metaOption"];
$showObjects 	= 	&$_SESSION["showObjects"];
$search_option 		= 	&$_SESSION["search_option"];
$mod_option 		= 	&$_SESSION["mod_option"];
$date_option 		= 	&$_SESSION["date_option"];
$limitResults 		= 	&$_SESSION["limitResults"];
$limitColValue 		= 	&$_SESSION["limitColValue"];
$limitCol 		= 	&$_SESSION["limitCol"];
$curPage		=	&$_GET["curPage"];
$searchType		=	&$_SESSION["searchType"];
$accountFilter		=	&$_SESSION["accountFilter"];
$accountFilterId	=	&$_SESSION["accountFilterId"];
$colFilter		=	&$_SESSION["colFilter"];
$colFilterId		=	&$_SESSION["colFilterId"];
$metaOption		= 	&$_SESSION["metaOption"];

//change our view if requested by the user, otherwise rely on the config file setting
if ($_REQUEST["pageView"]) $_SESSION["pageView"] = $_REQUEST["pageView"];
if (!$_SESSION["pageView"]) $_SESSION["pageView"] = DEFAULT_BROWSE_VIEW;

//our sorting defaults
if ($_REQUEST["sortField"]) $sortField = $_REQUEST["sortField"];
if ($_REQUEST["sortDir"]) $sortDir = $_REQUEST["sortDir"];
if ($_REQUEST["curPage"]) $curPage = $_REQUEST["curPage"];

//default result limt per page
if (!$limitResults) $limitResults = RESULTS_PER_PAGE;

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

$display = showObjects($conn,$objectArray,null,1);		

//create our results navigation and toolbar
$arr = createFindNav($searchString,$_SESSION["searchCount"],$objectArray["timeCount"],$curPage,$limitResults);
$functionBar = &$arr["function"];
$pageBar = &$arr["page"];

$sql = "SELECT name,parent_id FROM dm_view_objects WHERE object_id='$objectId'";
$sInfo = single_result($conn,$sql);
	
//save our url
if ($_SESSION["searchCount"] > 0) $_SESSION["saveUrl"] = $_SERVER["REQUEST_URI"];
else $_SESSION["saveUrl"] = null;

?>

<?

//for processing searches, moves, deletes, and the like
include("app/search_function.inc.php");
include("app/finder.inc.php");

//get the collection we are viewing
$view_parent = $_REQUEST["view_parent"];
if ($view_parent==NULL) {

  if ($_SESSION["homeDir"]) {

    $view_parent = $_SESSION["homeDir"];
    //set permissions for this home directory
    $cb = returnUserObjectPerms($conn,$_SESSION["homeDir"]);
    define("CUSTOM_BITSET",$cb);

  }        
  else $view_parent="0";
}

//if we are viewing a different parent, reset the counts
if ($_REQUEST["view_parent"]!=$_SESSION["view_parent"] || !$_REQUEST["view_parent"]) {
  $_SESSION["searchCount"] = null;
}

//prevent ranks from showing up if we just did a search
$_SESSION["showRank"] = null;

//our sorting defaults
if ($_REQUEST["sortField"]) $sortField = $_REQUEST["sortField"];
if ($_REQUEST["sortDir"]) $sortDir = $_REQUEST["sortDir"];

//change our view if requested by the user, otherwise rely on the config file setting
if ($_REQUEST["pageView"]) $_SESSION["pageView"] = $_REQUEST["pageView"];
if (!$_SESSION["pageView"]) $_SESSION["pageView"] = DEFAULT_BROWSE_VIEW;

//get our results for this category and put them in an array
$objectArray = execCategory($conn,$view_parent,$sortField,$sortDir,$_REQUEST["curPage"]);

//display our files
$display = showObjects($conn,$objectArray,$view_parent,1);

//save our url
if ($objectArray["count"] > 0) $_SESSION["saveUrl"] = $REQUEST_URI;
else $_SESSION["saveUrl"] = null;

?>

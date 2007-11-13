<? 

$option = null;
$option["conn"] = $conn;
$option["mode"] = "link";
$option["curValue"] = $_REQUEST["view_parent"];
$option["divName"] = "collectionTree";
$string = "<div class=\"browseTree\" id=\"collectionTree\"></div>\n";
$string .= tree_view($option);

$option = null;
$option["content"] = $string;
$option["leftHeader"] = _BROWSE_COL;

$leftColumnContent .= leftColumnDisplay($option);

#!/usr/local/bin/php

<?
/********************************************************
	This file will reindex your files stored in
	docmgr.  Please set your dirPath below
	and run this script at the command line
	by typing "php reindex.php".
*********************************************************/

die("You must comment me out first\n");

//path to docmgr data directory
$dirPath = "/www/doc";

/*********************************************************
	don't modify anything below this line
*********************************************************/

define("ALT_FILE_PATH",$dirPath);

//include our config file
include($dirPath."/config/config.php");
include($dirPath."/config/app-config.php");
include($dirPath."/header/callheader.php");

//set our path defines
$filePath = getFilePath(FILE_DIR,$dirPath);

define("ALT_FILE_PATH",$dirPath);
define("DATA_DIR",$filePath."/data");
define("TMP_DIR",$filePath."/tmp");
define("DOC_DIR",$filePath."/document");
define("THUMB_DIR",$filePath."/thumbnails");
define("PREVIEW_DIR",$filePath."/preview");

include($dirPath."/app/common.inc.php");
include($dirPath."/app/object.inc.php");
include($dirPath."/app/index_function.inc.php");

$siteModArr = loadSiteStructure($dirPath."/modules/center/");
$_SESSION["siteModList"] = $siteModArr["list"];
$_SESSION["siteModInfo"] = $siteModArr["info"];

//configure DocMGR to use avail external apps
setExternalApps();
loadObjects();

$conn = db_connect(DBHOST,DBUSER,DBPASSWORD,DBPORT,DBNAME);

if ($argc > 1) {
	echo "Reindexing object ".$argv[1]."\n";

	//clear our our current index
	$sql = "DELETE FROM dm_index WHERE object_id='".$argv[1]."'";
	db_query($conn,$sql);

	$sql = "SELECT * FROM dm_object WHERE id='".$argv[1]."'";
	$list = total_result($conn,$sql);

} else {

	//clear our our current index
	$sql = "DELETE FROM dm_index";
	db_query($conn,$sql);

	$sql = "SELECT * FROM dm_object";
	$list = total_result($conn,$sql);

}

$num = count($list["id"]);

for ($row=0;$row<$num;$row++) {

	$obj = $list["id"][$row];
	$name = $list["name"][$row];

	echo "Now adding \"".$name."\" to the queue\n";
	indexObject($conn,$obj,0);

}

if (defined("DISABLE_BACKINDEX")) echo "Reindexing complete\n";
else echo "All files have been added to the queue.  The indexing process will be complete when docmgr-indexer.php is no longer running\n";


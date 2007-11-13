#!/usr/local/bin/php

<?

die("You must uncomment this line first\n");

$dirPath = "/www/doc";
define("USER_ID","1");

/***************************************************
	don't modify anything below this line
***************************************************/

define("ALT_FILE_PATH",$dirPath);

//include our config file
include($dirPath."/config/config.php");
include($dirPath."/config/app-config.php");

//our libs
include($dirPath."/header/callheader.php");

//set our path defines
$filePath = getFilePath(FILE_DIR,$dirPath);

define("DATA_DIR",$filePath."/data");
define("TMP_DIR",$filePath."/tmp");
define("THUMB_DIR",$filePath."/thumbnails");
define("PREVIEW_DIR",$filePath."/preview");

//call our primary includes
include($dirPath."/app/common.inc.php");
include($dirPath."/app/object.inc.php");
include($dirPath."/app/thumb_function.inc.php");

$conn = db_connect(DBHOST,DBUSER,DBPASSWORD,DBPORT,DBNAME);

$siteModArr = loadSiteStructure(ALT_FILE_PATH."/modules/center/");
$_SESSION["siteModList"] = $siteModArr["list"];
$_SESSION["siteModInfo"] = $siteModArr["info"];
     
//configure DocMGR to use avail external apps
setExternalApps();
loadObjects();

$sql = "SELECT id,name,summary,version,object_type FROM dm_view_objects";
$list = total_result($conn,$sql);

if (!defined("THUMB_SUPPORT")) die ("Thumbnail support disabled\n");

for ($row=0;$row<$list["count"];$row++) {

	$objectId 	= 	&$list["id"][$row];
	$fileName	=	&$list["name"][$row];
	
	echo "Now creating thumbnail for ".$fileName."\n";
	thumbObject($conn,$objectId);

}

if (defined("DISABLE_BACKTHUMB")) echo "Thumbnailing complete\n";
else echo "All files have been added to the queue.  The thumbnail process will be complete when docmgr-thumbnail.php is no longer running\n";



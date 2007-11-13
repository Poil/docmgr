#!/usr/local/bin/php

<?

die("You must uncomment this line first\n");

$dirPath = "/www/doc";

/***************************************************
	don't modify anything below this line
***************************************************/

define("ALT_FILE_PATH",$dirPath);

//include our config file
include($dirPath."/config/config.php");
include($dirPath."/config/app-config.php");
include($dirPath."/header/callheader.php");
include($dirPath."/app/common.inc.php");

//set our path defines
$filePath = getFilePath(FILE_DIR,$dirPath);

define("DATA_DIR",$filePath."/data");
define("TMP_DIR",$filePath."/tmp");
define("THUMB_DIR",$filePath."/thumbnails");
define("PREVIEW_DIR",$filePath."/preview");
define("DOC_DIR",$filePath."/document");

createFileSubDir(DATA_DIR);
createFileSubDir(THUMB_DIR);
createFileSubDir(PREVIEW_DIR);
createFileSubDir(DOC_DIR);
        

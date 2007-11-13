#!/usr/local/bin/php

<?
/********************************************************
	This file will reset your files' md5 checksums
	 stored in docmgr.  Please set your dirPath below
	and run this script at the command line
	by typing "php checksum.php".
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

include($dirPath."/lib/accperms.php");
include($dirPath."/lib/arrays.php");
include($dirPath."/lib/calc.php");
include($dirPath."/lib/customforms.php");
include($dirPath."/lib/data_formatting.php");
include($dirPath."/lib/email.php");
include($dirPath."/lib/filefunctions.php");
include($dirPath."/lib/misc.php");
include($dirPath."/lib/modules.php");
include($dirPath."/lib/postgresql.php");
include($dirPath."/lib/presentsite.php");
include($dirPath."/lib/sanitize.php");
include($dirPath."/lib/xml.php");

//set our path defines
$filePath = getFilePath(FILE_DIR,$dirPath);

define("ALT_FILE_PATH",$dirPath);
define("DATA_DIR",$filePath."/data");
define("DOC_DIR",$filePath."/document");
define("TMP_DIR",$filePath."/tmp");
define("THUMB_DIR",$filePath."/thumbnails");

include($dirPath."/app/common.inc.php");

$conn = db_connect(DBHOST,DBUSER,DBPASSWORD,DBPORT,DBNAME);

//first, update the md5sums for all files
$sql = "SELECT dm_file_history.*,dm_dirlevel.level1,dm_dirlevel.level2 FROM dm_file_history
               LEFT JOIN dm_dirlevel ON (dm_file_history.object_id = dm_dirlevel.object_id)
               ";
$list = list_result($conn,$sql);

echo "Updating file checksums\n";

for ($i=0;$i<$list["count"];$i++) {

    $id = $list[$i]["id"];
    $l1 = $list[$i]["level1"];
    $l2 = $list[$i]["level2"];
    
    $file = DATA_DIR."/".$l1."/".$l2."/".$id.".docmgr";
    $md5sum = md5_file($file);
    
    $sql = "UPDATE dm_file_history SET md5sum='$md5sum' WHERE id='$id'";
    if (!db_query($conn,$sql)) echo "There was an error updating file ".$id."\n";

}

echo "File checksum update complete\n";
echo "Fixing revision history\n";

//get all objects
$sql = "SELECT * FROM dm_object WHERE object_type='file'";
$list = list_result($conn,$sql);

for ($i=0;$i<$list["count"];$i++) {

  $objectId = $list[$i]["id"];

  //get all files belong to this object, with the highest version
  $sql = "SELECT * FROM dm_file_history WHERE object_id='$objectId' ORDER BY version DESC";
  $info = single_result($conn,$sql);

  $sql = "UPDATE dm_object SET version='".$info["version"]."' WHERE id='$objectId'";
  if (!db_query($conn,$sql)) echo "There was an error updating version info for ".$list[$i]["name"]."\n";

}

echo "Revision history update complete\n";





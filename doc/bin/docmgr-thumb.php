<?php

/******************************************************************************
  Indexer script.  It will only work if called from the root docmgr directory
******************************************************************************/

/******************************************************************************
    preliminary configuration and variable setting
******************************************************************************/

//get our includes
//first call the config file to get our settings, call our base functions, and get our wrapper
include("config/config.php");
include("config/app-config.php");

//the rest of our includes with our base functions
include("header/callheader.php");
include("app/common.inc.php");
include("app/object.inc.php");
include("app/thumb_function.inc.php");

//set our path defines
define("DATA_DIR",FILE_DIR."/data");
define("THUMB_DIR",FILE_DIR."/thumbnails");
define("PREVIEW_DIR",FILE_DIR."/preview");
define("TMP_DIR",FILE_DIR."/tmp");

//set to use short tags
ini_set("short_open_tag","1");

//load our center modules to see the objects
$siteModArr = loadSiteStructure("modules/center/");
$_SESSION["siteModList"] = $siteModArr["list"];
$_SESSION["siteModInfo"] = $siteModArr["info"];
$siteModList = &$_SESSION["siteModList"];
$siteModInfo = &$_SESSION["siteModInfo"];
        
//load our objects so we can call their indexing functions
loadObjects();

//setup which apps are available to docmgr
setExternalApps();

//connect to the db
$conn = db_connect(DBHOST,DBUSER,DBPASSWORD,DBPORT,DBNAME);

//allow indexing of a certain objectId.  This is for debugging only
if (in_array("--thumb-object",$argv)) {

  //we are looking for the id passed after our parameter
  $key = array_search("--thumb-object",$argv) + 1;
  $obj = $argv[$key];
  
  if ($obj) {

    //index the item    
    echo "Thumbnailing and Creating preview for object ".$obj."\n";
    runObjectThumb($conn,$obj);

  } else echo "Invalid object id passed\n";
  
  die;
  
}


/*******************************************************************************
    Now it's time to process our batch.  We continue to run until
    there are no more batches left
*******************************************************************************/


while (1) {

    //get the ids of all the objects in this batch
    $sql = "SELECT * FROM dm_thumb_queue ORDER BY id";
    $list = total_result($conn,$sql);
    $objArr = &$list["object_id"];

    debugMsg(1,"No objects found in the queue.  Exiting");

    //get out if there's nothing to do
    if (!$list["count"]) exit;

    debugMsg(1,$list["count"]." objects found in the queue.  Proceeding to thumbnail");

    for ($i=0;$i<$list["count"];$i++) {
      
      $obj = $list["object_id"][$i];
      $id = $list["id"][$i];

      //index the item    
      runObjectThumb($conn,$obj);

      //delete this item from the queue
      $sql = "DELETE FROM dm_thumb_queue WHERE id='$id'";
      db_query($conn,$sql);

    }

    //sleep 10 seconds before checking for another batch
    sleep(5);

}


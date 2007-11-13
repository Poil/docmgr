<?php


//create a temporary id field in dm_object
$sql = "ALTER TABLE dm_object ADD COLUMN old_obj_id integer";
db_query($newconn,$sql);


/***************************************************************
  update the files
***************************************************************/

echo "Migrating Files\n";

//insert the files into the new database
$sql = "SELECT * FROM dm_object";
$list = list_result($oldconn,$sql);

for ($row=0;$row<$list["count"];$row++) {

  $id = $list[$row]["id"];

  if (!$list[$row]["status_date"]) $list[$row]["status_date"] = date("Y-m-d H:i:s");
  if (!$list[$row]["create_date"]) $list[$row]["create_date"] = date("Y-m-d H:i:s");

  $option = null;
  $option["id"] = $id;
  $option["name"] = addslashes(stripslashes(stripslashes($list[$row]["name"])));  
  $option["summary"] = addslashes(stripslashes(stripslashes($list[$row]["summary"])));
  $option["version"] = $list[$row]["version"];
  $option["status"] = $list[$row]["status"];
  $option["status_date"] = $list[$row]["status_date"];
  $option["status_owner"] = $list[$row]["status_owner"];
  $option["create_date"] = $list[$row]["create_date"];
  $option["object_owner"] = $list[$row]["file_owner"];	//the account that created this
  $option["object_type"] = 2;
  $option["reindex"] = $list[$row]["reindex"];
  $option["old_obj_id"] = $list[$row]["id"];

  if (!$option["status_date"]) $option["status_date"] = date("Y-m-d H:i:s");

  dbInsertQuery($newconn,"dm_object",$option);

}

fixObjSeq($newconn);

echo "Migrating categories\n";


/***************************************************************
  update the categories
***************************************************************/
$sql = "SELECT * FROM dm_category";
$list = list_result($oldconn,$sql);

for ($row=0;$row<$list["count"];$row++) {


  $option = null;
  $option["name"] = addslashes($list[$row]["name"]);  
  $option["summary"] = addslashes($list[$row]["description"]);
  $option["object_owner"] = $list[$row]["cat_owner"];	//the account that created this
  $option["status_date"] = $list[$row]["modified"];
  $option["status"] = "0";
  $option["object_type"] = 1;
  $option["old_obj_id"] = $list[$row]["id"];

  if (!$option["status_date"]) $option["status_date"] = date("Y-m-d H:i:s");

  $objId = dbInsertQuery($newconn,"dm_object",$option);

}

fixObjSeq($newconn);

/***************************************************************
  update the link between the categories and their parents
***************************************************************/

//first find all unique owners of our categories
$sql = "SELECT DISTINCT owner FROM dm_category";
$list = list_result($oldconn,$sql);

for ($row=0;$row<$list["count"];$row++) {

  $oldOwner = $list[$row]["owner"];

  //find out the new id of our owner if not "0"
  if ($oldOwner=="0") $ownerId = "0";
  else {

    $sql = "SELECT id FROM dm_object WHERE old_obj_id='".$oldOwner."' AND object_type='1';";
    $owner = single_result($newconn,$sql);
    $ownerId = $owner["id"];

  }

  if (!$ownerId) $ownerId = "0";

  //get all categories that belonged to this owner
  $sql = "SELECT * FROM dm_category WHERE owner='".$oldOwner."'";
  $catList = list_result($oldconn,$sql);
  
  $sql = null;
  
  //loop through the collections that belong to this owner and insert them into the database
  for ($i=0;$i<$catList["count"];$i++) {

    //find out the new id of this collection
    $sqlFind = "SELECT id FROM dm_object WHERE old_obj_id='".$catList[$i]["id"]."' AND object_type='1';";
    $info = single_result($newconn,$sqlFind);

    $sql .= "INSERT INTO dm_object_parent (object_id,parent_id) VALUES ('".$info["id"]."','".$ownerId."');";

  }

  if ($sql) db_query($newconn,$sql);
  
}


/***************************************************************
  update the link between the files and their parents
***************************************************************/

//first find all unique owners of our categories
$sql = "SELECT DISTINCT cat_id FROM dm_file_cat";
$list = list_result($oldconn,$sql);

for ($row=0;$row<$list["count"];$row++) {

  $oldOwner = $list[$row]["cat_id"];

  //find out the new id of our owner if not "0"
  if ($oldOwner=="0") $ownerId = "0";
  else {

    $sql = "SELECT id FROM dm_object WHERE old_obj_id='".$oldOwner."' AND object_type='1';";
    $owner = single_result($newconn,$sql);
    $ownerId = $owner["id"];

  }

  //get all categories that belonged to this owner
  $sql = "SELECT * FROM dm_file_cat WHERE cat_id='".$oldOwner."'";
  $fileList = list_result($oldconn,$sql);
  
  $sql = null;
  
  if (!$ownerId) $ownerId = "0"; 
  
  //loop through the collections that belong to this owner and insert them into the database
  for ($i=0;$i<$fileList["count"];$i++) {

    $sql .= "INSERT INTO dm_object_parent (object_id,parent_id) VALUES ('".$fileList[$i]["file_id"]."','".$ownerId."');";

  }

  if ($sql) db_query($newconn,$sql);
  
}

/**************************************************************************
  now permissions for the categories
  Manage -> 2
  bitset 3 -> 12 
  bitset 2 -> 8
**************************************************************************/

echo "Updating collection permissions\n";

$sql = "SELECT * FROM dm_cat_permissions";
$list = list_result($oldconn,$sql);

for ($row=0;$row<$list["count"];$row++) {

  $bitset = $list[$row]["bitset"];
  
  if ($bitset=="3") $bitset = "12";
  elseif ($bitset=="2") $bitset = "8";

  $sql = "SELECT id FROM dm_object WHERE old_obj_id='".$list[$row]["cat_id"]."' AND object_type='1';";
  $info = single_result($newconn,$sql);

  //if nothing is here, it came from a stale cat_perm entry
  if (!$info) continue;

  $opt = null;
  $opt["object_id"] = $info["id"];
  $opt["group_id"] = $list[$row]["group_id"];
  $opt["bitset"] = $bitset;

  //skip if this is an orphanned entry
  if (!checkObjExist($newconn,$info["id"])) continue; 
  
  dbInsertQuery($newconn,"dm_object_perm",$opt);    

}

echo "Updating file permissions\n";

//permissions for the files
$sql = "SELECT * FROM dm_file_permissions";
$list = list_result($oldconn,$sql);

for ($row=0;$row<$list["count"];$row++) {

    //skip if this is an orphanned entry
    if (!checkObjExist($newconn,$list[$row]["object_id"])) continue;

  $bitset = $list[$row]["bitset"];
  
  if ($bitset=="3") $bitset = "12";
  elseif ($bitset=="2") $bitset = "8";

  //get the group_id for this auth_object
  $sql = "SELECT id FROM auth_groups WHERE auth_objectid='".$list[$row]["auth_objectid"]."';";
  $info = single_result($oldconn,$sql);
  $groupId = $info["id"];
  
  $opt = null;
  $opt["object_id"] = $list[$row]["object_id"];
  $opt["group_id"] = $groupId;
  $opt["bitset"] = $bitset;

  dbInsertQuery($newconn,"dm_object_perm",$opt);    

}

echo "Migrating discussions\n";

//we need to migrate dm_discussion, dm_file_history, dm_file_log, dm_index

/**********************************************************
  migrate the discussion table
**********************************************************/

$sql = "SELECT * FROM dm_discussion";
$list = list_result($oldconn,$sql);

for ($i=0;$i<$list["count"];$i++) {

    //skip if this is an orphanned entry
    if (!checkObjExist($newconn,$list[$i]["object_id"])) continue;

  $opt = null;
  $opt["id"] = $list[$i]["id"];
  $opt["object_id"] = $list[$i]["object_id"];
  $opt["header"] = addslashes($list[$i]["header"]);
  $opt["account_id"] = $list[$i]["account_id"];
  $opt["content"] = addslashes($list[$i]["content"]);
  $opt["owner"] = $list[$i]["owner"];
  $opt["time_stamp"] = $list[$i]["date_stamp"];
  
  dbInsertQuery($newconn,"dm_discussion",$opt);
  
}

echo "Migrating file history\n";

/**********************************************************
  migrate the file history table
**********************************************************/

$sql = "SELECT * FROM dm_file_history";
$list = list_result($oldconn,$sql);

for ($i=0;$i<$list["count"];$i++) {

    //skip if this is an orphanned entry
    if (!checkObjExist($newconn,$list[$i]["object_id"])) continue;

  $opt = null;
  $opt["id"] = $list[$i]["id"];
  $opt["object_id"] = $list[$i]["object_id"];
  $opt["size"] = addslashes($list[$i]["size"]);
  $opt["version"] = $list[$i]["version"];
  $opt["modify"] = addslashes($list[$i]["modify"]);
  $opt["object_owner"] = $list[$i]["owner_id"];

  if (!$opt["size"]) $opt["size"] = "0";
  
  dbInsertQuery($newconn,"dm_file_history",$opt);
  
}


echo "Migrating Logs\n";

/**********************************************************
  migrate the file log table
**********************************************************/

$sql = "SELECT * FROM dm_file_log";
$list = list_result($oldconn,$sql);

for ($i=0;$i<$list["count"];$i++) {

  //figure out the log type
  $entry = $list[$i]["entry"];

  //skip if this is an orphanned entry
  if (!checkObjExist($newconn,$list[$i]["object_id"])) continue;

  if ($entry=="File uploaded into system" || $entry=="File imported into system") $logType = "OBJ_CREATED";
  else if (stristr($entry,"File emailed")) $logType = "OBJ_EMAILED";
  else if (stristr($entry,"File updated to version")) $logType = "OBJ_CHECKED_IN";
  else if (stristr($entry,"promoted to latest version")) $logType = "OBJ_VERSION_PROMOTE";
  else if (stristr($entry,"File checked out")) $logType = "OBJ_CHECKED_OUT";
  
  $opt = null;
  $opt["account_id"] = $list[$i]["account_id"];
  $opt["object_id"] = $list[$i]["object_id"];
  $opt["log_time"] = addslashes($list[$i]["date"]);
  $opt["log_type"] = $logType;
  
  dbInsertQuery($newconn,"dm_object_log",$opt);
  
}

echo "Migrating file size information\n";

$sql = "SELECT * FROM dm_object WHERE object_type='2';";
$list = list_result($newconn,$sql);

for ($row=0;$row<$list["count"];$row++) {

  $objId = $list[$row]["id"];

  $sql = "SELECT size FROM dm_file_history WHERE object_id='$objId' ORDER BY version DESC LIMIT 1";
  $info = single_result($newconn,$sql);

  $size = $info["size"];
  
  $sql = "UPDATE dm_object SET filesize='$size' WHERE id='$objId';";
  db_query($newconn,$sql);
  
}


echo "Migrating the index\n";

/**********************************************************
  migrate the index table
**********************************************************/

$sql = "SELECT * FROM dm_index";
$list = list_result($oldconn,$sql);

//if we don't find dm_index, assume this is an older version of docmgr
//and we need to perform a reindex
if ($list["count"]) {

  for ($i=0;$i<$list["count"];$i++) {

    //skip if this is an orphanned entry
    if (!checkObjExist($newconn,$list[$i]["object_id"])) continue;

    $opt = null;
    $opt["object_id"] = $list[$i]["object_id"];
    $opt["idxtext"] = addslashes($list[$i]["idxtext"]);
    if (defined("TSEARCH2_INDEX")) $opt["idxfti"] = addslashes($list[$i]["idxfti"]);
  
    dbInsertQuery($newconn,"dm_index",$opt);
  
  }

} else {

  echo "\n";
  echo "It looks like you are using a pre-0.47 release of docmgr.\n";
  echo "You will need to reindex your files after the database migration.\n";
  echo "Please see the scripts/reindex.php file for more information\n";
  echo "\n";
  
}


function checkObjExist($conn,$objId) {

  $sql = "SELECT id FROM dm_object WHERE id='$objId';";
  $info = single_result($conn,$sql);
  
  if ($info["id"]) return true;
  else return false;
  
}


function fixObjSeq($conn) {

  $sql = "SELECT id FROM dm_object ORDER BY id DESC LIMIT 1";
  $info = single_result($conn,$sql);
  
  $max = $info["id"];
 
  if (!$max) return false;
 
  $sql = "SELECT SETVAL('dm_object_id_seq','".$max."');";
  db_query($conn,$sql);

}


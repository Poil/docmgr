<?php

//fix all our sequences for these tables

echo "Cleaning up after ourselves\n";

fixSequence($newconn,"auth_accounts");
fixSequence($newconn,"auth_groups");
fixSequence($newconn,"dm_discussion");
fixSequence($newconn,"dm_file_history");
fixSequence($newconn,"dm_object");

$sql = "ALTER TABLE dm_object DROP COLUMN old_obj_id;";
db_query($newconn,$sql);

//vacuum the database
db_vacuum($newconn);

function fixSequence($conn,$table) {

  $seq = $table."_id_seq";
  
  $sql = "SELECT id FROM $table ORDER BY id DESC LIMIT 1";
  $info = single_result($conn,$sql);
  $val = $info["id"];

  if ($val) {
    $sql = "SELECT SETVAL('$seq','$val');";
    db_query($conn,$sql);
  }

}

<?php

echo "Dropping foreign key constraint.  One of these will produce an error.  You can ignore it.\n";

echo "Trying the named key\n";
$sql = "ALTER TABLE dm_object_log DROP CONSTRAINT \"dm_object_log_object_id_fkey\";";
db_query($conn,$sql);

echo "Trying the unnamed key\n";
$sql = "ALTER TABLE dm_object_log DROP CONSTRAINT \"\$1\";";
db_query($conn,$sql);

echo "\n\n";

$sql = "
ALTER TABLE dm_file_history ADD COLUMN md5sum TEXT;
ALTER TABLE dm_object_log ADD COLUMN log_data TEXT;
CREATE TABLE dm_email_anon (
object_id integer,
pin text,
link_encoded text,
date_expires timestamp without time zone,
account_id integer,
notify text,
dest_email text
);
";

if (db_query($conn,$sql)) echo "Database upgrade to 0.51 completed successfully\n";
else die("Database upgrade failed\n");

echo "Updating file checksum database\n";

$sql = "SELECT id FROM dm_file_history";
$list = list_result($conn,$sql);

for ($i=0;$i<$list["count"];$i++) {

  $id = $list[$i]["id"];

  $file = DATA_DIR."/".$id.".docmgr";

  if (!is_file($file)) continue;

  $checksum = md5_file($file);
  
  $sql = "UPDATE dm_file_history SET md5sum='$checksum' WHERE id='$id';";
  db_query($conn,$sql);

}

echo "Checksums updated succesfully\n";


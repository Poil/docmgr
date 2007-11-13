#!/usr/local/bin/php
<?php

/***************************************************************************
  Be sure to uncomment upgradePrev if you are upgrading from 0.50.
  If you are upgrading from 0.51 or later, you can run the script as is
***************************************************************************/

//path to docmgr data directory
$dirPath = "/www/doc";

//path to the files directory in docmgr
$filePath = "/www/doc/files";

//upgrading from 0.55
//$upgrade56 = 1;

//upgrading from 0.54
//$upgrade55 = 1;

//upgrading from 0.53.x
//$upgrade54 = 1;

//upgrading from 0.51 or 0.52
//$upgrade53 = 1;

//upgrading from 0.50.x
//$upgrade51 = 1;

die("You must comment me out first\n");


/*********************************************************
        don't modify anything below this line
*********************************************************/
        
//include our config file
include($dirPath."/config/config.php");
include($dirPath."/config/app-config.php");
        
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


define("ALT_FILE_PATH",$dirPath);
define("DATA_DIR",$filePath."/data");
define("DOC_DIR",$filePath."/document");
define("TMP_DIR",$filePath."/tmp");
define("THUMB_DIR",$filePath."/thumbnails");

$conn = db_connect(DBHOST,DBUSER,DBPASSWORD,DBPORT,DBNAME);

//call our past upgrade files if set
if ($upgrade51) include("include/upgrade51.php");			
if ($upgrade51 || $upgrade53) include("include/upgrade53.php");
if ($upgrade51 || $upgrade53 || $upgrade54) include("include/upgrade54.php");
if ($upgrade51 || $upgrade53 || $upgrade54 || $upgrade55) include("include/upgrade55.php");


//which index field are we using
if (defined("TSEARCH2_INDEX")) $idxfield = "idxfti";
else $idxfield = "idxtext";


$sql = "

ALTER TABLE dm_object ADD COLUMN token TEXT;
DROP VIEW dm_view_objects;

CREATE VIEW dm_view_objects AS
  SELECT dm_object.id, dm_object.name, dm_object.summary, dm_object.object_type, dm_object.create_date, dm_object.object_owner, dm_object.status, dm_object.status_date, dm_object.status_owner, dm_object.version, dm_object.\"reindex\", dm_object.filesize, dm_object.token, dm_object_parent.object_id, dm_object_parent.parent_id, dm_object_perm.account_id, dm_object_perm.group_id, dm_object_perm.bitset, dm_dirlevel.level1, dm_dirlevel.level2
  FROM dm_object
  LEFT JOIN dm_object_parent ON dm_object.id = dm_object_parent.object_id
  LEFT JOIN dm_object_perm ON dm_object.id = dm_object_perm.object_id
  LEFT JOIN dm_dirlevel ON dm_object.id = dm_dirlevel.object_id;

CREATE TABLE dm_object_related (
  object_id integer NOT NULL,
  related_id integer NOT NULL
);

CREATE INDEX dm_object_related_object_id_idx ON dm_object_related USING btree (object_id);
CREATE INDEX dm_object_related_related_id_idx ON dm_object_related USING btree (related_id);

CREATE VIEW dm_view_related AS
  SELECT dm_object_related.*,dm_object.name,dm_object.object_type FROM dm_object_related
  LEFT JOIN dm_object ON dm_object_related.related_id = dm_object.id;

CREATE TABLE db_version (version FLOAT NOT NULL);
INSERT INTO db_version (version) VALUES (0.57);

CREATE TABLE dm_thumb_queue (
  id SERIAL NOT NULL,
  object_id integer,
  account_id integer,
  notify_user boolean,
  create_date timestamp without time zone
);
 
";

if (db_query($conn,$sql)) echo "Upgrade to 0.57 database completed successfully\n";
else echo "Upgrade to 0.57 failed\n";

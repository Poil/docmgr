#!/usr/local/bin/php
<?php

die("You must comment me out first\n");

/*************************************************************
  To use this script, you must first create a new database
  which your data will be migrated to.  Just drop to the
  command line and run (as your database user )
   "createdb -D <dbname>".  Be sure to set "$newname" 
   to <dbname> below.
*************************************************************/

$dirPath = "/www/doc";

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

//your old database
$oldhost = "localhost";
$olduser = "postgres";
$oldpass = "secret";
$oldport = "5432";
$oldname = "olddocmgr";

//your new database
$newhost = "localhost";
$newuser = "postgres";
$newpass = "secret";
$newport = "5432";
$newname = "newdocmgr";

$oldconn = db_connect($oldhost,$olduser,$oldpass,$oldport,$oldname);
$newconn = db_connect($newhost,$newuser,$newpass,$newport,$newname);

include("include/db.php");
include("include/account.php");
include("include/obj.php");
include("include/cleanup.php");


<?
/*****************************************************************************************
  Fileame: config.inc.php

  Purpose: Contains all settings for site

  Created: 12-27-01
  Updated: 06-24-2006
              Reorganized and split out certain config options

******************************************************************************************/

/********************************************
	Required Settings
********************************************/

//connection settings
define("DBHOST","127.0.0.1");
define("DBUSER","elawman");
define("DBPASSWORD","secret");
define("DBPORT","5432");
define("DBNAME","docmgr");

//use ldap for accounts.  If set, alter ldap settings
//in ldap-config.php
//define("USE_LDAP","1");

//the directory you want the import module to look at (no trailing slash)
define("IMPORT_DIR","/import");

//absolute or relative path to the docmgr files directory (no trailing slash)
define("FILE_DIR","files");

//administrator email (also return address for outgoing emails
define("ADMIN_EMAIL","admin@somewhere.com");

//site url for emailing links to files in event and task notifications
//also, this must be set appropriately for fckeditor to work.  If a client
//uses a url different from this one, fckeditor will not load
define("SITE_URL","http://localhost/doc/");

//set this if you are using postgresql 8.0.x or EARLIER
//define("USE_OID","1");

/******************************************************************
	Indexing Options
******************************************************************/

//number of simultaneous processes for ocr.  No more than the 
//number of processors in your machine.  HT processors can count as 2
define("MAX_OCR","1");

//do not index more than this many words. 
//define("INDEX_WORD_LIMIT","1000");

//use the tsearch2 indexing system
//define("TSEARCH2_INDEX","1");

//regular expression used by preg_replace when stripping content
//from imported files.  whatever you put here is what those functions
//will keep.  This is case INSENSITVE  
//	Examples:
//	a-z -> keep all letters
//	0-9 -> keep all letters and numbers
define("REGEXP_OPTION","[:alnum:]");

/*****************************************************************
        Optional Permissions Settings
*****************************************************************/

//default permissions settings for files.  If "normal", other users
//can view/edit files with no permissions set on them.  if "strict",
//users cannot view files they do not own unless specified explicitly
define("OBJPERM_LEVEL","normal");

//only allow administrators to create objects at the root level
//define("ADMIN_ROOTLEVEL","1");

//allow automated logins with cookies
define("USE_COOKIES","1");

//allow removal of past file revisions (yes or no)
define("FILE_REVISION_REMOVE","yes");

//allow removal of past document revisions (yes or no)
define("DOC_REVISION_REMOVE","yes");

/*****************************************************************
	Optional Settings
*****************************************************************/

//set the default language for users
define("DEFAULT_LANG","English");

//default search results per page;
define("RESULTS_PER_PAGE","10");

//number of pages of results to show at once
define("PAGE_RESULT_LIMIT","20");

//default search results per page;
define("PAGE_BROWSE_RESULTS","1");

//max number of seconds per page per file
define("EXECUTION_TIME","60");

//date format for entering and viewing (either mm/dd/yyyy or dd/mm/yyyy);
define("DATE_FORMAT","mm/dd/yyyy");

//default view for browsing categories.  Can be either "list" or "thumb"
define("DEFAULT_BROWSE_VIEW","list");

//number of file histories to keep, 0 for unlimited
define("FILE_REVISION_LIMIT","0");

//number of document histories to keep, 0 for unlimited
define("DOC_REVISION_LIMIT","0");

//send a md5 checksum file with all email attachments
define("SEND_MD5_CHECKSUM","1");

//remove support for background indexing
//define("DISABLE_BACKINDEX","1");

//remove support for background thumbnailing
//define("DISABLE_BACKTHUMB","1");

//remove support for ajax browsing if you don't like it
//define("DISABLE_AJAX","1");

//if a file fails md5check when viewing, this allows the user
//to view the file anyways (after a warning is displayed)
//define("BYPASS_MD5CHECK","1");

/*******************************************************************
  Things you probably don't need to change
*******************************************************************/

//what do you want the default page in docmgr to be
//your options are "find" for the advanced finder, or 
//"browse" for the category browser, and "home" for the dashboard
define("DEFAULT_MOD","home");

//tsearch2 profile to use for indexing (ignore if not using tsearch2)
//if you are indexing a non-english language, use "simple", or
//you can setup a language-specific profile through tsearch2 and
//specify it here.  The "simple" profile will not try do dictionary
//compares or determine roots of words.  If you're unsure, you
//can safely leave this unchanged
define("TSEARCH2_PROFILE","default");

//encoding of your database.  you probably don't need to change this
//if changed, make sure you use the value iconv (http://www.php.net/iconv) 
//recognizes for your encoding, not the encoding name postgresql uses
define("DB_CHARSET","ISO-8859-1");

//set this if nobody can delete objects except adminstrators
//define("RESTRICTED_DELETE","1");

//site theme
define("SITE_THEME","default");


/******************************************************************
	these are only needed it you are running two installs
	on one server under the same domain name.  If you set
	these, you HAVE to access DocMGR using this url, instead
	of by an ip address or WINS addres.
******************************************************************/
//domain of your server
//define("SITE_DOMAIN","docserver.woodlake.eastwestp.com");

//url path to your server (not filesystem path)
//define("SITE_COOKIE_PATH","/doc");


/*************************************************************************
        Security Options
*************************************************************************/


//login banner.  Displayed on the login page
/*
define("WARNING_BANNER","*---------------- NOTICE - PROPRIETARY SYSTEM 
--------------*<BR> This  system is intended to be used solely by  
authorized <BR> users  for  legitimate  corporate  business.   Users  
are <BR> monitored to the extent necessary  to properly administer <BR> 
the system and to investigate unauthorized access or use. <BR> By 
accessing  this  system,  you  are  consenting to this <BR> monitoring.   
Unauthorized use is subject to prosecution. <BR> *---------------- 
NOTICE - PROPRIETARY SYSTEM --------------* ");
*/

//Enable account lockout feature - affects all users but administrators
define("ENABLE_ACCOUNT_LOCKOUT",1);

// Number of minutes to lock out the account, 0 = forever
define("ACCOUNT_LOCKOUT_TIME",5);

// Number of attempts before an account is locked
define("ACCOUNT_LOCKOUT_ATTEMPTS",5);

// Number of minutes before a session is timed out
//define("SESSION_TIMEOUT",20);

// Select whether cookies should only be sent over secure connections
//define("SECURE_COOKIES",1);

// Require that emails containing attachments only be sent to users
// of the system who have associated email addresses, otherwise, use
// anonymous access to send emails
//define("REQUIRE_SYSTEM_EMAIL","1");

//characters we disallow in a filename.  Anything set it here
//will prevent a file from being uploaded if set
define("DISALLOW_CHARS","\"/*");

/*************************************************************************
	Do not modify anything below this line
*************************************************************************/

//required system defines
define("APP_VERSION","0.58");
define("SITE_TITLE","DocMGR ".APP_VERSION);

//do we process auths in this site
define("PROCESS_AUTH","1");

//set error reporting to not show notices
error_reporting(E_ALL ^ E_NOTICE);

//reload modules every time for development
define("DEV_MODE","1");

//the debug level for outputting messages (0 - 5).
define("DEBUG","5");

//our directory levels (DO NOT CHANGE UNLESS YOU KNOW WHAT YOU ARE DOING!)
define("LEVEL1_NUM","16");
define("LEVEL2_NUM","256");

$exemptRequest = array();
$exemptRequest[] = "editorContent";
$exemptRequest[] = "apixml";

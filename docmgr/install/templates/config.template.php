<?php

//template pattern = array("value","enabled","comment","hidden from setup")

$template = array();

$template["Required"] = array();

//database
$template["Required"]["DBHOST"] = array("localhost",true,"Database Host",false);
$template["Required"]["DBUSER"] = array("postgres",true,"Database User",false);
$template["Required"]["DBPASSWORD"] = array("secret",true,"Database Password",false);
$template["Required"]["DBPORT"] = array("5432",true,"Database Port",false);
$template["Required"]["DBNAME"] = array("docmgr",true,"Database Name",false);

//site settings
$template["Required"]["SITE_URL"] = array("http://".str_replace("index.php","",$_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"]),true,"Full site URL.  Must have trailing slash!",false);
$template["Required"]["SITE_PATH"] = array("/www/docmgr",true,"Full site path.  No trailing slash required",false);

//these are hidden by default
$template["Required"]["FILE_DIR"] = array("[[SITE_PATH]]/files",true,"Absolute path to DocMGR files directory",true);
$template["Required"]["TMP_DIR"] = array("[[FILE_DIR]]/tmp",true,"Path to DocMGR tmp folder",true);
$template["Required"]["DATA_DIR"] = array("[[FILE_DIR]]/data",true,"Path to DocMGR tmp folder",true);
$template["Required"]["THUMB_DIR"] = array("[[FILE_DIR]]/thumbnails",true,"Path to DocMGR thumbnail folder",true);
$template["Required"]["PREVIEW_DIR"] = array("[[FILE_DIR]]/preview",true,"Path to DocMGR preview folder",true);
$template["Required"]["DOC_DIR"] = array("[[FILE_DIR]]/document",true,"Path to DocMGR documents folder",true);
$template["Required"]["HOME_DIR"] = array("[[FILE_DIR]]/home",true,"Path to DocMGR home folder",true);
$template["Required"]["IMPORT_DIR"] = array("[[FILE_DIR]]/import",true,"Path to DocMGR Import Folder",true);

//ldap
$template["Required"]["USE_LDAP"] = array("1",false,"Enable LDAP for accounts",true);

$template["Email"] = array();

//admin email
$template["Email"]["ADMIN_EMAIL"] = array("admin@mydomain.com",true,"Admin email set as return address for system emails",false);

$template["Email"]["SMTP_HOST"] = array("localhost",true,"Address of your SMTP server",false);
$template["Email"]["SMTP_PORT"] = array("25",true,"Port of your SMTP server",false);
$template["Email"]["SMTP_AUTH"] = array("1",false,"Use SMTP Authentication",true);
$template["Email"]["SMTP_AUTH_LOGIN"] = array("mailuser",false,"SMTP Auth Username.",true);
$template["Email"]["SMTP_AUTH_PASSWORD"] = array("secret",false,"SMTP Auth Password.",true);

/******************************************************************
	Indexing Options
******************************************************************/
$template["Indexing"] = array();

$template["Indexing"]["REGEXP_OPTION"] = array("-a-z0-9_",true,"Regular expression used to determine what characters to index",false);
$template["Indexing"]["INDEX_WORD_LIMIT"] = array("1000",false,"Limit index to this many words",false);

/*****************************************************************
        Permissions Settings
*****************************************************************/
$template["Permissions"] = array();

$template["Permissions"]["USE_COOKIES"] = array("1",true,"Allow automated logins with cookies",false);
$template["Permissions"]["COOKIE_TIMEOUT"] = array("14",true,"Number of days until cookie expires",false);
$template["Permissions"]["FILE_REVISION_REMOVE"] = array("yes",true,"Allow removal of past file revisions",false);
$template["Permissions"]["DOC_REVISION_REMOVE"] = array("yes",true,"Allow removal of past document revisions",false);

/*****************************************************************
	Optional Settings
*****************************************************************/

$template["Optional"]["DEFAULT_LANGUAGE"] = array("en",true,"Default language for application",false);
$template["Optional"]["RESULTS_PER_PAGE"] = array("20",true,"Default search results per page",false);
$template["Optional"]["PAGE_RESULT_LIMIT"] = array("20",true,"Number of pages of results to show at once",false);
$template["Optional"]["EXECUTION_TIME"] = array("60",true,"Max number of seconds of processing per page per file",false);
$template["Optional"]["DATE_FORMAT"] = array("mm/dd/yyyy",true,"Date format for entering and viewing dates (either mm/dd/yyyy or dd/mm/yyyy)",false);
$template["Optional"]["FILE_REVISION_LIMIT"] = array("0",true,"Number of file histories to keep.  O for unlimited",false);
$template["Optional"]["DOC_REVISION_LIMIT"] = array("0",true,"Number of document histories to keep.  O for unlimited",false);
$template["Optional"]["SEND_MD5_CHECKSUM"] = array("1",false,"Send md5 checksum file w/ all email attachments",false);
$template["Optional"]["BYPASS_MD5CHECK"] = array("1",false,"Allow file to be viewed even md5 check fails (after warning displayed)",false);
$template["Optional"]["USE_TRASH"] = array("1",true,"Use trash can instead of direct delete",false);
$template["Optional"]["TSEARCH2_PROFILE"] = array("english",true,"Tsearch2 profile to use for indexing",false);
$template["Optional"]["ROOT_NAME"] = array("Root Level",true,"Name for the top level bookmark",false);
$template["Optional"]["DEFAULT_PERMISSIONS"] = array("00000000000000000000110000001000",true,"default permissions.  user can alter own profile, insert objects into system, and create in the root collection",false);
$template["Optional"]["BROWSE_GROUPBY"] = array("object_type",false,"group browse results by object type",false);
$template["Optional"]["DEFAULT_MODULE"] = array("docmgr",true,"Change default module to display after login",false);
$template["Optional"]["SITE_THEME"] = array("default",true,"Default theme for DocMGR",false);
$template["Optional"]["DMEDITOR_DEFAULT_SAVE"] = array("docmgr",true,"Default file type for DocMGR's built-in editor to save as.  Options are 'docmgr','odt','doc'... or whatever you set allow_dmsave tag to in extensions.xml file",false);

/*************************************************************************
        Security Options
*************************************************************************/

$template["Security"] = array();

$template["Security"]["WARNING_BANNER"] = array("Warning!!!!",false,"Login banner displayed on login page",false);
$template["Security"]["ENABLE_ACCOUNT_LOCKOUT"] = array("1",true,"Enable account lockout feature - affects all users but admins",false);
$template["Security"]["ACCOUNT_LOCKOUT_TIME"] = array("5",true,"Number of minutes to lock out account. 0 = forever",false);
$template["Security"]["ACCOUNT_LOCKOUT_ATTEMPTS"] = array("5",true,"Number of failed login attempts for an account is locked",false);
$template["Security"]["SECURE_COOKIES"] = array("1",false,"Select whether cookies should only be sent over secure connections",false);
$template["Security"]["DISALLOW_CHARS"] = array("\"/*",true,"Characters we disallow in a filename",false);
$template["Security"]["RESTRICTED_DELETE"] = array("1",false,"Set this if nobody can delete objects except administrators",false);

/******************************************************************
  Don't change
******************************************************************/
$template["Unchangeable"] = array();
$template["Unchangeable"]["hidden"] = 1;

$template["Unchangeable"]["API_URL"] = array("api.php",true,"url to docmgr api",false);
$template["Unchangeable"]["DIGEST_REALM"] = array("SabreDAV",true,"used for digest authentication on webdav",false);
$template["Unchangeable"]["PROTO_DEFAULT"] = array("JSON",true,"Our proto transfer protocol",false);
$template["Unchangeable"]["PERM_BITLEN"] = array("32",true,"length of our permissions bitmask",false);
$template["Unchangeable"]["PROCESS_AUTH"] = array("1",true,"Process authentications on this site",false);
$template["Unchangeable"]["DEV_MODE"] = array("1",false,"Reload modules every time for development",false);
$template["Unchangeable"]["DEBUG"] = array("5",false,"Debugging level",false);
$template["Unchangeable"]["LEVEL1_NUM"] = array("16",true,"Top directory level (DO NOT CHANGE)",true);
$template["Unchangeable"]["LEVEL2_NUM"] = array("256",true,"Second directory level (DO NOT CHANGE)",true);

//just written directly to the config file
$template["Suffix"] = "

//set error reporting to not show notices
error_reporting(E_ALL ^ E_NOTICE ^ E_STRICT);

//turn on error reporting
ini_set(\"display_error\",\"1\");

\$exemptRequest = array();
\$exemptRequest[] = \"editor_content\";
\$exemptRequest[] = \"apidata\";
\$exemptRequest[] = \"to\"; 
\$exemptRequest[] = \"from\"; 
\$exemptRequest[] = \"cc\";
\$exemptRequest[] = \"bcc\";

include(\"config-custom.php\");

";

<?php

//set the include path to work with relative paths
ini_set("include_path",".");

//first call the config file to get our settings, call our base functions, and get our wrapper
include("config/config.php");
include("config/app-config.php");

//include our ldap file if set
if (defined("USE_LDAP")) include("config/ldap-config.php");

//set our site theme here.  This will make it accessible to the includes that need it
if (!defined("SITE_THEME")) die("No theme is defined for the site");
define("THEME_PATH","themes/".SITE_THEME);

//the rest of our includes with our base functions
include("header/callheader.php");

//this gets rid of a hidden session form field which we do not want
ini_set("session.use_trans_sid","0");

//set the valid session cookie path if defined
if (defined("SITE_COOKIE_PATH")) ini_set("session.cookie_path",SITE_COOKIE_PATH);
if (defined("SITE_DOMAIN")) ini_set("session.cookie_domain",SITE_DOMAIN);

//whether cookies should only be sent over secure connections.
if (defined("SECURE_COOKIES")) ini_set("session.cookie_secure",SECURE_COOKIES);

//set to use short tags
ini_set("short_open_tag","1");

//start our session
session_start();

//make our request variables safe
sanitizeRequest($exemptRequest);

//include the proper file depending on db or ldap auth
if (defined("USE_LDAP")) {include("lib/ldap.php");}
else {include("lib/db.php");}

//get our current cookie information.  If it does not match the path
//destroy our current session information
if (defined("SITE_COOKIE_PATH") && defined("SITE_DOMAIN") && !defined("DEV_MODE")) {
  $sessionInfo = session_get_cookie_params();
  if ($sessionInfo["path"]!=SITE_COOKIE_PATH || $sessionInfo["domain"]!=SITE_DOMAIN) session_destroy();
}

//if session timouts are enabled, check to see if the session has expired
if (defined("SESSION_TIMEOUT")){
   if ( ($_SESSION["timestamp"]!= NULL) && ( $_SESSION["timestamp"] < ( time() - (SESSION_TIMEOUT*60)) )){
	session_destroy();
	header("Location: index.php?timeout=true");
   }
   else {
   	$_SESSION["timestamp"] = time();
   }

}

//connect
$conn = db_connect(DBHOST,DBUSER,DBPASSWORD,DBPORT,DBNAME);

//we now do our authentications on the local database
$auth_conn = $conn;
$cent_conn = $conn;
$_SESSION["conn"] = $conn;

//get the users browser type
$browser = browser_check($_SERVER["HTTP_USER_AGENT"]);
define("BROWSER","$browser");

//Get our site layout if we have not already
if ($_SESSION["siteModList"] && $_SESSION["siteModInfo"] && !defined("DEV_MODE")) {

  $siteModList = &$_SESSION["siteModList"];
  $siteModInfo = &$_SESSION["siteModInfo"];

}
else {

  $siteModArr = loadSiteStructure("modules/center/");
  $_SESSION["siteModList"] = $siteModArr["list"];
  $_SESSION["siteModInfo"] = $siteModArr["info"];
  $siteModList = &$_SESSION["siteModList"];
  $siteModInfo = &$_SESSION["siteModInfo"];

}

//set our permission defines
setPermDefines();
setCustomPermDefines();


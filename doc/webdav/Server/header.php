<?php

//the rest of our includes with our base functions
include(WEBDAV_PATH."/config/config.php");
include(WEBDAV_PATH."/config/app-config.php");

//include our ldap file if set
if (defined("USE_LDAP")) include(WEBDAV_PATH."/config/ldap-config.php");

//the rest of our includes with our base functions
include(WEBDAV_PATH."/lib/accperms.php");
include(WEBDAV_PATH."/lib/arrays.php");
include(WEBDAV_PATH."/lib/calc.php");
include(WEBDAV_PATH."/lib/customforms.php");
include(WEBDAV_PATH."/lib/data_formatting.php");
include(WEBDAV_PATH."/lib/email.php");
include(WEBDAV_PATH."/lib/filefunctions.php");
include(WEBDAV_PATH."/lib/misc.php");
include(WEBDAV_PATH."/lib/modules.php");
include(WEBDAV_PATH."/lib/postgresql.php");
include(WEBDAV_PATH."/lib/presentsite.php");
include(WEBDAV_PATH."/lib/sanitize.php");
include(WEBDAV_PATH."/lib/xml.php");

include(WEBDAV_PATH."/app/common.inc.php");
include(WEBDAV_PATH."/app/object.inc.php");
include(WEBDAV_PATH."/app/search_function.inc.php");
include(WEBDAV_PATH."/app/index_function.inc.php");
include(WEBDAV_PATH."/app/thumb_function.inc.php");

if (defined("USE_LDAP")) include(WEBDAV_PATH."/lib/ldap.php");
else include(WEBDAV_PATH."/lib/db.php");

//set our path defines
$filePath = getFilePath(FILE_DIR,WEBDAV_PATH);

define("DATA_DIR",$filePath."/data");
define("TMP_DIR",$filePath."/tmp");
define("THUMB_DIR",$filePath."/thumbnails");

//our alternate file path for our extensions.xml file
define("ALT_FILE_PATH",WEBDAV_PATH);

//make our request variables safe
sanitizeRequest();

$conn = db_connect(DBHOST,DBUSER,DBPASSWORD,DBPORT,DBNAME);

$_SESSION["conn"] = $conn;

setPermDefines();
setCustomPermDefines();

//configure DocMGR to use avail external apps
setExternalApps();

//load our modules
//Get our site layout if we have not already
if ($_SESSION["siteModList"] && $_SESSION["siteModInfo"] && !defined("DEV_MODE")) {
	$siteModList = &$_SESSION["siteModList"];
	$siteModInfo = &$_SESSION["siteModInfo"];
}
else {
	$siteModArr = loadSiteStructure(WEBDAV_PATH."/modules/center/");
	$_SESSION["siteModList"] = $siteModArr["list"];
	$_SESSION["siteModInfo"] = $siteModArr["info"];
	$siteModList = &$_SESSION["siteModList"];
	$siteModInfo = &$_SESSION["siteModInfo"];
}

//load our objects
loadObjects();

if ($_SESSION["user_id"]) {

	define("USER_ID",$_SESSION["user_id"]);

	//set the bitset info for our permissions
	include(WEBDAV_PATH."/auth/function.inc.php");
	userPermSet($conn,$_SESSION["user_id"],$_SESSION["user_authid"]);

}


//get all our categories put in an array for us to use later
$sql = "SELECT * FROM dm_view_collections ORDER BY id";
$_SESSION["catList"] = total_result($_SESSION["conn"],$sql);

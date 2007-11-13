<?

define("ALT_FILE_PATH","../");

//call this file to get our path to the thumbnails
include("../config/config.php");
include("../config/app-config.php");

//the rest of our includes with our base functions
include("../header/callheader.php");

include("../app/common.inc.php");
include("../app/custom_form.inc.php");
include("../app/object.inc.php");
include("../app/index_function.inc.php");
include("../app/tree.inc.php");
include("../app/thumb_function.inc.php");
include("../auth/function.inc.php");

session_id($_REQUEST["sessionId"]);
session_start();

//don't go any farther if there is no session.  Someone is getting here by cheating
if (!$_SESSION["user_id"]) return false;

$conn = db_connect(DBHOST,DBUSER,DBPASSWORD,DBPORT,DBNAME);

//set our permission defines
setPermDefines();
setCustomPermDefines();

//get our request variables
$parentId = $_REQUEST["parentId"];
$divName = $_REQUEST["divName"];
//set our defines and permissions for this user as obtained from the sessionid

//process our define permissions.  If access is disabled, show the login form
if (userPermSet($conn,$_SESSION["user_id"])) {

	//set our user information from that which is returned from the function
	define("USER_ID",$_SESSION["user_id"]);
	define("USER_LOGIN",$_SESSION["user_login"]);
	define("USER_EMAIL",$_SESSION["user_email"]);
	define("USER_FN",$_SESSION["user_fn"]);
	define("USER_LN",$_SESSION["user_ln"]);

}
else die("Error!");

$parentId = $_REQUEST["parentId"];
$expandSingle = $_REQUEST["expandSingle"];

$arr = array();
$xml = null;

if ($expandSingle) {
  $xml = expandSingleCol($conn,$parentId);
  $xmlmode = "singlecoltree";
}
else {
  $xml = expandValueCol($conn,$parentId);
  $xmlmode = "coltree";
}

//put it all together
$str .= createXmlHeader($xmlmode);
//if ($mode) $str .= xmlEntry("mode",$mode);
//if ($formName) $str .= xmlEntry("formName",$formName);
if ($divName) $str .= xmlEntry("divName",$divName);
if ($expandSingle) $str .= xmlEntry("expandSingle",$expandSingle);
$str .= $xml;
$str .= createXmlFooter();

echo $str;
die;


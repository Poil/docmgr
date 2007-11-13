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
include("../app/thumb_function.inc.php");
include("../auth/function.inc.php");

session_id($_REQUEST["sessionId"]);
session_start();

//set our path to our tmp, data, and thumbnail directories.  This
//must be done before our main function includes are called.  (no trailing slashes)
$d = FILE_DIR;
if ($d[0]=="/") $dirPath = $d;
else $dirPath = "../".$d."/";

define("TMP_DIR",$dirPath."tmp");
define("DATA_DIR",$dirPath."data");
define("THUMB_DIR",$dirPath."thumbnails");

//don't go any farther if there is no session.  Someone is getting here by cheating
if (!$_SESSION["user_id"]) return false;

$conn = db_connect(DBHOST,DBUSER,DBPASSWORD,DBPORT,DBNAME);

//set our permission defines
setPermDefines();
setCustomPermDefines();

//set our defines and permissions for this user as obtained from the sessionid
if (userPermSet($conn,$_SESSION["user_id"])) {

	//set our user information from that which is returned from the function
	define("USER_ID",$_SESSION["user_id"]);
	define("USER_LOGIN",$_SESSION["user_login"]);
	define("USER_EMAIL",$_SESSION["user_email"]);
	define("USER_FN",$_SESSION["user_fn"]);
	define("USER_LN",$_SESSION["user_ln"]);

}
else die("Error!");


if ($_REQUEST["editfile"]) {

	$objectId = $_REQUEST["objectId"];
	$fileId = $_REQUEST["fileId"];

	$cb = returnUserObjectPerms($conn,$objectId);

	if (!bitset_compare(BITSET,ADMIN,null) && !bitset_compare($cb,OBJ_MANAGE,OBJ_ADMIN)) die("Permissions Error");

	if ($_REQUEST["editfile"]=="delete" && $objectId) {

		loadObjects(1);
		deleteObject($conn,$objectId);

	}
	elseif ($_REQUEST["editfile"]=="rotate" && $objectId && $fileId) {
		rotateFile($conn,$objectId,$fileId,$_REQUEST["dir"]);
	}

	$str = createXmlHeader("fileedit");
	$str .= createXmlFooter();
	echo $str;
	
}
                                                 
function rotateFile($conn,$objectId,$fileId,$dir) {

	//put our path in a variable
	$d = FILE_DIR;

	//if the thumb_dir is an absolute path, point directly to it.
	//if it's relative, move up a directory to get to the file
	if ($d[0]=="/") $dirPath = $d;
	else $dirPath = "../".$d;

	$objDir = returnObjPath($conn,$objectId);

	$filePath = DATA_DIR."/".$objDir."/".$fileId.".docmgr";
	$thumbPath = THUMB_DIR."/".$objDir."/".$objectId.".docmgr";

	if ($dir=="left") $deg = "270";
	else $deg = "90";

	//rotate the main file
	system(APP_MOGRIFY." -rotate \"".$deg."\" \"".$filePath."\"");
	system(APP_MOGRIFY." -rotate \"".$deg."\" \"".$thumbPath."\"");

	return true;

}

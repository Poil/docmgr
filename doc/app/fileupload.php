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

if (defined("USE_LDAP")) {
  include("../config/ldap-config.php");
  include("../lib/ldap.php");
}
else include("../lib/db.php");

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

//set the execution time for uploading and file processing
if (defined("EXECUTION_TIME")) ini_set("max_execution_time",EXECUTION_TIME);

//setup which apps are available to docmgr
setExternalApps();

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

if ($_FILES["fileUpload"]) {

        loadObjects(1);

	//make sure we have edit permissions on the parent
	$cb = returnUserObjectPerms($conn,$_REQUEST["parentId"]);

	if (!bitset_compare(BITSET,ADMIN,null) && 
	    !bitset_compare($cb,OBJ_EDIT,OBJ_ADMIN) &&
	    !bitset_compare($cb,OBJ_MANAGE,null)) die("Permissions Error");

        $pathArr = $_FILES['fileUpload']['tmp_name'];
        $nameArr = $_FILES['fileUpload']['name'];
        $num = count($pathArr);
        
        for ($i=0;$i<$num;$i++) {
       
          $fileName = $nameArr[$i];
          $filePath = $pathArr[$i];
       
          //set all our options into the array with corresponding keys.  These will
          //be passed to the file_insert function, which handles inserting the file into the system
          $option = null;
          $option["conn"] = $conn;
          $option["name"] = smartslashes($fileName);
          $option["filepath"] = $filePath;
          $option["delete_files"] = "yes";
          $option["parentId"] = $_REQUEST["parentId"];
          $option["objectType"] = "file";
          $option["objectOwner"] = $_SESSION["user_id"];
          $option["thumbForeground"] = 1;
          if ($objectId = createObject($option)) $successMessage = _FILE_UPLOAD_SUCCESS;
          else $errorMessage = _FILE_UPLOAD_ERROR;
       
          if ($errorMessage) break;
       
        }
       
	echo "<html><body>done</body></html>";

}
                                                 


                                                 
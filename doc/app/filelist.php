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

define("THUMB_DIR",FILE_DIR."/data");

//don't go any farther if there is no session.  Someone is getting here by cheating
if (!$_SESSION["user_id"]) return false;

$conn = db_connect(DBHOST,DBUSER,DBPASSWORD,DBPORT,DBNAME);

//set our permission defines
setPermDefines();
setCustomPermDefines();
setExternalApps();

//get our request variables
$parentId = $_REQUEST["parentId"];

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

//get all files that are images belonging to this parent
$fields = "id,name,summary,object_type,create_date,object_owner,status,status_date,status_owner,filesize::numeric,version,parent_id,level1,level2";

$sql = "SELECT DISTINCT $fields,
	 (SELECT id FROM dm_file_history WHERE object_id=dm_view_objects.id AND version=dm_view_objects.version LIMIT 1) AS file_id
	 FROM dm_view_objects WHERE parent_id='$parentId' AND object_type='file'";
if (!bitset_compare(BITSET,ADMIN,null)) $sql .= " AND ".permString($conn);
$sql .= " ORDER BY name";
$list =  list_result($conn,$sql);

//start our xml output
$str = null;
$c = 0;

//if the user isn't an admin, requery the database and get permissions for our current objects
if ($list["count"] > 0 && !bitset_compare(BITSET,ADMIN,null)) {
	$idArr = array();
	foreach ($list AS $curObj) if ($curObj["id"]) $idArr[] = $curObj["id"];
	$sql = "SELECT * FROM dm_object_perm WHERE object_id IN (".implode(",",$idArr).");";
	$permArr = total_result($conn,$sql);
}
                                                                                        
	
for ($i=0;$i<$list["count"];$i++) {

        $dir = $list[$i]["level1"]."/".$list[$i]["level2"];

	$file = $list[$i]["name"];
	$path = SITE_URL."app/showimage.php?objectId=".$list[$i]["id"]."&objDir=".$dir;
	$thumburl = SITE_URL."app/showthumb.php?objectId=".$list[$i]["id"]."&sessId=".$_REQUEST["sessionId"]."&objDir=".$dir."&time=".time();
	$fileurl = SITE_URL."app/showimage.php?fileId=".$list[$i]["file_id"]."&fileName=".$list[$i]["name"]."&objDir=".$dir."&sessId=".$_REQUEST["sessionId"];
	$allowedit = null;

	if (defined("THUMB_SUPPORT")) {

	  //get the bitset for this object if the user isn't an admin
	  if (!bitset_compare(BITSET,ADMIN,null)) {
		$bitset = returnObjBitset($list[$i]["id"],$permArr);
		if (bitset_compare($bitset,OBJ_MANAGE,OBJ_ADMIN)) $allowedit = 1;
          }
          else $allowedit = 1;

        }
        
	//allow rotates
	$type = return_file_type($file);
	if ($type!="image") continue;
	
	//create our file node
	$str .= "<file>\n";
	$str .= xmlEntry("objectId",$list[$i]["id"]);
	$str .= xmlEntry("fileId",$list[$i]["file_id"]);
	$str .= xmlEntry("filename",$file);
	$str .= xmlEntry("filepath",$path);
	$str .= xmlEntry("thumburl",$thumburl);
	$str .= xmlEntry("fileurl",$fileurl);

	//allow the user to edit the file
	if ($allowedit) $str .= xmlEntry("allowedit","1");

	$str .= "</file>\n";
	$c++;
		
}


//add our count, header, and footer
$str = 	createXmlHeader("filelist")."\n".
 	xmlEntry("filecount",$c)."\n".
 	$str."\n".
 	createXmlFooter();

echo $str;


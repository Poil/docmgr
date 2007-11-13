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
  $xml = expandSingleTree($conn,$parentId);
  $xmlmode = "singlecoltree";
}
else {
  $xml = expandValueTree($conn,$parentId);
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





//just show a single level of collections
function expandSingleTree($conn,$curValue) {

  //somehow, this is faster than the two table query method
  if (bitset_compare(BITSET,ADMIN,null)) {
  $sql = "SELECT DISTINCT id,name,parent_id,object_type,
           (SELECT count(id) FROM 
             (SELECT id,parent_id FROM dm_view_objects) AS mytable
             WHERE parent_id=dm_view_objects.id) AS child_count
             FROM dm_view_objects WHERE parent_id='$curValue' ORDER BY name
              ";
  }
  else { 
    $permStr = permString();
    //a little fancy query footwork
    $sql = "SELECT DISTINCT id,name,parent_id,object_type
             (SELECT count(id) FROM 
               (SELECT id,parent_id FROM dm_view_objects WHERE ".$permStr.") AS mytable
               WHERE parent_id=dm_view_objects.id) AS child_count
             FROM dm_view_objects WHERE parent_id='$curValue' AND ".$permStr." ORDER BY name
              ";
  }  

  $list = list_result($conn,$sql);

  $str = null;
  
  for ($i=0;$i<$list["count"];$i++) {

    //first, get the info for this file
    $str .= "<object>\n";
    $str .= xmlEntry("id",$list[$i]["id"]);
    $str .= xmlEntry("name",$list[$i]["name"]);
    $str .= xmlEntry("parent_id",$list[$i]["parent_id"]);
    $str .= xmlEntry("child_count",$list[$i]["child_count"]);
    $str .= xmlEntry("viewmod",returnObjectViewer($list[$i]["object_type"]));
    $str .= "</object>\n";

  }

  return $str;
  
}


function expandValueTree($conn,$curValue) {

  //get all collections that need to be displayed
  $sql = "SELECT DISTINCT id,name,parent_id,object_type FROM dm_view_objects";  
  if (!bitset_compare(BITSET,ADMIN,null)) $sql .= " WHERE ".permString($conn);
  $sql .= " ORDER BY name ";
  $catInfo = total_result($conn,$sql);

  $arr = array();

  if ($curValue) {

    if (!is_array($curValue)) $curValue = array($curValue);
  
    $num = count($curValue);

    for ($i=0;$i<$num;$i++) 
      $arr = array_merge($arr,returnCatOwner($catInfo,$curValue[$i],null));

    $arr = array_values(array_unique($arr));

  }

  //get the parent of the current, then pass it on to return info for all files
  //which are on the same level.  we also pass our value array so we can 
  //decent to the next level at a hit
  if ($catInfo["count"] > 0) {
    $keys = array_keys($catInfo["parent_id"],"0");
    $xml = showColXml($catInfo,$keys,$arr);
  }
  else $xml = null;
  
  return $xml;

}



function showColXml($catInfo,$keys,$arr) {

  if (!$keys) return false;

  foreach ($keys AS $childKey) {
  
    //first, get the infor for this file
    $str .= "<object>\n";
    $str .= xmlEntry("id",$catInfo["id"][$childKey]);
    $str .= xmlEntry("name",$catInfo["name"][$childKey]);
    $str .= xmlEntry("parent_id",$catInfo["parent_id"][$childKey]);
    $str .= xmlEntry("viewmod",returnObjectViewer($catInfo["object_type"][$childKey]));
    
    //now show the children
    $keys = array_keys($catInfo["parent_id"],$catInfo["id"][$childKey]);

    //show the children count
    $str .= xmlEntry("child_count",count($keys));

    //if in the expansion array show the children
    if (@in_array($catInfo["id"][$childKey],$arr)) {

      $str .= "<children>\n";
      $str .= showColXml($catInfo,$keys,$arr);
      $str .= "</children>\n";

    }

    $str .= "</object>\n";
  
  }



  return $str;

}


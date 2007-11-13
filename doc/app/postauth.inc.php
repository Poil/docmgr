<?

//load our objects
loadObjects();

//if it's a view request, set our object id and th enew module
if ($_REQUEST["view"]) {

  //determine the object id from the path
  $objInfo = getObjectFromPath($conn,$_REQUEST["view"]);
  
  //if we returned something, set our module to view it with and set the object id for perm checking
  if ($objInfo) {

    $mod = $objInfo["object_type"];
    $module = $_SESSION["siteModInfo"][$mod]["viewer"];

    //custom processing for collections.  I know I shouldn't be setting the request variables, but this is easier
    if ($module=="browse") $_REQUEST["view_parent"] = $objInfo["id"];
    else $_REQUEST["objectId"] = $objInfo["id"];

  } else echo "not found";

}

//if there is an object, get our current user's permissions with it.  The view_parent portion ensures
//the user has permissions to add files to that collection while browsing within it. 
if ($_REQUEST["objectId"] || $_SESSION["objectId"] || ($_REQUEST["view_parent"] && $module=="browse")) {

  if ($_REQUEST["objectId"]) $objId = $_REQUEST["objectId"];
  elseif ($_REQUEST["view_parent"] && $module=="browse") $objId = $_REQUEST["view_parent"];
  elseif ($_SESSION["objectId"]) $objId = $_SESSION["objectId"];
  
  $cb = returnUserObjectPerms($conn,$objId);

  //set our bitset for this file
  define("CUSTOM_BITSET",$cb);

  //if our module is an object, or check_type is set for this module, make sure someone isn't trying to open
  //the wrong object with the wrong module
  if ( (isObject($module) || $_SESSION["siteModInfo"][$module]["check_type"]) && !checkObjType($conn,$module,$objId)) {
    die(_OBJ_TYPE_ERROR);
  }

  //define the directory levels our object uses
  define("OBJECT_DIR",returnObjPath($conn,$objId));

}

$sql = "SELECT * FROM auth_settings WHERE account_id='".USER_ID."'";
$info = single_result($conn,$sql);

$_SESSION["curLang"] = $info["language"];
$_SESSION["homeDir"] = $info["home_directory"];

//process a passed language change
if ($_POST["setLang"]) $_SESSION["curLang"] = $_POST["setLang"];
//use our default language if it is not set
else if (!$_SESSION["curLang"]) $_SESSION["curLang"] = DEFAULT_LANG;

include("lang/".$_SESSION["curLang"].".php");

//default charset if missed
if (!defined("LANG_CHARSET")) define("LANG_CHARSET","ISO-8859-1");

//default I18N value if missed
if (!defined("LANG_I18N")) define("LANG_I18N","en");

//use a name that makes more sense
define("VIEW_CHARSET",LANG_CHARSET);

// Setting the Content-Type header with charset
header("Content-Type: text/html; charset=".VIEW_CHARSET);

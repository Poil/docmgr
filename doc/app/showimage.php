<?

$thumb = "../images/thumbnails/file.png";
 
define("ALT_FILE_PATH","../");

//call this file to get our path to the thumbnails
include("../config/config.php");
include("../app/common.inc.php");
include("../lib/xml.php");

session_id($_REQUEST["sessId"]);
session_start();

//make sure someone isn't pulling a fast one with the objDir
if (strstr($_REQUEST["objDir"],"..")) return false;
if ($_REQUEST["objDir"][0]=="/") return false;

define("DATA_DIR",FILE_DIR."/data");

//don't go any farther if there is no session.  Someone is getting here by cheating
if (!$_SESSION["user_id"]) return false;

displayFile($_REQUEST["fileId"],$_REQUEST["fileName"],$_REQUEST["objDir"]);

function displayFile($fileId,$fileName,$objDir) {

    //put our path in a variable
    $d = DATA_DIR."/".$objDir;

    //if the thumb_dir is an absolute path, point directly to it.
    //if it's relative, move up a directory to get to the file
    if ($d[0]=="/") $filePath = $d."/".$fileId.".docmgr";
    else $filePath = "../".$d."/".$fileId.".docmgr";

    if (!file_exists($filePath)) {
      $filePath = "../themes/default/images/thumbnails/file.png";
      $fileName = "file.png";
    }

    $mime = return_file_mime($fileName,$filePath);

    header("Content-Type: $mime");
    readfile($filePath);

}

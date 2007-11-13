<?


$objectId = $_REQUEST["objectId"];
$pageAction = $_POST["pageAction"];


if ($pageAction=="save" && !$error) {

    $content = cleanupEditorStr($_POST["editorContent"]);

    $option = null;
    $option["conn"] = $conn;
    $option["documentContent"] = $content;
    $option["objectId"] = $objectId;
    documentObject::runUpdate($option);

    //tack on an error message from the function
    if (defined("ERROR_MESSAGE")) $errorMessage .= "<br>".ERROR_MESSAGE;

}

$sql = "SELECT id, (SELECT name FROM dm_object WHERE id=object_id) AS name 
        FROM dm_document WHERE object_id='$objectId' ORDER BY version DESC LIMIT 1";
$info = single_result($conn,$sql);

$file = FILE_DIR."/document/".returnObjPath($conn,$objectId)."/".$info["id"].".docmgr";

if (file_exists($file)) $text = formatEditorStr(file_get_contents($file));

//get the parent of this object.  If multiple, just use the first
$sql = "SELECT parent_id FROM dm_object_parent WHERE object_id='$objectId'";
$docinfo = single_result($conn,$sql);
$parentId = $docinfo["parent_id"];

//set the window title to the name of the document
$siteTitle = $info["name"];

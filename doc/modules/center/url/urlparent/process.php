<?

$objectId = $_SESSION["objectId"];

if ($_SESSION["objectId"]==NULL) {
	$errorMessage = "No object is specified";
	return false;
}
    

if ($_POST["pageAction"]=="update") {

	$errorMessage = null;
	$successMessage = null;

	if (!parentUpdate($conn,$objectId,$_POST["parentId"])) $errorMessage = _UPDATE_ERROR;
	else $successMessage = _UPDATE_SUCCESS;

	if (defined("ERROR_MESSAGE")) $errorMessage .= "<br>".ERROR_MESSAGE;
	
}


$sql = "SELECT parent_id FROM dm_object_parent WHERE object_id='$objectId';";
$arr = total_result($conn,$sql);
$parentValue = &$arr["parent_id"];


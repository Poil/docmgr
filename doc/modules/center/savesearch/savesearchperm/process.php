<?

$objectId = $_SESSION["objectId"];

if ($_SESSION["objectId"]==NULL) {
	$errorMessage = "No object is specified";
	return false;
}
    

if ($_POST["pageAction"]=="update") {

	$viewEditArr = explode(",",$_POST["view_edit_value"]);
	$viewArr = explode(",",$_POST["view_value"]);
	
	//remove the empty end value
	@array_pop($viewEditArr);
	@array_pop($viewArr);

	$errorMessage = null;
	$successMessage = null;

	if (!permUpdate($conn,$objectId,$viewEditArr,$viewArr)) $errorMessage = _UPDATE_ERROR;
	else $successMessage = _UPDATE_SUCCESS;

}



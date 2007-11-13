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

	if (permUpdate($conn,$objectId,$viewEditArr,$viewArr)) {

		$successMessage = _UPDATE_SUCCESS;

		//if we are resetting permissions on children, get the children
		//and run the permUpdate function
		if ($_POST["resetPerm"]) {

			//get the children ids
			$objArr = returnObjChildren($conn,$objectId);
			$num = count($objArr);
			
			for ($i=0;$i<$num;$i++) permUpdate($conn,$objArr[$i],$viewEditArr,$viewArr);
			
		}

	}
	else $successMessage = _UPDATE_ERROR;

}



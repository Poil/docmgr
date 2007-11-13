<?

/***************************************
	set our variables
***************************************/
$objectAction = $_REQUEST["objectAction"];

if ($_POST["deleteObject"]) {

	//remove the trailing delimiter
	$objectArr = explode("|",$objectAction);
	array_pop($objectArr);

	$sql = null;
	$successMessage = 1;
	
	//insert with our new parent
	for ($row=0;$row<count($objectArr);$row++) {

		//make sure we have permissions to move this object
		$cb = returnUserObjectPerms($conn,$objectArr[$row]);
		if (!bitset_compare($cb,OBJ_MANAGE,OBJ_ADMIN)) continue;

		//if it fails, get our error and prevent the window from closing
		if (!deleteObject($conn,$objectArr[$row])) {
			$errorMessage = _OBJECT_REMOVE_ERROR."<br>".ERROR_MESSAGE;
			$successMessage = null;
		}
		
	}

}


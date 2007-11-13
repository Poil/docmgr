<?

/***************************************
	set our variables
***************************************/
$newCategory = $_REQUEST["newCategory"];
$objectAction = $_REQUEST["objectAction"];
$objectId = $_REQUEST["objectId"];
$curValue = $_REQUEST["curValue"];

if ($_POST["moveObject"] && $_POST["parentId"]!=null) {

	//remove the trailing delimiter
	$objectArr = explode("|",$objectAction);
	array_pop($objectArr);

	//make sure our source category is not the same as our destination
	if (in_array($_POST["parentId"],$objectArr)) {
	
		$errorMessage = _COLLECTION_PARENT_ERROR;
		return false;
	
	}

	//make sure we are not moving something to a category where an object with its name already exists
	//there may be a more efficient way to do this without so many queries to the db
	foreach ($objectArr AS $curObj) {

		$sql = "SELECT name FROM dm_object WHERE id='$curObj'";
		$info = single_result($conn,$sql);

		if (!checkObjName($conn,$info["name"],$_POST["parentId"])) {
			$errorMessage = ERROR_MESSAGE;
			return false;   //stop processing this file
		}

	}


	$sql = null;

	//first, make sure we are allow to edit anything in the destination folder, since we are 
	//moving things there
	$cb = returnUserObjectPerms($conn,$_POST["parentId"]);
	if (!bitset_compare($cb,OBJ_MANAGE,OBJ_ADMIN)) {
		$errorMessage = _OBJ_MOVE_PERM_ERROR;
		return false;
	}

	//delete all current objects
	$sql = "DELETE FROM dm_object_parent WHERE object_id IN (".implode(",",$objectArr).");";
	if (db_query($conn,$sql)) {

		$sql = null;

		//insert with our new parent
		for ($row=0;$row<count($objectArr);$row++) {

			//make sure we have permissions to move this object
			$cb = returnUserObjectPerms($conn,$objectArr[$row]);
			if (!bitset_compare($cb,OBJ_EDIT,OBJ_ADMIN)) continue;

			$sql .= "INSERT INTO dm_object_parent 
						(object_id,parent_id) 
						VALUES 
						('".$objectArr[$row]."','".$_POST["parentId"]."');";

		}

		if ($sql) {
			if (db_query($conn,$sql)) $successMessage = _OBJECT_MOVE_SUCCESS;
			else $errorMessage = _OBJECT_MOVE_ERROR;
		}
		
	} else $errorMessage = _OBJECT_MOVE_SUCCESS;


}


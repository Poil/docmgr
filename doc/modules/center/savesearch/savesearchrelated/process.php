<?

$objectId = $_SESSION["objectId"];

if ($_SESSION["objectId"]==NULL) {
	$errorMessage = "No object is specified";
	return false;
}
    

if ($_POST["pageAction"]=="update") {

	$errorMessage = null;
	$successMessage = null;

	beginTransaction($conn);

	if (@in_array($objectId,$_POST["relateId"])) {

		$errorMessage = _RELATE_SELF_ERROR;
	
	} else {

		$sql = "DELETE FROM dm_object_related WHERE object_id='$objectId';";
	
		if (is_array($_POST["relateId"])) {
			foreach ($_POST["relateId"] AS $rId) {
				if (!$rId) continue;
				$sql .= "INSERT INTO dm_object_related (object_id,related_id) VALUES ('$objectId','$rId');";
			}
		}

		if (db_query($conn,$sql)) $successMessage = _UPDATE_SUCCESS;
		else $errorMessage = _UPDATE_ERROR;

		//if desired, reverse teh relation
		if ($_POST["reverseRelate"]) {

			$sql = null;		
			//create a relation for our selected objects back to the parent
			foreach ($_POST["relateId"] AS $rId) {
				if (!$rId) continue;
				$sql .= "DELETE FROM dm_object_related WHERE object_id='$rId' AND related_id='$objectId';";
				$sql .= "INSERT INTO dm_object_related (object_id,related_id) VALUES ('$rId','$objectId');";
			}
			
			if (db_query($conn,$sql)) $successMessage = _UPDATE_SUCCESS;
			else $errorMessage = _UPDATE_ERROR;
		
		}

		endTransaction($conn);

	}

}


$sql = "SELECT related_id FROM dm_object_related WHERE object_id='$objectId';";
$arr = total_result($conn,$sql);
$relatedValue = &$arr["related_id"];

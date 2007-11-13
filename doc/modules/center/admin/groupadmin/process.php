<?

$pageAction = $_POST["pageAction"];
$name = $_POST["name"];
$groupId = $_POST["group_id"];

switch ($pageAction) {

	case "update":

		$sql = "UPDATE auth_groups SET name='$name' WHERE id='$groupId'";

		if (db_query($conn,$sql)) $successMessage = _UPDATE_SUCCESS;
		else $errorMessage = _UPDATE_ERROR;

		$level = null;
		for ($row=0;$row<count($_POST["perm"]);$row++) $level |= $_POST["perm"][$row];

		//make sure user has ability to update admin bit
                if (bitset_compare($level,ADMIN,null) && !bitset_compare(BITSET,ADMIN,null)) $errorMessage = _ADMINPERM_UPDATE_ERROR;
		else permInsert($conn,"auth_groupperm","group_id",$groupId,$level);

		break;

	case "add":

		//make sure the group does not exist
		$sql = "SELECT id FROM auth_groups WHERE name='$name';";
		$num = num_result($conn,$sql);

		if ($num>0) $errorMessage = _GROUP_EXISTS;
		else {

			//add the new group
			$sql = "INSERT INTO auth_groups (name) VALUES ('$name')";

			if ($result = db_query($conn,$sql)) {

				$groupId = db_insert_id("auth_groups","id",$conn,$result);
				$successMessage = _CREATE_SUCCESS;

			}
			else $errorMessage = _CREATE_ERROR;

			$level = null;	

			for ($row=0;$row<count($_POST["perm"]);$row++) $level |= $_POST["perm"][$row];

			//make sure user has ability to update admin bit
			if (bitset_compare($level,ADMIN,null) && !bitset_compare(BITSET,ADMIN,null)) $errorMessage = _ADMINPERM_UPDATE_ERROR;
			else permInsert($conn,"auth_groupperm","group_id",$groupId,$level);

		}

		break;
		
	//delete the group
	case "delete":

		$sql = "DELETE FROM auth_groups WHERE id='$groupId';";

		if (db_query($conn,$sql)) {

			$sql = "DELETE FROM auth_groupperm WHERE group_id='$groupId';";
			$sql .= "DELETE FROM dm_object_perm WHERE group_id='$groupId';";
			db_query($conn,$sql);

			$successMessage = _DELETE_SUCCESS;
			$groupId = null;
		
		}
		else $errorMessage = _DELETE_ERROR;

		break;
		

}

//pull the info for this group
if ($groupId) {

	$sql = "SELECT * FROM auth_groups WHERE id='$groupId'";
	$groupInfo = single_result($conn,$sql);

	$sql = "SELECT bitset FROM auth_groupperm WHERE group_id='$groupId';";
	$temp = single_result($conn,$sql);
	
	$groupBitset = &$temp["bitset"];

}

if ($groupId && $groupInfo["name"]) {

	$question_visibility = "visible";
	$question_position = "static";
	$cancel_visibility = "hidden";
	$cancel_position = "absolute";
	$pass_visibility = "hidden";
	$pass_position = "absolute";

} else {

	$question_visibility = "hidden";
	$question_position = "absolute";
	$cancel_visibility = "visible";
	$cancel_position = "static";
	$pass_visibility = "visible";
	$pass_position = "static";

}

$tabControl = 	"
				<a href=\"#\"  onClick=\"submitForm('update');\">"._UPDATE."</a>
				&nbsp;&nbsp;
				";

if ($groupId)  {
	$tabControl .= "
				|&nbsp;&nbsp;
				<a href=\"#\"  onClick=\"return submitForm('delete');\">"._DELETE."</a>
				&nbsp;&nbsp;|&nbsp;&nbsp;
				<a href=\"#\"  onClick=\"submitForm('clear');\">"._CLEAR_FORM."</a>
				&nbsp;&nbsp;
				";
}


$toolBar = modTreeMenu($module);



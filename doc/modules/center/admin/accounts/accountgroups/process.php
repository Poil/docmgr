<?
$pageAction = $_POST["pageAction"];
$accountId = $_SESSION["accountId"];

if ($_SESSION["accountId"]==NULL) {
	$errorMessage = "No account is specified";
	return false;
}

if ($accountId==USER_ID && !bitset_compare(BITSET,ADMIN,null)) {
	$permErrorMessage = "You cannot alter your own groups.";
	return false;
}

if ($pageAction=="update") {

	$sql = "DELETE FROM auth_grouplink WHERE accountid='$accountId';";
	
	for ($row=0;$row<count($_POST["accountGroup"]);$row++) {
	
		$sql .= "INSERT INTO auth_grouplink 	(accountid,groupid) 
							VALUES 
							('$accountId','".$_POST["accountGroup"][$row]."');";
	
	}

	if (db_query($conn,$sql)) $successMessage = _UPDATE_SUCCESS;
	else $errorMessage = _UPDATE_ERROR;	

}


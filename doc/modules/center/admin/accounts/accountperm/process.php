<?
$pageAction = $_POST["pageAction"];
$accountId = $_SESSION["accountId"];

if ($_SESSION["accountId"]==NULL) {
	$errorMessage = "No account is specified";
	return false;
}

if ($pageAction=="update") {
	
	$level = null;
	$errorMessage = null;
	
	//collect all set bits
	for ($row=0;$row<count($_POST["perm"]);$row++) $level |= $_POST["perm"][$row];

	//make sure user has ability to update admin bit
	if (bitset_compare($level,ADMIN,null) && !bitset_compare(BITSET,ADMIN,null)) $errorMessage = _ADMINPERM_UPDATE_ERROR;
	
	//proceed if allowed	
	if (!$errorMessage) {

		permInsert($conn,"auth_accountperm","account_id",$accountId,$level);
		if ($_POST["accountEnable"]=="TRUE"){
			$sql = "UPDATE auth_accountperm SET enable=TRUE,failed_logins_locked=FALSE,locked_time=NULL,failed_logins=0 WHERE account_id='$accountId';";
		}
		else {
			$sql = "UPDATE auth_accountperm SET enable=FALSE WHERE account_id='$accountId';";
		}
		db_query($conn,$sql);

		$successMessage = _UPDATE_SUCCESS;

	}
	
}


$accountInfo = returnAccountInfo($conn,$accountId,null);


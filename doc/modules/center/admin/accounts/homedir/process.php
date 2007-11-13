<?
$pageAction = $_POST["pageAction"];
$accountId = $_SESSION["accountId"];

if ($_SESSION["accountId"]==NULL) {
	$errorMessage = "No account is specified";
	return false;
}

if ($pageAction=="update") {

	// make sure that the user is even allowed to update their profile
	$option = null;
	$option["conn"] = $conn;


	if ( bitset_compare(BITSET,MANAGE_USERS,ADMIN) || bitset_compare(BITSET,EDIT_PROFILE,ADMIN)){

		//change the language
		if ($_POST["homeDir"]) $_SESSION["homeDir"] = $_POST["homeDir"];
		$opt = null;
		$opt["home_directory"] = $_POST["homeDir"];
		
		updateAccountSetting($conn,$accountId,$opt);

	}


}


$accountInfo = returnAccountInfo($conn,$accountId,null);

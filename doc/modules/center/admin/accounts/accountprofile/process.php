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

		$option["first_name"] = $_POST["firstName"];
		$option["last_name"] = $_POST["lastName"];
		$option["email"] = $_POST["email"];
		$option["phone"] = $_POST["phone"];
		$option["accountId"] = $_SESSION["accountId"];

		//only allow login changes if the user is manage_users or admin
		if (bitset_compare(BITSET,MANAGE_USERS,ADMIN)) $option["login"] = $_POST["login"];

		$arr = updateAccountProfile($option);
		extract($arr);	//this pulls successMessage or errorMessage from our results

		//change the language
		if ($_POST["setLang"]) $_SESSION["curLang"] = $_POST["setLang"];
		$opt = null;
		$opt["language"] = $_POST["setLang"];
		updateAccountSetting($conn,$accountId,$opt);

	}


}


$accountInfo = returnAccountInfo($conn,$accountId,null);

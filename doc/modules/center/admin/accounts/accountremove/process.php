<?
$pageAction = $_POST["pageAction"];
$accountId = $_SESSION["accountId"];

if ($_SESSION["accountId"]==NULL) {
	$errorMessage = "No account is specified";
	return false;
}

if ($pageAction=="delete") {

	$successMessage = null;

	$option = null;
	$option["conn"] = $conn;
	$option["accountId"] = $_SESSION["accountId"];

	if ($_POST["deleteConfirm"]=="yes") {
	
		$arr = deleteAccount($option);
		extract($arr);	//this pulls successMessage or errorMessage from our results
		
		if ($successMessage) $accountId = null;
		
	} else $errorMessage = _ACCOUNT_REMOVE_CONFIRM_ERROR;

}

if ($accountId) $accountInfo = returnAccountInfo($conn,$accountId,null);
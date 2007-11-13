<?
$pageAction = $_POST["pageAction"];

if ($_SESSION["accountId"]==NULL) {
	$errorMessage = "No account is specified";
	return false;
}

if ($pageAction=="update") {

	if ($_POST["password1"]!=$_POST["password2"]) {
		$errorMessage = _PASSWORD_NOMATCH;
		return false;
	}

	$option = null;
	$option["conn"] = $conn;
	$option["password"] = $_POST["password1"];
	$option["accountId"] = $_SESSION["accountId"];
	
	$arr = updateAccountPassword($option);
	extract($arr);	//this pulls successMessage or errorMessage from our results

}

<?
$pageAction = $_POST["pageAction"];
$accountId = $_SESSION["accountId"];

if ($pageAction=="insert") {

	$option = null;
	$option["conn"] = $conn;
	$option["first_name"] = $_POST["firstName"];
	$option["last_name"] = $_POST["lastName"];
	$option["login"] = $_POST["login"];
	$option["email"] = $_POST["email"];
	$option["location"] = $_POST["location"];
	$option["phone"] = $_POST["phone"];
	$option["homeDirectory"] = $_POST["homeDirectory"];
	$option["password"] = $_POST["password1"];

	$successMessage = null;
	
	$arr = insertAccount($option);
	extract($arr);	//this pulls accountId and successMessage or errorMessage from our results

	if ($successMessage) {

		//set the language
		$opt = null;
		$opt["language"] = $_POST["setLang"];
		updateAccountSetting($conn,$accountId,$opt);

	}

}

//hide the site headers
$hideHeader = 1;




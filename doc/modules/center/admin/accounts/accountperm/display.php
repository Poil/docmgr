<?

//permissions for this app from our xml file
$permArray = $_SESSION["definePermArray"];

if ($accountInfo) {

	$sql = "SELECT bitset,enable,failed_logins FROM auth_accountperm WHERE account_id='$accountId'";
	$permInfo = single_result($auth_conn,$sql);

}

/*************************************************************
	Lay it all out
*************************************************************/

//basic info layout
$siteContent .= "<div class=\"pageHeader\">
		"._BASIC_ACCOUNT_PERM.":
		</div>
		<form name=\"pageForm\" method=post onSubmit=\"return formCheck();\">
		<input type=hidden name=\"pageAction\" id=\"pageAction\" value=\"update\">
		<input type=hidden name=\"module\" id=\"module\" value=\"accounts\">
		<input type=hidden name=\"includeModule\" id=\"includeModule\" value=\"accountperm\">
		<ul>

		";

$siteContent .= createPermCheckbox($permInfo["bitset"]);		

$enableNo = null;
$enableYes = null;

if ($permInfo["enable"]=="f") $enableNo = " CHECKED ";
else $enableYes = " CHECKED ";

$siteContent .= " <br>";
if ($permInfo["failed_logins"] > 0) {
	$siteContent .= "<br> <font color=\"red\">There have been </font><font color=\"black\"><B>".$permInfo["failed_logins"]."</B></font> <font color=\"red\">failed login attempts against this account</font><br><BR>";
}
$siteContent .= "
		<div class=\"formHeader\">
		"._ENABLE_ACCOUNT."?
		</div>
		<input type=radio ".$enableYes." name=\"accountEnable\" id=\"accountEnable\" value=\"TRUE\"> "._YES."
		&nbsp;&nbsp;&nbsp;
		<input type=radio ".$enableNo." name=\"accountEnable\" id=\"accountEnable\" value=\"FALSE\"> "._NO."
		<br><br>
		<input type=submit value=\""._SUBMIT_CHANGES."\">
		</ul>
		</form>
		";

?>

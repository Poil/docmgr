<script language="javascript">
self.focus();

function genLogin() {

	var fn = document.pageForm.firstName.value;
	var ln = document.pageForm.lastName.value;
	var login = document.pageForm.login.value;
	
	if (fn != "" && ln != "" && login == "") { 

		var newLogin = fn.substr(0,1).toLowerCase() + ln.toLowerCase();
		document.pageForm.login.value = newLogin;
		
	}

}

function formCheck() {

	if (document.getElementById("login").value == "") {
		alert("You must enter a login name");
		document.getElementById("login").focus();
		return false;
	}

	if (document.getElementById("password1").value == "") {
		alert("You must enter a password");
		document.getElementById("password1").focus();
		return false;
	}

	if (document.getElementById("password1").value != document.getElementById("password2").value) {
		alert("The passwords do not match");
		document.getElementById("password1").focus();
		return false;
	}
	
	return true;

}

function refreshParent() {

	//get our recent accountId if there is any
	var accountId = document.pageForm.accountId.value;

	//refresh the parent to show the recently added account
	if (accountId!="") {
		newUrl = "index.php?module=accounts&accountId=" + accountId; 
		window.opener.location.href = newUrl;
	}

}

function closeWindow() {

	//show the account in the parent window
	refreshParent();
	
	//close this window
	self.close();
	
}

</script>

<?

$num = count($_SESSION["availLang"]);
$langText = null;

if ($num > 0) {

	$langText = "<br><br><div class=\"formHeader\">"._USE_LANG."</div>
			<select name=\"setLang\" size=1>\n";

	foreach ($_SESSION["availLang"] AS $lang) {

		if ($_SESSION["curLang"]==$lang) $selected = " SELECTED ";
		else $selected = null;

		$langText .= "<option ".$selected." value=\"".$lang."\">".$lang;
	}

	$langText .= "</select>";

} else $langText = "<input type=hidden name=\"setLang\" id=\"setLang\" value=\"".DEFAULT_LANG."\">\n";

$siteContent .= "<div style=\"padding:10px\">
		<div class=\"pageHeader\">
		Create New Account
		</div>
		<br>
		<form name=\"pageForm\" method=post onSubmit=\"return formCheck();\">	
		<input type=hidden name=\"pageAction\" id=\"pageAction\" value=\"insert\">
		<input type=hidden name=\"module\" id=\"module\" value=\"createaccount\">
		<input type=hidden name=\"accountId\" id=\"accountId\" value=\"".$accountId."\">
		<table width=100%>
		<tr><td width=50% valign=top>
			<div class=\"formHeader\">"._FIRST_NAME."</div>
			<input type=text name=\"firstName\" id=\"firstName\" value=\"".$accountInfo["first_name"]."\" onBlur=\"genLogin()\">
			<br><br>
			<div class=\"formHeader\">"._LAST_NAME."</div>
			<input onBlur=\"genLogin();\" type=text name=\"lastName\" id=\"lastName\" value=\"".$accountInfo["last_name"]."\" onBlur=\"genLogin()\">
			<br><br>
			<div class=\"formHeader\">"._LOGIN."</div>
			<input type=text name=\"login\" id=\"login\" value=\"".$accountInfo["login"]."\">
			<br><br>
			<div class=\"formHeader\">"._ENTER_NEW_PASSWORD."</div>
			<input type=password name=\"password1\" id=\"password1\" value=\"\">
			<br><br>
			<div class=\"formHeader\">"._AGAIN_TO_CONFIRM."</div>
			<input type=password name=\"password2\" id=\"password2\" value=\"\">
			<br><br>
		</td><td width=50% valign=top>
			<div class=\"formHeader\">"._EMAIL."</div>
			<input type=text size=30 name=\"email\" id=\"email\" value=\"".$accountInfo["email"]."\">
			<br><br>
			<div class=\"formHeader\">"._PHONE."</div>
			<input type=text size=30 name=\"phone\" id=\"phone\" value=\"".$accountInfo["phone"]."\">
			".$langText."
		</td></tr>
		</table>
		<div style=\"float:left\">
		<input type=submit value=\""._SUBMIT_CHANGES."\">
		</div>
		<div style=\"float:right\">
		<input type=button onClick=\"closeWindow();\" value=\""._CLOSE_WINDOW."\">
		</div>
		</form>
		<script language=\"javascript\">document.pageForm.firstName.focus();</script>
		</div>
		";


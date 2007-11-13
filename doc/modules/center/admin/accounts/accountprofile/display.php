<script language="javascript">
function genLogin() {

	var fn = document.pageForm.fn.value;
	var ln = document.pageForm.ln.value;
	var login = document.pageForm.account_login.value;
	
	if (fn != "" && ln != "" && login == "") { 

		var newLogin = fn.substr(0,1).toLowerCase() + ln.toLowerCase();
		document.pageForm.account_login.value = newLogin;
		
	}

}

function formCheck() {

	if (document.getElementById("login").value == "") {
		alert("You must enter a login name");
		return false;
	}

	return true;
}

</script>

<?

$availLang = retrieveLang();

$num = count($availLang);
$langText = null;

if ($num > 0) {

	$langText = "<br><br><div class=\"formHeader\">"._USE_LANG."</div>
			<select name=\"setLang\" size=1>\n";

	foreach ($availLang AS $lang) {

		if ($_SESSION["curLang"]==$lang) $selected = " SELECTED ";
		else $selected = null;

		$langText .= "<option ".$selected." value=\"".$lang."\">".$lang;
	}

	$langText .= "</select>";

} else $langText = "<input type=hidden name=\"setLang\" id=\"setLang\" value=\""._DEFAULT_LANG."\">\n";

if ((bitset_compare(BITSET,MANAGE_USERS,ADMIN))||(bitset_compare(BITSET,EDIT_PROFILE,null))) {
$siteContent .= "<div class=\"pageHeader\">
		"._PROFILE_INFO."
		</div>
		<br>
			<form name=\"pageForm\" method=post onSubmit=\"return formCheck();\">	
			<input type=hidden name=\"pageAction\" id=\"pageAction\" value=\"update\">
			<input type=hidden name=\"module\" id=\"module\" value=\"accounts\">
			<input type=hidden name=\"includeModule\" id=\"includeModule\" value=\"accountprofile\">
			<div class=\"formHeader\">"._FIRST_NAME."</div>
			<input type=text name=\"firstName\" id=\"firstName\" value=\"".$accountInfo["first_name"]."\" onBlur=\"genLogin()\">
			<br><br>
			<div class=\"formHeader\">"._LAST_NAME."</div>
			<input type=text name=\"lastName\" id=\"lastName\" value=\"".$accountInfo["last_name"]."\" onBlur=\"genLogin()\">
			<br><br>
			<div class=\"formHeader\">"._LOGIN."</div>";

if (!bitset_compare(BITSET,MANAGE_USERS,ADMIN)){
	$siteContent .= $accountInfo["login"];
}
else {	
	$siteContent .= "<input type=text ".$loginReadonly." name=\"login\" id=\"login\" value=\"".$accountInfo["login"]."\">";
}
$siteContent .= 
			"<br><br>
			<div class=\"formHeader\">"._EMAIL."</div>
			<input type=text size=30 name=\"email\" id=\"email\" value=\"".$accountInfo["email"]."\">
			<br><br>
			<div class=\"formHeader\">"._PHONE."</div>
			<input type=text size=20 name=\"phone\" id=\"phone\" value=\"".$accountInfo["phone"]."\">
			".$langText."
			<br><br><br>
			<input type=submit value=\""._SUBMIT_CHANGES."\">
			</form>
			";
}
else 
{
	// Just display the user's information

$siteContent .= "<div class=\"pageHeader\">
		"._PROFILE_INFO."
		</div>
		<br>
			<div class=\"formHeader\">"._FIRST_NAME."</div>
			".$accountInfo["first_name"]."
			<br><br>
			<div class=\"formHeader\">"._LAST_NAME."</div>
			".$accountInfo["last_name"]."
			<br><br>
			<div class=\"formHeader\">"._LOGIN."</div>
			".$accountInfo["login"]."
			<br><br>
			<div class=\"formHeader\">"._EMAIL."</div>
			".$accountInfo["email"]."
			<br><br>
			<div class=\"formHeader\">"._PHONE."</div>
			".$accountInfo["phone"]."
			";
}
	if ($accountInfo["modified_by"]) {

		$temp = returnAccountInfo($auth_conn,$accountInfo["modified_by"],null);
		$tv = date_time_view($accountInfo["date_modified"]);
	
		$siteContent .= "<div class=\"formHeader\">
				Last Modified Date & Time
				</div>
				".$tv[0]." at ".$tv[1]." by ".$temp["login"]."
				<br><br>
				";

	}

	if ($accountInfo["created_by"]) {
	
		$temp = returnAccountInfo($auth_conn,$accountInfo["created_by"],null);
		$tv = date_time_view($accountInfo["date_created"]);
	
		$siteContent .= "	<div class=\"formHeader\">
				Account Creation Date & Time
				</div>
				".$tv[0]." at ".$tv[1]." by ".$temp["login"]."
				<br><br>
				";
	
	
	
	}



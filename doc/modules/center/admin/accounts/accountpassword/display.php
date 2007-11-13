<script language="javascript">

function checkPass() {

	if (document.getElementById("password1").value != document.getElementById("password2").value) {
		alert("<?echo _PASSWORD_NOMATCH;?>");
		document.getElementById("password1").focus();
		return false;
	}

	if (document.getElementById("password1").value=="") {
		alert("<?echo _MUST_ENTER_PASSWORD;?>");
		document.getElementById("password1").focus();
		return false;
	}

}

</script>

<?

$siteContent .= "<div class=\"pageHeader\">
		"._MTDESC_ACCOUNTPASSWORD."
		</div>
		<form name=\"pageForm\" onSubmit=\"return checkPass();\" method=post>
		<input type=hidden name=\"pageAction\" id=\"pageAction\" value=\"update\">
		<input type=hidden name=\"module\" id=\"module\" value=\"accounts\">
		<input type=hidden name=\"includeModule\" id=\"includeModule\" value=\"accountpassword\">
		<div class=\"formHeader\">
		"._ENTER_NEW_PASSWORD."
		</div>
		<input type=password name=\"password1\" id=\"password1\" value=\"\">
		<br><br>
		<div class=\"formHeader\">
		"._AGAIN_TO_CONFIRM."
		</div>
		<input type=password name=\"password2\" id=\"password2\" value=\"\">
		<br><br>
		<input type=submit value=\""._RESET_PASSWORD."\">
		</form>
		";


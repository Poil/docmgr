<?

if ($accountId) {

	$siteContent .= "<div class=\"pageHeader\">
			"._MTDESC_ACCOUNTREMOVE."
			</div>
			<br>
			<form name=\"pageForm\" onSubmit=\"return checkPass();\" method=post>
			<input type=hidden name=\"pageAction\" id=\"pageAction\" value=\"delete\">
			<input type=hidden name=\"module\" id=\"module\" value=\"accounts\">
			<input type=hidden name=\"includeModule\" id=\"includeModule\" value=\"accountremove\">
			<div class=\"formHeader\">
			"._ACCOUNT_REMOVE_CONFIRM." \"".$accountInfo["login"]."\"?
			</div>
			<input type=radio CHECKED name=deleteConfirm id=deleteConfirm value=\"no\"> "._NO."	
			&nbsp;&nbsp;&nbsp;			
			<input type=radio name=deleteConfirm id=deleteConfirm value=\"yes\"> "._YES."
			<br><br>
			<input type=submit value=\""._REMOVE_ACCOUNT."\">
			</form>
			";

}


<?
$content = "<table cellpadding=3 border=0>
			<tr><td>
			"._WELCOME." ".USER_FN."
			</td><td align=right>
			";
	
if ((bitset_compare(BITSET,EDIT_PROFILE,ADMIN)) || (bitset_compare(BITSET,EDIT_PASSWORD,ADMIN)))
	$content .= "<a href=\"index.php?module=accounts&accountId=".USER_ID."\" class=\"main\">["._PROFILE."]</a>";

$content .= "	</td></tr>";

if (count($_SESSION["availLang"]) > 1) {

	$content .= "<form name=\"langForm\" method=post><tr><td colspan=2>
			<div>
			"._CHANGE_LANGUAGE."
			</div>
			<select onChange=\"document.langForm.submit()\"
				name=\"setLang\" 
				size=1 
				class=\"dropdownSmall\">";

	foreach ($_SESSION["availLang"] AS $lang) {

		if ($_SESSION["curLang"]==$lang) $selected = " SELECTED ";
		else $selected = null;

		$content .= "<option ".$selected." value=\"".$lang."\">".$lang;
	}
	$content .= "</select></td></tr></form>";
	
}

$content .= "</table>";
$content .= "	<table><tr><td>Your last successful login was: ".$_SESSION["last_login"]." Eastern Time</td></tr>";
if ($_SESSION["failed_logins"] > 0) {
   $content .= "	<tr><td><font color=\"red\"><B>".$_SESSION["failed_logins"]." </B> failed attempt(s)</font></td></tr>";
}
$content .= "</table>";

$option = null;
$option["leftHeader"] = _DOCMGR_MESSAGES;
$option["content"] = $content;

$leftColumnContent .= leftColumnDisplay($option);

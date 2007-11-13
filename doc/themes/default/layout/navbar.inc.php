<?
/************************************************************************************

	Create our links for the main Navigation Bar

************************************************************************************/

//get all modules under this one.  Basically finds all owner paths with this module path
$keys = @array_keys($siteModList["owner_path"],"modules/center/");

$content = "<table border=0 width=100%>";

$counter = "0";

$num = count($keys);

for ($row=0;$row<$num;$row++) {

	$key = $keys[$row];

	$permCheck = setPermCheck($siteModList["permissions"][$key]);

	if ($permCheck) {

		if (!bitset_compare(BITSET,$permCheck,ADMIN)) $hide = 1;
		else $hide = null;
	}
	else $hide = null;

	if ($siteModList["hidden"][$key]=="1") $hide = 1;
	if ($siteModList["auth_only"][$key]=="1" && !$_SESSION["authorize"]) $hide = 1;

	if (!$hide) {

		$modText = "_MT_".strtoupper($siteModList["link_name"][$key]);

		if (defined($modText)) $text = constant($modText);
		else $text = $siteModList["module_name"][$key];

		$navBar .= "&nbsp;&nbsp;<a href=\"index.php?module=".$siteModList["link_name"][$key]."\" class=\"navBarLink\">".$text."</a>";
		$navBar .= "&nbsp;&nbsp;|";

	}

}

if (defined("USER_ID")) {

	if ((bitset_compare(BITSET,EDIT_PROFILE,ADMIN)) || (bitset_compare(BITSET,EDIT_PASSWORD,ADMIN))) {
        	$navBar .= "&nbsp;&nbsp;<a href=\"index.php?module=accounts&accountId=".USER_ID."\">"._PROFILE."</a>";
        	$navBar .= "&nbsp;&nbsp;|";
	}

	$navBar .= "&nbsp;&nbsp;<a href=\"index.php?logout=1\" class=\"navBarLink\">"._LOGOUT."</a>";
	$navBar .= "&nbsp;&nbsp;";
}

//show the module toolbar and nav toolbar is passed
if ($toolBar) $toolBar = "<div class=\"toolBar\">".$toolBar."</div>\n";


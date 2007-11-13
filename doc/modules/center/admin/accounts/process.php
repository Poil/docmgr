<?

$pageAction = $_POST["pageAction"];
$includeModule = $_REQUEST["includeModule"];

if ($_REQUEST["accountId"]!=NULL && !$pageAction) $accountId = $_REQUEST["accountId"];
elseif ($_SESSION["accountId"]!=NULL) $accountId = $_SESSION["accountId"];

if (!$includeModule && $accountId) $includeModule="accountprofile";

/********************************************************************
	process our search string
********************************************************************/

if ($pageAction=="search") {

	$searchString = $_REQUEST["searchString"];

	$searchHeader = "<div class=\"toolHeader\">
			Results For \"".$searchString."\"
			</div>
			";

	$option = null;
	
	if (defined("USE_LDAP")) {
		if (defined("GLOBAL_ADMIN")) $option["search_base"] = LDAP_BASE;
		else $option["search_base"] = SEARCH_BASE;
	}

	//create our search parameter array
	$option = null;
	$option["conn"] = $conn;
	$option["searchParm"]["login"] = $searchString;
	$option["searchParm"]["first_name"] = $searchString;
	$option["searchParm"]["last_name"] = $searchString;
	$option["wildcard"] = "last";

	$searchResults = returnAccountList($option);

	//if we find one match, load this account
	if ($searchResults["count"]==1) {
		$accountId = $searchResults["id"][0];
		$pageAction = null;
		$_POST["pageAction"] = null;
		$includeModule="accountprofile";
	}
	else {
		$_SESSION["accountId"] = null;
		$accountId = null;
		$includeModule = null;
	}

}

if ($accountId!=NULL) {

	$_SESSION["accountId"] = $accountId;
	$accountInfo = returnAccountInfo($conn,$accountId,LDAP_BASE);

	//make sure this user can alter the account.  If the account is an admin, then they cannot
	if ($accountId!=USER_ID) {

		//see if this user has permissions to edit other users
		if (!bitset_compare(BITSET,MANAGE_USERS,ADMIN))
			$siteErrorMessage = "You do not have permissions to edit other users\n";
		else {

			//see if this is an non-admin user trying to edit an administrator			 
	 		if (!bitset_compare(BITSET,ADMIN,null)) {

	 			$bitset_temp = returnUserBitset($conn,$accountId);	
	 			if (bitset_compare($bitset_temp,ADMIN,null)) 
	 				$permErrorMessage = "You cannot edit this user.  This user is an administrator.";
			}

		}
			
	}

}

/****************************************************************************************
	make sure this user can access this module, then extract error message
	if there is any.
*****************************************************************************************/
$permError = null;
$arr = null;

//process our module permissions
$arr = checkModPerm($includeModule,BITSET);
if (is_array($arr)) extract($arr);

if ($permError) die($errorMessage);

//processing for our sub module.We cannot call these with a function, or
//we do not get information passed from process to display like we want
$processPath = $siteModInfo["$includeModule"]["module_path"]."process.php";
$functionPath = $siteModInfo["$includeModule"]["module_path"]."function.php";

if (file_exists($processPath)) include($processPath);
if (file_exists($functionPath)) include($functionPath);

$toolBar = modTreeMenu($module);

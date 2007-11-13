<?
/************************************************************************************************************
	ldap.php
	
	Holds account processing and search functions for
	an ldap database
	
	02-07-2005 - Fixed returnAccountInfo returning an error if it did not find an account Id (Eric L.)
	02-14-2005 - Split group info
	11-20-2005 - Stripped file down more and added support for an ldap map file

***********************************************************************************************************/

//call our extended attributes file
if (defined("LDAP_SHADOW")) include("ldap_shadow.php");
if (defined("LDAP_SAMBA")) include("ldap_samba.php");
if (defined("LDAP_EWP")) include("ldap_ewp.php");

//sanity checking.  If our attributes are not defined, this won't work
if (!defined("LDAP_UID")) die("ldap attribute \"LDAP_UID\" is not defined");
if (!defined("LDAP_UIDNUMBER")) die("ldap attribute \"LDAP_UIDNUMBER\" is not defined");
if (!defined("LDAP_CN")) die("ldap attribute \"LDAP_CN\" is not defined");
if (!defined("LDAP_GECOS")) die("ldap attribute \"LDAP_GECOS\" is not defined");
if (!defined("LDAP_SN")) die("ldap attribute \"LDAP_SN\" is not defined");
if (!defined("LDAP_GIVENNAME")) die("ldap attribute \"LDAP_GIVENNAME\" is not defined");
if (!defined("LDAP_TELEPHONENUMBER")) die("ldap attribute \"LDAP_TELEPHONENUMBER\" is not defined");
if (!defined("LDAP_MAIL")) die("ldap attribute \"LDAP_MAIL\" is not defined");
if (!defined("LDAP_USERPASSWORD")) die("ldap attribute \"LDAP_USERPASSWORD\" is not defined");


function checkAccount($login) {

	$optionArray["accountLogin"] = $login;
	$info = @ldap_account_search($optionArray);	

	if (is_array($info)) return false;
	else return true;

}

function checkValidLogin($login) {

	//only allow special chars _ and . in a login
	$arr = array(   ",","/","?","'","\"","!","@","#",
			"%","^","&","*","(",")","+","=",
			"}","{","[","]","|","\\",":",";","<",
			">"
			);

	$num = count($arr);

	for ($row=0;$row<$num;$row++) if (strstr($login,$arr[$row])) return false;

	return true;

}

function getNextAccountId() {

	$opt["search_base"] = LDAP_ROOT;
	$arr = returnAccountList($opt);

	rsort($arr["id"]);
	
	return $arr["id"][0] + 1;

}

function insertAccount($option) {

	$ds = ldap_connect(LDAP_SERVER,LDAP_PORT);
	ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, LDAP_PROTOCOL);
	$r = ldap_bind($ds,BIND_DN,BIND_PASSWORD);

	extract($option);

	$arr = array();
	$result = array();
	
	if (!$login) {
		$result["errorMessage"] = "You must enter a login to create the account";
		return $result;
	}

	//grab a first name if there is none
	if (!$firstName) $firstName = $login;

	//make sure our login doesn't have bad characters in it
	if (!checkValidLogin($login)) {
		$result["errorMessage"] = "Invalid characters used in login";
		return $result;
	}

	
	// prepare our objectclass and common data
	$arr["objectclass"][0]="person";
	$arr["objectclass"][1]="organizationalPerson";
	$arr["objectclass"][2]="top";
	$arr["objectclass"][3]="inetOrgPerson";
	$arr["objectclass"][4]="posixAccount";

	$dn = LDAP_UID."=".$login.",".LDAP_BASE;

	if ($first_name && $last_name) $fullname = $first_name." ".$last_name;
	else {
		if ($first_name) $fullname = $first_name;
		elseif ($last_name) $fullname = $last_name;
	}

	if ($fullname) $arr[LDAP_CN] = $fullname;
	if ($fullname) $arr[LDAP_GECOS] = $fullname;
	if ($last_name) $arr[LDAP_SN] = $last_name;
	if ($first_name) $arr[LDAP_GIVENNAME] = $first_name;
	if ($phone) $arr[LDAP_TELEPHONENUMBER] = $phone;
	if ($email) $arr[LDAP_MAIL] = $email;

	$accountId = getNextAccountId();

	$arr[LDAP_UID] = $login;
	$arr[LDAP_UIDNUMBER] = $accountId;
	$arr[LDAP_GIDNUMBER] = DEFAULT_GID;

	if (!$homeDirectory) $homeDirectory = "/home/".$login;
	$arr["homeDirectory"] = $homeDirectory;

	if (ldap_add($ds,"$dn",$arr)) {

		//add extended attributes if necessary
		if (defined("LDAP_SHADOW") && !addShadow($accountId)) $result["errorMessage"] = "Shadow addition failed";
		if (defined("LDAP_SAMBA") && !addSamba($accountId)) $result["errorMessage"] = "Samba addition failed";
		if (defined("LDAP_EWP")   && !addEWP($accountId,$option)) $result["errorMessage"] = "EWP addition failed";

		//set the password
		$option["accountId"] = $accountId;
		updateAccountPassword($option);

		//we failed, delete our entry
		if (!$result["errorMessage"]) {

			$result["successMessage"] = "Account created successfully";

			//insert a base permission record for this account
			$opt = null;
			$opt["account_id"] = $accountId;
			$opt["bitset"] = "0";
			$opt["enable"] = "t";
			if (defined("APP_ID")) $opt["app_id"] = APP_ID;

			dbInsertQuery($conn,"auth_accountperm",$opt);

		}
		
	}
	else $result["errorMessage"] = "Account creation failed";

	$result["accountId"] = $accountId;

	ldap_close($ds);
	return $result;

}


function updateAccountProfile($option) {

	$ds = ldap_connect(LDAP_SERVER,LDAP_PORT);
	ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, LDAP_PROTOCOL);
	$r = ldap_bind($ds,BIND_DN,BIND_PASSWORD);

	extract($option);

	$arr = array();
	$result = array();
	
	if ($accountId==NULL) {
		$result["errorMessage"] = "The account id must be passed to update the account";
		return $result;
	}

	$accountInfo = returnAccountInfo(null,$accountId,null);

	if ($login) {

		//make sure our login doesn't have bad characters in it
		if (!checkValidLogin($login)) {
			$result["errorMessage"] = "Invalid characters used in login";
			return $result;
		}

		//they do not match, check to see if the new one exists
		if ($login!=$accountInfo["login"]) {

			//check to make sure this does not exist.
			if (!checkAccount($login)) {
				$result["errorMessage"] = "It looks like you are trying to change your username.<br>
							The username \"".$login."\" is already in use.
							Please choose again.";

				return $result;
			} else {
				$result = renameAccount($ds,$option,$accountInfo);
				return $result;
			}

		}

	} else $login = $accountInfo["login"];
	
	//since we go by account id we should be able to just yank the search base from here
	$objectClass = $accountInfo["objectClass"];
	$dn = $accountInfo["dn"];

	if ($first_name && $last_name) $fullname = $first_name." ".$last_name;
	else {
		if ($first_name) $fullname = $first_name;
		elseif ($last_name) $fullname = $last_name;
	}

	if ($fullname) $arr[LDAP_CN] = $fullname;
	if ($fullname) $arr[LDAP_GECOS] = $fullname;
	if ($last_name) $arr[LDAP_SN] = $last_name;
	if ($first_name) $arr[LDAP_GIVENNAME] = $first_name;
	if ($phone) $arr[LDAP_TELEPHONENUMBER] = $phone;
	if ($email) $arr[LDAP_MAIL] = $email;

	if (ldap_modify($ds,"$dn", $arr)) $result["successMessage"] = "Account updated successfully";
	else $result["errorMessage"] = "Account updated failed";
	
	ldap_close($ds);
	return $result;

}

function createCryptPassword($password) {

	if (LDAP_CRYPT=="MD5") $cryptpw = "{MD5}".base64_encode(pack("H*",md5($password)));
	else $cryptpw = "{CRYPT}".crypt($password);

	return $cryptpw;

}


function updateAccountPassword($option) {

	$ds = ldap_connect(LDAP_SERVER,LDAP_PORT);
	ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, LDAP_PROTOCOL);
	$r = ldap_bind($ds,BIND_DN,BIND_PASSWORD);

	extract($option);

	$arr = array();
	$result = array();
	
	if ($accountId==NULL) {
		$result["errorMessage"] = "The account id must be passed to update the account";
		return $result;
	}

	if (!$password) {
		$result["errorMessage"] = "You must specify a password";
		return $result;
	}

	//get our current account info
	$accountInfo = returnAccountInfo(null,$accountId,null);
	$login = $accountInfo["login"];
	
	$dn = $accountInfo["dn"];

	$arr[LDAP_USERPASSWORD] = createCryptPassword($password);

	//set our samba password if enabled
	if (defined("LDAP_SAMBA")) updateSambaPassword($accountId,$password);
	if (defined("LDAP_EWP")) updateEWPPassword($accountId,$password);
	
	//run the query
	if (ldap_modify($ds,"$dn", $arr)) $result["successMessage"] = "Account updated successfully";
	else $result["errorMessage"] = "Account updated failed";
	
	ldap_close($ds);
	return $result;

}


//takes ldap results from ldap_search and makes them ready
//for reinsertion
function fixLdapArray($arr) {
	
	$newarr = array();

	$keys = array_keys($arr);
	
	for ($row=0;$row<count($keys);$row++) {

		$key = $keys[$row];

		if (is_numeric($key) || $key=="count" || $key=="objectClass") continue;

		if (is_array($arr[$key])) $newarr[$key] = $arr[$key][0];
		else $newarr[$key] = $arr[$key];
	
	}

	$c = 0;
	$keys = array_keys($arr["objectClass"]);
	for ($row=0;$row<count($keys);$row++) {

		$key = $keys[$row];

		if (is_numeric($arr["objectClass"][$key])) continue;
		$newarr["objectClass"][$c] = $arr["objectClass"][$key];
		$c++;
		
	}

	return $newarr;
}


function renameAccount($ds,$option,$accountInfo) {

	extract($option);

	//return the current info for this account
	$cnString = "(".LDAP_UIDNUMBER."=".$accountId.")";

	$sr = ldap_search($ds,LDAP_BASE,"$cnString");
	$entry = ldap_first_entry($ds,$sr);
	$arr = ldap_get_attributes($ds,$entry);

	//update the info if stuff passed from the form
	if ($first_name && $last_name) $fullname = $first_name." ".$last_name;
	else {
		if ($first_name) $fullname = $first_name;
		elseif ($last_name) $fullname = $last_name;
	}

	if ($fullname) $arr[LDAP_CN] = $fullname;
	if ($fullname) $arr[LDAP_GECOS] = $fullname;
	if ($last_name) $arr[LDAP_SN] = $last_name;
	if ($first_name) $arr[LDAP_GIVENNAME] = $first_name;
	if ($phone) $arr[LDAP_TELEPHONENUMBER] = $phone;
	if ($email) $arr[LDAP_MAIL] = $email;

	//add the new uid to the entry array	
	$arr[LDAP_UID] = $login;

	$arr = fixLdapArray($arr);

	$newdn = LDAP_UID."=".$login.",".LDAP_BASE;
	$olddn = $accountInfo["dn"];	

	if (ldap_add($ds,"$newdn", $arr)) {
		ldap_delete($ds,"$olddn");
		$result["successMessage"] = "Account updated successfully";
	}
	else $result["errorMessage"] = "Account updated failed";

	ldap_close($ds);	
	return $result;

}




function deleteAccount($option) {

	$ds = ldap_connect(LDAP_SERVER,LDAP_PORT);
	ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, LDAP_PROTOCOL);
	$r = ldap_bind($ds,BIND_DN,BIND_PASSWORD);

	extract($option);

	$arr = array();
	$msg = array();
	
	if ($accountId==NULL) {
		$msg["errorMessage"] = "The account id must be passed to delete the account";
		return $msg;
	}

	$accountInfo = returnAccountInfo($conn,$accountId,null);
	$login = $accountInfo["login"];
	$searchBase = $accountInfo["searchBase"];
	$dn = $accountInfo["dn"];

	if (ldap_delete($ds,"$dn")) {

       		$sql = "DELETE FROM auth_accountperm WHERE account_id='$accountId';";
       		db_query($conn,$sql);
       
       		$msg["successMessage"] = "Account removed successfully";

	} else {
		$msg["errorMessage"] = "Account removal failed";
	}

	ldap_close($ds);
	return $msg;

}


function returnAccountInfo($conn,$accountId) {

	$option = null;
	$option["accountId"] = $accountId;
	$option["search_base"] = LDAP_ROOT;	//since we search by id, expand search to entire tree
	
	$temp = ldap_account_search($option);

	$accountInfo["id"] 		= 	$accountId;
	$accountInfo["login"] 		= 	$temp[LDAP_UID][0];
	$accountInfo["first_name"] 	= 	$temp[LDAP_GIVENNAME][0];
	$accountInfo["last_name"] 	= 	$temp[LDAP_SN][0];
	$accountInfo["email"] 		= 	$temp[LDAP_MAIL][0];
	$accountInfo["phone"]		=	$temp[LDAP_TELEPHONENUMBER][0];
	$accountInfo["dn"]		=	$temp["dn"];
	$accountInfo["objectClass"]	=	$temp["objectClass"];

	if (defined("LDAP_SHADOW")) $accountInfo = returnShadowInfo($accountInfo,$temp);
	if (defined("LDAP_SAMBA")) $accountInfo = returnSambaInfo($accountInfo,$temp);
	if (defined("LDAP_EWP")) $accountInfo = returnEwpInfo($accountInfo,$temp);

	$dn = $temp["dn"];

	//pop the count off the object class if it's there
	if (is_array($accountInfo["objectClass"])) array_shift($accountInfo["objectClass"]);
	
	//return the search base
	$pos = strpos($dn,",") + 1;
	$accountInfo["searchBase"] = substr($dn,$pos);

	$pos = strpos($dn,",") + 1;
	$accountInfo[LDAP_CN] = substr($dn,$pos);

	if ($accountInfo) return $accountInfo;
	else return false;

}

/*******************************************************************
	We can filter our results using the following keys:
	$opt["searchParm"]["null"] = "where key is null";
	$opt["searchParm"]["notnull"] = "where key is not null";
	$opt["searchParm"]["key"]  = "where key = value"
*******************************************************************/
function returnAccountList($option) {

	if (!$option["sort"]) $option["sort"] = LDAP_UID;
	if ($option["sort"]=="login") $option["sort"] = LDAP_UID;
	if (!$option["search_base"]) $option["search_base"] = LDAP_BASE;
	
	//turn our parameter array into a string
	if ($option["searchParm"]) {

		$keys = array_keys($option["searchParm"]);

		for ($row=0;$row<count($keys);$row++) {

			$key = $keys[$row];
			$text = $key;

			if ($text=="login") $text = LDAP_UID;
			elseif ($text=="first_name") $text = LDAP_GIVENNAME;
			elseif ($text=="last_name") $text = LDAP_SN;
	
			$string = $text."=".$option["searchParm"][$key];

			if ($option["wildcard"]=="last") $string .= "*";

			$option["searchString"][] = $string;

		}
	
	}

	$temp = ldap_account_search($option);

	//return our result count
	$accountList["count"] = $temp["count"];
	$un = strtolower(LDAP_UIDNUMBER);
	$gn = strtolower(LDAP_GIVENNAME);
	$tp = strtolower(LDAP_TELEPHONENUMBER);
	
	for ($row=0;$row<$temp["count"];$row++) {

		$accountList["id"][] 			= 	$temp[$row][$un][0];
		$accountList["login"][] 		= 	$temp[$row][LDAP_UID][0];
		$accountList["first_name"][]	 	= 	$temp[$row][$gn][0];
		$accountList["last_name"][] 		= 	$temp[$row][LDAP_SN][0];
		$accountList["email"][] 		= 	$temp[$row][LDAP_MAIL][0];
		$accountList["phone"][]			=	$temp[$row][$tp][0];

		if (defined("LDAP_SHADOW")) $accountList = returnShadowList($accountList,$temp,$row);
		if (defined("LDAP_SAMBA")) $accountList = returnSambaList($accountList,$temp,$row);
		if (defined("LDAP_EWP")) $accountList = returnEwpList($accountList,$temp,$row);

	}

	if ($accountList) return $accountList;
	else return false;


}


function formatAccountList($info) {

	$un = strtolower(LDAP_UIDNUMBER);
	$gn = strtolower(LDAP_GIVENNAME);

	for ($row=0;$row<$info["count"];$row++) {

		$accountList["id"][] 			= 	$info[$row][$un][0];
		$accountList["login"][] 		= 	$info[$row][LDAP_UID][0];
		$accountList["first_name"][]	 	= 	$info[$row][$gn][0];
		$accountList["last_name"][] 		= 	$info[$row][LDAP_SN][0];
		$accountList["email"][] 		= 	$info[$row][LDAP_MAIL][0];

	}

	if ($accountList) return $accountList;
	else return false;

}


function ldap_account_search($optionArray) {

	extract($optionArray);

	$server = LDAP_SERVER;
	$ldap_password = BIND_PASSWORD;
	$bind_string = BIND_DN;

	$ds=ldap_connect(LDAP_SERVER,LDAP_PORT);
	ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, LDAP_PROTOCOL);

	if (!$search_base) $search_base = LDAP_BASE;
	if ($search_filter) $searchFilter = $search_filter;

	if ($ds) {

		$r = ldap_bind($ds,"$bind_string","$ldap_password");

		if ($optionArray["accountLogin"]!=null) {

			$cnString = LDAP_UID."=".$optionArray["accountLogin"];
			$cnString = "(&".LDAP_FILTER."(".UID."=".$optionArray["accountLogin"]."))";
		
			$sr = ldap_search($ds,$search_base,"$cnString");
			if ($entry = ldap_first_entry($ds,$sr)) {

				$info = ldap_get_attributes($ds,$entry);
				$dn = ldap_get_dn($ds,$entry);
				$info["dn"] = $dn;

			}
			else return false;

		}	
		elseif ($optionArray["accountId"]!=null) {

			$cnString = LDAP_UIDNUMBER."=".$optionArray["accountId"];
			$cnString = "(&".$searchFilter."(uidNumber=".$optionArray["accountId"]."))";

			$sr = ldap_search($ds,$search_base,"$cnString");
			if ($entry = ldap_first_entry($ds,$sr)) {

				$info = ldap_get_attributes($ds,$entry);
				$dn = ldap_get_dn($ds,$entry);
				$info["dn"] = $dn;

			}
			else return false;

		}	
		elseif ($optionArray["searchString"]!=null) {

			if ($optionArray["searchOption"] == "AND") $opt = "&";
			else $opt = "|";

			$cnString = "(".$opt;

			for ($row=0;$row<count($optionArray["searchString"]);$row++) {

				$cnString .= "(".$optionArray["searchString"][$row].")";

			}

			$cnString .= ")";

			$cnString = "(&".$searchFilter.$cnString.")";
			$sr = ldap_search($ds,$search_base,"$cnString");
			if ($optionArray["sort"]) ldap_sort($ds,$sr,$optionArray["sort"]);
			$info = ldap_get_entries($ds,$sr);
		

		}
		else {

			$cnString = "(&".$searchFilter."(".LDAP_UID."=*))";
			$sr = ldap_search($ds,$search_base,"$cnString");
			if ($optionArray["sort"]) ldap_sort($ds,$sr,$optionArray["sort"]);
			$info = ldap_get_entries($ds,$sr);
		}


		ldap_close($ds);

		return $info;

	}

	ldap_close($ds);


}

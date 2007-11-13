<?
/**************************************************************************************

	This file is used when a site is db-enabled.  All functions that are dual
	(can do something if database or something else if ldap) will be included
	in this file.  This file contains the database versions of these functions

**************************************************************************************/


function checkAccount($conn,$login) {

	$sql = "SELECT id FROM auth_accounts WHERE login='$login';";
	$info = single_result($conn,$sql);

	if ($info) return true;
	else return false;

}

function checkValidLogin($login) {

	//only allow special chars _ and . in a login
	$arr = array(	",","/","?","'","\"","!","@","#",
			"$","%","^","&","*","(",")","+","=",
			"}","{","[","]","|","\\",":",";","<",
			">"
			);
			
	$num = count($arr);
	
	for ($row=0;$row<$num;$row++) if (strstr($login,$arr[$row])) return false;

	return true;

}

function insertAccount($option) {

	extract($option);
	
	$r = array();

	//make sure our login doesn't have bad characters in it
	if (!checkValidLogin($login)) {
		$r["errorMessage"] = "Invalid characters used in login";
		return $r;
	}
	
	//check to make sure this does not exist.
	if (checkAccount($conn,$login)) {

		$r["errorMessage"] = "This Account Already Exists";
		return $r;	
	}
	else {

		$opt["login"] = $login;
		$opt["password"] = md5($password);
		$opt["first_name"] = addslashes($first_name);
		$opt["last_name"] = addslashes($last_name);
		$opt["email"] = $email;
		$opt["phone"] = $phone;
		
		if ($accountId = dbInsertQuery($conn,"auth_accounts",$opt)) {

			$r["successMessage"] = "Account Created Successfully";
			$r["accountId"] = $accountId;
			$r["authObjectId"] = $authObjectId;						

			//insert a base permission record for this account
			$opt = null;
			$opt["account_id"] = $accountId;
			$opt["bitset"] = "0";
			$opt["enable"] = "t";
			if (defined("APP_ID")) $opt["app_id"] = APP_ID;
			
			dbInsertQuery($conn,"auth_accountperm",$opt);
			
		}
		else {

			$r["errorMessage"] = "Account Creation Failed";

		}
		
	}

	return $r;
		
}

function updateAccountPassword($option) {

	extract($option);
	$result = array();
	
	if ($accountId==NULL) {
		$result["errorMessage"] = "The account id must be passed to update the account";
		return $result;
	}

	if (!$password) {
		$result["errorMessage"] = "You must specify a password";
		return $result;
	}

	//hash the password
	$password = md5($password);
	
	//update the account
	$sql = "UPDATE auth_accounts SET password='$password' WHERE id='$accountId';";
	if (db_query($conn,$sql)) $result["successMessage"] = "Password updated successfully";
	else $result["errorMessage"] = "Password update failed";

	return $result;	


}

/******************************************************************************
	this function is responsible for updating basic account information
******************************************************************************/
function updateAccountProfile($option) {

	extract($option);

	$r = array();
	
	if (!$accountId) {
		$r["errorMessage"] = "The account id must be passed to update the account";
		return $r;
	}

	$accountInfo = returnAccountInfo($conn,$accountId,null);

	if ($login) {

		//make sure our login doesn't have bad characters in it
		if (!checkValidLogin($login)) {
			$r["errorMessage"] = "Invalid characters used in login";
			return $r;
		}

		//they do not match, check to see if the new one exists
		if ($login!=$accountInfo["login"]) {

			//check to make sure this does not exist.
			if (checkAccount($conn,$login)) {
				$r["errorMessage"] = "It looks like you are trying to change your username.<br>
							The username \"".$login."\" is already in use.
							Please choose again.";

				return $r;
			}		
		}

	}

	//convert our variables into a format our insert function will understand
	$opt = null;
	$opt["login"] = $login;
	$opt["first_name"] = addslashes($first_name);
	$opt["last_name"] = addslashes($last_name);
	$opt["email"] = $email;
	$opt["phone"] = $phone;
	$opt["where"] = "id='".$accountId."'";

	if (dbUpdateQuery($conn,"auth_accounts",$opt)) $r["successMessage"] = "Account Updated Successfully";
	else $r["errorMessage"] = "Account Not Updated";

	return $r;
	
}


function deleteAccount($option) {

	extract($option);
	$msg = array();
	
	$sql = "DELETE FROM auth_accounts WHERE id='$accountId';";
	$sql .= "DELETE FROM auth_accountperm WHERE account_id='$accountId';";
	$sql .= "DELETE FROM auth_grouplink WHERE accountid='$accountId';";
	if (db_query($conn,$sql)) $msg["successMessage"] = "Account deleted successfully";
	else $msg["errorMessage"] = "Account removal failed";
	
	return $msg;

}


function returnAccountInfo($conn,$accountId) {

	$sql = "SELECT * FROM auth_accounts WHERE id='$accountId'";
	$accountInfo = single_result($conn,$sql);

	if ($accountInfo) return $accountInfo;
	else return false;

}


function returnAccountList($option) {

	$conn = $option["conn"];

	$sql = "SELECT * FROM auth_accounts ";
	
	if ($option["searchParm"]) {
	
		$sql .= " WHERE ";
		
		$keys = array_keys($option["searchParm"]);
		
		for ($row=0;$row<count($keys);$row++) {
		
			$key = $keys[$row];
			$value = $option["searchParm"][$key];
			
			$sql .= $key." ILIKE '".$value."%' OR ";
		
		}

		$sql = substr($sql,0,strlen($sql)-3);	
	
	}

	$sql .= " ORDER BY login";

	$list = total_result($conn,$sql);

	if ($list) return $list;
	else return false;

}


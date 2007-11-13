<?php

/*******************************************************************

	function.inc.php
	
	This file contains the generic auth functions which
	are used for db and ldap based authentication
	
*******************************************************************/

function logout($conn) {

	@session_unset();
	@session_destroy();

	$path = substr($_SERVER["PHP_SELF"],0,strlen($_SERVER["PHP_SELF"]) - 9);
	$domain = $_SERVER["SERVER_NAME"];	

	setcookie("login","",time()-3600,"$path","$domain",0);
	setcookie("password","",time()-3600,"$path","$domain",0);

	$_SESSION = null;

	//pause 1/2 second to prevent brute force password cracking
	usleep(500000);

}

function userPermSet($conn,$accountId) {

	//if this has already been done, set our defines and get out of here
	if ($_SESSION["bitset"]) {

		define("BITSET",$_SESSION["bitset"]);
		define("USER_GROUPS",$_SESSION["user_groups"]);
		return true;

	}

	//get the total bitset for this user
	$bitset_temp = returnUserBitset($conn,$accountId);

	//get out of here if nothing was returned	
	if ($bitset_temp==NULL) return false;
	
	//set the combined bit value
	if (!defined("BITSET")) define("BITSET",$bitset_temp);

	//store bitset in a session
	if (defined("BITSET")) $_SESSION["bitset"] = BITSET;

	return true;
	
}

function returnUserBitset($conn,$accountId) {

	$permArr = array();

	//get the account permissions
	$sql = "SELECT bitset,enable FROM auth_accountperm WHERE account_id='$accountId';";
	$accountInfo = single_result($conn,$sql);

	//if using ldap and there is no permissions entry, create one
	if (!$accountInfo) {
	
		if  (defined("USE_LDAP") && defined("LDAP_PERMCREATE")) {
	
			$accountInfo = null;
			$accountInfo["bitset"] = LDAP_PERMCREATE;
			$accountInfo["enable"] = "t";
			$accountInfo["account_id"] = $accountId;
			dbInsertQuery($conn,"auth_accountperm",$accountInfo);
		
		} else return false;
	}
	//get out of here if the account is disabled
	else if ($accountInfo["enable"]!="t") return false;

	//Now, figure out what groups this user belongs to, and get all group permissions
	$sql = "SELECT auth_grouplink.groupid AS groupid, auth_groupperm.bitset AS bitset FROM auth_grouplink
			LEFT JOIN auth_groupperm ON (auth_grouplink.groupid = auth_groupperm.group_id)
			WHERE auth_grouplink.accountid='$accountId'";	 
	$groupInfo = total_result($conn,$sql);

	//we are going to explode this into a delimited string so I can store all groups in a single define
	if (is_array($groupInfo["groupid"])) $group_string = implode(",",array_values(array_unique($groupInfo["groupid"])));
	else $group_string = null;
	
	$_SESSION["user_groups"] = $group_string;
	define("USER_GROUPS",$group_string);

	//now loop through all our bitset values, and combine them to create our users bitset
	$bitset_temp = null;

	if ($accountInfo) $permArr[] = $accountInfo["bitset"];
	if ($groupInfo) $permArr = array_merge($permArr,$groupInfo["bitset"]);

	for ($row=0;$row<count($permArr);$row++) {

		if ($permArr[$row]==NULL) continue;
		if (!$bitset_temp) $bitset_temp = $permArr[$row];

		//if these are not the same number, set bits present in either
		if ($bitset_temp!=$permArr[$row]) $bitset_temp = (int)$bitset_temp | (int)$permArr[$row];

	}

	return $bitset_temp;

}


function last_login_check($conn,$id){
	$sql = "SELECT last_success_login,failed_logins FROM auth_accountperm WHERE account_id='$id'";
	$login_attempts = single_result($conn,$sql);

	return $login_attempts;
}


// this function will reset the number of login attempts to 0
function reset_failed_login_count($conn,$id){
        $sql = "UPDATE auth_accountperm SET failed_logins=0,last_success_login=now() WHERE account_id='$id';";
        db_query($conn,$sql);
}

// this function will increment the number of login attempts
function update_failed_login_attempts($conn,$login){
	$sql="SELECT id FROM auth_accounts WHERE login='$login'";
	$loginIdArray = single_result($conn,$sql);
	$accountId = $loginIdArray["id"];
	if ($accountId > 0){
        	$sql = "UPDATE auth_accountperm SET failed_logins=failed_logins+1 WHERE account_id=$accountId;";
        	db_query($conn,$sql);

		// if account lockout is enabled, check to see if the failed logins is > than that permited
		//    if so, lock the account
		if (defined("ENABLE_ACCOUNT_LOCKOUT")){
			lock_account($conn,$accountId);
		}
	}
}


// this function will lock an account if the number of failed logins exceeds
// the allowed number, account lockout is enabled, and so long as it is not an administrative account
function lock_account($conn,$id){


	// check to make sure the account is not an administrator or part of an administrative group
	$bitset = returnUserBitset($conn,$id)	;
	if (!bitset_compare($bitset,ADMIN,ADMIN)){

		// verify that the number of login attempts exceeds the allowed number
      	  	$sql="SELECT failed_logins FROM auth_accountperm WHERE account_id='$id';";
		$failLogin = single_result($conn,$sql);
		if ( $failLogin["failed_logins"] >= ACCOUNT_LOCKOUT_ATTEMPTS ){

			// disable account and timestamp
			$sql = "UPDATE auth_accountperm SET failed_logins_locked=TRUE,enable=FALSE,locked_time=now() WHERE account_id='$id';";
			db_query($conn,$sql);
		}
	}
}

//this function will unlock an account after a specified period of time
function time_unlock_account($conn,$id) {

	if (ACCOUNT_LOCKOUT_TIME > 0){
		$lockout_time=ACCOUNT_LOCKOUT_TIME." minutes";
		
		// see if this user has been locked out and if the lockout time has passed
		$sql = "SELECT * FROM auth_accountperm WHERE account_id='$id' AND  failed_logins_locked=TRUE AND locked_time < now() - INTERVAL '$lockout_time'";
        	$login_attempts = single_result($conn,$sql);

		
		if ($login_attempts["failed_logins_locked"]=="t"){
			$sql = "UPDATE auth_accountperm SET failed_logins_locked=FALSE,enable=TRUE,locked_time=NULL WHERE account_id='$id';";
	       	 	db_query($conn,$sql);
		}

	}	
}

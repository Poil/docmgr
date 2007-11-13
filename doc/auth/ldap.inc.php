<?php

/*******************************************************************

	ldap.inc.php
	
	This file contains the functions to do account
	authentication against an ldap database
	
*******************************************************************/

//encrypts the password based on the stored password encryption in the ldap db
function returnCryptPassword($password,$ldap_pw) {

	if (strlen($password) > 25) $cryptpw = $password;
	else {

		if (LDAP_CRYPT == "MD5") {
			$cryptpw = base64_encode(pack("H*",md5($password)));
			$cryptpw = substr($cryptpw,2,22);
		}
		else {
			$salt =  substr($ldap_pw,0,CRYPT_SALT_LENGTH);
			$cryptpw = crypt($password,$salt);
		}
        
	}
	
	return $cryptpw;

}

//compares the passed password to that in the ldap db
function password_check($conn,$login,$password) {

	$optionArray["accountLogin"] = $login;
	$accountInfo = ldap_account_search($optionArray);

	//if we have a central rep of app perms, check to make sure this
	//user can access this application
	if($accountInfo["count"]!=FALSE) {

		$ldap_pw = substr($accountInfo["userPassword"][0],7);
		$cryptpw = returnCryptPassword($password,$ldap_pw);

		if ($cryptpw == $ldap_pw) {

			$ra = array();
			$ra["id"] = $accountInfo["uidNumber"][0];
			$ra["login"] = $accountInfo["login"][0];
			$ra["email"] = $accountInfo["email"][0];
			$ra["firstName"] = $accountInfo["givenName"][0];
			$ra["lastName"] = $accountInfo["sn"][0];
			$ra["cryptPass"] = $cryptpw;

			return $ra;

		} else return false;

	} else return false;

}


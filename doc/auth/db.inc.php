<?php

/******************************************************************************************
	db.inc.php
	
	This file stores functions for authentication to a postgresql database.  
*******************************************************************************************/


/******************************************************************************************
Function password_check
Purpose:  This function verifies a user's password in the database
Inputs:
	$conn: The database connection
	$login:  The users login id;
	$password:  The user's password;

********************************************************************************************/

function password_check($conn,$login,$password) {

	//if it's longer than 25 characters, it came from a cookie
	if (strlen($password)<=25) $pass = md5($password);
	else $pass = $password;

	$sql = "SELECT * FROM auth_accounts WHERE login='$login' AND password='$pass'";
	$accountInfo = single_result($conn,$sql);

	if ($accountInfo) {

		$ra = array();
		$ra["id"] = $accountInfo["id"];
		$ra["login"] = $accountInfo["login"];
		$ra["email"] = $accountInfo["email"];
		$ra["firstName"] = $accountInfo["first_name"];
		$ra["lastName"] = $accountInfo["last_name"];
		$ra["cryptPass"] = $pass;

		return $ra;

	} else return false;

}


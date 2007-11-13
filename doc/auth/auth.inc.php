<?

//get our authorization functions
if (defined("USE_LDAP")) include_once("auth/ldap.inc.php");
else include_once("auth/db.inc.php");

//our common auth functions
include_once("auth/function.inc.php");

//logout out.  If there is a login set to show it
if ($_REQUEST["logout"]) {

	logout($conn);

	//go back to the main page
	header("Location: index.php");

}
//user is not logged in, process them
elseif ($_SESSION["authorize"]!="1") {

	//get our values from the cookie or post or session
	if (defined("USE_COOKIES") && $_COOKIE["login"])	$login =	$_COOKIE["login"];
	elseif ($_POST["login"]) $login = 	$_POST["login"];
	elseif ($_GET["login"]) 	$login =	$_GET["login"];

	if (defined("USE_COOKIES") && $_COOKIE["password"]) $password = $_COOKIE["password"];
	elseif ($_POST["password"]) 	$password = $_POST["password"];
	elseif ($_GET["password"]) 	$password	= $_GET["password"];


	//call our auth processing if any of the below variables are present.  This prevents someone from passing
	//phony variables through a form or address bar

	//user is trying to login, process the information
	if ($login || $password) {

		//check to see if the user and password combo exist
	     if ($accountInfo = password_check($auth_conn,$login,$password)) {
			
			//set our user information from that which is returned from the function
			$_SESSION["user_id"] 	= 	$accountInfo["id"];
			$_SESSION["user_login"] = 	$login;
			$_SESSION["user_password"] = 	$password;
			$_SESSION["user_email"] = 	$accountInfo["email"];
			$_SESSION["user_fn"] 	= 	$accountInfo["firstName"];
			$_SESSION["user_ln"] 	= 	$accountInfo["lastName"];
			$encryptPass 		= 	$accountInfo["cryptPass"];
			
			$last_login_info= last_login_check($conn,$accountInfo["id"]);
			$last_login_info["last_success_login"] = preg_replace('/\.\d*/','', $last_login_info["last_success_login"]);
			$_SESSION["last_login"]  =	$last_login_info["last_success_login"];
			$_SESSION["failed_logins"] =	$last_login_info["failed_logins"];

			//default to the current login date if none is selected
			if ($_SESSION["last_login"]=="1970-01-01 00:00:00") $_SESSION["last_login"] = date("Y-m-d H:i:s");

			//set our session value so we do not get requeried.
			$_SESSION["authorize"] = "1";

			if (defined("USE_COOKIES") && $_POST["savePassword"]) {

				$expire = time()+60*60*24*30;
				$path = substr($_SERVER["PHP_SELF"],0,strlen($_SERVER["PHP_SELF"]) - 9);
				$domain = $_SERVER["SERVER_NAME"];

				//set the cookie
				setcookie("login","$login","$expire","$path","$domain");
				setcookie("password","$encryptPass","$expire","$path","$domain");

				//set our permission defines
				$show_login_form = null;

			}

			//if logging is enabled, set to log this event
			if (defined("SITE_LOG")) $logUserLogin = 1;
			else $logUserLogin = null;

		//user does not exist, destroy all sessions and call the incorrect login page
	    	} else {
			update_failed_login_attempts($conn,$login);
	        	logout($conn);
	        	$show_login_form="incorrect";

	    	}


	//This calls the form for logging in when the page is called by the header file

	}
	else {

		logout($conn);
		$show_login_form="1";

	}


}

//set our defines if the user is authorized
if ($_SESSION["authorize"]=="1") {

	//if we have gotten this far and there was a passed query string, redirect to that
	if ($_POST["queryString"]) header("Location: index.php?".$_POST["queryString"]);

	// Check to see if the account is locked and if it has passed the account lockout period
	//   if so - unlock the account
	time_unlock_account($conn,$_SESSION["user_id"]);

	//process our define permissions.  If access is disabled, show the login form
	if (!userPermSet($conn,$_SESSION["user_id"],$_SESSION["user_authid"])) {

		logout($conn);
		$show_login_form="incorrect";

	}
	else {
	
		//set our user information from that which is returned from the function
		define("USER_ID",$_SESSION["user_id"]);
		define("USER_LOGIN",$_SESSION["user_login"]);
		define("USER_PASSWORD",$_SESSION["user_password"]);
		define("USER_EMAIL",$_SESSION["user_email"]);
		define("USER_FN",$_SESSION["user_fn"]);
		define("USER_LN",$_SESSION["user_ln"]);
		
		reset_failed_login_count($conn,$_SESSION["user_id"]);
	}
	
}


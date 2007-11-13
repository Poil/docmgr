<?
/********************************************************************************************/
//
//	Filename:
//		misc.php
//      
//	Summary:
//		this file contains functions common to both prospect and contract applications
//		They should still be somewhat generic
//           
//	Modified:
//             
//		09-02-2004
//			Code cleanup.  Moved functions that don't belong out
//
//       04-19-2006
//          -More consolidation of functions.
//          -merged function.inc.php into file
//          -Created new files for removed functions
//              *file_functions.inc.php
//              *sanitize.inc.php
//              *calc_functions.inc.php                    
//          -Renamed file from common.inc.php to misc.php
//
//
/*********************************************************************************************/
/*********************************************************
//  returns the type of browser the user is using.
//  this function must be passed $HTTP_USER_AGENT.
*********************************************************/
function browser_check($var) {

	if (eregi("MSIE",$var)) {
		$browser_type="ie";
	}
	else {
		$browser_type="mozilla";
	}
	echo strpos($HTTP_USER_AGENT,"MSIE");
	return $browser_type;
}
/*********************************************************
*********************************************************/
function login_return($conn,$id) {

	//return anonymous if there is no id
	if ($id=="0" || !$id) return SITE_ADMIN;

	$sql = "SELECT login FROM auth_accounts WHERE id='$id'";

	if ($value = single_result($conn,$sql)) return $value["login"];
	else return false;

}
/*********************************************************
*********************************************************/
function selfClose($url = null) {

	//set to refresh the parent if url is not specified
	if (!$url) $url = "window.opener.location.href";
	else $url = "\"".$url."\"";
	
	echo "<script type=\"text/javascript\">
		var url = ".$url.";
		window.opener.location.href = url;
		self.close();
		</script>
		";

}
/*********************************************************
*********************************************************/
function selfFocus() {

	echo "<script type=\"text/javascript\">\n";
	echo "self.focus();\n";
	echo "</script>\n";

}
/*********************************************************
*********************************************************/
function includeStylesheet($path) {

	echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"".$path."\">\n";

}
/*********************************************************
*********************************************************/
function includeJavascript($path) {

	echo "<script src=\"".$path."\"></script>\n";

}
/*********************************************************
*********************************************************/
function preventCache() {

	Header("Cache-control: private, no-cache");
	Header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
	Header("Pragma: no-cache");

}
/*********************************************************
*********************************************************/
function translateHtmlEntities($string) {

	$trans_tbl = get_html_translation_table(HTML_ENTITIES);
	$trans_tbl = array_flip($trans_tbl);
	
	$original = strtr($string,$trans_tbl);

	return $original;
}
/*************************************************************************************
//	checkStrongPassword:
//	checks the password to see if it is considered difficult to crack.
//	Returns a string containing what it thinks is wrong with your password.  
//	If "strong password" is returned, then all is well.  cracklib must be 
//	compiled into php, or this will generate a php error
**************************************************************************************/
function checkStrongPassword($conn,$accountId,$pwd) {

	//if there is no accountId, return false since we can't check against the username
	if (!$accountId) return "no account id specified";

	$info = returnAccountInfo($conn,$accountId,null);
	$login = &$info["login"];
	$fn = &$info["first_name"];
	$ln = &$info["last_name"];

	//make sure the user's first name, last name, and login aren't in the password
	if (stristr($pwd,$login)) return "it contains your username";
	if (stristr($pwd,$fn)) return "it contains your name";
	if (stristr($pwd,$ln)) return "it contains your name";

	// Perform password check with craclib
	$check = crack_check($pwd);

	// Retrieve messages from cracklib
	return crack_getlastmessage();

}


function dbInsertQuery($conn,$table,$option,$idField = "id") {

	$ignoreArray = array("conn","table","debug","query");

	$keys = array_keys($option);

	$fieldString = null;
	$valueString = null;

	for ($row=0;$row<count($keys);$row++) {

		$field = $keys[$row];
		$value = $option[$field];

		if (!in_array($field,$ignoreArray) && $value!=null) {

			$fieldString .= $field.",";
			$valueString .= "'".$value."',";

		}	


	}

	if ($fieldString && $valueString) {

		$fieldString = substr($fieldString,0,strlen($fieldString) - 1);
		$valueString = substr($valueString,0,strlen($valueString) - 1);
			
		$sql = "INSERT INTO $table (".$fieldString.") VALUES (".$valueString.");";
		if ($option["debug"]) echo $sql."<br>\n";
		if ($option["query"]) return $sql;
		
		if ($result = db_query($conn,$sql)) {
	
			$returnId = db_insert_id($table,$idField,$conn,$result);
			if ($returnId) return $returnId;
			else return true;

		} else return false;

	} else return false;

}

function dbUpdateQuery($conn,$table,$option,$sanitize = null) {

	$ignoreArray = array("conn","table","where","debug","query");

	$keys = array_keys($option);

	$queryString = null;

	for ($row=0;$row<count($keys);$row++) {

		$field = $keys[$row];
		$value = $option[$field];

		if (!in_array($field,$ignoreArray)) {

			if ($value!=null) {
				if ($sanitize) 
					$queryString .= $field."='".sanitize($value)."',";
				else
					$queryString .= $field."='".$value."',";
			}
			else $queryString .= $field."=NULL,";
		} 	


	}

	if ($queryString) {

		$queryString = substr($queryString,0,strlen($queryString) - 1);
			
		$sql = "UPDATE $table SET ".$queryString." WHERE ".$option["where"];

		if ($option["debug"]) echo $sql."<br>\n";
		if ($option["query"]) return $sql;

		if (db_query($conn,$sql)) return true;
		else return false;

	} else return false;

}

function debug($level,$msg) {

    if (php_sapi_name()=="cli") $sep = "\n";
    else $sep = "<br>";
    
    if (defined("DEBUG") && DEBUG >= $level) {

    	//if from webdav then use webdav function
    	if (class_exists("webdavfunc")) webdavfunc::checkOutput($msg."\n");
    	else echo $msg.$sep;

    }
    
}
      


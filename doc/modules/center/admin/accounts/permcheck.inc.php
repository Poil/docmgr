<?

/*
okay, this file needs to do this

if the user is an admin or user manager, then they have all rights

if the user is the first account in a list for household, then they can manage other accounts
in that household, but no other accounts

if the user has none of the above, bounce them.


*/


/******************************************************************
	Determine user permissions for this household
*******************************************************************/

if ($accountId == USER_ID) return true;
if (bitset_compare(BITSET,MANAGE_USERS,ADMIN)) return true;

if (!$accountId) $accountId = USER_ID;
if (!$homeId) $homeId = USER_HOMEID;

$sql = "SELECT * FROM auth_accounts WHERE home_id='$homeId' ORDER BY id;";
$list = total_result($auth_conn,$sql);

if ($list) {

	if ($list["id"][0] == USER_ID) define("MANAGE_HOME","1");

	if (in_array($accountId,$list["id"])) return true;
	else die("You have entered an illegal user id");

}



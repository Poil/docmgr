<?

//delete account and group entries in the new database
$sql = "
DELETE FROM auth_accounts;
DELETE FROM auth_groups;
DELETE FROM auth_grouplink;
DELETE FROM auth_accountperm;
DELETE FROM auth_groupperm;
";
db_query($newconn,$sql);

$opt = null;

$sql = "SELECT auth_accounts.*, auth_permlink.bitset FROM auth_accounts 
	LEFT JOIN auth_permlink 
	ON auth_accounts.auth_objectid = auth_permlink.auth_objectid";
$accountList = total_result($oldconn,$sql);
for ($row=0;$row<$accountList["count"];$row++) {

	$opt = null;
	$opt["id"] = $accountList["id"][$row];
	$opt["login"] = addslashes($accountList["login"][$row]);	
	$opt["password"] = $accountList["password"][$row];			
	$opt["first_name"] = addslashes($accountList["first_name"][$row]);	
	$opt["last_name"] = addslashes($accountList["last_name"][$row]);	
	$opt["email"] = $accountList["email"][$row];	
	$opt["phone"] = $accountList["phone"][$row];	

	dbInsertQuery($newconn,"auth_accounts",$opt);	

	$origBitset = $accountList["bitset"][$row];
	$bitset = null;
	
	//migrate our permissions
	if (bitset_compare($origBitset,"1",null)) $bitset |= 1;		//admin
	if (bitset_compare($origBitset,"2",null)) $bitset |= 2;		//manage users
	if (bitset_compare($origBitset,"4",null)) $bitset |= 4;		//manage groups
	if (bitset_compare($origBitset,"8",null)) $bitset |= 8;		//insert collections -> insert objects
	if (bitset_compare($origBitset,"16",null)) $bitset |= 8;	//insert files -> insert objects
	if (bitset_compare($origBitset,"32",null)) $bitset |= 16;	//manage profile

	$opt = null;
	$opt["account_id"] = $accountList["id"][$row];
	$opt["bitset"] = $bitset;
	$opt["enable"] = "t";
	dbInsertQuery($newconn,"auth_accountperm",$opt);
	
}


echo "Accounts and their permissions moved\n";

$sql = "SELECT auth_groups.*, auth_permlink.bitset FROM auth_groups 
	LEFT JOIN auth_permlink 
	ON auth_groups.auth_objectid = auth_permlink.auth_objectid";
$groupList = total_result($oldconn,$sql);

for ($row=0;$row<$groupList["count"];$row++) {

	$opt = null;
	$opt["id"] = $groupList["id"][$row];
	$opt["name"] = addslashes($groupList["name"][$row]);	

	dbInsertQuery($newconn,"auth_groups",$opt);	

	$origBitset = $groupList["bitset"][$row];
	$bitset = null;
	
	//migrate our permissions
	if (bitset_compare($origBitset,"1",null)) $bitset |= 1;		//admin
	if (bitset_compare($origBitset,"2",null)) $bitset |= 2;		//manage users
	if (bitset_compare($origBitset,"4",null)) $bitset |= 4;		//manage groups
	if (bitset_compare($origBitset,"8",null)) $bitset |= 8;		//insert collections -> insert objects
	if (bitset_compare($origBitset,"16",null)) $bitset |= 8;	//insert files -> insert objects
	if (bitset_compare($origBitset,"32",null)) $bitset |= 16;	//manage profile

	$opt = null;
	$opt["group_id"] = $groupList["id"][$row];
	$opt["bitset"] = $bitset;
	dbInsertQuery($newconn,"auth_groupperm",$opt);
	
}


echo "Groups and their permissions moved\n";

//move grouplink
//move the account information over
$sql = "SELECT * FROM auth_grouplink";
$linkList = list_result($oldconn,$sql);

for ($row=0;$row<$linkList["count"];$row++) {

	$opt = null;
	$opt["accountid"] = $linkList[$row]["accountid"];
	$opt["groupid"] = $linkList[$row]["groupid"];
	
	dbInsertQuery($newconn,"auth_grouplink",$opt);	
	
}



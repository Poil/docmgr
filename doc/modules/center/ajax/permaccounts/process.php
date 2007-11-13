<?

$searchString = $_REQUEST["searchString"];
$permFilter = $_REQUEST["permFilter"];
$objectId = $_REQUEST["objectId"];

//do some stuff if passed an object
if ($objectId) {

  //make sure the user has permissions to manage this object
  $cb = returnUserObjectPerms($conn,$objId);
  if (!bitset_compare($cb,OBJ_MANAGE,OBJ_ADMIN)) die("You have accessed this module incorrectly");
  
  $sql = "SELECT * FROM dm_view_perm WHERE id='$objectId';";
  $objPermInfo = total_result($conn,$sql);

}

if ($permFilter!="groups") {

  //search the accounts
  $option = null;
  $option["conn"] = $conn;
  $option["sort"] = "login";

  if ($searchString) {
    $option["searchParm"]["login"] = $searchString;
    $option["searchParm"]["first_name"] = $searchString;	
    $option["searchParm"]["last_name"] = $searchString;
    $option["wildcard"] = "last";
  }
  $accountList = returnAccountList($option);

}

if ($permFilter!="accounts") {

  //get our groups sorted by name and limit by our filter
  $sql = "SELECT * FROM auth_groups";
  if ($searchString) $sql .= " WHERE name ILIKE '".$searchString."%' ";
  $sql .= " ORDER BY name";
  $groupList = total_result($conn,$sql);
}

//create a new array with the keys named like we want
if ($accountList["count"]) $aType = array_fill(0,$accountList["count"], "account");
if ($groupList["count"]) $gType = array_fill(0,$groupList["count"], "group");

//merge our arrays into an array with a common key name
if (is_array($groupList["id"]) && is_array($accountList["id"])) {

  $searchResults["id"] = array_merge($groupList["id"],$accountList["id"]);
  $searchResults["name"] = array_merge($groupList["name"],$accountList["login"]);
  $searchResults["type"] = array_merge($gType,$aType);

} else if (is_array($groupList["id"]) && !is_array($accountList["id"])) {

  $searchResults["id"] = $groupList["id"];
  $searchResults["name"] = $groupList["name"];
  $searchResults["type"] = $gType;

} else if (!is_array($groupList["id"]) && is_array($accountList["id"])) {

  $searchResults["id"] = $accountList["id"];
  $searchResults["name"] = $accountList["login"];
  $searchResults["type"] = $aType;

}

//store our count for later
if ($searchResults) $searchResults["count"] = count($searchResults["id"]);


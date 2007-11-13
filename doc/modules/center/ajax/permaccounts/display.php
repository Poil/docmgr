<?

$str = createXMLHeader("permaccounts");

for ($i=0;$i<$searchResults["count"];$i++) {

  //null our variables
  $checkBitset = null;
  $manageCheck = null;
  $editCheck = null;
  $viewCheck = null;

  //if there's an object, set which ones are checked
  if ($objPermInfo) {

    //figure out if a box is checked
    if ($searchResults["type"][$i]=="group" && @in_array($searchResults["id"][$i],$objPermInfo["group_id"])) {
      $key = array_search($searchResults["id"][$i],$objPermInfo["group_id"]);
      $checkBitset = $objPermInfo["bitset"][$key];
    } else if ($searchResults["type"][$i]=="account" && @in_array($searchResults["id"][$i],$objPermInfo["account_id"])) {
      $key = array_search($searchResults["id"][$i],$objPermInfo["account_id"]);
      $checkBitset = $objPermInfo["bitset"][$key];	
    }

    //if there is a bitset for this account set, check it out
    if ($checkBitset) {
      if (bitset_compare($checkBitset,OBJ_MANAGE,null)) $manageCheck = 1;
      if (bitset_compare($checkBitset,OBJ_EDIT,null)) $editCheck = 1;
      if (bitset_compare($checkBitset,OBJ_VIEW,null)) $viewCheck = 1;
    }

    //skip if looking for only selected entries    
    if (!$manageCheck && !$editCheck && !$viewCheck && $permFilter=="selected") continue;
  
  }

  $str .= "<entry>\n";
  $str .= xmlEntry("id",$searchResults["id"][$i]);
  $str .= xmlEntry("name",$searchResults["name"][$i]);
  $str .= xmlEntry("type",$searchResults["type"][$i]);  
  if ($manageCheck) $str .= xmlEntry("manage_check","1");
  if ($editCheck) $str .= xmlEntry("edit_check","1");
  if ($viewCheck) $str .= xmlEntry("view_check","1");
  $str .= "</entry>\n";
  
}

$str .= createXMLFooter();

die($str);

<?

class coldeleteHelper {

function loadHelper($info,$bitset) {

  //if rd set, only admins can delete the collection
  if (defined("RESTRICTED_DELETE") && !bitset_compare(BITSET,ADMIN,null)) return false;

  //if the user isn't a manager or admin, get out
  if (!bitset_compare($bitset,OBJ_MANAGE,OBJ_ADMIN)) return false;
  
  $arr["icon"] = THEME_PATH."/images/icons/delete.png";
  $arr["link"] = "openModalWindow('coldelete','".$info["id"]."',350,100);";
  $arr["title"] = _DELETE_OBJECT;

  return $arr;

}

}
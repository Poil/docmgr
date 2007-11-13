<?

class filecheckinHelper {

function loadHelper($info) {

  //if it's checked out and this isn't the person that checked it out, return false
  if ($info["status"]==1 && $info["status_owner"]!=USER_ID) return false;

  //if it's not checked out, but this is not the file owner or an admin, get out
  if ($info["status"]=="0" && (!bitset_compare(BITSET,ADMIN,null) && $info["object_owner"]!=USER_ID)) return false;

  //show the info
  $arr["icon"] = THEME_PATH."/images/icons/update.png";
  $arr["link"] = "location.href='index.php?module=file&includeModule=filecheckin&objectId=".$info["id"]."'";
  $arr["title"] = _UPDATE_FILE;
  
  return $arr;

}

}
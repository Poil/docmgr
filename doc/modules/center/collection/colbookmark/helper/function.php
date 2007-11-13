<?

class colbookmarkHelper {

function loadHelper($info,$bitset) {

  $arr["icon"] = THEME_PATH."/images/icons/bookmark.png";
  $arr["link"] = "openModuleWindow('colbookmark','".$info["id"]."',400,150);";
  $arr["title"] = _BOOKMARK_THIS_COLLECTION;

  return $arr;

}

}
<?

class zipcollectionHelper {

function loadHelper($info,$bitset) {

  $arr["icon"] = THEME_PATH."/images/icons/zip.png";
  $arr["link"] = "zipCollection('".$info["id"]."');";
  $arr["title"] = _ZIP_COLLECTION;

  return $arr;

}

}
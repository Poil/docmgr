<?

class filepropertiesHelper {

function loadHelper($info) {

  $arr["icon"] = THEME_PATH."/images/icons/property.png";
  $arr["link"] = "location.href='index.php?module=file&objectId=".$info["id"]."'";
  $arr["title"] = _PROPERTIES;
  
  return $arr;

}

}
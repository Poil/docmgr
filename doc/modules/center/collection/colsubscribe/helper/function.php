<?

class colsubscribeHelper {

function loadHelper($info) {

  $arr["icon"] = THEME_PATH."/images/icons/subscribe.png";
  $arr["link"] = "openModuleWindow('colsubscribe','".$info["id"]."',450,350)";
  $arr["title"] = _SUBSCRIBE_COL;
  
  return $arr;

}

}

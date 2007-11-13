<?

class filesubscribeHelper {

function loadHelper($info) {

  $arr["icon"] = THEME_PATH."/images/icons/subscribe.png";
  $arr["link"] = "openModuleWindow('filesubscribe','".$info["id"]."',450,350)";
  $arr["title"] = _SUBSCRIBE_FILE;
  
  return $arr;

}

}

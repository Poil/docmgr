<?

class fileemailHelper {

function loadHelper($info) {

  $arr["icon"] = THEME_PATH."/images/icons/mail.png";
  $arr["link"] = "openModuleWindow('fileemail','".$info["id"]."',600,450)";
  $arr["title"] = _EMAIL_FILE;
  
  return $arr;

}

}

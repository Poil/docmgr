<?

class colemailHelper {

function loadHelper($info) {

  $arr["icon"] = THEME_PATH."/images/icons/mail.png";
  $arr["link"] = "openModuleWindow('colemail','".$info["id"]."',600,450)";
  $arr["title"] = _EMAIL_COLLECTION;
  
  return $arr;

}

}

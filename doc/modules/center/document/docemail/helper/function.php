<?

class docemailHelper {

  function loadHelper($info) {

    $arr["icon"] = THEME_PATH."/images/icons/mail.png";
    $arr["link"] = "openModuleWindow('docemail','".$info["id"]."',600,450)";
    $arr["title"] = _EMAIL_DOCUMENT;
  
    return $arr;

  }

}

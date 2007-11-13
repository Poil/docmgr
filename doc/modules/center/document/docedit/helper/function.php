<?

class doceditHelper {

function loadHelper($info) {

  $arr["icon"] = THEME_PATH."/images/icons/edit.png";
  $arr["link"] = "openModuleWindow('docedit','".$info["id"]."',800,600);";
  $arr["title"] = _EDIT_FILE;

  return $arr;

}

}

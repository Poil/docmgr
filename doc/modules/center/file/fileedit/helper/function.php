<?

class fileeditHelper {

function loadHelper($info) {

  // allow edit if file is a plaintext file
  if (return_file_type($info["name"]) != "txt") return false;
  
  $arr["icon"] = THEME_PATH."/images/icons/edit.png";
  $arr["link"] = "openModuleWindow('fileedit','".$info["id"]."','800','600');";
  $arr["title"] = _EDIT_FILE;

  return $arr;

}

}

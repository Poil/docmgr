<?

class filepreviewHelper {

  function loadHelper($info) {

    $path = $info["level1"]."/".$info["level2"];

    if (file_exists(PREVIEW_DIR."/".$path."/".$info["id"].".docmgr")) {

      $arr["icon"] = THEME_PATH."/images/icons/preview.png";
      $arr["link"] = "showObjectPreview('filepreview','".$info["id"]."','".addslashes($info["name"])."','".$path."','".session_id()."');";
      $arr["title"] = _PREVIEW_FILE;

      return $arr;

    }

  }

}

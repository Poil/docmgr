<?

class docpropHelper {

  function loadHelper($info) {

    $arr["icon"] = THEME_PATH."/images/icons/property.png";
    $arr["link"] = "location.href='index.php?module=document&objectId=".$info["id"]."'";
    $arr["title"] = _PROPERTIES;
  
    return $arr;

  }

}

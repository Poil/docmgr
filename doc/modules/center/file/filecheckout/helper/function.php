<?

class filecheckoutHelper {

function loadHelper($info) {

  if ($info["status"]==1) return false;

  $arr["icon"] = THEME_PATH."/images/icons/checkout.png";
  $arr["link"] = "location.href='index.php?module=filecheckout&objectId=".$info["id"]."'";
  $arr["title"] = _CHECKOUT_FILE;
  
  return $arr;

}

}

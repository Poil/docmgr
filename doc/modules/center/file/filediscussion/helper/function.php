<?

class filediscussionHelper {

function loadHelper($info) {

  if (!$info["discussion"]) return false;
  
  $arr["icon"] = THEME_PATH."/images/icons/talk.gif";
  $arr["link"] = "location.href='index.php?module=file&includeModule=filediscussion&objectId=".$info["id"]."'";
  $arr["title"] = _VIEW_DISCUSS;
  
  return $arr;

}

}

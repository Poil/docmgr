<?

class docpdfHelper {

  function loadHelper($info) {

    $arr["icon"] = THEME_PATH."/images/icons/pdf.png";
    $arr["link"] = "location.href='index.php?module=docpdf&objectId=".$info["id"]."'";
    $arr["title"] = _VIEW_AS_PDF;
  
    return $arr;

  }

}

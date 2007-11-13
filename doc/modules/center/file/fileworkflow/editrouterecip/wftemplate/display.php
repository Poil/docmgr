<?

$str = createXmlHeader("wftemplate");
$str .= xmlEntry("pageAction",$pageAction);
if ($errorMessage) $str .= xmlEntry("error",$errorMessage);
else {

  if ($pageAction=="templatelist" || $pageAction=="loadsavelist") {

    for ($i=0;$i<$tempList["count"];$i++) {

      $str .= "<template>\n";
      $str .= xmlEntry("id",$tempList[$i]["id"]);
      $str .= xmlEntry("name",$tempList[$i]["name"]);
      $str .= "</template>\n";
  
    }

  }

}

$str .= createXmlFooter();

echo $str;
die;
  
<?

$curValue = $_REQUEST["curValue"];
$expandSingle = $_REQUEST["expandSingle"];
$mode = $_REQUEST["mode"];
$formName = $_REQUEST["formName"];
$divName = $_REQUEST["divName"];
$arr = array();
$xml = null;

if ($expandSingle) {
  $xml = expandSingleObj($conn,$curValue);
  $xmlmode = "singleobjtree";
}
else {
  $xml = expandValueObj($conn,$curValue);
  $xmlmode = "objtree";
}

//put it all together
$str .= createXmlHeader($xmlmode);
if ($mode) $str .= xmlEntry("mode",$mode);
if ($formName) $str .= xmlEntry("formName",$formName);
if ($divName) $str .= xmlEntry("divName",$divName);
if ($expandSingle) $str .= xmlEntry("expandSingle",$expandSingle);
$str .= $xml;
$str .= createXmlFooter();

echo $str;
die;


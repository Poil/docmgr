<?

$curValue = $_REQUEST["curValue"];
$expandSingle = $_REQUEST["expandSingle"];
$mode = $_REQUEST["mode"];
$formName = $_REQUEST["formName"];
$divName = $_REQUEST["divName"];
$arr = array();
$xml = null;

if ($expandSingle) {
  $xml = expandSingleCol($conn,$curValue);
  $xmlmode = "singlecoltree";
}
else {
  $xml = expandValueCol($conn,$curValue);
  $xmlmode = "coltree";
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


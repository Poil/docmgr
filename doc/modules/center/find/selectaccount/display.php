<?
/***********************************************
  Category and questions content
***********************************************/
$curArr = explode(",",$_REQUEST["curVal"]);

$opt = null;
$opt["conn"] = $conn;
$arr = returnAccountList($opt);


$str = "<select multiple style=\"width:175px\" name=\"accountList\" id=\"accountList\" size=\"16\">\n";

for ($i=0;$i<$arr["count"];$i++) {

  if (in_array($arr["id"][$i],$curArr)) $select = " SELECTED ";
  else $select = null;

  $str .= "<option value=\"".$arr["id"][$i]."\" ".$select.">".$arr["login"][$i]."\n";

}

$str .= "</select>\n";

$content = "

<form name=\"pageForm\" method=post>

<div style=\"float:right;padding-top:5px\">
<input type=\"button\" onClick=\"updateAccount();\" value=\""._UPDATE_SELECTION."\">
</div>

<div style=\"padding-top:10px\">
".$str."
</div>

</form>
";

$opt = null;
$opt["leftHeader"] = "Select Account";
$opt["content"] = $content;

$siteContent = sectionDisplay($opt);

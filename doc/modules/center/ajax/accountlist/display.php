<?

//return all of our accounts
$opt["conn"] = $conn;
$arr = returnAccountList($opt);

$str = createXmlHeader("accountlist");

for ($i=0;$i<$arr["count"];$i++) {

  $str .= "<account>\n";
  $str .= xmlEntry("id",$arr["id"][$i]);
  $str .= xmlEntry("first_name",$arr["first_name"][$i]);
  $str .= xmlEntry("last_name",$arr["last_name"][$i]);
  $str .= xmlEntry("email",$arr["email"][$i]);
  $str .= xmlEntry("login",$arr["login"][$i]);
  $str .= "</account>\n";
  
}

$str .= createXmlFooter();

echo $str;

die;
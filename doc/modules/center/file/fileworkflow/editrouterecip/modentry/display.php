<?

$str = createXmlHeader("entrylist");
$str .= xmlEntry("pageAction",$pageAction);

if ($errorMessage) $str .= xmlEntry("error",$errorMessage);

for ($i=0;$i<$eList["count"];$i++) {

  $str .= "<route>\n";
  $str .= xmlEntry("id",$eList[$i]["id"]);
  $str .= xmlEntry("workflow_id",$eList[$i]["workflow_id"]);
  $str .= xmlEntry("account_id",$eList[$i]["account_id"]);
  $str .= xmlEntry("account_name",returnAccountName($conn,$eList[$i]["account_id"]));
  $str .= xmlEntry("task_type",$eList[$i]["task_type"]);
  $str .= xmlEntry("task_notes",$eList[$i]["task_notes"]);
  $str .= xmlEntry("date_due",dateView($eList[$i]["date_due"],1));
  $str .= xmlEntry("date_complete",$eList[$i]["date_complete"]);
  $str .= xmlEntry("status",$eList[$i]["status"]);
  $str .= xmlEntry("sort_order",$eList[$i]["sort_order"]);
  $str .= xmlEntry("comment",$eList[$i]["comment"]);
  $str .= "</route>\n";
  
}

$str .= createXmlFooter();

echo $str;
die;
  
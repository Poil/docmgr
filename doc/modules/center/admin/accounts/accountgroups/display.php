<?

//get our list of groups
$sql = "SELECT * FROM auth_groups ORDER BY id";
$groupList = total_result($conn,$sql);

//get the groups our member belongs to
$sql = "SELECT groupid FROM auth_grouplink WHERE accountid='$accountId';";
$memberList = total_result($conn,$sql);

//get the list of permissions for each group
$sql = "SELECT * FROM auth_groupperm";
$permList = total_result($conn,$sql);

$checked = null;
$groupText = null;
$readonly = null;

for ($row=0;$row<$groupList["count"];$row++) {

  //if the group has higher permissions than our logged in user, skip (so they can't promote themselves)
  if (@in_array($groupList["id"][$row],$permList["group_id"])) {
    $key = array_search($groupList["id"][$row],$permList["group_id"]);
    $temp_bitset = &$permList["bitset"][$key];
    if (!bitset_compare(BITSET,$temp_bitset,ADMIN)) continue;;
  }

  if (@in_array($groupList["id"][$row],$memberList["groupid"])) $checked = " CHECKED ";
  else $checked = null;

  $groupText .= "<li style=\"list-style-type:none\">\n";
  $groupText .= "<input type=checkbox 
                        name=\"accountGroup[]\" 
                        id=\"accountGroup[]\"
                        ".$checked." 
                        ".$readonly."
                        value=\"".$groupList["id"][$row]."\"
                        >\n";
  $groupText .= $groupList["name"][$row]."\n";
  $groupText .= "</li>\n";

}

//basic info layout
$siteContent .= "    <div class=\"pageHeader\">
                "._MTDESC_ACCOUNTGROUPS."
                </div>
                <form name=\"pageForm\" method=post>
                <input type=hidden name=\"pageAction\" id=\"pageAction\" value=\"update\">
                <input type=hidden name=\"module\" id=\"module\" value=\"accounts\">
                <input type=hidden name=\"includeModule\" id=\"includeModule\" value=\"accountgroups\">
                <ul>
                ".$groupText."
                <br><br>
                <input type=submit name=\"submit\" value=\""._UPDATE."\">
                </ul>
                </form>
                ";
                


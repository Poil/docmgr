<script language="javascript">
var entermsg = "<?echo _GROUP_ENTER_ERROR;?>";
var deletemsg = "<?echo _DELETE_CONFIRM;?>";
</script>
<?

$permError = null;

$option=null;
$option["table"] = "auth_groups";
$option["fieldName"] = "name";
$option["fieldValue"] = "id";
$option["value"] = $groupId;
$option["name"] = "group_id";
$option["conn"] = $conn;
$option["size"] = "10";
$option["order"] = "name";
$option["change"] = "submitForm('select');";

$groupDD = createDropdown($option);

//create our permissions checkboxes
if ($groupId) $permString = createPermCheckbox($groupBitset);

$groupForm = "	<div class=\"formHeader\">"._NAME.":</div>
		<input type=text name=\"name\" id=\"name\" value=\"".$groupInfo["name"]."\">
		<br><br><br>
		";

if ($groupId) {

	if (bitset_compare($groupBitset,ADMIN,null) && !bitset_compare(BITSET,ADMIN,null)) { 
		$groupForm = "<div class=\"errorMessage\">"._GROUP_EDIT_PERM_ERROR."</div>\n";
		$permError = 1;
	}
	else
		$groupForm .= $permString;		
}

$updateBtn = "<input type=button style=\"width:175px\" onClick=\"return submitForm('update');\" value=\""._UPDATE_GROUP."\"><br><br>\n";
$addBtn = "<input type=submit style=\"width:175px\" onClick=\"return submitForm('add');\" value=\""._ADD_GROUP."\"><br><br>\n";
$deleteBtn = "<input type=button style=\"width:175px\" onClick=\"return submitForm('delete');\" value=\""._DELETE_GROUP."\"><br><br>\n";

//don't display buttons if we have a permissions error
if ($permError) $btnString = null;
else {

	if ($groupId) 
		$btnString = $updateBtn.$addBtn.$deleteBtn;	
	else
		$btnString = $addBtn;

}

$content = "

<form name=\"pageForm\" method=\"post\">
<input type=hidden name=\"pageAction\" id=\"pageAction\" value=\"add\">
<input type=hidden name=\"module\" id=\"module\" value=\"".$module."\">

<div style=\"width:100%;\">
<br>
<table width=80% border=0 align=center>
<tr><td width=40% valign=top>
	<div class=\"formHeader\">"._SELECT_GROUP.":</div>
	<br>
	".$groupDD."
</td><td valign=top width=40%>
	".$groupForm."
	<br><br>
	".$btnString."
</td></tr>
</table>
</form>
</div>

";

$option = null;
$option["leftHeader"] = _MT_GROUPADMIN;
$option["content"] = $content;

$siteContent .= sectionDisplay($option);


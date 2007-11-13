<?

includeJavascript("javascript/permissions.js");
includeStylesheet(THEME_PATH."/css/permissions.css");
$onPageLoad = "loadPermissions('permDiv');";

/************************************************
	file list content
************************************************/

$path_len = strlen(IMPORT_DIR) + 1;

//if the user tries to pass a phony directory, stop the script
if (!stristr($path,IMPORT_DIR)) die(_INVALID_DIR);

//get our list of files and directories
$listArray = listDirectory($path,null,null);

if ($path!=IMPORT_DIR) {

	$temp = explode("/",$path);
	array_pop($temp);

	$backPath = implode("/",$temp);

	$back = "<a href=\"javascript:selectPath('".addslashes($backPath)."');\"><b><-- "._BACK."</b></a><br>";

}


$content1 = 	$back."
			<table width=100%>
			<tr><td valign=top>
				".showFiles($path,$listArray)."
			</td><td valign=top>
				<input type=button onClick=\"formSubmit('import');\" value=\""._IMPORT_OBJECTS."\">
			</td></tr>
			</table>
			";

$header1 = $path;


/***********************************************
	Category and questions content
***********************************************/
$option = null;
$option["conn"] = $conn;
$option["mode"] = "radio";
$option["formName"] = "parentId";
$option["divName"] = "collist";
if ($view_object) $option["curValue"] = $view_object;

$content2 = "

		<a name=\"#categories\">
		<br>
		<div id=\"questions\">
		<div class=\"areaHeader\">
			"._DELETE_AFTER_IMPORT."?
		</div>
		<input type=radio name=delete_files value=\"yes\" >"._YES."
		&nbsp;&nbsp;
		<input type=radio name=delete_files value=\"no\" CHECKED >"._NO."
		<br><br>
		</div>
		<div id=\"categories\">
		<div class=\"areaHeader\">
		"._SELECT_DEFAULT_COLLECTION."
		</div>
		<div style=\"padding-left:15px;padding-top:10px\">
		<div id=\"collist\"></div>
		</div>
		</div>
		".tree_view($option);

$header2 = _FILE_PREF;


/**********************************************
	File permissions content
**********************************************/

$content = "

<form name=pageForm method=post>
<input type=hidden name=module id=module value=\"".$module."\">
<input type=hidden name=pageAction id=pageAction value=\"\">
<input type=hidden name=path id=path value=\"".$path."\">

<div style=\"width:100%;\">
<table width=100% border=0>
<tr><td valign=top width=50% style=\"padding-right:10px\">

	<div class=\"pageHeader\" id=\"pathDisplay\">
	".IMPORT_DIR."
	</div>
	<div id=\"fileList\"></div>

</td><td valign=top width=50%>

	<div id=\"questionList\">
	<div class=\"pageHeader\">
	".$header2."
	</div>
	".$content2."
	</div>
	<br><br>
	<div id=\"permList\">
	<div class=\"pageHeader\">
	"._GROUPS."
	</div>
	<input type=checkbox CHECKED onClick=\"showPerm();\" name=\"permInherit\" id=\"permInherit\" value=\"1\">
	"._INHERIT_PERM_PARENT."
	<br><br>
	<div id=\"catPermissions\">

	<div id=\"permDiv\"></div>

	</div>
	</div>

</td></tr>
</table>
</form>
</div>

<script language=\"javascript\">
showPerm();
loadDir('".IMPORT_DIR."');
</script>

";

$option = null;
$option["hideHeader"] = 1;
$option["content"] = $content;
$siteContent .= sectionDisplay($option);


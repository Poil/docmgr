<? 

if ($successMessage1) selfClose();

$option = null;
$option["conn"] = $conn;
$option["mode"] = "checkbox";
$option["formName"] = "parentId[]";
$option["divName"] = "selectcol";

$str = "
	<form name=\"pageForm\" method=post>
	<br>

	<div style=\"float:right;margin-right:10px;\">
	<div class=\"formHeader\">
	"._BOOKMARK."
	</div>
	<input type=radio name=\"bookmark\" id=\"bookmark\" value=\"yes\"> "._YES."
	&nbsp;&nbsp;&nbsp;&nbsp;
	<input type=radio name=\"bookmark\" id=\"bookmark\" value=\"no\" CHECKED> "._NO."
	</div>

	<div class=\"formHeader\">
	"._NAME."
	</div>
	<input type=text name=\"searchName\" id=\"searchName\" value=\"\">
	<div class=\"cleaner\"></div>
	<br><br>
	<div class=\"formHeader\">
	"._SELECT_DESTINATION."
	</div>
	<div id=\"selectcol\" style=\"width:300px;max-height:290px;overflow:auto;\"></div>
	<br><br>
	<input type=submit onClick=\"return checkName();\" name=\"createQuery\" value=\""._CREATE_QUERY."\">
	</form>
	".tree_view($option);

$opt = null;
$opt["leftHeader"] = "Create Saved Search";
$opt["content"] = $str;
$siteContent = sectionDisplay($opt);


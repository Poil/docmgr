<? 

$sql = "SELECT * FROM dm_object WHERE id='$objectId'";
$info = single_result($conn,$sql);

//get the user's current email address
$accountInfo = returnAccountInfo($conn,USER_ID,null);
$defaultEmail = $accountInfo["email"];

if (!$defaultEmail) $content = "<br><div class=\"errorMessage\">"._NO_EMAIL_ERROR."</div>";
else {


$content = "

<form name=\"pageForm\" method=\"post\">

<input type=hidden name=\"objectId\" id=\"objectId\" value=\"".$objectId."\">
<input type=hidden name=\"module\" id=\"module\" value=\"fileemail\">
<input type=hidden name=\"pageAction\" id=\"pageAction\" value=\"\">
<input type=hidden name=\"includeModule\" id=\"includeModule\" value=\"fileemail\">
<br><br>
<div class=\"formHeader\">"._TO.":</div>";

if (defined("REQUIRE_SYSTEM_EMAIL")){
    	$content .="
	<div id=\"anonOpts1\" style=\"visibility:visible\">
	<SELECT ID=\"emailSystemUsers\" NAME=\"emaillSystemUsers[]\" MULTIPLE SIZE=5> ";
	$opt = null;
	$opt["conn"] = $conn;
	$opt["filter"] = "(email!=NULL)";
	$opt["sort"] = "last_name";
	$email_list = returnAccountList($opt);
	
	for ($row=0;$row<$email_list["count"];$row++) {
		$content .= "<OPTION VALUE=".$email_list["id"][$row].">".$email_list["first_name"][$row] ." ".$email_list["last_name"][$row]."\n" ;
	}
	$content .= " </select>
	</div>
	<div id=\"anonOpts2\" style=\"visibility:hidden\">";
	$onClickVal="cycleObject('anonOpts');cycleObject('anonOpts1');cycleObject('anonOpts2')";
	$enddiv="</div>";
}
else {
	$onClickVal="cycleObject('anonOpts')";
	$enddiv="";
}
$content .= "
<input type=text size=30 name=\"emailTo\" id=\"emailTo\">".
$enddiv."<BR><br>
<div class=\"formHeader\" >"._YOUR_EMAIL.":</div>
".$defaultEmail."
<br><br>
<div class=\"formHeader\">
<input type=checkbox name=\"anonRecip\" id=\"anonrecip\" value=\"1\" onClick=\"".$onClickVal."\">
"._SEND_TO_ANON_RECIP."
</div>

<div id=\"anonOpts\" style=\"visibility:hidden;position:absolute\">
<br>
<table cellpadding=0 cellspacing=0 width=100%>
<tr><td width=50% valign=top>
  <div class=\"formHeader\">
  "._VALID_LINK_TIME."
  </div>
  <input type=text size=4 maxlength=4 name=\"linkTime\" id=\"linkTime\" value=\"8\">
  <select name=\"linkTimeType\" id=\"linkTimeType\">
  <option value=\"minutes\" >"._MINUTES."
  <option value=\"hours\" SELECTED>"._HOURS."
  <option value=\"days\">"._DAYS."
  <option value=\"weeks\">"._WEEK."
  <option value=\"months\">"._MONTH."
  </select>
  </div>
</td><td width=50%>
 ";
 
if ($_SESSION["user_email"]) {
  $content .= "<div class=\"formHeader\">"._NOTIFY_FILE_VIEW."</div>
              <input type=radio name=\"notify\" id=\"notify\" CHECKED value=\"email\"> "._YES."
              &nbsp;&nbsp;&nbsp;&nbsp;  
              <input type=radio name=\"notify\" id=\"notify\" value=\"none\"> "._NO."
              ";
}              

$content .= "

</td></tr>
</table>
</div>
<br>
<div class=\"formHeader\">"._COMMENTS.":</div>
<textarea name=\"emailComments\" id=\"emailComments\" rows=6 cols=60></textarea>
<br><br>
<input type=\"button\" onClick=\"return submitEmailForm();\" name=\"sendEmail\" value=\""._SEND_EMAIL."\">
</form>

";

}

$header = _EMAILING." \"".$info["name"]."\"";

$opt = null;
$opt["leftHeader"] = $header;
$opt["content"] = $content;
$siteContent .= sectionDisplay($opt);

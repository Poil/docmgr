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
<input type=hidden name=\"module\" id=\"module\" value=\"colemail\">
<input type=hidden name=\"pageAction\" id=\"pageAction\" value=\"\">
<br><br>
<div class=\"formHeader\">"._TO.":</div>
<input type=text size=30 name=\"emailTo\" id=\"emailTo\">
<br><br>
<div class=\"formHeader\">"._YOUR_EMAIL.":</div>
<input type=text READONLY size=30 name=\"emailFrom\" id=\"emailFrom\" value=\"".$defaultEmail."\">
<br><br>
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

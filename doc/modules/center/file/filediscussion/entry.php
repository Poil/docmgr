<?

$pageContent .= "<br>";

if ($threadId && !$messageId) {

	$inputSubmit = "<input type=\"submit\" name=\"replyPost\" id=\"replyPost\" value=\""._SUBMIT."\">";

}
elseif ($messageId ) {
	
	$sql = "SELECT header,content FROM dm_discussion WHERE id='$messageId';";
	$info = single_result($conn,$sql);

	$leftHeader = "Editing \"".$info["header"]."\"";
	$replyText = $info["header"];
	$replyContent = $info["content"];

	$inputSubmit = "<input type=\"submit\" name=\"changePost\" id=\"changePost\" value=\""._SUBMIT."\">";

}
else  {

	//$leftHeader = "Submit New Post";

	$inputSubmit = "<input type=\"submit\" name=\"newPost\" id=\"newPost\" value=\""._SUBMIT."\">";
}

if (!$threadId) 

	$subject = "	<tr><td>
				<div class=\"formHeader\">
				"._SUBJECT.":
				</div>
				<input type=text name=header id=header value=\"".$threadInfo["header"]."\">
				</td></tr>
				";

else $subject = "<input type=hidden name=header id=header value=\"".$threadInfo["header"]."\">";

$pageContent .= 
	"<table>
	".$subject."
	<tr><td valign=top>
		<br>
		<div class=\"formHeader\">
		"._MESSAGE.":
		</div>
		<textarea rows=6 cols=40 name=content>".$replyContent."</textarea>
	</td></tr>
	<tr><td>
		<br>
		".$inputSubmit."
	</td></tr>
	</table>

	";


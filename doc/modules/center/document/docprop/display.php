<?

$infoStr = $objOwner;
if ($createDate) $infoStr .= " "._ON." ".$createDate[0]." "._AT." ".$createDate[1];

$pageContent .= "

<form name=pageForm method=post>

<input type=hidden name=\"pageAction\" id=\"pageAction\" value=\"update\">
<input type=hidden name=\"includeModule\" id=\"includeModule\" value=\"colprop\">

	<div class=\"formHeader\">
	"._NAME."
	</div>
	<input type=text size=30 name=\"name\" id=\"name\" value=\"".$curInfo["name"]."\"><br>
	<br><br>
	<div class=\"formHeader\">
	"._DESCRIPTION."
	</div>
	<textarea rows=4 cols=25 name=\"summary\" id=\"summary\">".$curInfo["summary"]."</textarea>
	<br><br>
	<div class=\"formHeader\">
	"._CREATED."
	</div>
	".$infoStr."
	<br><br>
	<input type=\"submit\"  value=\""._SUBMIT_CHANGES."\">

</form>

";


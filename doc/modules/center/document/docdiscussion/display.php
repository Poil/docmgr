<?

$controls = array();
$pages = null;
if (!$curPage) $curPage = "1";

if ($threadId) $controls[] = "<a class=\"boldLink\"  href=\"javascript:postReply('".$threadId."');\">"._REPLY_TOPIC."</a>";

if (!$pageAction || $pageAction=="list")	
	$controls[] = "<a class=\"boldLink\"  href=\"javascript:createTopic();\">"._POST_TOPIC."</a>";

if ($pageAction=="entry" || $pageAction=="viewThread")
	$controls[] = "<a class=\"boldLink\"  href=\"index.php?module=".$module."&includeModule=".$includeModule."\">"._BACK_TOPIC_LIST."</a>";

if ($threadId) {


	$numPages = $totalResults/10;
	if (strstr($numPages,".")) $numPages = intVal($numPages) + 1;
	if ($numPages=="0") $numPages = "1";


	$pages .= "<div class=\"pageNumbers\">[&nbsp;";

	for ($row=1;$row<=$numPages;$row++) {

		$url = "index.php?module=".$module."&page=".$page."&pageAction=viewThread&threadId=".$threadId."&curPage=".$row;

		if ($curPage == $row) $pages .= $row."&nbsp;";
		else $pages .= "<a class=\"boldLink\"  href=\"".$url."\" class=main>".$row."</a>&nbsp;";

	}

	$pages .= "]&nbsp;&nbsp;</div>";

}

$pageContent .= "<div style=\"padding-bottom:15px\">".implode("&nbsp;&nbsp;|&nbsp;&nbsp;",$controls)."</div>
		<form name=\"pageForm\" method=\"post\">
		<input type=hidden name=\"pageAction\" id=\"pageAction\" value=\"\">
		<input type=hidden name=\"includeModule\" id=\"includeModule\" value=\"docdiscussion\">

		<input type=hidden name=threadId id=threadId value=\"".$threadId."\">
		<input type=hidden name=messageId id=messageId value=\"".$messageId."\">
		<input type=hidden name=origThread id=origThread value=\"".$origThread."\">
		";

if (!$pageAction || $pageAction=="list") include("list.php");
elseif ($pageAction=="entry") include("entry.php");
elseif ($pageAction=="viewThread") include("thread.php");



$pageContent .= "</form>\n";


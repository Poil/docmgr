<?
/*****************************************************************************************************

	body.inc.php

	This file displays the site including all side columns and logos

*****************************************************************************************************/
?>
<html>
<head>
<title>
<?
if ($siteTitle) echo $siteTitle;
else echo SITE_TITLE;
?>
</title>

<META http-equiv="Content-Type" content="text/html; charset=<?echo VIEW_CHARSET;?>">

<?
/****************************************************
	our stylesheets and javascript files
****************************************************/
includeStylesheet(THEME_PATH."/css/core.css");
if ($modStylesheet) includeStylesheet($modStylesheet);	//module stylesheet
if ($modCss) includeStylesheet($modCss);        //module stylesheet that lives in the theme modcss directory

showSiteJs();

includeJavascript("jslib/core.js");
includeJavascript("jslib/dom.js");
includeJavascript("jslib/ajax.js");
includeJavascript("jslib/string.js");

includeJavascript("javascript/docmgr.js");
includeJavascript("javascript/tree.js");
includeJavascript("javascript/browse.js");
includeJavascript("javascript/discussion.js");

if ($modJs) includeJavascript($modJs);	//module javascript

//load common object js files
loadObjectJs();

if ($onPageLoad) $onPageLoadStr = "onLoad=\"".$onPageLoad."\"";
?>

</head>

<body leftmargin=0 topmargin=0 rightmargin=0 <?echo $onPageLoadStr;?>>
<div id="objectPreview"></div>
<div id="objectMenu"></div>
<div class="siteHeader">
	<div class="titlePicBar">
	<img src="<?echo THEME_PATH;?>/images/titlepic.png" border=0>
	</div>
	<div class="navBar">
	<? echo $navBar; ?>
	</div>
	<br>
	<div id="siteStatus"></div>

</div>
<div class="siteBody">

	<div class="siteLeftColumn">
	<?echo $leftColumnContent;?>
	</div>
	<div class="siteCenterColumn" id="siteCenterColumn">
	<?if ($siteMessage) echo $siteMessage;?>
	<? echo $toolBar; ?>
	<?echo $siteContent;?>
	</div>

</div>
</body>
</html>

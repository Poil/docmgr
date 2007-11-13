<?
/*****************************************************************************************************

	noheader.inc.php
	
	This file displays the actual site layout, but does not display the logo, toolbars,
	left, or right columns.  This is useful for simple popup windows.  It is called
	if the $hideHeader variable is set

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

showSiteJS();

includeJavascript("jslib/core.js");
includeJavascript("jslib/dom.js");
includeJavascript("jslib/ajax.js");
includeJavascript("jslib/string.js");

includeJavascript("javascript/docmgr.js");
includeJavascript("javascript/tree.js");
includeJavascript("javascript/browse.js");
if ($modJs) includeJavascript($modJs);	//module javascript

if ($onPageLoad) $onPageLoadStr = "onLoad=\"".$onPageLoad."\"";

?>

</head>

<body leftmargin=0 topmargin=0 rightmargin=0 <?echo $onPageLoadStr;?>>
<div class="siteBody">

	<div class="siteCenterColumnNoHeader">
	<?if ($siteMessage) echo $siteMessage."<br>";?>
	<?echo $siteContent;?>
	</div>

</div>
</body>
</html>

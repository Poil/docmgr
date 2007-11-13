<?
/*****************************************************************************************************

	logo.inc.php
	
	This file displays the site layout with the top logo, but with no
	left or right columns.  This is useful if we have a site with just
	a left column, but don't want that column to display during a login
	screen

*****************************************************************************************************/
?>
<html>
<head>
<LINK rel="SHORTCUT ICON" href="<? echo THEME_PATH."/docmgrlogo.ico"; ?>">
<title>
<?echo SITE_TITLE;?>
</title>

<META http-equiv="Content-Type" content="text/html; charset=<?echo VIEW_CHARSET;?>">

<?
/****************************************************
	our stylesheets and javascript files
****************************************************/
includeStylesheet(THEME_PATH."/css/core.css");
if ($modCss) includeStylesheet($modCss);        //module stylesheet that lives in the theme modcss directory

if ($modStylesheet) includeStylesheet($modStylesheet);	//module stylesheet

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
else $onPageLoadStr = null;
?>

</head>

<body leftmargin=0 topmargin=0 rightmargin=0 <?echo $onPageLoadStr;?>>
<div class="siteHeader">
<div class="titlePicBar">
	<img src="<?echo THEME_PATH;?>/images/titlepic.png" border=0>
</div>
<div class="navBar">
	<? echo $navBar; ?>
</div>
<br><br>
<div class="toolBar">
	<? echo $toolBar; ?>
</div>
</div>
<div class="siteBody">

	<div class="siteCenterColumnNoHeader">
	<?if ($siteMessage) echo $siteMessage;?>
	<?echo $siteContent;?>
	</div>

</div>
</body>
</html>

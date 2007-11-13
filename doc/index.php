<?php

include("header/header.inc.php");

//get our module information for our file includes
if ($_POST["module"]) $module = $_POST["module"];
elseif ($_GET["module"]) $module = $_GET["module"];

$show_login_form = $_REQUEST["show_login_form"];

//permissions time.  Check to see if we defined a permission limit for this page,
//and if the user qualifies.  We only do this if the user is logged in

//initialize our variables
$permError = null;
$checkValue = null;

if (file_exists("app/preauth.inc.php")) include("app/preauth.inc.php");

//here we will call the default module
if (!$module) $module = DEFAULT_MOD;

//do we authorize people in this site to access any module
if (defined("PROCESS_AUTH")) include("auth/auth.inc.php");

//if we are authorized, prevent the login form from showing up
if ($_SESSION["authorize"]=="1") {
	$_REQUEST["show_login_form"] = null;
	$show_login_form = null;
}

//just show the login if we get this far and the user isn't logged in
if (defined("PROCESS_AUTH") && !defined("USER_ID") && !$show_login_form) $show_login_form = 1;

//only do this if we are not displaying a login
if (!$show_login_form) {

	/****************************************************************************************
		make sure this user can access this module, then extract error message 
		if there is any. 
	*****************************************************************************************/
	$permError = null;
	$arr = null;
	
	//process our module permissions
	$arr = checkModPerm($module,BITSET);
	if (is_array($arr)) extract($arr);

	//include our custom processing for this application
	if (file_exists("app/postauth.inc.php")) include("app/postauth.inc.php");

	//default to a view_charset if not set
	if (!defined("VIEW_CHARSET")) define("VIEW_CHARSET",DB_CHARSET);

	//process our custom permissions
	if (defined("CUSTOM_BITSET")) {
		$arr = checkCustomModPerm($module,CUSTOM_BITSET);
		if (is_array($arr)) extract($arr);
	}

	//start processing our module if all is well permission-wise
	if (!$permError) {

		$modPath = $siteModInfo[$module]["module_path"];
		$modStylesheet = null;
		$modJs = null;
		$modCss = null;

		//determine our process file and our display file
		$process_path = $modPath."process.php";
		$style_path = $modPath."stylesheet.css";
		$js_path = $modPath."javascript.js";
		$display_path = $modPath."display.php";
		$function_path = $modPath."function.php";
		$css_path = THEME_PATH."/modcss/".$module.".css";

		//load any optional function files in the module directory
		if (file_exists("$function_path")) include("$function_path");
		if (file_exists("$process_path")) include("$process_path");

		//these get called by our body.inc.php file
		if (file_exists("$style_path")) $modStylesheet = $style_path;
		if (file_exists("$js_path")) $modJs = $js_path;
		if (file_exists("$css_path")) $modCss = $css_path;

		//define our display module if there is one
		if (file_exists("$display_path") && !defined("DISPLAY_MODULE")) define("DISPLAY_MODULE","$display_path");

	}

}

/********************************************************************
	Call our layout files
********************************************************************/
if (!defined("SITE_THEME")) die("No theme is defined for the site");

//create a define for referencing all our theme objects (css,layout,images)
define("THEME_PATH","themes/".SITE_THEME);

$leftColumnContent = null;
$rightColumnContent = null;
$siteContent = null;

//backwards compatible for hideHeader calls
if ($hideHeader) $siteTemplate = "blank";
else if ($_REQUEST["forceEmpty"]) $siteTemplate = "empty";

//allow changing of the template from the url bar
if ($_REQUEST["siteTemplate"]) $siteTemplate = $_REQUEST["siteTemplate"];

//get our module template for display
if ($siteTemplate) $template = $siteTemplate;
else {

        $template = $siteModInfo[$module]["template"];
        if (!$template) $template = "normal";

}

//if we are calling the empty template, stop here after display
if ($template=="empty") {
  //call our module's display file
  if ($show_login_form) include("auth/login.inc.php");
  else if (defined("DISPLAY_MODULE")) include(DISPLAY_MODULE);
  echo $siteContent;
  die;
} 

//call our left column modules
include("header/left.inc.php");

//call our right column modules
include("header/right.inc.php");

//call our module's display file
if ($show_login_form) {
  include("lang/".DEFAULT_LANG.".php");
  include("auth/login.inc.php");
}
else if (defined("DISPLAY_MODULE")) include(DISPLAY_MODULE);

//any display any messages from our modules
if ($successMessage) $siteMessage = "<div class=\"successMessage\">".$successMessage."</div>\n";
elseif ($errorMessage) $siteMessage = "<div class=\"errorMessage\">".$errorMessage."</div>\n";
else $siteMessage = null;


//call our navbar utility if it exists
if (file_exists(THEME_PATH."/layout/navbar.inc.php")) include(THEME_PATH."/layout/navbar.inc.php");


//call logo file if available for login, otherwise call the template
if ($show_login_form && is_file(THEME_PATH."/layout/logo.php")) {
        include(THEME_PATH."/layout/logo.php"); //show the logo with the login form
} else {
        $templateFile = THEME_PATH."/layout/".$template.".php";
        if (file_exists($templateFile)) include($templateFile);
        else die("Template $template does not exist");
}


<?

//files common to this app
include("app/common.inc.php");
include("app/custom_form.inc.php");
include("app/object.inc.php");
include("app/index_function.inc.php");
include("app/thumb_function.inc.php");
include("app/tree.inc.php");

if (!is_writable(FILE_DIR)) die("Error!  files/ directory is not writable by webserver");

//must be done before our main function includes are called.  (no trailing slashes)
define("TMP_DIR",FILE_DIR."/tmp");
define("DATA_DIR",FILE_DIR."/data");
define("THUMB_DIR",FILE_DIR."/thumbnails");
define("PREVIEW_DIR",FILE_DIR."/preview");
define("DOC_DIR",FILE_DIR."/document");

//create our subdirectories.  If they already exist, nothing is done
if (!$_SESSION["subdirCheck"]) {

  createFileSubDir(DATA_DIR);
  createFileSubDir(THUMB_DIR);
  createFileSubDir(PREVIEW_DIR);
  createFileSubDir(DOC_DIR);
  $_SESSION["subdirCheck"] = 1;

}
        
        

//set the execution time for uploading and file processing
if (defined("EXECUTION_TIME")) ini_set("max_execution_time",EXECUTION_TIME);

//setup which apps are available to docmgr
setExternalApps();

//null our session saying our objects have been loaded
$_SESSION["objectsLoaded"] = null;

/********************************************************************************
  from here, we are going to handle a special mod setting called skip_auth.
  this will perform the module loading and theme display with NO permissions
  checking.  This can only be set in the module.xml file
********************************************************************************/

if ($siteModInfo[$module]["skip_auth"]=="1") {

  $modPath = $siteModInfo[$module]["module_path"];
  $modStylesheet = null;
  $modJs = null;
  $siteContent = null;

  //call our default language file
  include("lang/".DEFAULT_LANG.".php");
  
  //determine our process file and our display file
  $process_path = $modPath."process.php";
  $style_path = $modPath."stylesheet.css";
  $js_path = $modPath."javascript.js";
  $display_path = $modPath."display.php";
  $function_path = $modPath."function.php";

  //load any optional function files in the module directory
  if (file_exists("$function_path")) include("$function_path");
  if (file_exists("$process_path")) include("$process_path");
  
  //these get called by our body.inc.php file
  if (file_exists("$style_path")) $modStylesheet = $style_path;
  if (file_exists("$js_path")) $modJs = $js_path;

  if (file_exists("$display_path")) include("$display_path");


  /********************************************************************
    Call our layout files
  ********************************************************************/
  if (!defined("SITE_THEME")) die("No theme is defined for the site");

  //create a define for referencing all our theme objects (css,layout,images)
  define("THEME_PATH","themes/".SITE_THEME);

  //any display any messages from our modules
  if ($successMessage) $siteMessage = "<div class=\"successMessage\">".$successMessage."</div>\n";
  elseif ($errorMessage) $siteMessage = "<div class=\"errorMessage\">".$errorMessage."</div>\n";
  else $siteMessage = null;

  //call our body file.  This contains the structure for our site.  only the logo page is allowed here
  if ($hideHeader) include(THEME_PATH."/layout/blank.php");
  else include(THEME_PATH."/layout/logo.php");

  //quit processing here
  die;
}  
  
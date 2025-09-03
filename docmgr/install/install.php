<?php
return false;

//required files
include("lib/pgsql.php");
include("lib/logger.php");
include("lib/misc.php");
include("lib/xml.php");
include("lib/sanitize.php");
include("config/version.php");
include("classes/template.php");

//set error reporting to not show notices
error_reporting(E_ALL ^ E_NOTICE ^ E_STRICT);

//don't do these if this isn't the first installation
if (!file_exists("config/config.php"))
{
  define("DEBUG","5");
  define("DEV_MODE","1");
}

//setup our browser information
set_browser_info();

//which step are we on
session_start();

//we made it to here, record there's a setup in process
$_SESSION["installInProgress"] = 1;

//our main theme
define("THEME_PATH","themes/default");

//start at the beginning
if (!isset($_POST["step"])) 
{

  $_POST["step"] = "0";
  $_POST["nextstep"] = "0";

}

//vars we'll need
$finished = null;
$errorMessage = null;

//init our base and app config setup classes
$cf = new TEMPLATE("config");
$af = new TEMPLATE("app");

//setup our possible config classes
$steps = array();
$stepArr[] = "verify";
$stepArr[] = "config";
$stepArr[] = "database";
$stepArr[] = "app";

//get their files
foreach ($stepArr AS $file)
{
  require_once("classes/".$file.".php");
}

//init our current class based on the step we're on
$curClass = $stepArr[$_POST["step"]];
$c = new $curClass();

//if there's a submitted process, handle it
if (isset($_POST["action"]))
{
  if ($_POST["action"]=="next") 
	{
	
	  $c->process();
	
	  //if no errors loads the next step
	  $err = $c->getError();
	  
	  if (!$err) 
	  {
	    $_POST["step"]++;
	
	    //if there's another class to load, load it.
	    if (isset($stepArr[$_POST["step"]]))
	    {
	
	      $curClass = $stepArr[$_POST["step"]];
	      $c = new $curClass();
	
	    }
	    //otherwise bail
	    else
	    {
	
	      $str = file_get_contents("install/install.php");
	
	      $str = preg_replace("/<\?php\n/","<?php\nreturn false;\n",$str);
	      
	      file_put_contents("install/install.php",$str);
	
	      $finished = 1;
	    
	    }
	
	  } else $errorMessage = $err;
	
	}
	//go back a page
	else if ($_POST["action"]=="back")
	{
	
	  $_POST["step"]--;
	
	  $curClass = $stepArr[$_POST["step"]];
	  $c = new $curClass();
	
	}
	
}

//setup our main form
$siteContent = "
<form name=\"pageForm\" method=\"post\">
<input type=\"hidden\" name=\"step\" id=\"step\" value=\"".$_POST["step"]."\">
<input type=\"hidden\" name=\"action\" id=\"action\" value=\"\">
";

//all done, show the final message
if ($finished) 
{

  //write our files
  $cf->writeFile();
  $af->writeFile();

  $siteContent .= "<div style=\"padding:10px;width:600px;\">
                    <h3>Your setup is complete.</h3>
                    <p>
                      If you want to run setup again, just remove the \"return false;\" 
                      line at the top of the install/install.php file.  It is recommended that you
                      remove the entire install/ directory once you are done with setup.
                    </p>
                    <p>
                      If this is a new installation, the default username and password is admin/admin.
                    </p>
                    <p>
                      If you are upgrading an existing installation, don't forget to move your
                      files/ directory from the old installation to the new one.
                    </p>
                    <p>
                      <a href=\"index.php\"><b><u>Click to login</u></b></a>
                    </p>
                  </div>
                  ";

}
//still going, load main config form
else
{

  //give us a back button if not on first page
  if ($_POST["step"]!=0) 
  {
    $backStyle = "style=\"visibility:visible;position:relative;\"";
  }
  else
  {
    $backStyle = "style=\"visibility:hidden;position:absolute;\"";
  }
  
  //next button, end toolbar, show class content
  $siteContent .= "	<div class=\"errorMessage\">".$errorMessage."</div>
                    <div style=\"width:600px;padding-left:10px;\">
                      ".$c->display()."
                    </div>
                    ";
  

}

//end form
$siteContent .= "</form>";

//load our display template
include("normal.php");

//always stop here to prevent the anything else from loading
die;


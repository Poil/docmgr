<?php

//get our 2x methods
include("dbupgrade/upgrade2x.php");

//get our 1x methods for here
include("dbupgrade/upgrade1x.php");

class DATABASE
{

  private $file;
  private $errorMessage;
    
  /******************************************************
    FUNCTION:	getError
    PURPOSE:	returns an existing class error
  ******************************************************/  
  public function getError()
  {
    return $this->errorMessage;
  }
  
  /******************************************************
    FUNCTION:	throwError
    PURPOSE:	throws a class error
  ******************************************************/  
  public function throwError($err)
  {
    $this->errorMessage = $err;
  }

  
  /******************************************************
    FUNCTION:	display
    PURPOSE:	displays the form for entering config
              information
  ******************************************************/  
  public function display()
  {

    $content = "<h3>Database Setup</h3>
                <p>The installer will now create or update your database.</p>
                <p>If you are upgrading an existing DocMGR installation, you MUST backup your database before continuing.
                    The installer may make irreversible changes to your current database!</p>
                ";
                
    return $content;  
  
  }

  /******************************************************
    FUNCTION:	process
    PURPOSE:	writes our submitted values to the
              config file and saves the file
  ******************************************************/  
  public function process()
  {

    $DB = $this->connectDB();

    if ($DB->error()) die($DB->error());

    $this->setVer($DB);
    $this->checkDB($DB);
    
  }

  private function connectDB()
  {

    //get our stored config values
    $dbhost = $_SESSION["config"]["Required"]["DBHOST"][0];
    $dbuser = $_SESSION["config"]["Required"]["DBUSER"][0];
    $dbpassword = $_SESSION["config"]["Required"]["DBPASSWORD"][0];
    $dbport = $_SESSION["config"]["Required"]["DBPORT"][0];
    $dbname = $_SESSION["config"]["Required"]["DBNAME"][0];

    //conect to the database    
    $DB = new POSTGRESQL($dbhost,$dbuser,$dbpassword,$dbport,$dbname);

    return $DB;
  
  }
  
  private function setVer($DB)
  {

    $ver = $DB->version();
    $arr = explode(".",$ver);

    if ($arr[0]=="8") $pgVer = "8x";
    else $pgVer = "9x";

    $_SESSION["pgVersion"] = $pgVer;
    
  }


  protected function checkDB($DB)
  {

    //see if the database already exists
    $sql = "SELECT tablename FROM pg_tables WHERE tablename NOT LIKE 'pg%'
                                            AND	tablename NOT LIKE 'sql%'";
    $info = $DB->fetch($sql);

    if ($info["count"] > 0)
    {

      $dbVersion = 0;
      $currentVersion = (int)DB_VERSION;
      
      //keep going until all upgrades are done      
      while ($dbVersion < $currentVersion)
      {
      
        //get the database's version
        $sql = "SELECT * FROM db_version";
        $ver = $DB->single($sql);
      
        $dbVersion = $ver["version"];

        //stop here.  mission accomplished
        if ($dbVersion==$currentVersion) break;

        //upgrade if they don't match
        if ($dbVersion > $currentVersion)
        {

          $this->throwError("You appear to be running a later database version than this installation contains.  Abandoning setup");
          return false;      
      
        }
        else if ($dbVersion < $currentVersion)
        {
          $this->upgradeDB($DB,$dbVersion);
        }
     
      //end while loop 
      }
    
    }
    //create from scratch
    else
    {
      $this->createDB($DB);    
    }
  
    return true;
      
  }

  protected function createDB($DB)
  {

    //if using an 8x version, we need to make plpgsql manually
    if ($_SESSION["pgVersion"]=="8x") 
    {
      $sql = "CREATE PROCEDURAL LANGUAGE plpgsql;";
      $DB->query($sql);
    }
    
    $file = "install/docmgr.pgsql";

    //create the database from scratch
    $sql = file_get_contents($file);
    $DB->query($sql);

  }

  protected function upgradeDB($DB,$version)
  {
	
	  $ret = true;

	  $u1 = new UPGRADE1X($DB);
	  $u2 = new UPGRADE2X($DB);
	  
    if (!$version || $version<2010041401) 
    {
      $u1->verRC10();
      $version = 2010041401;
    }
    
    if ($version<2010101001)
    {
      $u1->verRC14();
      $version = 2010101001;
    }

    if ($version<2011021501)
    {
      $u1->ver11();
      $version = 2011021501;
    }

    if ($version<2012052001)
    {
      $u1->upgrade124();
      $version = 2012052001;
    }

    //hack.  forgot to set the database version in docmgr.pgsql to match version.php 
    //when we released 2.0 -> 2.0.2
    if ($version==2012062801) $version = 2012082001;

    //upgrade 1.x to 2.0
    if ($version<2012082001)
    {
      $ret = $u2->ver20();
      $version = 2012082001;
    }

    //upgrade to 2.1
    if ($version<2013050101)
    {
      $ret = $u2->ver21();
      $version = 2013050101;
    }

    if ($ret==true)
    {
      //set our new database version
      $sql = "UPDATE db_version SET version='".DB_VERSION."'";
      $DB->query($sql);
    }

  }


}

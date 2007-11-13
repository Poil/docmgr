<?
//define path to docmgr.
define("WEBDAV_PATH","../");


/******************************************
  don't modify anything below here
******************************************/

//error_reporting(E_ERROR);
error_reporting(E_ALL ^ E_NOTICE);

require_once("Server/Filesystem.php");

$server = new HTTP_WebDAV_Server_Filesystem();
$server -> ServeRequest("/dev/null");

?>

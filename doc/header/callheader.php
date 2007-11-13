<?

if (defined("ALT_FILE_PATH")) $calldir = ALT_FILE_PATH."/";
else $calldir = null;

//the rest of our includes with our base functions
include($calldir."lib/accperms.php");
include($calldir."lib/arrays.php");
include($calldir."lib/calc.php");
include($calldir."lib/customforms.php");
include($calldir."lib/data_formatting.php");
include($calldir."lib/email.php");
include($calldir."lib/filefunctions.php");
include($calldir."lib/misc.php");
include($calldir."lib/modules.php");
include($calldir."lib/postgresql.php");
include($calldir."lib/presentsite.php");
include($calldir."lib/sanitize.php");
include($calldir."lib/xml.php");

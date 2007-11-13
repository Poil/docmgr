<?

$showMod = $_REQUEST["showMod"];

/****************************************************************************************
	make sure this user can access this module, then extract error message
	if there is any.
*****************************************************************************************/
$permError = null;
$arr = null;

//process our module permissions
$arr = checkCustomModPerm($showMod,CUSTOM_BITSET);
if (is_array($arr)) extract($arr);

if ($permError) die($errorMessage);

//processing for our sub module.We cannot call these with a function, or
//we do not get information passed from process to display like we want
$processPath = $siteModInfo["$showMod"]["module_path"]."process.php";
$functionPath = $siteModInfo["$showMod"]["module_path"]."function.php";

if (file_exists($processPath)) include($processPath);
if (file_exists($functionPath)) include($functionPath);

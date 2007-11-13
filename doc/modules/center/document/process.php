<?

$pageAction = $_POST["pageAction"];
$includeModule = $_REQUEST["includeModule"];

if ($_REQUEST["objectId"]!=NULL) $objectId = $_REQUEST["objectId"];
elseif ($_SESSION["objectId"]!=NULL) $objectId = $_SESSION["objectId"];

if (!$includeModule) $includeModule="docprop";

if ($objectId!=NULL) {

	$_SESSION["objectId"] = $objectId;

	//get our basic file information
	$sql = "SELECT * FROM dm_view_objects WHERE id='$objectId';";
	$colInfo = single_result($conn,$sql);
	$objName = $colInfo["name"];
	$objParent = $colInfo["parent_id"];
	
}


/****************************************************************************************
	make sure this user can access this module, then extract error message
	if there is any.
*****************************************************************************************/
$permError = null;
$arr = null;

//process our module permissions
$arr = checkCustomModPerm($includeModule,CUSTOM_BITSET);
if (is_array($arr)) extract($arr);

if ($permError) die($errorMessage);

//processing for our sub module.We cannot call these with a function, or
//we do not get information passed from process to display like we want
$processPath = $siteModInfo["$includeModule"]["module_path"]."process.php";
$functionPath = $siteModInfo["$includeModule"]["module_path"]."function.php";

if (file_exists($processPath)) include($processPath);
if (file_exists($functionPath)) include($functionPath);

$toolBar = returnNavList($conn,$objParent,null,$objName);

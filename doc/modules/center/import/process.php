<?

if ($_POST["parentId"]) $parentId = $_POST["parentId"];
if ($_POST["path"]) $path = $_POST["path"];

if (!$path) $path = IMPORT_DIR;

if ($_POST["importFiles"]) {

	//if no parent was passed, create/find the default Import category
	if (!$parentId) $parentId = createImportCategory($conn);

	//create our array to pass to the input function
	$option = null;
	$option["conn"] = $conn;
	$option["filePath"] = $_POST["filePath"];
	$option["parentId"] = $parentId;
		
	//import our files
	if (importObjects($option)) $successMessage = "Files imported successfully  ";
	else $errorMessage = "Files not imported  ";

	if (defined("IMPORT_ERROR_MESSAGE")) $errorMessage .= "<BR>".IMPORT_ERROR_MESSAGE;

	//vacuum the db because we just added a bunch of files	
	db_vacuum($conn);

}


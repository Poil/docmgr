<?
$page_name="upload";
$pageLoad="permShow();";
$form_name = "upload";

$parentId = $_REQUEST["parentId"];
$keyArr = returnKeywords();

if ($_POST["pageAction"] == "upload") {

	if ($_FILES["fileUpload"] || $_FILES["singleFile"]) {

		//if for some reason we have no collection, set it to "Unassigned"
		if (!$parentId) $parentId = "0";
		$pathArr = array();
		$nameArr = array();
		
		if ($_POST["modeStatus"]=="multiUpload") {

			$pathArr = $_FILES['fileUpload']['tmp_name'];
	                $nameArr = $_FILES['fileUpload']['name'];
	                $summary = null;
	                $fileVersion = null;
	                $num = count($pathArr);

			//null out our keywords so the hidden field doesn't get tied to our multi upload
			if (is_array($keyArr) && count($keyArr) > 0) {
				foreach ($keyArr AS $keyword) {
					$name = $keyword["name"];
					$_POST[$name] = null;
				}		
			}
  
		} else {
		
			$pathArr[0] = $_FILES['singleFile']['tmp_name'];
			$nameArr[0] = $_FILES['singleFile']['name'];
			$summary = $_POST["summary"];
			$fileVersion = $_POST["fileVersion"];
			$num = 1;
			
		}	
			
		$errorMessage = null;

		for ($i=0;$i<$num;$i++) {

			$fileName = $nameArr[$i];
			$filePath = $pathArr[$i];

			//set all our options into the array with corresponding keys.  These will
			//be passed to the file_insert function, which handles inserting the file into the system
			$option = null;
			$option["conn"] = $conn;
			$option["name"] = smartslashes($fileName);	
			$option["filepath"] = $filePath;
			$option["delete_files"] = "yes";
			$option["parentId"] = $parentId;
			$option["summary"] = $summary;
			$option["objectType"] = "file";
			$option["objectOwner"] = USER_ID;
			$option["customVersion"] = $fileVersion;	
			if ($objectId = createObject($option)) $successMessage = _FILE_UPLOAD_SUCCESS;
			else $errorMessage = _FILE_UPLOAD_ERROR;

			//tack on an error message from the function
			if (defined("ERROR_MESSAGE")) $errorMessage .= "<br>".ERROR_MESSAGE;

			if ($errorMessage) break;
			
		}

  

	}
	else $errorMessage = _FILE_UPLOAD_SELECT_ERROR;

}

$hideHeader = 1;


?>
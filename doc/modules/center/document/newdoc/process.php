<?

$parentId = $_REQUEST["parentId"];
if (!$parentId) $parentId = "0";

if ($_POST["pageAction"]=="update") {

  $option = null;
  $option["conn"] = $conn;
  $option["name"] = $_POST["name"];
  $option["summary"] = $_POST["summary"];
  $option["parentId"] = $parentId;
  $option["objectType"] = "document";
  $option["objectOwner"] = USER_ID;
  
  //insert the collection 
  if ($objectId = createObject($option)) $successMessage = _CREATE_SUCCESS;
  else {
    $errorMessage = _CREATE_ERROR;
    if (defined("ERROR_MESSAGE")) $errorMessage .= "<br>".ERROR_MESSAGE;
  }
              
}

$hideHeader = 1;

 
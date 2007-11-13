<?
$indexPage = 1;
$parentId = $_REQUEST["parentId"];

if ($_POST["pageAction"]=="update") {

  $option = null;
  $option["conn"] = $conn;
  $option["name"] = $_POST["name"];
  $option["summary"] = $_POST["summary"];
  $option["parentId"] = $parentId;
  $option["objectType"] = "url";
  $option["objectOwner"] = USER_ID;
  $option["url"] = $_POST["url"];

  //insert the collection
  if ($objectId = createObject($option)) $successMessage = _CREATE_SUCCESS;
  else {
    $errorMessage = _CREATE_ERROR;
    if (defined("ERROR_MESSAGE")) $errorMessage .= "<br>".ERROR_MESSAGE;
  }
}

$hideHeader = 1;

 
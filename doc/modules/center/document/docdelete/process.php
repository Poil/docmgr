<?

/************************************************
  file delete module
************************************************/

$objectId = $_REQUEST["objectId"];

if ($_POST["deleteObject"]) {

  if (deleteObject($conn,$objectId)) $successMessage = 1;
  else $errorMessage = _OBJECT_REMOVE_ERROR;

}

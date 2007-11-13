<?

$pageAction = $_POST["pageAction"];
$objectId = $_SESSION["objectId"];

if ($_SESSION["objectId"]==NULL) {
  $errorMessage = "No object is specified";
  return false;
}


if ($_POST["pageAction"]=="update") {

  $opt = null;
  $opt["conn"] = $conn;
  $opt["objectId"] = $objectId;
  $opt["objectType"] = "savesearch";
  $opt["name"] = $_POST["name"];
  $opt["summary"] = $_POST["summary"];

  if (!updateObject($opt)) {
    $errorMessage = _UPDATE_ERROR;
    if (defined("ERROR_MESSAGE")) $errorMessage .= "<br>".ERROR_MESSAGE;
  }
  else $successMessage = _UPDATE_SUCCESS;

}

//get our latest info for our form
$sql = "SELECT * FROM dm_object WHERE id='$objectId'";
$curInfo = single_result($conn,$sql);

if ($curInfo["create_date"]) $createDate = date_time_view($curInfo["create_date"],$altDateFormat);

$info = returnAccountInfo($conn,$curInfo["object_owner"],null);
$objOwner = $info["login"];

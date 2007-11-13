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
  $opt["objectType"] = "url";
  $opt["name"] = $_POST["name"];
  $opt["summary"] = $_POST["summary"];

  if (!updateObject($opt)) {
    $errorMessage = _UPDATE_ERROR;
    if (defined("ERROR_MESSAGE")) $errorMessage .= "<br>".ERROR_MESSAGE;
  }
  else {

    $successMessage = _UPDATE_SUCCESS;

    //add the url to the dm_url table
    $sql = "DELETE FROM dm_url WHERE object_id='$objectId';
            INSERT INTO dm_url (object_id,url) VALUES ('$objectId','".$_POST["url"]."');";
    db_query($conn,$sql);

  }
  
}

//get our latest info for our form
$sql = "SELECT dm_object.*,(SELECT url FROM dm_url WHERE object_id=dm_object.id) AS url FROM dm_object WHERE id='$objectId'";
$curInfo = single_result($conn,$sql);

if ($curInfo["create_date"]) $createDate = date_time_view($curInfo["create_date"],$altDateFormat);

$info = returnAccountInfo($conn,$curInfo["object_owner"],null);
$objOwner = $info["login"];

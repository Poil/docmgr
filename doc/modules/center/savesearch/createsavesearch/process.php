<?

//create the new query
if ($_POST["createQuery"]) {

  //insert our parent records
  if ($_POST["parentId"]) $parentId = $_POST["parentId"];
  else $parentId = array("0");

  $option = null;
  $option["conn"] = $conn;
  $option["name"] = $_POST["searchName"];
  $option["parentId"] = $parentId;
  $option["objectType"] = "savesearch";
  $option["objectOwner"] = USER_ID;

  //insert the collection
  if ($objectId = createObject($option)) $successMessage = _CREATE_SUCCESS;
  else {
      $errorMessage = _CREATE_ERROR;
      if (defined("ERROR_MESSAGE")) $errorMessage .= "<br>".ERROR_MESSAGE;
  }
            
  //create a bookmark
  if ($_POST["bookmark"] == "yes") {

    $opt = null;
    $opt["object_id"] = $objectId;
    $opt["account_id"] = USER_ID;
    $opt["name"] = $_POST["searchName"];
    dbInsertQuery($conn,"dm_bookmark",$opt);

  }
}

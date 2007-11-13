<?

$pageAction = $_POST["pageAction"];
$objectId = $_SESSION["objectId"];

if ($_SESSION["objectId"]==NULL) {
  $errorMessage = "No object is specified";
  return false;
}
    
if ($_POST["update"]) {

  $opt = null;
  $opt["status_date"] = date("Y-m-d H:i:s");
  $opt["status_owner"] = USER_ID;
  $opt["where"] = "id='$objectId'";
 
  if (dbUpdateQuery($conn,"dm_object",$opt)) {

    $opt = null;
    $opt["date1"] = $_POST["date1"];
    $opt["date2"] = $_POST["date2"];
    $opt["search_string"] = $_POST["searchString"];
    if (is_array($_POST["showObjects"])) $opt["show_objects"] = implode(",",$_POST["showObjects"]);
    $opt["search_option"] = @implode("|",$_POST["search_option"]);
    $opt["date_option"] = $_POST["date_option"];
    $opt["mod_option"] = $_POST["mod_option"];
    $opt["object_id"] = $objectId;
    $opt["meta_option"] = $_POST["metaOption"];
    $opt["col_filter"] = $_POST["colFilter"];
    $opt["col_filter_id"] = $_POST["colFilterId"];
    $opt["account_filter"] = $_POST["accountFilter"];
    $opt["account_filter_id"] = $_POST["accountFilterId"];
    $opt["search_type"] = $_POST["searchType"];
    $opt["where"] = "object_id='$objectId'";

    dbUpdateQuery($conn,"dm_savesearch",$opt);
    
    $successMessage = _UPDATE_SUCCESS;

  }
  else $errorMessage = _UPDATE_ERROR;

}


//get our latest info for our form
$sql = "SELECT dm_savesearch.*,(SELECT name FROM dm_object WHERE id=object_id) AS name FROM dm_savesearch WHERE object_id='$objectId'";
$curInfo = single_result($conn,$sql);


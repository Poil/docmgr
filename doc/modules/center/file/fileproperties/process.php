<?

$pageAction = $_POST["pageAction"];
$objectId = $_SESSION["objectId"];

if ($_SESSION["objectId"]==NULL) {
  $errorMessage = "No object is specified";
  return false;
}
                
if ($pageAction=="update") {

	$opt = null;
	$opt["conn"] = $conn;
	$opt["objectId"] = $objectId;
	$opt["objectType"] = "file";
	$opt["name"] = $_POST["fileName"];

	//only add if it's not "No summary available"
	if ($_POST["fileSummary"]!=_NO_SUMMARY_AVAIL) $opt["summary"] = $_POST["fileSummary"];

	if (!updateObject($opt)) {
		$errorMessage = _UPDATE_ERROR;
		if (defined("ERROR_MESSAGE")) $errorMessage .= "<br>".ERROR_MESSAGE;	
	}
	else {
	
		//update the keywords
		//log the key field data if available
		$keyArr = returnKeywords();
		$num = count($keyArr);
		
		if (is_array($keyArr) && $num > 0) {

			//clear any existing entries
			$sql = "DELETE FROM dm_keyword WHERE object_id='$objectId';";
			db_query($conn,$sql);

			$option = null;
			$option["object_id"] = $objectId;

			foreach ($keyArr AS $keyword) {

				$name = $keyword["name"];
				$option[$name] = $_POST[$name];

			}

			//insert the data
			dbInsertQuery($conn,"dm_keyword",$option);

		}

		$successMessage = _UPDATE_SUCCESS;

 	 }

}
else if ($pageAction=="clearCheckout") {

	$sql = "UPDATE dm_object SET status='0' WHERE id='$objectId';";
	if (db_query($conn,$sql)) $successMessage = "Status cleared successfully";
	else $errorMessage = "Status not cleared";

}


//get latest info for this object
$sql = "SELECT * FROM dm_object WHERE id='$objectId';";
$fileInfo = single_result($conn,$sql);

$file_name = $fileInfo["name"];
$version = $fileInfo["version"];
$creation_date=date_time_view($fileInfo["create_date"],$altDateFormat);
$last_modify_date=date_time_view($fileInfo["status_date"],$altDateFormat);

if ($fileInfo["summary"]) $file_summary = stripslashes($fileInfo["summary"]);
else $file_summary=_NO_SUMMARY_AVAIL;

$info = returnAccountInfo($conn,$fileInfo["object_owner"],null);
$file_owner = $info["login"];


if ($fileInfo["status"]==1) {
	$status_num=1;
	$check_status=_CHECKED_OUT;
	$check_date=date_time_view($fileInfo["status_date"],$altDateFormat);
	$font_color="red";
		
	$info = returnAccountInfo($conn,$fileInfo["status_owner"],null);
	$check_user = $info["login"];

}
else {
	$status_num=0;
	$check_status=_AVAIL_EDIT;
	$check_user=null;
	$check_date=null;
	$font_color="green";
}


$sql = "SELECT * FROM dm_file_history WHERE object_id='$objectId' AND version='$version'";
$info = single_result($conn,$sql);

$file_id=$info["id"];
$file_size=$info["size"];
$file_notes = $info["notes"];
if ($info["custom_version"]) $customVersion = $info["custom_version"];
else $customVersion = "Not Set";

$info = returnAccountInfo($conn,$info["object_owner"],null);
$last_modify_user = $info["login"];


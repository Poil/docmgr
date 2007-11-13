<?

//if we are passing from a url, set our limits
if ($_REQUEST["object_id"]) {
  $objectId = $_REQUEST["object_id"];
  $hideHeader = 1;
  $accountFilter = "share";
}

if ($objectId==NULL) {
	$errorMessage = "No object is specified";
	return false;
}
                

<?

$objectId = $_SESSION["objectId"];

if ($objectId==NULL) {
	$errorMessage = "No object is specified";
	return false;
}
                

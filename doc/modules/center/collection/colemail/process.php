<?

$hideHeader = 1;

$objectId = $_REQUEST["objectId"];
$pageAction = $_POST["pageAction"];
$module = $_REQUEST["module"];

if ($pageAction=="send") {

	$emailTo = $_REQUEST["emailTo"];
	$emailFrom = $_REQUEST["emailFrom"];
	$emailComments = stripsan($_REQUEST["emailComments"]);

	if ($emailTo && $emailFrom) {

		$sql = "SELECT * FROM dm_object WHERE id='$objectId'";
		$info = single_result($conn,$sql);

		//setup our anonymous link and insert into the database
		$subject = _EMAIL_COLLECTION." \"".$info["name"]."\"";

		$sendTo = array();

		//see if there are multiple recipients
		if (!strstr($emailTo,",")) $sendTo[] = $emailTo;
		else $sendTo = explode(",",$emailTo);

		//loop through our email recips and send them
		for ($i=0;$i<count($sendTo);$i++) { 

			$emailMessage = $emailComments."<br><br>";
			$url = SITE_URL."index.php?module=browse&view_parent=".$objectId;
			$emailMessage .= "<a href=\"".$url."\">".$url."</a>\n";

			if (send_email($sendTo[$i],$emailFrom,$subject,$emailMessage,null)) {
			
					$successMessage = _EMAIL_SUCCESS;

					//log the email
					logEvent($conn,OBJ_ANON_EMAILED,$objectId,$sendTo[$i]);

			} else {
				$errorMessage = _EMAIL_ERROR;
				break;
			}
				
		}
			

	} else $errorMessage = _EMAIL_ERROR;

}



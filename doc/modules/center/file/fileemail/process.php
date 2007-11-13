<?

$hideHeader = 1;

$objectId = $_REQUEST["objectId"];
$pageAction = $_POST["pageAction"];
$module = $_REQUEST["module"];

if ($pageAction=="send") {
	if (defined("REQUIRE_SYSTEM_EMAIL") && !$_POST["anonRecip"]){
		for ($i=0;$i<count($_POST["emaillSystemUsers"])-1;$i++){
		   if ( preg_match("/^\d+$/",$_POST["emaillSystemUsers"][$i])){
			$sql = "SELECT email FROM auth_accounts WHERE id=".$_POST["emaillSystemUsers"][$i];	
		 	$emailTo1 = single_result($conn,$sql);
			$emailTo .= $emailTo1["email"].",";
		   }
		}
		if ( preg_match("/^\d+$/",$_POST["emaillSystemUsers"][$i])){
			$sql = "SELECT email FROM auth_accounts WHERE id=".$_POST["emaillSystemUsers"][$i];	
		 	$emailTo1 = single_result($conn,$sql);
			$emailTo .= $emailTo1["email"];
		}
	}
	else {	
		$emailTo = $_REQUEST["emailTo"];
	}
	$accountInfo = returnAccountInfo($conn,USER_ID,null);
	$emailFrom = $accountInfo["email"];
	$emailComments = stripsan($_REQUEST["emailComments"]);

	$sql = "SELECT id,name,version,
			(SELECT id FROM dm_file_history WHERE dm_file_history.object_id = dm_object.id AND dm_file_history.version=dm_object.version) AS file_id
			FROM dm_object WHERE id='$objectId'";
	$info = single_result($conn,$sql);

	//create our file path
	$filePath = DATA_DIR."/".returnObjPath($conn,$objectId)."/".$info["file_id"].".docmgr";

	//assemble our attachment array	
	$attach[0]["name"] = $info["name"];
	$attach[0]["path"] = $filePath;
	$attach[0]["description"] = stripslashes($info["summary"]);
	
	//verify the md5sum for the file, log the results
	if (!fileChecksum($conn,$info["file_id"],$filePath)) {
		$errorMessage = _INVALID_MD5SUM_WARNING;
		logEvent($conn,OBJ_CHECKSUM_VERIFY_FAIL,$objectId);
		return false;
	} else {	
		logEvent($conn,OBJ_CHECKSUM_VERIFY_PASS,$objectId);
	}

	//scan the file and log the results before the view
	if (defined("CLAMAV_SUPPORT")) {

		$str = clamAvScan($filePath);

		if ($str===FALSE) logEvent($conn,OBJ_VIRUS_ERROR,$objectId);   	//scanning error, continue
		elseif ($str=="clean") logEvent($conn,OBJ_VIRUS_PASS,$objectId);       //file clean, continue
		else {
			logEvent($conn,OBJ_VIRUS_FAIL,$objectId,$str); 		//virus found, stop and alert
			$errorMessage = $str;
			return false;
		}

	}

	//add a checksum file to the attachment
	if (defined("SEND_MD5_CHECKSUM")) {
		$md5file = createChecksum($conn,$info["file_id"],$attach[0]["name"]);

		$attach[1]["name"] = "checksum.md5";
		$attach[1]["path"] = $md5file;

	}
  
	if ($emailTo && $emailFrom) {

		//setup our anonymous link and insert into the database
		if ($_POST["anonRecip"]) {

			$subject = _EMAIL_FILE." \"".$info["name"]."\"";

			$sendTo = array();

			//see if there are multiple recipients
			if (!strstr($emailTo,",")) $sendTo[] = $emailTo;
			else $sendTo = explode(",",$emailTo);

			//loop through our email recips and send them
			for ($i=0;$i<count($sendTo);$i++) { 

				$emailMessage = $emailComments."<br><br>";

				$pin = rand(100000,999999);
				$time = time();
				$encLink = sha1($time);
			
				//convert our linktime to seconds and add to current			
				$timefactor=0;
				switch ($_POST["linkTimeType"]){
				case "minutes":
					$timefactor=60;
					break;
				case "hours":
					$timefactor=3600;
					break;
				case "days":
					$timefactor=86400;
					break;
				case "weeks":
					$timefactor=604800;
					break;
				case "months":
					$timefactor=2629743;
					break;
				}
			       
				if ( preg_match("/^[0-9]{1,4}$/" , $_POST["linkTime"]) && ($timefactor > 0)){
					$secs = ($_POST["linkTime"] * $timefactor) + $time;
					$dateExpires = date("Y-m-d H:i:s",$secs);
				} else {
					$errorMessage = _EMAIL_ERROR;
					break;
				}

				if ($_POST["notify"]) $notify = $_POST["notify"];
				else $notify = "none";
			
				//insert our link auth info into the database			
				$opt = null;
				$opt["object_id"] = $objectId;
				$opt["pin"] = $pin;		
				$opt["link_encoded"] = $encLink;
				$opt["date_expires"] = $dateExpires;
				$opt["account_id"] = USER_ID;
				$opt["notify"] = $notify;
				$opt["dest_email"] = $sendTo[$i];
			
				dbInsertQuery($conn,"dm_email_anon",$opt);
		
				$url = SITE_URL."index.php?module=anonaccess&auth=".$encLink;
	
				//add the link to the file and the pin number to the email
				$emailMessage .= "You may access \"".$info["name"]."\" with the following link:<br><br>";
				$emailMessage .= "<a href=\"".$url."\">".$url."</a>";
				$emailMessage .= "<br><br>Your pin number is $pin<br><br>";
				$emailMessage .= "This link expires ".str_replace("&nbsp;"," ",dateView($dateExpires))."<br><br>";
				
				if (send_email($sendTo[$i],$emailFrom,$subject,$emailMessage,null)) {
			
						$successMessage = _EMAIL_SUCCESS;

						//log the email
						logEvent($conn,OBJ_ANON_EMAILED,$objectId,$sendTo[$i]);

				} else {
					$errorMessage = _EMAIL_ERROR;
					break;
				}
				
			}
			

		} else {
		
			$subject = _EMAIL_FILE." \"".$info["name"]."\"";
			$emailMessage = $emailComments."<br><br>";

			if (send_email($emailTo,$emailFrom,$subject,$emailMessage,$attach)) {
			
				$successMessage = _EMAIL_SUCCESS;

				//log the email
				logEvent($conn,OBJ_EMAILED,$objectId,$emailTo);

			} else $errorMessage = _EMAIL_ERROR;

		}
		
	} else $errorMessage = _EMAIL_ERROR;

}


?>

<?php


function viewFile($conn,$objId,$anonInfo) {

	$sql = "SELECT name,version FROM dm_object WHERE id='$objId'";
	$objInfo = single_result($conn,$sql);
	$realname=$objInfo["name"];
	$version=$objInfo["version"];

	$sql = "SELECT id FROM dm_file_history WHERE object_id='$objId' AND version='$version'";
	$info = single_result($conn,$sql);
	$file_id = $info["id"];
	$file_action = "view";
	
	$info = returnAccountInfo($conn,$anonInfo["account_id"],null);
	
	//we have to include path
	if ($anonInfo["notify"]=="email") $email = $info["email"];
	else $email = null;

	//notify the sender the file was viewed	
	if ($email) {
		$emailTo = $email;
		$emailFrom = ADMIN_EMAIL;
		$subject = _FILE_VIEW_NOTIFY;
		$emailMessage = $realname." "._VIEWED_BY." ".$anonInfo["dest_email"]." "._VIEWED_DATE." ".date("D dS M, Y h:i a")." "._VIEWED_BY_IP." ".$_SERVER["REMOTE_ADDR"];
		send_email($emailTo,$emailFrom,$subject,$emailMessage,null);
	}

	$current_date=date("Y-m-d H:i:s");

	// get the filename
	$filename = DATA_DIR."/".returnObjPath($conn,$objId)."/".$file_id.".docmgr";

	//verify the md5sum for the file, log the results
	if (!fileChecksum($conn,$file_id,$filename)) {
		$errorMessage = _INVALID_MD5SUM_WARNING;
		logEvent($conn,OBJ_CHECKSUM_VERIFY_FAIL,$objId);
		return false;
	} else {
		logEvent($conn,OBJ_CHECKSUM_VERIFY_PASS,$objId);
	}

	//scan the file and log the results before the view
	if (defined("CLAMAV_SUPPORT")) {

		$str = clamAvScan($filename);

		if ($str===FALSE) logEvent($conn,OBJ_VIRUS_ERROR,$objId);   //scanning error, continue
		elseif ($str=="clean") logEvent($conn,OBJ_VIRUS_PASS,$objId);       //file clean, continue
		else {
			logEvent($conn,OBJ_VIRUS_FAIL,$objId,$str); //virus found, stop and alert
			$errorMessage = $str;
			return false;
		}

	}

	//log the view with the extra information
	logEvent($conn,OBJ_ANON_VIEWED,$objId,$anonInfo["dest_email"],$anonInfo["account_id"]);

	// send headers to browser to initiate file download
	header ("Content-Type: application/octet-stream");
	header ("Content-Type: application/force-download");
	header ("Content-Disposition: attachment; filename=\"$realname\"");
	header ("Content-Transfer-Encoding:binary");
	header ("Cache-Control: must-revalidate, post-check=0, pre-check=0");
	header ("Pragma: public");

	readfile_chunked($filename);

	die; 

}


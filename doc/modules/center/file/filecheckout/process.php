<?
/************************************************************************************************
	view.php

	Updated:
		01-26-2002:  Removed the header.inc.php include on line 15 which caused a file
		corruption when downloading text or pdf files
	Updated:
		03-27-2002:  Viewing files fixed.  The view now works without loading another 
		page as well.  

*************************************************************************************************/

$objectId = $_REQUEST["objectId"];

if ($objectId) {

	$sql = "SELECT name,version FROM dm_object WHERE id='$objectId'";
	$objInfo = single_result($conn,$sql);
	$realname=$objInfo["name"];
	$version=$objInfo["version"];

	$sql = "SELECT id FROM dm_file_history WHERE object_id='$objectId' AND version='$version'";
	$info = single_result($conn,$sql);
	$file_id = $info["id"];
	$file_action = "view";

	//we have to include path

	$current_date=date("Y-m-d H:i:s");

	// get the filename
	$filename = DATA_DIR."/".returnObjPath($conn,$objectId)."/".$file_id.".docmgr";

        //verify the md5sum for the file, log the results
        if (!fileChecksum($conn,$file_id,$filename)) {
                $errorMessage = _INVALID_MD5SUM_WARNING;
                logEvent($conn,OBJ_CHECKSUM_VERIFY_FAIL,$objectId);
                return false;
        } else {
                logEvent($conn,OBJ_CHECKSUM_VERIFY_PASS,$objectId);
        }

        //scan the file and log the results before the view
        if (defined("CLAMAV_SUPPORT")) {

                $str = clamAvScan($filename);

                if ($str===FALSE) logEvent($conn,OBJ_VIRUS_ERROR,$objectId);           //scanning error, continue
                elseif ($str=="clean") logEvent($conn,OBJ_VIRUS_PASS,$objectId);       //file clean, continue
                else {
                        logEvent($conn,OBJ_VIRUS_FAIL,$objectId,$str);                 //virus found, stop and alert
                        $errorMessage = $str;
                        return false;
                }

        }
                                                        
  	//log the checkout
	logEvent($conn,OBJ_CHECKED_OUT,$objectId);
                                                      
	//force a download
	// send headers to browser to initiate file download
	header ("Content-Type: application/octet-stream");
	header ("Content-Type: application/force-download");
	header ("Content-Length: ".filesize($filename));
	header ("Content-Disposition: attachment; filename=\"$realname\"");

	header ("Content-Transfer-Encoding:binary");
	header ("Cache-Control: must-revalidate, post-check=0, pre-check=0");
	header ("Pragma: public");

	readfile_chunked($filename);

	//update the db to show the file was checked out, and by whom
	$sql = "UPDATE dm_object SET status='1',status_owner='".USER_ID."',status_date='$current_date' WHERE id='$objectId'";
	db_query($conn,$sql);

	//send a subscription alert
	sendEventNotify($conn,$objectId,"OBJ_CHECKOUT_ALERT");

}

die;

?>



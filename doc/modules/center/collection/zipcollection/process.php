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

	//create the archive
	$arc = zipCollection($conn,$objectId);
	
	//get out if there was an error
	if (!$arc) {
		echo "<div style=\"color:red;font-weight:bold\">The archive could not be created.</div>\n";
		die;
	}

	//archive name
	$arr = explode("/",$arc);
	$name = array_pop($arr);

	// send headers to browser to initiate file download
	header ("Content-Type: application/zip");
	header ("Content-Type: application/force-download");
	header ("Content-Length: ".filesize("$arc"));
	header ("Content-Disposition: attachment; filename=\"$name\"");
	header ("Content-Transfer-Encoding:binary");
	header ("Cache-Control: must-revalidate, post-check=0, pre-check=0");
	header ("Pragma: public");

	readfile_chunked("$arc");

}

die;


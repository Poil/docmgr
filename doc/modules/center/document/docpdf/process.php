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
	$realname=$objInfo["name"].".pdf";
	$version=$objInfo["version"];

	$sql = "SELECT id FROM dm_document WHERE object_id='$objectId' AND version='$version'";
	$info = single_result($conn,$sql);
	$documentId = $info["id"];
	$file_action = "view";
	
	//we have to include path
	$current_date=date("Y-m-d H:i:s");

	// get the filename
	$filename = FILE_DIR."/document/".returnObjPath($conn,$objectId)."/".$documentId.".docmgr";

	//get the contents and write to a temp file
	$xhtml = document2pdf($conn,file_get_contents($filename));
	$pdffile = TMP_DIR."/".USER_LOGIN."-".time().".pdf";

	//if there are no contents, then stop here
	if (strlen($xhtml)==0) die(_DOC_EMPTY_PDF_ERROR);

	//call the dompdf library and convert our xhtml to a file
	require_once("modules/center/document/docpdf/dompdf/dompdf_config.inc.php");
        $dompdf = new DOMPDF();
        $dompdf->load_html($xhtml);
        $dompdf->render();
        file_put_contents($pdffile,$dompdf->output());

	//log the view
	logEvent($conn,OBJ_VIEWED,$objectId);

	// send headers to browser to initiate file download
	header ("Content-Type: application/pdf");
	header ("Content-Type: application/force-download");
	header ("Content-Length: ".filesize($pdffile));
	header ("Content-Disposition: attachment; filename=\"$realname\"");
	
	header ("Content-Transfer-Encoding:binary");
	header ("Cache-Control: must-revalidate, post-check=0, pre-check=0");
	header ("Pragma: public");

	readfile_chunked($pdffile);

}

die;

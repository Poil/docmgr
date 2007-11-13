<?

function genBinaryAvail($arr,$key) {

	if ($arr[$key]==1) return "<li class=\"successMessage\">\"".$key."\" "._BINARY_FOUND."</li>";
	else return "<li class=\"errorMessage\">\"".$key."\" "._BINARY_NOTFOUND." ".$_SERVER["PATH"]."</li>";

}

function pdfSupport() {

	//figure out which of our external progs exist
	$arr = getExternalApps();

	if (defined("PDF_SUPPORT")) $supportStatus = _ENABLED;
	elseif (defined("DISABLE_PDF")) $supportStatus = _DISABLED_IN_CONFIG;
	else $supportStatus = _DISABLED;

	//show our pdf status
	$string = "<div class=\"formHeader\">"._SUPPORT_STATUS."</div>";
	$string .= $supportStatus;
	$string .= "<br><br>";
	
	//show our details for pdf support
	if (defined("PDF_SUPPORT")) { 	

		$string .= "<div class=\"formHeader\">"._SUPPORT_DETAILS."</div>";
		if (defined("XPDF_SUPPORT")) $string .= "XPDF "._SUPPORT_ENABLED;
		$string .= "<br>";

		if (defined("DISABLE_ENCAP_OCR"))	$string .= "Encapsulated PDF "._DISABLED_IN_CONFIG."\n";
		else if (defined("PDF_SUPPORT") && defined("OCR_SUPPORT")) $string .= "Encapsulated PDF "._ENABLED."\n";
		else $string .= "Encapsulated PDF "._SUPPORT_DISABLED."\n";		
		$string .= "<br>";

		if (defined("THUMB_SUPPORT")) $string .= "PDF Thumbnail "._SUPPORT_ENABLED."";
		else $string .= "PDF Thumbnail "._SUPPORT_DISABLED."";
		$string .= "<br><br>";

	}
	//otherwise, just show what binaries we found
	$string .= "<div class=\"formHeader\">PDF "._RELATED_BINARIES."</div>";
	$string .= genBinaryAvail($arr,"pdftotext");
	$string .= genBinaryAvail($arr,"pdfimages");
	$string .= genBinaryAvail($arr,"pdftoppm");
	$string .= genBinaryAvail($arr,"ocr");
	$string .= genBinaryAvail($arr,"convert");
	$string .= genBinaryAvail($arr,"mogrify");
	$string .= genBinaryAvail($arr,"montage");

	return $string;

}


function imageSupport() {

	//figure out which of our external progs exist
	$arr = getExternalApps();

	//ocr support
	if (defined("OCR_SUPPORT")) $ocrText = "OCR "._SUPPORT_ENABLED;
	elseif (defined("DISABLE_OCR")) $ocrText = "OCR "._DISABLED_IN_CONFIG;
	else $ocrText = "OCR "._SUPPORT_DISABLED;

	if (defined("THUMB_SUPPORT")) $thumbText = "Thumbnail "._SUPPORT_ENABLED;
	elseif (defined("DISABLE_THUMB")) $thumbText = "Thumbnail "._DISABLED_IN_CONFIG;
	else $thumbText = "THUMB "._SUPPORT_DISABLED;

	$string = "<div class=\"formHeader\">
		   Image "._SUPPORT_DETAILS."
		   </div>
		   ";

	if ($arr["imagemagick"] && defined("OCR_SUPPORT")) 
		$string .= "Basic Image OCR "._SUPPORT_ENABLED."\n";
	else 
		$string .= "Basic Image OCR "._SUPPORT_DISABLED."\n";

	$string .= "<br>";

	if (defined("THUMB_SUPPORT") && $arr["imagemagick"]) $string .= "Basic Image Thumbnail "._SUPPORT_ENABLED."\n";
	else $string .= "Basic Image Thumbnail "._SUPPORT_DISABLED."\n";

	$string .= "<br><br>";

	$string .= "<div class=\"formHeader\">
		   TIFF "._SUPPORT_DETAILS."
		   </div>
		   ";

	if ($arr["libtiff"] && defined("OCR_SUPPORT")) 
		$string .= "TIFF Image OCR "._SUPPORT_ENABLED."\n";
	else 
		$string .= "TIFF Image OCR "._SUPPORT_DISABLED."\n";
	$string .= "<br>";

	if (defined("THUMB_SUPPORT") && $arr["libtiff"]) $string .= "TIFF Image Thumbnail "._SUPPORT_ENABLED."\n";
	else $string .= "TIFF Image Thumbnail "._SUPPORT_DISABLED."\n";
	$string .= "<br><br>";

	//otherwise, just show what binaries we found
	$string .= "<div class=\"formHeader\">Image and TIFF "._RELATED_BINARIES."</div>";
	$string .= genBinaryAvail($arr,"ocr");
	$string .= genBinaryAvail($arr,"convert");
	$string .= genBinaryAvail($arr,"mogrify");
	$string .= genBinaryAvail($arr,"montage");
	$string .= genBinaryAvail($arr,"tiffsplit");
	$string .= genBinaryAvail($arr,"tiffinfo");

	return $string;

}

function miscSupport() {

	$arr = getExternalApps();

	$string = "<div class=\"formHeader\">
			Email "._SUPPORT_DETAILS."
			</div>
			";
	
	if (defined("DISABLE_EMAIL")) $string .= "Email "._DISABLE_IN_CONFIG."\n";
	elseif (!$arr["email"]) $string .= "Email "._SUPPORT_NOT_COMPILED."\n";
	else {
		$string .= "Email "._SUPPORT_ENABLED."\n";
		if (defined("PHP_IMAP_SUPPORT")) $string .= _WITH." PHP_IMAP";
		else $string .= _WITH." Sendmail";
	}

	$string .= "<br><br>";

	$string .= "<div class=\"formHeader\">ClamAV "._SUPPORT."</div>\n";
	if (defined("CLAMAV_SUPPORT")) $string .= "Clam Antivirus "._SUPPORT_ENABLED."\n";
	else $string .= "Clam AntiVirus "._SUPPORT_DISABLED."\n";
	$string .= "<br><br>";

	$string .= "<div class=\"formHeader\">Extended Extension Checking "._SUPPORT."</div>\n";
	if (defined("FILE_SUPPORT")) $string .= "File Extension "._SUPPORT_ENABLED."\n";
	else $string .= "File Extension "._SUPPORT_DISABLED."\n";
	$string .= "<br><br>";

	$string .= "<div class=\"formHeader\">Misc Thumbnail "._SUPPORT."</div>\n";
	if (defined("ENSCRIPT_SUPPORT") && defined("THUMB_SUPPORT")) $string .= "Text Thumbnail "._SUPPORT_ENABLED."\n";
	else $string .= "Text Thumbnail "._SUPPORT_DISABLED."\n";
	$string .= "<br>";
	if (defined("DOC_SUPPORT") && defined("THUMB_SUPPORT")) $string .= "MS Word Thumbnail "._SUPPORT_ENABLED."\n";
	else $string .= "MS Word Thumbnail "._SUPPORT_DISABLED."\n";
	$string .= "<br><br>";

	$string .= "<div class=\"formHeader\">Web "._SUPPORT."</div>\n";
	if (defined("URL_SUPPORT")) $string .= "URL Download "._SUPPORT_ENABLED."\n";
	else $string .= "URL Download "._SUPPORT_DISABLED."\n";
	$string .= "<br><br>";

	$string .= "<div class=\"formHeader\">Zip Collection "._SUPPORT."</div>\n";
	if (defined("ZIP_SUPPORT")) $string .= "Zip Collection "._SUPPORT_ENABLED."\n";
	else $string .= "Zip Collection "._SUPPORT_DISABLED."\n";
	$string .= "<br><br>";

	$string .= "<div class=\"formHeader\">Miscellaneous "._RELATED_BINARIES."</div>\n";
	$string .= genBinaryAvail($arr,"convert");
	$string .= genBinaryAvail($arr,"mogrify");
	$string .= genBinaryAvail($arr,"montage");
	$string .= genBinaryAvail($arr,"enscript");
	$string .= genBinaryAvail($arr,"antiword");
	$string .= genBinaryAvail($arr,"clamav");
	$string .= genBinaryAvail($arr,"file");
	$string .= genBinaryAvail($arr,"sendmail");
	$string .= genBinaryAvail($arr,"wget");
	$string .= genBinaryAvail($arr,"zip");
	$string .= genBinaryAvail($arr,"unzip");

	return $string;

}


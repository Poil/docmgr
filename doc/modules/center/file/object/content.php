<?

class fileContent  {

	/******************************************************************
		Our content extraction functions
	******************************************************************/
	
	function getImageContent ($filepath,$ext) {
	
		$multiOCR = null;
		$rs = null;

		if (!defined("OCR_SUPPORT")) return false;
		if (defined("DISABLE_OCR")) return false;
	
		//check to see if this is a tiff image with multiple pages
	     	if ($ext=="tiff") {

			//find out if multipage, use tiffinfo
			$app = APP_TIFFINFO;
			$numpages = `$app "$filepath"`;
			$numpages=substr_count($numpages,"TIFF Directory");
	
			$dir = substr($filepath,0,strrpos($filepath,"/") + 1);
	
			//if there is more than one tiff image, use the proccessing below
			if ($numpages>1) {
	
				$multiOCR = 1;
	
				//we will ocr each page seperately, then form one string
				//and index that string
	
				//split the file and return the names
				$filePrefix = fileContent::tiffSplit($numpages,"$filepath");
	
				$tempFile = createTempFile();
				$dirPrefix = $dir.$filePrefix;
				
				//get all files with this file prefix in the directory
				$tiffArray=listDirectory($dir,array(".tif"),$filePrefix);
				$pnmArray = array();
	
				$timeout = EXECUTION_TIME * $numpages;
				ini_set("max_execution_time","$timeout");    //putting this here is an experiment
	
				system(APP_MOGRIFY." -format pnm ".$dirPrefix."*.tif");
	
				if (defined("MAX_OCR")) $count = MAX_OCR;
				else $count="1";
				
				for ($row=0;$row<count($tiffArray);$row++) {
				
					$pnmArray[$row] = $dir.str_replace(".tif",".pnm",$tiffArray[$row]);
				
				}
				
				//run the ocr program
				$string1 = fileContent::advOcr($count,$pnmArray);
	
				//clean up the string
				$newstring = string_clean($string1,null,null);
	
				//delete our temp files
				for ($row=0;$row<count($pnmArray);$row++) {
	
					$tiffFile = $dir.$tiffArray[$row];
					//delete the temp file
					@unlink("$tiffFile");
					@unlink("$pnmArray[$row]");
	
				}
	
			}
	
		}
	
		//use this if a single image, or a single page tiff
		if (!$multiOCR) {
	
			/* OCR section, the file is converted from orig type to pnm, and
			   then scanned with the ocr software.  The only file type
		 	   limitations are any pic files not supported by imagemagick */
	
			$filepath1 = TMP_DIR."/".rand().".pnm";
	
			//convert to grayscale, pnm format
			system(APP_CONVERT." ".$rs." \"".$filepath."\" \"".$filepath1."\" 2>&1");
	
			if (defined("MAX_OCR")) $count = MAX_OCR;
			else $count="1";

			//ocr the image and return string as a variable
			$string1 = fileContent::advOcr($count,array($filepath1));
	
			//the order of the steps below may need to change, we will see
			$newstring = string_clean($string1,null,null);
	
			//delete the temp file
			@unlink("$filepath1");
	
		}
	
		return $newstring;
	
	}
	
	function getTiffContent($filepath) {

		return fileContent::getImageContent($filepath,"tiff");
	
	}
	
	function getTxtContent($filepath) {

		return file_get_contents($filepath);
	
	}
	
	function getMarkupContent($filepath) {

		$str = file_get_contents($filepath);
		return removeTags($str);	
	
	}
	
	function getOpenOfficeContent($filepath) {
	
		return fileContent::getOpenDocumentContent($filepath);
	
	}
	
	function getOpenDocumentContent($filepath) {

		//unzip our info to stdout	
		$cmd = APP_UNZIP." \"".$filepath."\" content.xml";
		$temp = `$cmd`;

		//get the current encoding for this file and convert it to that of our database
		$encoding = preg_replace("/^<\?xml [^>]*encoding=\"([^\"]*)\".*/is","\$1",$temp);

		//remove our tags first.  Running charConv causes all our content to disappear for some reason
		$temp = removeTags($temp);

		//convert to our proper encoding for storage and get out		
		return charConv($temp,$encoding,DB_CHARSET);

	}
	
	
	function getPDFContent($filepath) {
	
		//one of these has to be defined
		if (!defined("PDF_SUPPORT")) return false;
		if (defined("DISABLE_PDF")) return false;
		$newstring = fileContent::xpdfProcess($filepath);

		//clean it up
		return $newstring;
	
	}
	
	function getOtherContent($filepath) {
	
		return true;
		
	}
	
	function getDocContent($filepath) {
	
		//if we don't have advanced doc support, treat the file as text
		if (!defined("DOC_SUPPORT")) return fileContent::getTxtContent($filepath);
	
		//pass the doc file through antiword and retrieve the text
		$app = APP_MSWORD_TEXT;
		$str = `$app "$filepath"`;

		//clean the text and pass it back
		return $str;
	
	}
	
	//this way, we pull an text from the file and ocr any images
	function xpdfProcess($filepath) {

		//get any text that we can
		$pdftotext = APP_PDFTOTEXT;
		$newstring = `$pdftotext "$filepath" - 2>/dev/null`;

		//if we have disabled encapsulated pdf support, or have no ocr support, return here
		if (defined("DISABLE_ENCAP_OCR") || !defined("OCR_SUPPORT")) return $newstring;

		//extract all images to a directory with this prefix
		$dir = TMP_DIR."/".rand()."/";
		$prefix = $dir."files";
	
		mkdir($dir);

		//extract images from the pdf.  this will also handle encapsulated pdfs
		system(APP_PDFIMAGES." -q \"".$filepath."\" \"".$prefix."\" 2>/dev/null");	

		if (defined("MAX_OCR")) $count = MAX_OCR;
		else $count="1";
	
		$fileArray = listDirectory($dir,null,null);
	
		//append the directory to the filename
		for ($row=0;$row<count($fileArray);$row++) $fileArray[$row] = $dir.$fileArray[$row];
	
		//append the ocr'd content from the extracted images
		$newstring .= fileContent::advOcr($count,$fileArray);
	
		//delete our tmp images
		for ($row=0;$row<count($fileArray);$row++) unlink($fileArray[$row]);
	
		//remove the temp directory
		rmdir($dir);
	
		return $newstring;
		
	}
	
	
	function runOcrProg($count,$files,$tempFile) {
	
		//we use the directory the files are in as our prefix.  This directory
		//must be created originally via a random string or this won't work
		$done = array();
		$pidArr = array();
		$pidStore = array();
		$running = array();

		//reduce our file array so we know we don't have empty values
		$files = reduceArray($files);
	
		//just process normally if there is just one picture
		if (count($files)=="1" && $files[0]) {

			debugMsg(5,"OCRing single file ".$files[0]."\n");		
			$cmd = APP_OCR." \"".$files[0]."\"";	
			exec("$cmd 1> $tempFile 2>/dev/null");
			return true;
		
		}
	
		while (count($files) > count($done)) {
	
			for ($row=0;$row<count($pidArr);$row++) {
			
				$pid = $pidArr[$row];
	
				//this pid is still running
				if ($pid) {
	
					$var = "PID_".$pid;
					$key = array_search($pid,$pidStore);
					$file = $files[$key];
	
					if (isPidRunning($pid)) {
				
						$start = $_SESSION[$var];
						$cur = getmicrotime();
						$diff = $cur - $start;
	
						$diff = floatValue($diff,2);	
	
						if (defined("EXECUTION_TIME")) $maxTime = EXECUTION_TIME - 10;
						else $maxTime = "50";
						
						//we have gone over our process limit. Kill the process, blank the session, and continue;
						if ($diff>$maxTime) {
					
							system(APP_KILL." \"".$pid."\"");
							$_SESSION[$var] = null;
	
							$pidArr[$row] = null;
							$pidArr = reduceArray($pidArr);
							$done[] = $file;
					
						} else continue;
	
					} else {
	
						$_SESSION[$var] = null;
						$pidArr[$row] = null;
						$pidArr = reduceArray($pidArr);
						$done[] = $file;
	
					}
				} 
			
			}
	
			if (count($pidArr) < $count) {
			
				$diff = $count - count($pidArr);
			
				for ($row=0;$row<$diff;$row++) {
	
					//take the first file that hasn't been finished
					$arr = array_values(array_diff($files,$done,$running));
					$file = $arr[0];

					if (!$file) continue;
						
					debugMsg(5,"OCRing file in array ".$file."\n");		
					$cmd = APP_OCR." \"$file\"";	
	
					$running[] = $file;
					$pid = runProgInBack($cmd,$tempFile);					
				
					$var = "PID_".$pid;
	
					//store the time we started this in a session
					$_SESSION[$var] = getmicrotime();
	
					//get the key of this file in the orig array
					$key = array_search($file,$files);
					$pidStore[$key] = $pid;
					$pidArr[] = $pid;
	
				
				}		
	
			}
	
			//a hack to stay around until the last file is processed
			$done = array_values(array_unique($done));
		
			sleep(1);
	
		}
	
		return true;
	
	}
	
	function advOcr($count,$fileArray) {
	
		$tempFile = createTempFile();

		fileContent::runOcrProg($count,$fileArray,$tempFile,null);
	
		if (file_exists($tempFile)) {
			$string = file_get_contents($tempFile);
			unlink($tempFile);
		}
	
		return $string;
	
	}
	
	
	//this function splits a tif file, and returns the names
	//of the files it creates as an array
	function tiffSplit($numpages,$userfile) {
	
		//split the tiff file
		//figure out what the names of the temp files will be.
		$pos = strrpos($userfile,"/");
		$dir_value = substr($userfile,0,$pos)."/";
	
		$firstloop = floor($numpages/26);
		$firstremainder = $numpages%26;
	
		//generate our file prefixes
		$file_prefix_num = rand(1,10000);
		$prefix_value = $dir_value.$file_prefix_num;
	
		//split the file
		exec(APP_TIFFSPLIT." ".$userfile." ".$prefix_value." 2>&1");
	
		return $file_prefix_num;
	
	}
	
	
}
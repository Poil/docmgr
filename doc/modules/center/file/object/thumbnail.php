<?php
class fileThumbnail {

	private $thumb;		//path of the file we create
	private $filepath;	//path of the file we are thumbnailing
	private $mode;		//our mode, (thumb or preview)
	private $imageSize;	//the size of the image we'll create
	private $mime;		//mime type of the object
	
	//constructor	
	public function __construct($mode,$filepath,$thumb,$mime) {
		
		$this->filepath = $filepath;
		$this->thumb = $thumb;
		$this->mime = $mime;
		$this->setMode($mode);	

	}
	
	//change the current mode of this class (preview or thumbnail)	
	public function setMode($newMode) {

		$this->mode = $newMode;
		
		if ($newMode=="preview") $this->imageSize = "300x300";
		else $this->imageSize = "100x100";
				
	}
	
	private function getImageDimensions($file = null) {

		//default to the passed file if not set	
		if (!$file) $file = $this->filepath;
		$arr = @getImageSize($file);
	
		if (!$arr[1]) return;

		$ratio = floatValue($arr[0] / $arr[1],"2");

		//get our size for the end ratio
		$finalArr = explode("x",$this->imageSize);
		$fw = $finalArr[0];
		$fh = $finalArr[1];

		//if the width and height is smaller, just return the image height
		if ($arr[0] <= $fw && $arr[1] <= $fh) {
			$dim = $arr[0]."x".$arr[1];
		}
		else if ($arr[0] <= $fw && $arr[1] > $fh) {
			$dim = intValue($fw/$ratio)."x".$fh;
		}
		else if ($arr[0] > $fw && $arr[1] <= $fh) {
			$dim = $fw."x".intValue($fh/$ratio);
		} 
		else {
			$dim = $this->imageSize;
		}

		return $dim;
	
	}
	
	public function createImageThumb() {

		$dim = $this->getImageDimensions();

		if (!$dim) return;

		//do not alter the image if it's too small, or shrink if it's too long
		if ($this->mode=="thumb") {
			system(APP_MONTAGE." -size ".$dim." -gravity center \"".$this->filepath."\" \"".$this->thumb."\"");
		} else {
			system(APP_CONVERT." -size \"".$dim."\" -thumbnail \"".$dim."\" \"".$this->filepath."\" \"".$this->thumb."\"");
		}
		
	}
	
	public function createTiffThumb() {

		//do we support tiff indexing
		if (!defined("TIFF_SUPPORT")) return false;
	
		//find out if multipage, use tiffinfo
		$app = APP_TIFFINFO;
		$numpages=`$app "$this->filepath"`;
		$numpages=substr_count($numpages,"TIFF Directory");

		$dim = $this->getImageDimensions();

		if (!$dim) return;
	
		//only one page, just resize it and be on our way
		if ($numpages=="1") {
		
			system(APP_CONVERT ." -resize ".$dim." \"".$this->filepath."\" \"".$this->thumb."\"");
		
		} else {
	
			$dir = TMP_DIR."/".rand();
			$tifprefix = $dir."/tiff";
	
			mkdir($dir);
			system(APP_TIFFSPLIT." \"".$this->filepath."\" $tifprefix");
			
			$fileArr = listDirectory($dir,null,null);
	
			$file = $dir."/".$fileArr[0];	
	
			system(APP_CONVERT." -resize ".$dim." \"".$file."\" \"".$this->thumb."\"");
					
			//todo: replace this with php function	
			if (is_dir($dir)) `rm -r $dir`;
	
		}
	
	}
	
	public function createPdfThumb() {
	
		//create a ppm of the first page with xpdf
		if (!defined("PDF_SUPPORT")) return false;
	
		$prefix = TMP_DIR."/".USER_ID;
		$firstPage = $prefix."-000001.ppm";
			
		system(APP_PDFTOPPM." -f 1 -l 1 \"".$this->filepath."\" ".$prefix);
	
		system(APP_CONVERT." -resize ".$this->imageSize." \"".$firstPage."\" \"".$this->thumb."\"");
	
		@unlink($firstPage);
					                    
	}

	public function createOpenDocumentThumb() {

		//make sure we have unzip functionality
		if (!defined("UNZIP_SUPPORT")) return false;

		$app = APP_UNZIP;
		$temp = `$app "$this->filepath" "Thumbnails/thumbnail.png"`;
		$fp = fopen($this->thumb,"wb");
		fwrite($fp,$temp);
		fclose($fp);

	       	system(APP_MOGRIFY." -size ".$this->imageSize." -resize ".$this->imageSize." \"".$this->thumb."\"");

	}

	public function createOpenOfficeThumb() {
	
		fileThumbnail::createOpenDocumentThumb();
		
	}                                                                                	

	
	public function createTxtThumb() {

		$tempfile = createTempFile("ps");

		if ($this->mime=="text/plain") {

			if (!defined("ENSCRIPT_SUPPORT")) return false;

			system(APP_ENSCRIPT." --pages=1 --word-wrap --output=".$tempfile." \"".$this->filepath."\"");
	
			$app = APP_IDENTIFY;
	        	$str = `$app -verbose "$tempfile" | grep Geometry`;
	        	$str = eregi_replace("[^0-9x]","",$str);
	        	$arr = explode("x",$str);

	        	if (!$arr[1]) return;
	
	        	$newwidth = intValue(($arr[0] / $arr[1]) * 300);
	        	$newsize = $newwidth."x300";

	        	system(APP_CONVERT." -size ".$newsize." -resize ".$newsize." \"".$tempfile."\" \"".$this->thumb."\"");
	
			@unlink($tempfile);
			
		}
	
	}
	
	public function createDocThumb() {
	
		//setup our temp ps file and our temp directory for extraction
		$tempfile = createTempFile("ps");
	
		if (!defined("DOC_SUPPORT")) return false;

		system(APP_MSWORD_THUMB." \"".$this->filepath."\" > \"".$tempfile."\"");
	
		//make sure the file was created properly
		if (!is_file($tempfile)) return false;
		if (filesize($tempfile)==0) return false;
	
		//shrink the ps document down to one page
		$str = file_get_contents($tempfile);
		$pos = strpos($str,"%%Page: 2");
	
		//there's more than one page, shrink it down to one
		if ($pos!==FALSE) {
	
			$str = substr($str,0,$pos);
			$str .= "%%Trailer\n%%Pages: 1\n%%EOF\n";
		
			$fp = fopen($tempfile,"w");
			fwrite($fp,$str);
			fclose($fp);
		
		}
	
	       	system(APP_CONVERT." -size 77x100 -resize 77x100 \"".$tempfile."\" \"".$this->thumb."\"");
		@unlink($tempfile);
	
	}
	
}
<?
/*****************************************************************************************
  Fileame: app-config.php

  Purpose: Contains all settings external applications called by docmgr
           This is part of an effort to abstract out all program calls.  DocMGR should
           expect only a certain kind of response from the calling program.  The used program
           should contain the appropriate cli options to pass the desired output.
  Created: 05-07-2006

******************************************************************************************/

/************************************************************************
  paths and command line options for our apps
************************************************************************/

/***********************************************************************
  The apps in this section should be able to be replaced with another
  app as long as the output is the same
***********************************************************************/

//our ocr program.  All ocred content should be output to stdout
define("APP_OCR","gocr -f UTF8");
//define("APP_OCR","ocrad --format=utf8");

//wget for url objects.  it should output everything to a file, taking
//<progname> -outputOpt <filename> <url-to-get>
define("APP_WGET","wget -O");

//ms word documents
//this one must output postscript to stdout
define("APP_MSWORD_THUMB","antiword -p letter ");
//this one must output text to stdout
define("APP_MSWORD_TEXT","antiword -t -m UTF-8.txt ");

/************************************************************************
  The apps in this section are hard-coded for now
************************************************************************/

//zip for collections
define("APP_ZIP","zip -r -q");

//xpdf
define("APP_PDFTOTEXT","pdftotext -nopgbrk -q");	//dont' remove the -nopgbrk option
define("APP_PDFIMAGES","pdfimages -q");
define("APP_PDFTOPPM","pdftoppm");

define("APP_SENDMAIL","sendmail");
define("APP_ENSCRIPT","enscript");
define("APP_FILE","file");

//php cli binary
define("APP_PHP","php");

//required apps sed
define("APP_SED","sed");
define("APP_PS","ps");
define("APP_CAT","cat");
define("APP_KILL","kill");
define("APP_CD","cd");

//clamav
define("APP_CLAMAV","clamscan");

//tiff processing
define("APP_TIFFINFO","tiffinfo");
define("APP_TIFFSPLIT","tiffsplit");


//unzipper
define("APP_UNZIP","unzip -p");

//imagemagick
define("APP_CONVERT","convert");
define("APP_MOGRIFY","mogrify");	//should convert all objects to pnm
define("APP_MONTAGE","montage");
define("APP_IDENTIFY","identify");

/*************************************************************************
	uncomment the following to disable docmgr support for them
*************************************************************************/

//Disable encapsulated pdf support.  This is no longer disabled by default
//because indexing is done in the background
//define("DISABLE_ENCAP_OCR","1");

//OCR support
//define("DISABLE_OCR","1");

//pdf support
//define("DISABLE_PDF","1");

//thumbnail support
//define("DISABLE_THUMB","1");

//email support
//define("DISABLE_EMAIL","1");

//clamav support
//define("DISABLE_CLAMAV","1");


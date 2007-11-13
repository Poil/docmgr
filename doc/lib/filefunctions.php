<?php

/*********************************************************
//this function figures out the path to our files, based on whether or
//not the filePath is relative
*********************************************************/
function getFilePath($filePath,$altFilePath) {

    //there is a leading slash, it's an absolute path
    if ($filePath[0]=="/") return $filePath;
    else {

        //return our relative path appended to the path
        //of the docmgr installation
        return $altFilePath."/".$filePath;

    }

}
/*******************************************************************
//  this function spits out the files in chunks to 
//  allow for larger files to be downloaded even though
//  memory_limit is low.  I took this directly from
//  the php website.
*******************************************************************/

function readfile_chunked ($filename) {

    $chunksize = 1*(1024*1024); // how many bytes per chunk (this is 1 mb)

    $buffer = null;

    if (!$handle = fopen($filename, 'rb')) return false;

    while (!feof($handle)) {
        $buffer = fread($handle, $chunksize);
        print $buffer;
    }

    return fclose($handle);

}
/*********************************************************
*********************************************************/
function fileExtension($file) {

    $pos = strrpos($file,".");
    if ($pos == "0") return false;
    else {

        $ext = strtolower(substr($file,$pos));

        return $ext;

    }
}
/*********************************************************
*********************************************************/
function fileIncludeType($ext) {

    $imageArray = array(    ".jpg",
                            ".png",
                            ".bmp",
                            ".gif",
                            ".tif",
                            ".tiff",
                            ".jpeg"
                            );

    $embedArray = array(    ".avi",
                            ".pdf",
                            ".mov",
                            ".doc"
                            );



    if (in_array($ext,$embedArray)) return "embed";
    elseif (in_array($ext,$imageArray)) return "image";
    else return "include";

}


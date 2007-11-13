<?

/*
        Plaintext Edit Module for DocMgr 0.53.1
            - based on some other modules (fileemail / filecheckin)

        (c) 2005 Timo Springmann

*/

// max filesize for files to edit online
define("MAXEDITSIZE",512000);

$hideHeader = 1;

$objectId = $_REQUEST["objectId"];
$pageAction = $_POST["pageAction"];
$module = $_REQUEST["module"];


// TODO:

// Temp-Files werden scheinbar nicht mehr angelegt. Wie funktioniert die Sache dann? werden die automatisch beim Import gelöscht?

//  Schrägstriche verdoppeln sich bei jedem Speichern -> PROBLEM!!!

if ($pageAction=="save" && !$error) {

    // get text out of formular
    $text = stripsan($_POST["text"]);

    // write out file to temp directory
    $tmpname = $objectId."".$_SESSION["user_id"]."".time();
    $tmppath = TMP_DIR."/".$tmpname;

    // Open File
    $handle = fopen($tmppath, "w");
    fputs($handle, $text);
    fclose($handle);

    //figure out what version we of file to open
    $sql = "SELECT name,version FROM dm_object WHERE id='".$objectId."';";
    $result = single_result($conn,$sql);
    $version = $result["version"];
    $newVersion = $result["version"] + 1;
    
    //set all our options into the array with corresponding keys.  These will
    //be passed to the file_insert function, which handles inserting the file into the system

    $option = null;
    $option["conn"] = $conn;
    $option["name"] = sanitizeString($result["name"]);
    $option["filepath"] = $tmppath;
    $option["delete_files"] = "yes";
    $option["updateFile"] = 1;
    $option["objectId"] = $objectId;
    $option["version"] = $newVersion;
    $option["objectOwner"] = USER_ID;
    $option["notes"] = _EDIT_REVNOTE;

    if (fileObject::runCreate($option)) {

        $successMessage = _FILE_UPLOAD_SUCCESS;
        indexObject($conn,$objectId,USER_ID,null);
        fileObject::thumbCreate($conn,$objectId);

        //log the events
        logEvent($conn,OBJ_CHECKED_IN,$objectId);
        sendEventNotify($conn,$objectId,"OBJ_CHECKIN_ALERT");

    }
    else $errorMessage = _FILE_UPLOAD_ERROR;

    //tack on an error message from the function
    if (defined("ERROR_MESSAGE")) $errorMessage .= "<br>".ERROR_MESSAGE;

}


    /*
        Get text file from filesystem and put content in formular

    */

    // get version of file
    $sql = "SELECT version FROM dm_object WHERE id='".$objectId."';";
    $info = single_result($conn,$sql);
    $version = $info["version"];

    // get mor informations of file
    $sql = "SELECT id,name,version,filesize,
        (SELECT id FROM dm_file_history WHERE dm_file_history.object_id = dm_object.id AND dm_file_history.version=dm_object.version) AS file_id
        FROM dm_object WHERE id='$objectId'";

    $info = single_result($conn,$sql);

    //create our file path
    $filePath = DATA_DIR."/".returnObjPath($conn,$objectId)."/".$info["file_id"].".docmgr";

    // if file isn't too large, open it.
    $text = "";
    if (($info["filesize"] < MAXEDITSIZE) && (file_exists($filePath))) {
    $handle = fopen ($filePath, "r");
    while (!feof($handle)) {
      $buffer = fgets($handle, 4096);	
      $text .= $buffer;
    }
      //get rid of the santized stuff we put in there.  Since we write directly to the file, the sanitized version
      //goes in there.  I think this is safe for the moment.  But, we may just want to give this field a special
      //name like "fileeditContent" or something and add it to the exclude list later.
      fclose ($handle);
    } else {
      if ($info["filesize"] >= MAXEDITSIZE) $error = _FILE_TOO_LARGE; 
      else $error = _FILE_NOT_FOUND.": ".$filePath;
    }


?>

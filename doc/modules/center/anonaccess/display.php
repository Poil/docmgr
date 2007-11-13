<?php

/*********************************************************
  this file is used to provide temporary
  access to specific files for users which are
  not docmgr users
*********************************************************/

$authString = $_REQUEST["auth"];
$dateStamp = date("Y-m-d H:i:s");

//query the database to find a user with this auth string
$sql = "SELECT dm_email_anon.*,(SELECT name FROM dm_object WHERE id=object_id) AS name
               FROM dm_email_anon WHERE link_encoded='$authString'";
$info = single_result($conn,$sql);

//make sure the date is still valid
if ($info["date_expires"] < $dateStamp) $errorMessage = _LINK_EXPIRED;
else {

  //check the pin and open the file if it is correct
  if ($_POST["openFile"]) {
  
    //query the database to find a user with this auth string
    $sql = "SELECT object_id FROM dm_email_anon WHERE link_encoded='$authString' AND pin='".$_POST["pin"]."'";
    $objInfo = single_result($conn,$sql);

    if (!$objInfo) $errorMessage = _PIN_INVALID;
    else {

      $objId = $objInfo["object_id"];

      if (!viewFile($conn,$objInfo["object_id"],$info)) $errorMessage = _FILE_VIEW_ERROR;

    }  
  
  } 
  
  $onPageLoad = "document.pageForm.pin.focus();";
  
  $siteContent = "
                  <br><br>
                  <form name=\"pageForm\" method=post>
                  <input type=hidden name=\"auth\" id=\"auth\" value=\"".$authString."\">
                  <center>
                  <div class=\"formHeader\">
                  "._ENTER_PIN." \"".$info["name"]."\"
                  <br><br>
                  "._LINK_EXPIRES_ON." ".dateView($info["date_expires"])."
                  </div>
                  <br>
                  <input size=6 maxlength=6 type=text name=\"pin\" id=\"pin\" value=\"\">
                  <br><br>
                  <input type=submit name=\"openFile\" id=\"openFile\" value=\""._OPEN_FILE."\">
                  </center>
                  </form>
                  ";

}

//while we are here, remove all entries older than their expiration date from the db
$sql = "DELETE FROM dm_email_anon WHERE date_expires < '$dateStamp';";
db_query($conn,$sql);




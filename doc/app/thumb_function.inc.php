<?php

function runObjectThumb($conn,$objectId) {

  //first, get our object's name and summary
  $sql = "SELECT id,name,summary,object_type FROM dm_object WHERE id='$objectId'";
  $objInfo = single_result($conn,$sql);

  $name = &$objInfo["name"];
  $summary = &$objInfo["summary"];
  $objectType = $objInfo["object_type"];

  //hand off to the object's specific function.  This should return any content in the
  //object to be indexed below.

  //debugMsg(1,"Now thumbnailing ".$name);

  //run the objects function that returns its content, if there is any
  $className = $objInfo["object_type"]."Object";

  //create the thumbnail
  $c = loadClassMethod($className,"thumbCreate");
  if (is_object($c)) $c->thumbCreate($conn,$objectId);

  //create the preview
  $d = loadClassMethod($className,"previewCreate");
  if (is_object($d)) $d->previewCreate($conn,$objectId);

}




//master function for thumbnailing and object
function thumbObject($conn,$objectId,$thumbForeground = null) {

      //now hand off to our other functions for the thumbnail creation
      //should be move this to the indexing script?
      if (defined("THUMB_SUPPORT") && !defined("DISABLE_THUMB")) {

        if (defined("DISABLE_BACKTHUMB") || $thumbForeground) runObjectThumb($conn,$objectId);
        else {

          //insert our object into the queue
          $opt = null;
          $opt["object_id"] = $objectId;
          $opt["account_id"] = USER_ID;
          dbInsertQuery($conn,"dm_thumb_queue",$opt);

          //we're all set, hand this off to our program
          //to run in the background
          if (defined("ALT_FILE_PATH")) $prog = APP_CD." ".ALT_FILE_PATH."; ".APP_PHP." bin/docmgr-thumb.php";
          else $prog = APP_PHP." bin/docmgr-thumb.php";

          //check to see if our app is running.If it is, we can exit
          //otherwise, we have to start it.
          if (!checkIsRunning("bin/docmgr-thumb.php")) runProgInBack("$prog");
          
        }

      }

}


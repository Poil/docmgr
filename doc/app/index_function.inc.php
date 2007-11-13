<?php


/******************************************************************
	Our string manipulation and cleanup functions.
	These functions are available to any 
	object specific indexing functions
******************************************************************/

function removeTags($string) {

	$string = trim(ereg_replace("<([^>]|\n)*>"," ",$string));
	
	//remove blank spaces.  All of the other non-alphanum chars have been removed
	$string = str_replace("nbsp"," ",$string);

	return $string;
}


function string_clean($passString) {

	$keepIndex = REGEXP_OPTION;
	
	//pass our text (or file) to tr to clean the text
	$exp = "/[^" . $keepIndex . "\\r\\n\\t ]/i";
	$string =  preg_replace($exp," ",$passString);

	//replace our tabs with spaces
	$string = str_replace("\t"," ",$string);
	$string = str_replace("\r"," ",$string);
	$string = str_replace("\n"," ",$string);

	//make all lowercase
	$string = strtolower($string);

	//clean out anything between a tag
	if ($cleanTags) $string = removeTags($string);

	//return here if we are using tsearch2
	if (defined("TSEARCH2_INDEX")) {

		//replace any 10 consecutive blank spaces with 1 space.  This should help keep tsearch2 under the 2k limit
		$string = str_replace("          "," ",$string);

		return $string;

	}

	/******************************************
		processing for the old index system
	******************************************/

	return uniqueString($string);	

}

//this strips all duplicate words from our string	
function uniqueString($string) {

	$wordArray = split(" ",$string);

	$wordArray = array_values(array_unique($wordArray));

	return implode(" ",$wordArray);

}



function runObjectIndex($conn,$objId,$propOnly = null) {

  //first, get our object's name and summary
  $sql = "SELECT id,name,summary,object_type FROM dm_object WHERE id='$objId'";
  $objInfo = single_result($conn,$sql);

  $name = &$objInfo["name"];
  $summary = &$objInfo["summary"];
  $objectType = $objInfo["object_type"];

  //hand off to the object's specific function.  This should return any content in the
  //object to be indexed below.

  debugMsg(1,"Now indexing ".$name);

  //start creating our index string if using tsearch2
  if (defined("TSEARCH2_INDEX")) {

    //remove the file extension from the name before we index
    $pos = strrpos($name,".");
    $noext = substr($name,0,$pos);
 
    //throw these into any array and spit out a unique version of the file name.Since we
    //are indexing the name twice, technically, this prevents the same word from being added
    //twice and falsely increasing the weight
    $idxName = uniqueString($name." ".$noext);
 
    //setup our indexing string with weighting. 'A' for the filename, 'B' for
    //the summary, and the default 'D' for the content
    $indexString = "setweight(to_tsvector('".TSEARCH2_PROFILE."','".sanitize($idxName)."'),'A')||
                      setweight(to_tsvector('".TSEARCH2_PROFILE."','".sanitize($summary)."'),'B') ";

    //if propOnly is passed, index the objetc's properties and exit
    //we'll only handle tsearch2 here.
    if ($propOnly) {

      $sql = "DELETE FROM dm_index WHERE object_id='$objId';";
      if ($indexString) $sql .= "INSERT INTO dm_index (object_id,idxfti) VALUES ('$objId',$indexString);";
      if (db_query($conn,$sql)) return true;
      else return false;

    }

  }

  //run the objects function that returns its content, if there is any
  $className = $objectType."Object";

  debugMsg(3,"Trying to load runIndex function in object class ".$className);

  $c = loadClassMethod($className,"runIndex");
  if (is_object($c)) {
    debugMsg(3,"Function found, extracting content");
    $content = $c->runIndex($conn,$objId);
  }
  else {
    debugMsg(3,"runIndex function not found in ".$className);
    $content = null;
  }
  
  //shorten it to the word limit if set
  if (defined("INDEX_WORD_LIMIT") && $content) {
    $arr = explode(" ",$content);
    $arr = array_slice($arr,0,INDEX_WORD_LIMIT);
    $content = implode(" ",$arr);
  }

  //prepare the content for indexing
  if ($content) $content = string_clean($content);

  //finish off our tsearch2 string and insert it.  We leave content untouched so the tsearch2 indexer can
  //index and rank it appropriately
  if (defined("TSEARCH2_INDEX")) {
  
    if ($content) $indexString .= " || setweight(to_tsvector('".TSEARCH2_PROFILE."','".sanitize($content)."'),'D') ";

    $sql = "DELETE FROM dm_index WHERE object_id='$objId';";
    $sql .= "INSERT INTO dm_index (object_id,idxfti) VALUES ('$objId',$indexString);";
    
    if (db_query($conn,$sql)) return true;
    else return false;

  //store content for the old indexer.  We need to clean up the content before we store it in our index
  } else {
  
    $sql = "DELETE FROM dm_index WHERE object_id='$objId';";
    if ($content) $sql .= "INSERT INTO dm_index (object_id,idxtext) VALUES ('$objId','".sanitize($content)."');";
    if (db_query($conn,$sql)) return true;
    else return false;
  
  }


}


/*******************************************************************
  Object indexing functions
*******************************************************************/

/*************************************************************
  indexObject
    This is the parent function of the indexing process.
    It needs to handle creating a new batch, adding the
    objects to the queue, and beginning the indexing program
    either itself or with child functions

    parameters 
      db resource and the object ids to index.
      object ids can be a single id or an array
*************************************************************/

function indexObject($conn,$obj,$accountId,$notifyUser = "f",$propsOnly=null) {

  //sanity checking
  if (!$obj || count($obj)==0) return false;

  if (defined("DISABLE_BACKINDEX")) return runForegroundIndex($conn,$obj);
  else return runBackgroundIndex($conn,$obj,$accountId,$notifyUser,$propsOnly);
  
}

function runForegroundIndex($conn,$obj) {

  runObjectIndex($conn,$obj);
  return true;

}

function runBackgroundIndex($conn,$obj,$accountId,$notifyUser = "f",$propsOnly=null) {

  //if updating properties only, just update name & summary if using tsearch2 and exit.
  //in this case, obj will always be a non-array
  if ($propsOnly) {

    //start creating our index string if using tsearch2
    if (defined("TSEARCH2_INDEX")) {

      $name = sanitizeString($propsOnly["name"]);
      $summary = sanitizeString($propsOnly["summary"]);

      //pull the existing content from the index so we can re-create our tsearch2 idx data
      $sqlCur = "SELECT setweight(idxfti,'D') FROM dm_index WHERE object_id='$obj';";
      $temp = single_result($conn,$sqlCur);
      $content = addslashes($temp[0]);
      
      $indexString = "setweight(to_tsvector('".TSEARCH2_PROFILE."','".$name."'),'A')";
      $indexString .= " || setweight(to_tsvector('".TSEARCH2_PROFILE."','".$summary."'),'B')";
      $indexString .= " || setweight('".$content."','D') ";
      
      //we do a delete instead of an update in case there is no entry there
      $sql .= "DELETE FROM dm_index WHERE object_id='$obj';";
      $sql .= "INSERT INTO dm_index (object_id,idxfti) VALUES ('$obj',$indexString);";
      if (!db_query($conn,$sql)) return false;
      
    }
    
    return true;
    
  }

  createIndexQueue($conn,$obj,$accountId,$notifyUser);

  //we're all set, hand this off to our indexing program
  //to run in the background
  if (defined("ALT_FILE_PATH")) $prog = APP_CD." ".ALT_FILE_PATH."; ".APP_PHP." bin/docmgr-indexer.php";
  else $prog = APP_PHP." bin/docmgr-indexer.php";

  //check to see if our app is running.If it is, we can exit
  //otherwise, we have to start it.
  if (!checkIsRunning("bin/docmgr-indexer.php")) runProgInBack("$prog");

  return true;

}

/*************************************************************
  createIndexQueue
  This adds all our objects to the queue to be indexed
*************************************************************/

function createIndexQueue($conn,$obj,$accountId,$notifyUser) {

  $opt = null;
  $opt["object_id"] = $obj;
  $opt["account_id"] = $accountId;
  $opt["notify_user"] = $notifyUser;
  $opt["create_date"] = date("Y-m-d H:i:s");
  if (dbInsertQuery($conn,"dm_index_queue",$opt)) return true;
  else return false;

}



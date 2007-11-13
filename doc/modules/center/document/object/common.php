<?

/********************************************************************************************

	Filename:
		common.php
      
	Summary:
		Holds functions specific only to the file module
                        
*********************************************************************************************/


class documentCommon {

/***************************************************************
	remove a revision for a file.  if file_id = earliest,
	remove the earliest available version.  Otherwise
	remove the passed id
***************************************************************/
function removeRevision($conn,$objectId,$fileId) {

	//sanity checking
	if (!$objectId) return false;
	if (!$fileId) return false;

	//remove our earliest revision to keep the limit where it should be
	if ($fileId=="earliest") {

		//config check
		if (!defined("DOC_REVISION_LIMIT") || DOC_REVISION_LIMIT=="0") return false;

		$sql = "SELECT id FROM dm_document WHERE object_id='$objectId' ORDER BY id";	
		$info = total_result($conn,$sql);

		if ($info["count"] > DOC_REVISION_LIMIT) {

		        //delete all entries that are less then our current count
		        $diff = $info["count"] - DOC_REVISION_LIMIT;

                        for ($i=0;$i<$diff;$i++) {
		  
			  $fileId = $info["id"][$i];
			
			  $sql = "DELETE FROM dm_document WHERE id='$fileId'";
			  if (db_query($conn,$sql)) {
				$file = DOC_DIR."/".returnObjPath($conn,$objectId)."/".$fileId.".docmgr";
				@unlink($file);
                          }

                        }      		

		}

        //this portion deletes the specified revision as determined by the fileId	
	} else {

		//config check
		if (!defined("DOC_REVISION_REMOVE") || DOC_REVISION_REMOVE=="no") return false;
		
		//get the latest version of this file
		$sql = "SELECT version FROM dm_document WHERE id='".$fileId."'";
		$hInfo = single_result($conn,$sql);

		//get the current version
		$sql = "SELECT version FROM dm_object WHERE id='$objectId'";
		$oInfo = single_result($conn,$sql);

		$sql = "DELETE FROM dm_document WHERE id='".$fileId."'";
		if (db_query($conn,$sql)) {

	  	  $file = DOC_DIR."/".returnObjPath($conn,$objectId)."/".$fileId.".docmgr";
	  	  @unlink($file);

	  	  //if the file was the latest revision, promote the next in line
	  	  if ($hInfo["version"]==$oInfo["version"]) {

			//get the next latest version of this file
			$sql = "SELECT id FROM dm_document WHERE object_id='".$objectId."' ORDER BY version DESC LIMIT 1";
			$info = single_result($conn,$sql);

			//promote our file, then reindex and rethumb
	  	  	documentCommon::promote($conn,$objectId,$info["id"]);
			indexObject($conn,$objectId,USER_ID,null);

		  } 

                } else return false;


	}

	return true;

}




//promotes a file to the latest revision for the object
function promote($conn,$objectId,$fileId) {

	/* with this, we will pretty much have to put the ids in an array, and alter them accordingly */
	$sql = "SELECT * FROM dm_document WHERE object_id='$objectId' ORDER BY version DESC";
	$info = total_result($conn,$sql);

	$id_array = &$info["id"];
	$version_array = &$info["version"];

	/* figure out which number in the array our orignal belongs to */
	$orig_num=array_search($fileId,$id_array);

	/* set the new file to the highest version number */
	$sql = null;

	//create a new array with the swapped orders
	$temp = array($fileId);
	foreach ($info["id"] AS $id) if ($id!=$fileId) $temp[] = $id;
	$num = count($temp);

	for ($row=0;$row<$num;$row++) {

		//a hack to fix the promote errors some people are getting
		if (!$temp[$row]) continue;

		$version = $num - $row;
		$sql .= "UPDATE dm_document SET version='$version' WHERE id='$temp[$row]';";

	}

	//update the database to use the highest one if there is a name field in the database
	$sql .= "UPDATE dm_object SET version='".max($version_array)."' WHERE id='$objectId';";
	
	//run our query
	if (db_query($conn,$sql)) return true;
	else return false;

}


}


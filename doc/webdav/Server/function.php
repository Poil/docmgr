<?php


class webdavfunc {

	function checkOutput($string) {

		//disable this function for now
		if (!defined("DEBUG")) return true;

		$fp = fopen("/tmp/webdav.txt","a");
		fwrite($fp,$string);
		fclose($fp);

	}


        /************************************************
        	return the type of object
	************************************************/
	function getObjType($path) {

        	$conn = $_SESSION["conn"];
        
        	if ($path=="/") return "collection";

        	$path = webdavfunc::fixPath($path);

		//get the object name
		$arr = explode("/",$path);
                $name = array_pop($arr);		
		$owner = webdavfunc::getCatOwner($path);

        	$sql = "SELECT object_type FROM dm_view_objects WHERE name='".addslashes($name)."' AND parent_id='$owner';";
        	$info = single_result($conn,$sql);

        	//return a value based on its object type        
        	return $info["object_type"];

        	//if we get here, the object does not exist
        	return false;      


	}	


	/***********************************************
		return the category's id in the database
	***********************************************/
	function getCollectionId($path) {

		$path = webdavfunc::fixPath($path);

		//return a root level path as 0
		if ($path=="/") return "0";

		$catList = $_SESSION["catList"];

		$dirArray  = explode("/",$path);

		$num = count($dirArray);
		$owner = "0";
		
		//start from the root level and work our way down
		for ($i=0;$i<$num;$i++) {

		  //skip empty directory
		  if (!$dirArray[$i]) continue;

                  //get all collections at this level		
                  $keys = array_keys($catList["parent_id"],$owner);		    
                  $match = null;

                  webdavfunc::checkOutput("looking for ".$dirArray[$i]." in ".$owner."\n");

                  //loop through our collections and find one that matches this entry
                  if (count($keys) > 0) {

                    foreach ($keys AS $key) {

                      //if we find a match, store the id and continue to next level
                      if ($catList["name"][$key]==$dirArray[$i]) {
                        $owner = $catList["id"][$key];
                        webdavfunc::checkOutput("setting owner to ".$owner."\n");
                        $match = 1;
                        break;
                      }

                    }

                  }

                  //if a match wasn't found, something messed up, return false
                  if (!$match) {
                    return false;
                  }
                  
                }                                                    

                //if we make it to here, return our owner
                return $owner;

	}

	/**********************************************
		return the id of the owning category
	**********************************************/
	function getCatOwner($path) {

		$path = webdavfunc::fixPath($path);


		if ($path=="/") return "0";

		//remove the trailing element from the array
		$arr = explode("/",$path);
		array_pop($arr);

		//if it's a top level connection, return "0"
		if (!$arr) return "0";

		webdavfunc::checkOutput("running getCatOwner on ".implode("/",$arr)."\n");

    		return webdavfunc::getCollectionId(implode("/",$arr));

	}

	/*********************************************************
		function checkObjExists:
			checks to see if a file or category is present
			in the database.  Returns true if it is, false
			if it isn't
	*********************************************************/

	function checkObjExists($path) {

		$arr = webdavfunc::getObjInfo($path);
		$objId = $arr["objInfo"]["id"];
		
		if ($objId) return true;
		else return false;
      
	}

	/*********************************************************
		function getObjInfo:
		checks to see if an object is a file or category.
		If it is a file, it returns an array of file info
	*********************************************************/
  
	function getObjInfo($path) {
      
		$conn = $_SESSION["conn"];

		//make sure the path is okay
		$path = webdavfunc::fixPath($path);

		//get the object name
		$arr = explode("/",$path);
                $name = array_pop($arr);		
		$owner = webdavfunc::getCatOwner($path);

		$arr["name"] = $name;
		$arr["parent_id"] = $owner;

		//now search to see if a category with this name exists.  If not, it's a file
		$sql = "SELECT * FROM dm_view_objects WHERE name='".addslashes($name)."' AND parent_id='$owner';";
		webdavfunc::checkOutput($sql."\n");
		$info = single_result($conn,$sql);
		$arr["objInfo"] = $info;         

		return $arr;
  
	}


	/*******************************************************************
		function fixpath:
		this removes any trailing slashes or url encoding from the link
		so we can process it
	********************************************************************/

	function fixPath($path) {

		if ($path=="/") return $path;

		//remove the url trailing slash if it exists
		if (substr($path,strlen($path)-1,1)=="/") $path = substr($path,0,strlen($path)-1); 
		$path = str_replace("%20"," ",$path);

		$arr = explode("/",$path);
		$c = count($arr) - 1;
		//$arr[$c] = utf8_decode($arr[$c]);	//remove as it is causing errors with certain languages

		$path = implode("/",$arr);
  
		return $path;         
         
	}

	function fixURL($path) {

		$path = str_replace("//","/",$path);
		$arr = explode("/",$path);
  
		for ($row=0;$row<count($arr);$row++) $arr[$row] = rawurlencode($arr[$row]);

		$path = implode("/",$arr);

		return $path;         
         
	}


	function checkWebdavPerms($objId,$mode) {

		$conn = $_SESSION["conn"];
		$cb = returnUserObjectPerms($conn,$objId);

		if (bitset_compare($cb,$mode,OBJ_ADMIN)) return true;
		else return false;
	}
	

	function pathSanityCheck($path) {

		$path = webdavfunc::fixPath($path);

		$arr = explode("/",$path);
		array_pop($arr);
  
		for ($row=0;$row<count($arr);$row++) {
			if ($arr[$row]) {
				$dir .= "/".$arr[$row];
				$info = webdavfunc::getCollectionId($dir);
				if (!$info) return false;
			}
		}  

		return true;

	}
                                                        

}


?>
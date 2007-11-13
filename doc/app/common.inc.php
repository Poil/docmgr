<?

/********************************************************************************************

	Filename:
		common.inc.php
      
	Summary:
		this file contains functions common to all modules in this application.
		They should still be somewhat generic
            
	Modified:
              
		09-02-2004
			Code cleanup.  Moved functions that don't belong out
                        
*********************************************************************************************/

function createFolder($path) {

	//get rid of the trailing "/" if it exists
	$check = substr($path,strlen($path)-1,1);

	if ($check=="/") $path = substr($path,0,strlen($path)-1);

	//turn the string into an array
	$pathArray = explode("/",$path);

	$string = null;

	//loop thru our array.  If any directory does not exist, create it
	for ($row=0;$row<count($pathArray);$row++) {

		$string .= $pathArray[$row]."/";

		if (!file_exists($string)) {

			if (mkdir($path)) chmod($path,"0493");
			else return false;

		}

	}

	return true;

}

function dirdel ($dirName) {
	$d = @opendir ($dirName);
	while($entry = @readdir($d)) {
		if ($entry != "." && $entry != ".." && $entry != "") {
			if (is_dir($dirName."/".$entry)) {
				dirdel($dirName."/".$entry);
			} else {
				@unlink ($dirName."/".$entry);
			}
		}
	}
	@closedir($d);
	@rmdir ($dirName);
}

function fileUpload($form,$filter,$destination) {

	$file = $_FILES[$form]['tmp_name'];
	$name = $_FILES[$form]['name'];
	$type = $_FILES[$form]['type'];

	createFolder($destination);

	//tack the name onto the directory
	$destination .= $name;

	if ($file) {

		//if we have a filter, apply it
		if ($filter) {

			if (in_array($type,$filter)) {
				move_uploaded_file("$file","$destination");
				return array("successMessage","File Uploaded Successfully");
			}
			else {
				return array("errorMessage","Cannot upload a file of this type");
			}

		}
		else {
			move_uploaded_file("$file","$destination");
			return array("successMessage","File Uploaded Successfully");
		}

	}
	else return array("errorMessage","Cannot upload a file of this type");
}


function recursiveList($directory) {

	$list = listDirectory($directory,null,null);

	$count = count($list);

	for ($row=0;$row<$count;$row++) {

		$fullPath = $directory."/".$list[$row];

		if (is_dir($fullPath)) {
			$temp = recursiveList($fullPath); 

			if ($temp) $list = array_merge($list,$temp);
		}

		$list[$row] = $fullPath;

	}

	return $list;

}


function recursiveDirCreate($conn,$path,$owner) {

	//split the path into an array
	$list = explode("/",$path);
	array_pop($list);

	$dt = date("Y-m-d h:i:s");

	//check to see if the directory exists.  If it does, move on to the next one
	//if it doesn't, create it.
	for ($row=0;$row<count($list);$row++) {

		if (!$list[$row]) continue;

		$sql = "SELECT id FROM dm_view_collections WHERE name='".$list[$row]."' AND parent_id='$owner' AND object_type='1'";	
		$info = single_result($conn,$sql);

		//it exists.  Make it the owner for the next one we check
		if ($info) $objectId = $info["id"];
		else {
	
                  $option = null;
                  $option["name"] = $list[$row];
                  $option["create_date"] = date("Y-m-d H:i:s");
                  $option["status_date"] = date("Y-m-d H:i:s");
                  $option["status"] = "0";
                  $option["object_type"] = "1";
                  $option["object_owner"] = USER_ID;
                  
                  $objectId = dbInsertQuery($conn,"dm_object",$option);

                  //setup the parent link for the collection
                  $sql = "INSERT INTO dm_object_parent (object_id,parent_id) VALUES ('$objectId','$owner');";
                  db_query($conn,$sql);

                  //insert the permissions inherited from the owning category
                  inheritParentPerms($conn,$objectId,$owner);

		}

		$owner = $catId;

	}

	//return catId, as it will be the last one
	return $objectId;

}


/****************************************************************************
	return an array with all possible information regarding this extension
****************************************************************************/

function return_file_type($filename,$filepath=null) {

	$info = return_file_info($filename,$filepath);
	return $info["fileType"];

}

function return_file_mime($filename,$filepath=null) {

	$info = return_file_info($filename,$filepath);
	return $info["mimeType"];

}

//returns true if we can index the file, false if we can't
function return_file_idxopt($filename,$filepath=null) {

	$info = return_file_info($filename,$filepath);
	if ($info["preventIndex"]) return false;
	else return true;
	
}


function return_file_proper_name($filename,$filepath=null) {

	$info = return_file_info($filename,$filepath);
	return $info["properName"];

}


function return_file_info($filename,$filepath = null) {

	$ret = array();
	$ext = return_file_extension($filename);

	if (defined("ALT_FILE_PATH")) $data = file_get_contents(ALT_FILE_PATH."/config/extensions.xml");
	else $data = file_get_contents("config/extensions.xml");

	//return our extensions in an array
	$arr = parseGenericXml("object",$data);

	//find the extension in our list
	$key = array_search($ext,$arr["extension"]);

        //the file has an extension and we've matched it up	
	if ($key!==FALSE) {

		$ret["mimeType"] = $arr["mime_type"][$key];
		$ret["fileType"] = $arr["custom_type"][$key];
		$ret["properName"] = $arr["proper_name"][$key];
		$ret["preventIndex"] = $arr["prevent_index"][$key];

	}
	else {

	        $useDefault = null;

	        //we couldnt find anything.  Let's use the "file" program to try and return
                //the file type
                if (defined("FILE_SUPPORT") && $filepath) {

                  $app = APP_FILE;
                  $str = `$app -ib "$filepath"`;
                  $strarr = explode(";",$str);
                  $mimeType = $strarr[0];

                  $key = array_search($mimeType,$arr["mime_type"]);

                  //we recognize the mime-type, grab the file's info                  
                  if ($key!==FALSE) {
                    $ret["mimeType"] = $arr["mime_type"][$key];
                    $ret["fileType"] = $arr["custom_type"][$key];
                    $ret["properName"] = $arr["proper_name"][$key];
                    $ret["preventIndex"] = $arr["prevent_index"][$key];
                  } else $useDefault = 1;
                  
                } else $useDefault = 1;

                //default to oct-stream                
                if ($useDefault) {
		  $ret["mimeType"] = "application/octet-stream";
		  $ret["fileType"] = "other";
		  $ret["properName"] = strtoupper($ext)." File";
		  $ret["preventIndex"] = "1";
		}
	
	}

	return $ret;

}

					
function return_file_extension($filename) {

	//get the file extension
	$pos=strrpos($filename,".") + 1;
	$extension = strtolower(trim(substr($filename,$pos)));

	return $extension;
}

function retrievePermissions($conn,$find_cat) {

	$sql = "SELECT id FROM auth_groups";
	$info = total_result($conn,$sql);

	$group_num = $info["count"];
	$g_id_array = &$info["id"];

	$sql = "SELECT * FROM dm_cat_permissions WHERE bitset='3' AND cat_id='$find_cat'";
	$info = total_result($conn,$sql);

	$id_array_view_edit = &$info["id"];
	$group_array_view_edit = &$info["group_id"];

	$sql = "SELECT * FROM dm_cat_permissions WHERE bitset='2' AND cat_id='$find_cat'";
	$info = total_result($conn,$sql);

	$id_array_view = &$info["id"];
	$group_array_view = &$info["group_id"];

	$returnArray = array($g_id_array,
					$id_array_view_edit,
					$group_array_view_edit,
					$id_array_view,
					$group_array_view);

	return $returnArray;

}

function name_return($conn,$table,$field,$object) {

	$sql = "SELECT $field FROM $table WHERE id='$object'";
	$info = single_result($conn,$sql);

	if ($info) return $info[$field];
	else return false;


}


function returnCatOwner($info,$id,$pass_array) {

	if (!$pass_array) $pass_array[] = $id;

	//see if there is an owner for this key
	$key = array_search($id,$info["id"]);
	
	$owner = $info["parent_id"][$key];

	//this exits if we are at the top.  
	//it now also exits if a category owns itself.  This should not happen, and will 
	//crash the webserver in a neverending loop if not checked here
	if ($owner!=0 && $owner!=$id) {

		$pass_array[] = $owner;
		$pass_array = returnCatOwner($info,$owner,$pass_array);

	}
	return $pass_array;
		
}

function returnNavList($conn,$id,$catInfo=null,$append=null,$class="main") {

	if (!$catInfo && $conn) {
	
		//get all collections that need to be displayed
		$sql = "SELECT DISTINCT id,name,parent_id,object_type FROM dm_view_collections ORDER BY name";
		$catInfo = total_result($conn,$sql);

		//get our collections we are allowed to see
		$sql = "SELECT id FROM dm_view_collections WHERE ".permString($conn);
		if (!bitset_compare(BITSET,ADMIN,null)) {
  		  $permList = total_result($conn,$sql);
  		  $permArr = &$permList["id"];
                }
  	}

	$string .= "<div class=\"menuNav\"><div class=\"menuEntry\">\n";
	$string .= "<a href=\"javascript:browseCollection('0');\" class=\"".$class."\">"._HOME."</a>";
	$string .= "</div></div>\n";

	//only keep going if we are not at the root level
	if ($id) {

		//get our array of category owners
		$ownerArray = array_reverse(returnCatOwner($catInfo,$id,null,null));

		for ($row=0;$row<count($ownerArray);$row++) {

			$obj = $ownerArray[$row];

			//setup some variables in case the user is using ie.
			if (BROWSER=="ie") {
				$conId = "id=\"".$obj."Container\"";
				$subId = "id=\"".$obj."Menu\"";
				$arrowLink = "id=\"".$obj."Arrow\" onMouseEnter=\"showNavMenu('".$obj."');\" onMouseLeave=\"hideNavMenu('".$obj."');\"";
			}

			$string .= "<div class=\"menuNav\" ".$conId.">\n";
			$string .= "<div class=\"menuArrow\" ".$arrowLink.">\n";
			$string .= "<img src=\"".THEME_PATH."/images/navarrow.gif\" border=0>\n";
			$string .= "<div class=\"menuSubNav\" ".$subId.">\n";

			//get info for the current collection
			$key = array_search($ownerArray[$row],$catInfo["id"]);

			//create an entry for the current collection
			if (BROWSER=="ie") {
				$entryId = $obj."EntryBase";
				$elink = "id=\"".$entryId."\" onMouseEnter=\"setClass(ge('".$entryId."'),'menuSubNavRowOver');\"  onMouseLeave=\"setClass(ge('".$entryId."'),'menuSubNavRow');\"";
			}
			
			//make sure the user has permissions to see this collection
			if ($permArr) {
			  if (in_array($obj,$permArr)) {
			    $name = $catInfo["name"][$key];
			    $link = "browseCollection('".$obj."');";
			  } else {
			    $name = "[Hidden]";
                            $link = "alert('"._OBJ_VIEW_PERM_ERROR."');";
                          }
			} else {
			  $name = $catInfo["name"][$key];
                          $link = "browseCollection('".$obj."');";
                        }

                        if (strlen($name) > 30) $showname = substr($name,0,30)."...";
                        else $showname = $name;                        

			$string .= "<div id=\"".$entryId."\" onClick=\"".$link."\" onMouseEnter=\"setClass(ge('".$entryId."'),'menuSubNavRowOver');\"  onMouseLeave=\"setClass(ge('".$entryId."'),'menuSubNavRow');\">\n";
			$string .= $showname;
			$string .= "</div>\n";

			//display all the categories in this level
			$keys = array_keys($catInfo["parent_id"],$catInfo["parent_id"][$key]);

			if (count($keys) > 0) { 

				foreach ($keys AS $subkey) {

					//skip the current one
					if ($catInfo["id"][$subkey]==$obj) continue;

					if (BROWSER=="ie") {
						$entryId = $obj."Entry".$subkey;
						$elink = "id=\"".$entryId."\" onMouseEnter=\"setClass(ge('".$entryId."'),'menuSubNavRowOver');\"  onMouseLeave=\"setClass(ge('".$entryId."'),'menuSubNavRow');\"";	
					}

					//make sure the user has permissions to see this collection
					if ($permArr) {
					  if (!in_array($catInfo["id"][$subkey],$permArr)) continue;
					}

                                        $subname = $catInfo["name"][$subkey];
                                        if (strlen($subname) > 30) $subname = substr($subname,0,30)."...";
								
					//create an entry for the other collections
					$string .= "<div ".$elink." onClick=\"browseCollection('".$catInfo["id"][$subkey]."')\">\n";
					$string .= $subname;
					$string .= "</div>\n";

				}			

			}
			//end submenu and arrow
			$string .= "</div>\n</div>\n";


			//show links for collections
			$string .= "<div class=\"menuEntry\">\n";
			$string .= "<a href=\"javascript:".$link."\" class=\"".$class."\">";
			$string .= $name;
			$string .= "</a>";
			$string .= "</div>\n";

			//end container div
			$string .= "</div>\n";
			
		}

	}

	//append a filename for navigation purposes if desired
	if ($append) $string .=  "<div class=\"menuNav\">
					<div class=\"menuEntry\">
						<img src=\"".THEME_PATH."/images/navarrow.gif\" border=0>
						 ".$append ."
					</div>
				</div>\n";

	$string .= "<div class=\"cleaner\">&nbsp;</div>\n";

	return $string;

}

function simpleNavList($conn,$id,$catInfo=null) {

	if (!$catInfo && $conn) {
	
		//get all collections that need to be displayed
		$sql = "SELECT DISTINCT id,name,parent_id,object_type FROM dm_view_collections ORDER BY name";
		$catInfo = total_result($conn,$sql);

		//get our collections we are allowed to see
		$sql = "SELECT id FROM dm_view_collections WHERE ".permString($conn);
		if (!bitset_compare(BITSET,ADMIN,null)) {
  		  $permList = total_result($conn,$sql);
  		  $permArr = &$permList["id"];
                }
  	}

	$string .= "<div class=\"navMenu\">\n";
	$string .= "<a href=\"javascript:browseCollection('0');\">"._HOME."</a>";
	$string .= "</div>\n";

	//only keep going if we are not at the root level
	if ($id) {

		//get our array of category owners
		$ownerArray = array_reverse(returnCatOwner($catInfo,$id,null,null));

		for ($row=0;$row<count($ownerArray);$row++) {

			$obj = $ownerArray[$row];

			$string .= "<div class=\"navMenu\" ".$arrowLink.">
					<img src=\"".THEME_PATH."/images/navarrow.gif\" border=0>
				    </div>\n";

			//get info for the current collection
			$key = array_search($ownerArray[$row],$catInfo["id"]);
			
			//make sure the user has permissions to see this collection
			if ($permArr) {
			  if (in_array($obj,$permArr)) {
			    $name = $catInfo["name"][$key];
			    $link = "browseCollection('".$obj."');";
			  } else {
			    $name = _HIDDEN;
                            $link = "alert('"._OBJ_VIEW_PERM_ERROR."');";
                          }
			} else {
			  $name = $catInfo["name"][$key];
                          $link = "browseCollection('".$obj."');";
                        }


			//show links for collections
			$string .= "<div class=\"navMenu\">\n";
			$string .= "<a href=\"javascript:".$link."\">";
			$string .= $name;
			$string .= "</a>";
			$string .= "</div>\n";

		}

	}

	$string .= "<div class=\"cleaner\">&nbsp;</div>\n";

	return $string;

}



function listDirectory($directory,$extFilter,$nameFilter) {

	$resultArray = array();

	$handle=@opendir($directory);

	while ($file = @readdir($handle)) {

		//skip directory markers
		if ($file!="." && $file!=".." && $file[0]!="." && substr($file,-1)!="~") {

			//skip this one if the name filter isn't matched
			if ($nameFilter) {
				
				$pos = strpos($file,trim($nameFilter));
				
				if ($pos===FALSE) continue;

			}

			//is our filter an array o
			if (is_array($extFilter)) {

				//get the extension;
				$pos = strrpos($file,".");
				$ext = strtolower(substr($file,$pos));

				if (in_array($ext,$extFilter)) $resultArray[] = $file;

			}
			else {
				$resultArray[] = $file;
			}

		}

	}
	@closedir($handle);

	return $resultArray;

}

function GetChildren($vals, &$i) { 

	$children = array(); // Contains node data

	/* Node has CDATA before it's children */
	if (isset($vals[$i]['value'])) 
	$children['VALUE'] = $vals[$i]['value']; 

	/* Loop through children */
	while (++$i < count($vals)) { 
		switch ($vals[$i]['type']) { 

		/* Node has CDATA after one of it's children 
		(Add to cdata found before if this is the case) */
		case 'cdata': 

			if (isset($children['VALUE']))
				$children['VALUE'] .= $vals[$i]['value']; 
			else
				$children['VALUE'] = $vals[$i]['value']; 
			break;

		/* At end of current branch */ 
		case 'complete': 
			if (isset($vals[$i]['attributes'])) {
				$children[$vals[$i]['tag']][]['ATTRIBUTES'] = $vals[$i]['attributes'];
				$index = count($children[$vals[$i]['tag']])-1;

				if (isset($vals[$i]['value'])) 
					$children[$vals[$i]['tag']][$index]['VALUE'] = $vals[$i]['value']; 
				else
					$children[$vals[$i]['tag']][$index]['VALUE'] = ''; 
			} else {
				if (isset($vals[$i]['value'])) 
					$children[$vals[$i]['tag']][]['VALUE'] = $vals[$i]['value']; 
				else
					$children[$vals[$i]['tag']][]['VALUE'] = ''; 
			}
			break; 

		/* Node has more children */
		case 'open': 
			if (isset($vals[$i]['attributes'])) {
				$children[$vals[$i]['tag']][]['ATTRIBUTES'] = $vals[$i]['attributes'];
				$index = count($children[$vals[$i]['tag']])-1;
				$children[$vals[$i]['tag']][$index] = array_merge($children[$vals[$i]['tag']][$index],GetChildren($vals, $i));
			} else {
				$children[$vals[$i]['tag']][] = GetChildren($vals, $i);
			}
			break; 

		/* End of node, return collected data */
		case 'close': 
			return $children; 
		} 
	} 
} 
		
function showProgress() {

	return true;

}

function displayFileSize($size,$floatsize = 2) {

	$kbTest = 1024;
	$mbTest = 1024*1024;
	$gbTest = 1024*1024*1024;

	//if greater than this size, return in kilobytes
	if ($size>=$gbTest) $size = number_format($size/$gbTest,$floatsize)." GB";
	elseif ($size>=$mbTest) $size = number_format($size/$mbTest,$floatsize)." MB";
	else $size = number_format($size/$kbTest,$floatsize)." KB";
	//else $size = $size." B";

	return $size;
	
}

function checkAppAvail($app) {

        //if the app is an absolute path, just return true
        if ($app[0]=="/") return true;

        //extract the app from it's command line args
        $app = extractApp($app);

	$str = `which "$app" 2>/dev/null`;

	//if which returns nothing, it couldn't find the app
	if (!$str) return false;

	$pos = strrpos($str,"/");
	$str = trim(substr($str,0,$pos));

	//make sure the app's path is in apache's path
	$pathArr = explode(":",$_SERVER["PATH"]);

	if (in_array($str,$pathArr)) return true;
	else return false;

}

function checkRequiredApp($app) {

        //if the app is an absolute path, just return true
        if ($app[0]=="/") return true;

        //extract the app from it's command line args
        $app = extractApp($app);

	$str = `which "$app" 2>/dev/null`;
	$error = null;

	//if which returns nothing, it couldn't find the app
  	if (!$str) $error = "1";
  	else {
  	  $pos = strrpos($str,"/");
  	  $str = trim(substr($str,0,$pos));

  	  //make sure the app's path is in apache's path
  	  $pathArr = explode(":",$_SERVER["PATH"]);

  	  if (!in_array($str,$pathArr)) $error = "1";;
        }

	if ($error) {
	  $message = "Error!  The application <b>$app</b> could not be found in ".$_SERVER["PATH"]."<br>
	              This application is required by DocMGR to run.<br><br>
	              ";
          die($message);
        }
}

//this function extracts the core app name from an absolute or relative path, and 
//the parameters pass to the app
function extractApp($app) {

  $arr = explode(" ",$app);
  return $arr[0];
  

}

//this function determines if our optional applications are available to docmgr
function getExternalApps() {

  $arr = array();

  //figure out which of our external progs exist
  if (checkAppAvail(APP_OCR)) $arr["ocr"] = 1;
  if (checkAppAvail(APP_WGET)) $arr["wget"] = 1;
  if (checkAppAvail(APP_ZIP)) $arr["zip"] = 1;
  if (checkAppAvail(APP_UNZIP)) $arr["unzip"] = 1;

  if (checkAppAvail(APP_MOGRIFY)) $arr["mogrify"] = 1;
  if (checkAppAvail(APP_CONVERT)) $arr["convert"] = 1;
  if (checkAppAvail(APP_MONTAGE)) $arr["montage"] = 1;
  if ($arr["mogrify"] && $arr["convert"] && $arr["montage"]) $arr["imagemagick"] = 1;

  if (checkAppAvail(APP_PDFTOTEXT)) $arr["pdftotext"] = 1;
  if (checkAppAvail(APP_PDFIMAGES)) $arr["pdfimages"] = 1;
  if (checkAppAvail(APP_PDFTOPPM)) $arr["pdftoppm"] = 1;
  if ($arr["pdftotext"] && $arr["pdfimages"] && $arr["pdftoppm"]) $arr["xpdf"] = 1;  

  if (checkAppAvail(APP_TIFFINFO)) $arr["tiffinfo"] = 1;
  if (checkAppAvail(APP_TIFFSPLIT)) $arr["tiffsplit"] = 1;
  if ($arr["tiffinfo"] && $arr["tiffsplit"]) $arr["libtiff"] = 1;

  if (function_exists("imap_open")) $arr["php_imap"] = 1;
  if (checkAppAvail(APP_SENDMAIL)) $arr["sendmail"] = 1;
  
  if ($arr["sendmail"] || $arr["php_imap"]) $arr["email"] = 1;

  if (checkAppAvail(APP_ENSCRIPT)) $arr["enscript"] = 1;

  if (checkAppAvail(APP_MSWORD_TEXT)) $arr["antiword"] = 1;

  if (checkAppAvail(APP_CLAMAV)) $arr["clamav"] = 1;

  if (checkAppAvail(APP_FILE)) $arr["file"] = 1;

  return $arr;

}

function setExternalApps() {

	if (!isset($_SESSION["setApps"])) {
	
            //check to make sure if we have these required programs.  If not, die
            checkRequiredApp(APP_PS);
            checkRequiredApp(APP_CAT);
            if (!defined("DISABLE_BACKINDEX")) {
              checkRequiredApp(APP_PHP);

              //make sure they are not using the cgi version of php
              $app = APP_PHP." -v";
              $str = `$app`;
              if (!strstr($str,"(cli)")) die("You are not using the cli version of php.  Please either install php-cli or disabled background indexing");

            }
            $_SESSION["setApps"] = getExternalApps();	

	}

	//url download support
	if ($_SESSION["setApps"]["wget"]) define("URL_SUPPORT","1");

	//zip archive support
	if ($_SESSION["setApps"]["zip"]) define("ZIP_SUPPORT","1");
	if ($_SESSION["setApps"]["unzip"]) define("UNZIP_SUPPORT","1");

	//advanced word support
	if ($_SESSION["setApps"]["antiword"]) define("DOC_SUPPORT","1");

	//ocr support
	if (!defined("DISABLE_OCR") && 
	  ($_SESSION["setApps"]["ocr"] && 
	  $_SESSION["setApps"]["libtiff"] && 
	  $_SESSION["setApps"]["imagemagick"])) define("OCR_SUPPORT","1");

	//pdf support
	if (!defined("DISABLE_PDF")) {

	  if ($_SESSION["setApps"]["xpdf"]) {
	    define("XPDF_SUPPORT","1");
	    define("PDF_SUPPORT","1");
          }

        }

	//thumbnail support
	if (!defined("DISABLE_THUMB") && $_SESSION["setApps"]["imagemagick"]) define("THUMB_SUPPORT","1");

	//tiff handling support
	if (defined("THUMB_SUPPORT") && $_SESSION["setApps"]["libtiff"]) define("TIFF_SUPPORT","1");
	
	//txt thumb support
	if (defined("THUMB_SUPPORT") && $_SESSION["setApps"]["enscript"]) define("ENSCRIPT_SUPPORT","1");

	//email support
	if (!defined("DISABLE_EMAIL") && $_SESSION["setApps"]["email"]) {

		define("EMAIL_SUPPORT","1");

		//set php_imap and sendmail availability
		if ($_SESSION["setApps"]["php_imap"]) define("PHP_IMAP_SUPPORT","1");
		if ($_SESSION["setApps"]["sendmail"]) define("SENDMAIL_SUPPORT","1");
		
	}

	//antivirus support
	if (!defined("DISABLE_CLAMAV") && $_SESSION["setApps"]["clamav"]) define("CLAMAV_SUPPORT","1");

	//file extended type checking support
	if ($_SESSION["setApps"]["file"]) define("FILE_SUPPORT","1");

	return true;
	
}

function inheritParentPerms($conn,$objectId,$parentId = null) {

	//get the parent id if not passed
	if (!$parentId) {
		$sql = "SELECT parent_id FROM dm_object_parent WHERE object_id='$objectId';";
		$info = single_result($conn,$sql);
		$parentId = $info["parent_id"];
		
		//if still no parent, exit
		if (!$parentId) return false;
	}

	//get the parent's permissions
	$sql = "SELECT * FROM dm_object_perm WHERE object_id='$parentId';";
	$list = total_result($conn,$sql);
	
	if ($list) {

		//delete existing entries for this object
		$sql = "DELETE FROM dm_object_perm WHERE object_id='$objectId';";
	
		//insert the new ones
		for ($row=0;$row<$list["count"];$row++) {
		
			$a = $list["account_id"][$row];
			$g = $list["group_id"][$row];
			$bitset = $list["bitset"][$row];

			if ($a) {
				$field = "account_id";
				$val = $a;
			}
			else {
				$field = "group_id";
				$val = $g;
			}
			
			$sql .= "INSERT INTO dm_object_perm (object_id,$field,bitset) 
							VALUES
							('$objectId','$val','$bitset');";
		
		}

		if (db_query($conn,$sql)) return true;
		else return false;
	
	}

	return true;
	
}

//update the parent of an object
function parentUpdate($conn,$objectId,$parentId) {

        //default to the home directory if nothing is passed
        if (!$parentId) $parentId = "0";

        //make sure an object with this name does not exist in our new collections
        $sql = "SELECT name FROM dm_object WHERE id='$objectId'";
        $info = single_result($conn,$sql);

        //we are doing an update, make sure an object with the same name does not already exist
        if (!checkObjName($conn,addslashes($info["name"]),$parentId,$objectId)) {
          $errorMessage = ERROR_MESSAGE;
          return false;
        }

	//remove the old entries
	$sql = "DELETE FROM dm_object_parent WHERE object_id='$objectId';";

	//put our parent into an array if it isn't already
	if (is_array($parentId)) $parentArray = $parentId;
	else $parentArray = array($parentId);

	for ($row=0;$row<count($parentArray);$row++) {

		//do not allow an object to be its own parent
		if ($parentArray[$row]==$objectId || $parentArray[$row]==NULL) continue;

		//insert the new entries
		$sql .= "INSERT INTO dm_object_parent (object_id,parent_id)
			VALUES
			('$objectId','".$parentArray[$row]."');";

	}

	if (db_query($conn,$sql)) return true;
	return false;

}

//get all available languages for docmgr
function retrieveLang() {

	$arr = listDirectory("lang/",null,null);
	$ret = array();
	$count = count($arr);

	if ($count=="0") die("No language files found in the \"lang/\" directory<br>");
	
	for ($row=0;$row<$count;$row++) {

	        //skip files that aren't php scripts
	        $ext = return_file_extension($arr[$row]);
                if ($ext!="php") continue;

		$pos = strpos($arr[$row],".");
		$ret[] = substr($arr[$row],0,$pos);

	}

	//sort by name
	sort($ret);

	return $ret;
	
}


//log an event for an object in the database
function logEvent($conn,$logType,$objectId,$data = null, $accountId = null) {

	if (defined("USER_ID")) $accountId = USER_ID;
	else $accountId = $accountId;

	if (!$accountId) $accountId = "0";
	
	$opt = null;
	$opt["object_id"] = $objectId;
	$opt["log_type"] = $logType;
	$opt["account_id"] = $accountId;
	$opt["log_time"] = date("Y-m-d H:i:s");

	//optional data for the log
	if ($data) $opt["log_data"] = $data;
	dbInsertQuery($conn,"dm_object_log",$opt,"object_id");

}

function returnLoglist() {

	$data = file_get_contents("config/logtypes.xml");
	return parseGenericXml("log_type",$data);

}

function returnLogType($logArr,$logType) {

	//get out if we don't have an array of possible logs
	if (!$logArr) return false;
	
	if (!in_array($logType,$logArr["link_name"])) return false;

	$langtext = "_LT_".$logType;
	
	if (defined($langtext)) $text = constant($langtext);
	else {
	
		$key = array_search($logType,$logArr["link_name"]);
		$text = $logArr["name"][$key];

	}

	return $text;	

}

//return a query to filter our objects to only allow those a non-admin can see
function permString() {

	$sql = "(";

	//if there is an entry for a group this user belongs to, they can see the object.
	if (defined("USER_GROUPS") && strlen(USER_GROUPS)>0)
		$sql .= " group_id IN (".USER_GROUPS.") OR ";

	$sql .= " account_id='".USER_ID."' OR ";

	//set default permissions for a file if no perms are set
	if (OBJPERM_LEVEL=="strict") 
		$sql .= " object_owner='".USER_ID."')";
	else
		$sql .= " bitset ISNULL)";

	return $sql;

}

//get a list of all possible alerts
function returnAlertList() {

        if (defined("ALT_FILE_PATH")) $file = ALT_FILE_PATH."config/alerts.xml";
        else $file = "config/alerts.xml";
	$data = file_get_contents("$file");
	return parseGenericXml("alert",$data);

}

//return the alert name for this type
function returnAlertType($alertArr,$alertType) {

	//get out if we don't have an array of possible alerts
	if (!$alertArr) $alertArr = returnAlertList();

	if (!in_array($alertType,$alertArr["link_name"])) return false;

	$el = "_AT_".$alertType;

        if (defined($el)) $alertMsg = constant($el);
	else {
	
		$key = array_search($alertType,$alertArr["link_name"]);
		$alertMsg = $alertArr["name"][$key];
		
	}
	        
	return $alertMsg;	

}

function createEventMsg($objName,$eventType,$actionName = null) {

	$alertArr = returnAlertList();

	$el = "_".$eventType;

        if (defined($el)) $str = constant($el);
        else $str = returnAlertType($alertArr,$eventType);;

        //append the name of the created file if necessary
        if ($eventType=="OBJ_CREATE_ALERT" && $actionName) $str .= ": ".$actionName;

        $msg = _FOLLOWING_EVENT_OCCURED." \"".$objName."\"<br><br><b>".$str."</b>\n";

	return $msg;

}

//send a subscription alert for all users for this object
function sendEventNotify($conn,$objectId,$eventType,$parent = null) {

        //get all names
        $sql = "SELECT id,name,parent_id FROM dm_view_objects WHERE id='$objectId' OR object_type='collection'";
        $catInfo = total_result($conn,$sql);

        //get our array of category owners
        $objArr = array_reverse(returnCatOwner($catInfo,$objectId,null));
        
	//get all users that are subscribed to this file
	$sql = "SELECT * FROM dm_subscribe WHERE object_id IN (".implode(",",$objArr).") AND event_type='$eventType';";
	$list = list_result($conn,$sql);

	//get out if there's no subscribers to this object and event
	if (!$list["count"]) return false;

	$sql = NULL;

	//get the object's information
	$sql = "SELECT name,object_type,version FROM dm_object WHERE id='$objectId';";
	$oInfo = single_result($conn,$sql);

        //if a collection is passed, we need to recognize this
        if ($parent) {
          $sql = "SELECT name FROM dm_object WHERE id='$parent'";
          $pInfo = single_result($conn,$sql);
          $objName = $pInfo["name"];
        } else $objName = $oInfo["name"];
	
	for ($i=0;$i<$list["count"];$i++) {
	
		//add the alert
		$sql = "INSERT INTO dm_alert (account_id,object_id,alert_type)
						VALUES
						('".$list[$i]["account_id"]."','$objectId','$eventType');";	
		db_query($conn,$sql);

		//send an email if desired by the user
		if ($list[$i]["send_email"]=="t" && defined("EMAIL_SUPPORT")) {


			$link = SITE_URL."index.php?module=".$oInfo["object_type"]."&objectId=".$objectId;

			$msg = createEventMsg($objName,$eventType,$oInfo["name"]);					
			$msg .= "<br>"._VIEW_OBJ_PROPERTIES;
			$msg .= "<br><br><a href=\"".$link."\">".$link."</a>\n";

			$sub = "DocMGR "._EVENT_NOTIFICATION." \"".$objName."\"";	

			$aInfo = returnAccountInfo($conn,$list[$i]["account_id"],null);

			//if the user wants the attachment, send the file with the attachment
			if ($list[$i]["send_file"]=="t" && $oInfo["object_type"]=="file" && 
			    ($eventType=="OBJ_CHECKIN_ALERT" || $eventType=="OBJ_CREATE_ALERT")) {
			
			    $sql = "SELECT id FROM dm_file_history WHERE object_id='$objectId' AND version='".$oInfo["version"]."'";
			    $fInfo = single_result($conn,$sql);
			    $filePath = DATA_DIR."/".returnObjPath($conn,$objectId)."/".$fInfo["id"].".docmgr";

			    //assemble our attachment array
			    $attach[0]["name"] = $oInfo["name"];
			    $attach[0]["path"] = $filePath;
                         			
			} else $attach = null;

			send_email($aInfo["email"],ADMIN_EMAIL,$sub,$msg,$attach);

		}

	}

	return true;
		
}

//send a task notify alert for this account
function sendTaskNotify($conn,$objId,$accountId) {

	if (!defined("EMAIL_SUPPORT")) return false;
	if (!$objId) return false;
	if (!$accountId) return false;
	
	//get the email address
	$info = returnAccountInfo($conn,$accountId,null);
	$addr = $info["email"];
	
	if (!$addr) return false;

	//get the object name
	$sql = "SELECT name FROM dm_object WHERE id='$objId';";
	$info = single_result($conn,$sql);

	if (!$info) return false;	

	$link = SITE_URL."index.php?module=file&includeModule=filetask&objectId=".$objId;

	$msg = createEventMsg($info["name"],"OBJ_TASK_ALERT");					
	$msg .= "<br>"._VIEW_FILE_TASK;
	$msg .= "<br><br><a href=\"".$link."\">".$link."</a>\n";
	
	$sub = "DocMGR "._TASK_NOTIFICATION." \"".$info["name"]."\"";	

	send_email($addr,ADMIN_EMAIL,$sub,$msg,null);

	return true;
		
}


//returns all immediate children of the current collection
function getCurrentChildren($arr,$colId,$val) {

	$keys = array_keys($arr["parent_id"],$colId);
	
	//there are children of this parent, cycle through and look for grandchildren
	if (count($keys) > 0) {
	
		foreach ($keys AS $key) {
	
			$val[] = $arr["id"][$key];
			$val = getCurrentChildren($arr,$arr["id"][$key],$val);

		}

	//return what we have if there are no children
	} 
	
	return $val;

}

//this function returns an array of all categories below the current one
function returnColChildren($conn,$colId) {

	//get out if no collection was passed
	if (!$colId) return false;

	//add the passed collection as the first entry
	$arr = array($colId);

	//get all collections
	$sql = "SELECT DISTINCT id,parent_id FROM dm_view_collections";
	$list = total_result($conn,$sql);

	//get the collections under our parent
	$arr = getCurrentChildren($list,$colId,$arr);		

	return $arr;
}

//this function returns an array of all objects below the curret one
function returnObjChildren($conn,$objId) {

	//get out if no collection was passed
	if (!$objId) return false;

	//add the passed collection as the first entry
	$arr = array($objId);

	//get all collections
	$sql = "SELECT DISTINCT id,parent_id FROM dm_view_objects";
	$list = total_result($conn,$sql);

	//get the collections under our parent
	$arr = getCurrentChildren($list,$objId,$arr);		

	return $arr;
}

/******************************************************************************
	scan a file for a virus.  this returns "clean" if nothing was found.
	it returns the name of the virus if an infection is found.  If there
	is a scan error, it returns false
*******************************************************************************/
function clamAvScan($filepath) {

	if (!defined("CLAMAV_SUPPORT")) return false;

	$app = APP_CLAMAV;
	$str = `$app --infected "$filepath"`;
	
	//return false if there is a scanning error
	if (strstr($str,"Scanned files: 0")) return false;

	//if no infected files are found, return true;
	if (strstr($str,"Infected files: 0")) return "clean";
	else {
	
		//viruses were found, display the found virus information
		$pos = strpos($str,"----------- SCAN SUMMARY -----------");
		$vf = trim(substr($str,0,$pos));

		$pos = strpos($vf,":") + 1;
		$vf = _VIRUS_WARNING."! ".substr($vf,$pos);					

		return $vf;
	
	}
}

/****************************************************************************
	this function compares the md5 sum of the file we're accessing
	to the stored value created at the time of file upload.  If
	the values do not match, we return false.
****************************************************************************/
function fileChecksum($conn,$id,$filepath) {

	//sanity checking
	if (!$id) return false;
	if (!$filepath) return false;
	if (!is_file($filepath)) return false;

	//get the stored md5sum
	$sql = "SELECT md5sum FROM dm_file_history WHERE id='$id';";
	$info = single_result($conn,$sql);

	//get the md5sum for the file we're trying to access
	$md5sum = md5_file($filepath);

	//make sure values exist for both
	if (!$md5sum || !$info["md5sum"]) return false;

	//return true if they match
	if ($md5sum==$info["md5sum"]) return true;
	else return false;
	
}

/**************************************************************************
	This function creates a checksum.md5 file with the path
	of the file and its checksum.  it returns the path
	to the checksum file if successful, false on failure
**************************************************************************/
function createChecksum($conn,$id,$filename) {

	//sanity checking
	if (!$id) return false;
	if (!$filename) return false;

	//get the stored md5sum
	$sql = "SELECT md5sum FROM dm_file_history WHERE id='$id';";
	$info = single_result($conn,$sql);

	$md5sum = $info["md5sum"];

	//create a temp directory for our user
	$dir = TMP_DIR."/".USER_LOGIN;
	$file = $dir."/checksum.md5";
	
	if (!is_dir("$dir")) mkdir("$dir");

	$str = $md5sum."  ./".$filename."\n";

	//make sure the file doesn't already exist
	@unlink($file);

	$fp = fopen("$file",w);
	fwrite($fp,$str);
	fclose($fp);

	return "$file";
	
}

function keywordTextField($opts) {

	//gives us title,name,value,size,style
	extract($opts);

	//default form size
	if (!$size) $size = "20";

	//setup style and/or class if passed
	if ($style) $style = "style=\"".$style."\"";
	if ($class) $class = "class=\"".$class."\"";

	//assemble our form
	$str = "<div class=\"formHeader\">
		".$title."
		</div>
		<input type=text name=\"".$name."\" id=\"".$name."\" size=\"".$size."\" ".$style." ".$class." value=\"".$value."\">
		";

	return $str;
	
}

function keywordDropdownField($opts) {

	//gives us title,name,value,size,style,option
	extract($opts);

	//default form size
	if (!$size) $size = "1";

	//setup style and/or class if passed
	if ($style) $style = "style=\"".$style."\"";
	if ($class) $class = "class=\"".$class."\"";

	$str = "<div class=\"formHeader\">
		".$title."
		</div>
		<select name=\"".$name."\" id=\"".$name."\" size=\"".$size."\" ".$style." ".$class.">
		";
		
	foreach ($option AS $curOpt) {

		if ($value == $curOpt) $select = " SELECTED ";
		else $select = null;

		$str .= "<option value=\"".$curOpt."\">".$curOpt."\n";	
	
	}

	$str .= "</select>\n";

	return $str;

}

//extract our keyword list and return an array
function returnKeywords() {

	$multi = array("option");
	if (defined(ALT_FILE_PATH)) $keyfile = ALT_FILE_PATH."/config/keywords.xml";
	else $file = "config/keywords.xml";
	
	if (file_exists($file)) $data = file_get_contents("config/keywords.xml");
	else return array();
	
	$arr = parseGenericXml("keyword",$data,$multi);

	//transpose it for better manipulation later
	return transposeArray($arr);

}


/* base expanding function */
function tree_view($option) {

	/* gives us $conn, $curValue,$formName,$submitFunction,$mode
	These have to be passed as an associative array to the function */
	extract($option);

	$str = null;

	if ($formName) {
	
		$name = $formName."TreeVal";
		//store the checked values
		$str .= "<input type=hidden name=\"".$name."\" id=\"".$name."\" value=\"".@implode(",",$curValue)."\">\n";

	}

	$str .= "<script type=\"text/javascript\">\n";
	$str .= "//begin_js_exec\n";

	if ($curValue==0) $curValue = null;
	if (count($curValue)==1 && !$curValue[0]) $curValue = null;
	if ($curValue > 0 && !is_array($curValue)) $curValue = array($curValue);
		
	$url = "index.php?module=coltree";
	if ($formName) $url .= "&formName=".$formName;
	if ($mode) $url .= "&mode=".$mode;
	if ($divName) $url .= "&divName=".$divName;
	for ($i=0;$i<count($curValue);$i++) $url .= "&curValue[]=".$curValue[$i];
	$str .= "loadXMLReq('".$url."');\n";
	$str .= "//end_js_exec\n";
	$str .= "</script>\n";
	
	return $str;

}

//this function indexes the name and summary of the object 
function indexObjectProps($conn,$objectId,$name,$summary) {

    $indexString = null;

	//process our tsearch2 indexing info if it's setup
	if (defined("TSEARCH2_INDEX")) {

		if ($name) $indexString .= " setweight(to_tsvector('".TSEARCH2_PROFILE."','".$name."'),'A') ";
		if ($name && $summary) $indexString .= " || ";
		if ($summary) $indexString .= " setweight(to_tsvector('".TSEARCH2_PROFILE."','".$summary."'),'B') ";
    
		$sql = "DELETE FROM dm_index WHERE object_id='$objectId';";
		if ($indexString) $sql .= "INSERT INTO dm_index (object_id,idxfti) VALUES ('$objectId',$indexString);";
		db_query($conn,$sql);

	}
      
}

function ajaxSelfClose() {

  $str = "
  <script language=\"javascript\">
  
  parentId = window.opener.document.getElementById(\"view_parent\");
  
  if (parentId) {
    url = \"index.php?module=browse&view_parent=\" + parentId.value;
  } else {
    url = window.opener.location.href;
  }

  window.opener.location.href = url;  
  self.close();

  </script>
    
  ";

  echo $str;

}

//checks to see if a program with the passed pid is running
function isPidRunning($pid) {

    if (!$pid) return false;

    $app = APP_PS;
    $str = `$app --no-headers --pid $pid`;
     
    if (strstr($str,$pid)) return true;
    else return false;
       
}

//checks to see if a program of the passed name is running       
function checkIsRunning($app) {

  $cmd = APP_PS." aux | grep \"".$app."\" | grep -c -v grep";
  $num = `$cmd`;

  if ($num > 0) return true;
  else return false;

}

//runs a program in the background
function runProgInBack($prog,$file = null) {

  //if no file, create an output file
  if (!$file) $file = "/dev/null";

  //output errors to the console if debug is turned on
  if (defined("DEBUG") && DEBUG > 0) $pid = exec("$prog >> $file & echo \$!");
  else $pid = exec("$prog >> $file 2>/dev/null & echo \$!");

  return $pid;

}

function createTempFile($ext = null) {

  if (!$ext) $ext = "txt";

  if (defined("USER_ID")) $fn = TMP_DIR."/".USER_ID."_".rand().".".$ext;
  else  $fn = TMP_DIR."/".rand().".".$ext;

  //if the file exists, remove it and create a new one with open permissions
  if (file_exists($fn)) unlink($fn);
  
  //create our empty file
  $fp = fopen($fn,"w");
  fclose($fp);

  //set the permissions as open as possible.  This way if an external script
  //is run as root, we can remove it as the webuser later
  chmod($fn,0777);

  return $fn;

}

/*********************************************************
  return the permissions set for this user in this object
*********************************************************/

function returnUserObjectPerms($conn,$objId) {

  //give the user admin access to all files
  if (bitset_compare(BITSET,ADMIN,null)) $cb |= OBJ_ADMIN;

  //check for other object properties which an admin doesn't necessarily have access to
  $sql = "SELECT object_owner,status,status_owner, 
            (SELECT id FROM dm_view_workflow WHERE object_id='$objId' AND account_id='".USER_ID."' AND status='pending' LIMIT 1) AS task
            FROM dm_object WHERE id='$objId';";
  $info = single_result($conn,$sql);

  //if it's the file's owner, give them all rights
  if ($info["object_owner"]==USER_ID) $cb |= OBJ_ADMIN;	

  //if the file is checked out and the user is the person that checked it out give them checkin rights
  if ($info["status"]=="1" && $info["status_owner"]==USER_ID) $cb |= OBJ_CHECKIN;
  elseif (!$info["status"] && ($info["object_owner"]==USER_ID || bitset_compare(BITSET,ADMIN,null))) $cb |= OBJ_CHECKIN;

  //if this is set, there is a pending task for this user on this object
  if ($info["task"]) $cb |= OBJ_TASK;

  //get the rest of the permissions for this file if it's a non-admin
  if (!bitset_compare(BITSET,ADMIN,null)) {
  
    //get the file's info 
    $sql = "SELECT status,status_owner,account_id,group_id,bitset FROM dm_view_perm WHERE object_id='$objId'";
    $perm = total_result($conn,$sql);

    if (!$perm["count"]) {

      //if no perms are set, give them view/edit perms unless we are in strict mode
      if (OBJPERM_LEVEL!="strict") $cb |= OBJ_EDIT;		
   
    } else {

      //extract the group ids into an array from our define
      $gArr = array();
      if (strlen(USER_GROUPS) > 0) $gArr = explode(",",USER_GROUPS);

      //figure out the user's permissions for this file based on their account id and
      //groups they belong to
      for ($row=0;$row<$perm["count"];$row++) {

        if ($perm["account_id"][$row]==USER_ID) $cb |= $perm["bitset"][$row];
        else if (in_array($perm["group_id"][$row],$gArr)) $cb |= $perm["bitset"][$row];
            
      }    
    
    }

  }

  return $cb;

}

//reformats our inline document for proper display
function formatEditorStr($str) {
  
  //re-add session id
  $sess = "sessId=".session_id();
  $str = str_replace("[DOCMGR_SESSION_MARKER]",$sess,$str);

  return $str;

}

//removes the session id and cleans up other items for document saving
function cleanupEditorStr($str) {

  //remove the current session id
  $sess = "sessId=".session_id();
  $str = str_replace($sess,"[DOCMGR_SESSION_MARKER]",$str);

  //fckeditor removes our & signs
  $str = str_replace("&amp;","&",$str);

  //stop magic quotes from screwing up our proper escaping of the string
  if (get_magic_quotes_gpc()) $str = stripslashes($str);
         
  return $str;
  
}

//echos some javascript to output site variables from php config settings
function showSiteJS() {

  //get our date format from the define if set
  if (defined("DATE_FORMAT")) $dateFormat = strtolower(DATE_FORMAT);
  else $dateFormat = "mm/dd/yyyy";
 
  //rewrite our php format to work with javascript
  $dateFormat = str_replace("mm","%m",$dateFormat);
  $dateFormat = str_replace("dd","%d",$dateFormat);
  $dateFormat = str_replace("yyyy","%Y",$dateFormat);
 
  //disable ajax if set
  echo "<script type=\"text/javascript\">\n";
  if (defined("DISABLE_AJAX")) echo "enableAjax = 0\n";
  else echo "enableAjax = 1;\n";
  echo "lang_i18n = \"".LANG_I18N."\";\n";
  echo "date_format = \"".$dateFormat."\";\n";
  echo "site_url = \"".SITE_URL."\";\n";
  echo "theme_path = \"".THEME_PATH."\";\n";
  echo "lang_file = \"".SITE_URL."lang/".LANG_I18N.".js\";\n";
  echo "callang_file = \"".SITE_URL."javascript/calendar/lang/calendar-".LANG_I18N.".js\";\n";
  echo "</script>\n";

  //our javascript language file
  $jslang = "lang/".LANG_I18N.".js";
  if (file_exists($jslang)) includeJavascript($jslang);

}


//return account name
function returnAccountName($conn,$id) {

  $info = returnAccountInfo($conn,$id,null);
  return $info["first_name"]." ".$info["last_name"];

}

//converts a string to an array that we can run php array functions on
function strtoarray($str) {

  if (!$str) return false;

  $arr = array();
  $len = strlen($str);

  for ($i=0;$i<$len;$i++) $arr[] = $str[$i];

  return $arr;

}

function debugMsg($level,$msg) {

  if (php_sapi_name()=="cli") $sep = "\n";
  else $sep = "<br>";

  if (DEBUG >= $level) echo $msg.$sep;
      
}
      
//update the settings for an account
function updateAccountSetting($conn,$accountId,$opt) {

  $sql = "SELECT account_id FROM auth_settings WHERE account_id='$accountId'";
  $info = single_result($conn,$sql);

  $opt["account_id"] = $accountId;

  //start appending values to send to the database
  if ($info) {
    $opt["where"] = "account_id='$accountId'";
    dbUpdateQuery($conn,"auth_settings",$opt);
  } else {
    dbInsertQuery($conn,"auth_settings",$opt);
  }

}


//create our subdirectories for storage
function createFileSubDir($path) {

  //create our directory if it doesn't exist
  if (!is_dir($path)) mkdir($path);
  
  //if it's not writable, error out
  if (!is_writable($path)) die("Error!  ".$path." is not writable by the webserver");

  //check to make sure it doesn't exist already
  if (!is_dir($path."/1")) {

    //make our first level of directories
    for ($i=1;$i<=LEVEL1_NUM;$i++) {

	  $level1Data = $path."/".$i;
	  @mkdir($level1Data);

	  for ($c=1;$c<=LEVEL2_NUM;$c++) {
	
		$level2Data = $level1Data."/".$c;
		@mkdir($level2Data);
	
          }

    }

  }

}


/* base expanding function */
function loadObjTree($option) {

	/* gives us $conn, $curValue,$formName,$submitFunction,$mode
	These have to be passed as an associative array to the function */
	extract($option);

	$str = null;

	if ($curValue==0) $curValue = null;
	if (count($curValue)==1 && !$curValue[0]) $curValue = null;
	if ($curValue > 0 && !is_array($curValue)) $curValue = array($curValue);
		
	$url = "index.php?module=objtree";
	if ($formName) $url .= "&formName=".$formName;
	if ($mode) $url .= "&mode=".$mode;
	if ($divName) $url .= "&divName=".$divName;
	for ($i=0;$i<count($curValue);$i++) $url .= "&curValue[]=".$curValue[$i];
	$str .= "loadXMLReq('".$url."');\n";

	return $str;
	
}


function getObjectFromPath($conn,$path) {

  //sanity checking
  if (!$path) return false;
  if ($path=="/") return "0";

  //first, get all our collections in an array
  $sql = "SELECT id,name,parent_id FROM dm_view_collections";
  $colarr = total_result($conn,$sql);

  //split into an array to process each
  $patharr = explode("/",$path);

  //remove the object name from the path and save for later
  $findObject = array_pop($patharr);

  //if it's toplevel object, just skip the rest
  if (count($patharr)==1) {
  
    $owner = "0";
    $match = 1;
    
  } else {

    $owner = "0";
    $num = count($patharr);		

    //start from the root level and work our way down
    foreach ($patharr as $dir) {

      //get all collections at this level		
      $keys = array_keys($colarr["parent_id"],$owner);		    
      $match = null;

      //loop through our collections and find one that matches this entry
      if (count($keys) > 0) {

        foreach ($keys AS $key) {

          //if we find a match, store the id and continue to next level
          if ($colarr["name"][$key]==$dir) {
            $owner = $colarr["id"][$key];
            $match = 1;
            break;
          }
        }
      }

    }

  }

  //if a match wasn't found, something messed up, return false
  if (!$match) return false;

  //if we make it to here, find a query with the appropriate name and this parent
  $sql = "SELECT id,name,object_type FROM dm_view_objects WHERE parent_id='$owner' AND name='".addslashes($findObject)."'";
  $info = single_result($conn,$sql);
  
  if ($info) return $info;
  else return false;


}


//parms are conn, objectId
function permUpdate($conn,$objectId) {

	//get out if there is no object id
	if (!$objectId) return false;

	//get our permissions for this object
	$cb = returnUserObjectPerms($conn,$objectId);

	//if the user doesn't have manage permissions, return false
	if (!bitset_compare($cb,OBJ_MANAGE,OBJ_ADMIN)) return false;

	//first, clear out existing permissions
	$sql = "DELETE FROM dm_object_perm WHERE object_id='$objectId';";

	//give our posted values shorter names
	$mVal = &$_POST["manageObject"];
	$eVal = &$_POST["editObject"];
	$vVal = &$_POST["viewObject"];	
	
	$arr = array();

	//basically we accumulate all values under the key for the object posted	
	for ($row=0;$row<count($mVal);$row++) {
		$key = $mVal[$row];
		$arr[$key] |= OBJ_MANAGE;
	}
	for ($row=0;$row<count($eVal);$row++) {
		$key = $eVal[$row];
		$arr[$key] |= OBJ_EDIT;
	}
	for ($row=0;$row<count($vVal);$row++) {
		$key = $vVal[$row];
		$arr[$key] |= OBJ_VIEW;
	}
	
	//now cycle through our accumlated values and insert into the database
	$keys = array_keys($arr);
	
	for ($row=0;$row<count($keys);$row++) {

		$key = $keys[$row];
	
		$a = explode("-",$key);

		$id = &$a[0];
		$type = &$a[1];
		$bitset = &$arr[$key];
		
		if ($type=="account") $field = "account_id";
		else $field = "group_id";
		
		$sql .= "INSERT INTO dm_object_perm (object_id,$field,bitset) 
						VALUES
						('$objectId','$id','$bitset');";

	}	

	if (db_query($conn,$sql)) return true;
	else return false;

}



<?php
/*********************************************************/
//         FILE: module.php
//  DESCRIPTION: Contains functions that handle the
//               processing of the information in the
//               module.xml files and the execution/display
//               for the site.
//                      
//     CREATION
//         DATE: 04-19-2006
//
//      HISTORY:
//
//
/*********************************************************/

/*********************************************************
*********************************************************/
function returnModuleOwner($id,$ownerArray) {

    $siteModSettings = $_SESSION["siteModSettings"];

    $key = array_search($id,$siteModSettings["modId"]);
    $owner = $siteModSettings["modOwner"][$key];

    //if 0, we have reached the top.  Just return the array as is.
    if ($owner=="0") return $ownerArray;
    else {

        //this one also is owned by another module.  Add to the array and check again
        $ownerArray[] = $owner;
        $ownerArray = returnModuleOwner($owner,$ownerArray);

        //return the new ownerArray
        return $ownerArray;

    }

}
/*********************************************************
*********************************************************/
function getPath($modArray,$id,$path) {

    //do a search for our id to get the key
    $key = array_search($id,$modArray["modId"]);

    $path = $modArray["modDirectory"][$key]."/".$path;

    //is their an owner
    if ($modArray["modOwner"][$key]!="0") {

        $id = $modArray["modOwner"][$key];

        $path = getPath($modArray,$id,$path);

    }

    return $path;

}
/*********************************************************
*********************************************************/
function getGroupPath($conn,$id,$path) {

    //get all groups and store them in an array
    $sql = "SELECT owner,path FROM in_groups WHERE id='$id'";
    $info = single_result($conn,$sql);

    if ($info) {

        $owner = $info["owner"];
        $newPath = $info["path"];

        $path = $newPath."/".$path;

        if ($owner!="0") $path = getGroupPath($conn,$owner,$path);

    }

    return $path;

}
/*********************************************************
*********************************************************/
function getOwner($bitpos,$ownerArray,$bitposArray,$string) {

    $string .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td><td>";

    $key = array_search($bitpos,$bitposArray);

    if ($ownerArray[$key]!="0") $string = getOwner($bitpos,$ownerArray,$bitposArray,$string);
    else return $string;

}
/*********************************************************
*********************************************************/
function getOwnerPath($str) {

	$len = strlen($str);

	//remove trailing slash
	if (substr($str,$len-1)=="/") $str = substr($str,0,$len-1);

	$pos = strrpos($str,"/") + 1;
	
	return substr($str,0,$pos);

}


/*********************************************************
*********************************************************/
function setPermCheck($permArr) {

	if (!is_array($permArr)) return false;
	
	for ($row=0;$row<count($permArr);$row++) if ($permArr[$row]) $permCheck |= constant($permArr[$row]);

	return $permCheck;
	
}


/**************************************************************
	this function creates links to all modules below
	it in a tabular format.  It still needs some love
**************************************************************/

function showModTabs($path,$module=null) {

	if (!$path) return false;

	$siteModList = showModLevel($path,"sort_order");

	$string = null;
	$counter = "0";

	$num = count($siteModList["owner_path"]);

	for ($row=0;$row<$num;$row++) {

		$hide = null;
		
		$permCheck = setPermCheck($siteModList["permissions"][$row]);

		if ($permCheck && !bitset_compare(BITSET,$permCheck,ADMIN)) $hide = 1;
		if ($siteModList["hidden"][$row]==1) $hide=1;

		if (!$hide) {

			if ($module==$siteModList["link_name"][$row]) $class = "selected";
			else $class = null;

			//show the translation for our module name if it exists
			$langmod = "_MT_".strtoupper($siteModList["link_name"][$row]);

			if (defined($langmod)) $modName = constant($langmod);
			else $modName = $siteModList["module_name"][$row];
			
			$string .= "	<div class=\"modButton\">
					<a class=\"".$class."\" 
					href=\"index.php?module=".$siteModList["link_name"][$row]."\"
					>
					".$modName."
					</a></div>\n";

		}

	}

	return $string;
	
}


/************************************************************
	This function generates a page with all sub
	modules and their descriptions.
************************************************************/

function showModTable($path,$sort = null) {

	if (!$path) return false;

	$siteModList = showModLevel($path,$sort);

	$string = "<table border=0 width=100%>
			<tr>\n";

	$counter = "0";

	$num = count($siteModList["module_name"]);
	$cell = "0";
	
	for ($row=0;$row<$num;$row++) {

		if ($cell=="2") {
			$string .= "</tr>\n<tr>";
			$cell = "0";
		}
		
		$hide = null;

		$permCheck = setPermCheck($siteModList["permissions"][$row]);
		if ($permCheck && !bitset_compare(BITSET,$permCheck,ADMIN)) $hide = 1;

		$customPermCheck = setPermCheck($siteModList["custom_perm"][$row]);
		if ($customPermCheck && !bitset_compare(CUSTOM_BITSET,$customPermCheck,null)) $hide = 1;

		if ($siteModList["hidden"][$row]==1) $hide=1;

		if (!$hide) {

			//show the translation for our module name if it exists
			$langmod = "_MT_".strtoupper($siteModList["link_name"][$row]);
			$langdesc = "_MTDESC_".strtoupper($siteModList["link_name"][$row]);

			//check for translations of the module name
			if (defined($langmod)) $modName = constant($langmod);
			else $modName = $siteModList["module_name"][$row];

			//check for translations of the module description
			if (defined($langdesc)) $modDesc = constant($langdesc);
			else $modDesc = $siteModList["module_description"][$row];

			$string .= "	<td width=50% valign=top style=\"padding-bottom:10px;\">
					<div>
					<a class=\"moduleLink\" 
					href=\"index.php?module=".$siteModList["link_name"][$row]."\"
					>
					".$modName."
					</a>
					</div>
					<div>
					".$modDesc."
					</div>
					</td>\n";

			$cell++;

		}

		
	}

	$string .= "</tr></table>";

	return $string;
	
}

function returnModImage($linkName) {

	if (!$linkName) return false;
	
	$siteModList = $_SESSION["siteModList"];
	
	$key = array_search($linkName,$siteModList["link_name"]);
	
	$baseImg = THEME_PATH."/images/modules/module.png";

	//the image order priority goes image in module directory, current theme image,
	//default theme image, and then no image
	$themeImg = THEME_PATH."/images/modules/".$siteModList["link_name"][$key].".png";
	$defaultImg = "themes/default/images/modules/".$siteModList["link_name"][$key].".png";
	$modImg = $siteModList["module_path"][$key].$siteModList["link_name"][$key].".png";

	if (file_exists($modImg)) $liImage = $modImg;
	elseif (file_exists($themeImg)) $liImage = $themeImg;
	elseif (file_exists($defaultImg)) $liImage = $defaultImg;
	else $liImage = $baseImg;

	return $liImage;

}

/**************************************************************
	this function creates links to all modules below
	it in a tabular format.  It still needs some love
**************************************************************/

function showModLinks($path,$module,$testFunction = null,$testParms = null) {

	if (!$path) return false;

	$siteModList = showModLevel($path,"sort_order");

	$string = "<ul style=\"list-style:none;margin-left:0px;padding-left:0px;\">\n";

	$counter = "0";

	$num = count($siteModList["owner_path"]);

	$baseImg = THEME_PATH."/images/modules/module.png";

	for ($row=0;$row<$num;$row++) {

		$hide = null;

		$permCheck = setPermCheck($siteModList["permissions"][$row]);

		if ($permCheck && !bitset_compare(BITSET,$permCheck,ADMIN)) $hide = 1;
		if ($siteModList["hidden"][$row]==1) $hide=1;

		$customPermCheck = setPermCheck($siteModList["custom_perm"][$row]);
		if ($customPermCheck && !bitset_compare(CUSTOM_BITSET,$customPermCheck,null)) $hide = 1;

		if (!$hide) {

			//show the translation for our module name if it exists
			$langmod = "_MT_".strtoupper($siteModList["link_name"][$row]);
			$langdesc = "_MTDESC_".strtoupper($siteModList["link_name"][$row]);

			//check for translations of the module name
			if (defined($langmod)) $modName = constant($langmod);
			else $modName = $siteModList["module_name"][$row];

			//check for translations of the module description
			if (defined($langdesc)) $modDesc = constant($langdesc);
			else $modDesc = $siteModList["module_description"][$row];

			$liImage = returnModImage($siteModList["link_name"][$row]);

			$string .= "<li>\n";
			$string .= "<table>";
			$string .= "<tr><td valign=top>\n";
			$string .= "<img src=\"".$liImage."\" style=\"vertical-align:bottom\">\n";
			$string .= "</td><td>\n";
			$string .= "<a 	class=\"moduleLink\" 
					href=\"index.php?module=".$module."&includeModule=".$siteModList["link_name"][$row]."\"
					>
					".$modName."
					</a>\n
					<br>
					".$modDesc."\n";
			$string .= "</td></tr>\n";
			$string .= "</table>\n";
			$string .= "</li>\n";

		}

	}

	$string .= "</ul>";

	return $string;
	
}

function includeModuleProcess($path) {

	if (!$path) return false;
	
	//determine our process file and our display file
	$process_path = $path."process.php";
	$function_path = $path."function.php";

	//load any optional function files in the module directory
	if (file_exists("$function_path")) include("$function_path");
	if (file_exists("$process_path")) include("$process_path");

}

function includeModuleDisplay($path) {

	if (!$path) return false;
	
	//determine our process file and our display file
	$style_path = $path."stylesheet.css";
	$js_path = $path."javascript.js";
	$display_path = $path."display.php";

	//these get called by our body.inc.php file
	if (file_exists("$style_path")) includeStylesheet("$style_path");
	if (file_exists("$js_path")) includeJavascript("$js_path");

	//define our display module if there is one
	if (file_exists("$display_path")) include("$display_path");;

}

                
                
function getCustomModPerms($module,$recursive="yes") {

        //just return here if it's not a recursive entry
        if ($recursive=="no") return $_SESSION["siteModInfo"][$module]["custom_perm"];

	//for custom permissions, we get the owning permissions as well
	$tmp = $_SESSION["siteModInfo"][$module];

	//extract our parent module names from our current module path
	$arr = explode("/",$tmp["module_path"]);

	//remove module/center/ and the trailing slash from the array
	array_shift($arr);
	array_shift($arr);
	array_pop($arr);

	//get the permissions for each module
	for ($row=0;$row<count($arr);$row++) {

		$mod = $arr[$row];

		if (is_array($customPermArr)) $customPermArr = array_merge($customPermArr,$_SESSION["siteModInfo"][$mod]["custom_perm"]);
		else $customPermArr = $_SESSION["siteModInfo"][$mod]["custom_perm"];

	}

	//get rid of duplicates
	if (is_array($customPermArr)) return array_values(array_unique($customPermArr));
	else return false;
	
}

//this function currently isn't used
function getRecursiveModInfo($module) {

	//for custom permissions, we get the owning permissions as well
	$tmp = $_SESSION["siteModInfo"][$module];

	//extract our parent module names from our current module path
	$arr = explode("/",$tmp["module_path"]);

	//remove module/center/ and the trailing slash from the array
	array_shift($arr);
	array_shift($arr);
	array_pop($arr);

	return $arr;

	
}


function checkModPerm($module,$bitset) {

	$permArr = $_SESSION["siteModInfo"][$module]["permissions"];
        $authOnly = $_SESSION["siteModInfo"][$module]["auth_only"];
        
        $ret = array();

        //the user should be logged in if auth_only is checked
        if ($authOnly && !$_SESSION["authorize"]) {

        	$ret["errorMessage"] = "You must be logged in to view this section";
        	$ret["permError"] = 1;

        	return $ret;
	
	}

	//run our perm check if there are perm requirements	
	if (is_array($permArr)) {

		$permCheck = setPermCheck($permArr);

		//run our permissions check
		if ($permCheck && !bitset_compare($bitset,$permCheck,ADMIN)) {

			if ($_SESSION["siteModInfo"][$module]["perm_message"])
				$msg = $_SESSION["siteModInfo"][$module]["perm_message"];
			else
				$msg = "You are not allowed to access this section";

			$ret["errorMessage"] = $msg;
			$ret["permError"] = 1;

		}
		
		return $ret;	

	} else return false;

}

function checkCustomModPerm($module,$bitset) {

	$permArr = $_SESSION["siteModInfo"][$module]["custom_perm"];

	//run our perm check if there are perm requirements	
	if (is_array($permArr)) {

		$ret = array();

		$permCheck = setPermCheck($permArr);

		//run our permissions check
		if ($permCheck && !bitset_compare($bitset,$permCheck,null)) {

			if ($_SESSION["siteModInfo"][$module]["perm_message"])
				$msg = $_SESSION["siteModInfo"][$module]["perm_message"];
			else
				$msg = "You are not allowed to access this section";

			$ret["errorMessage"] = $msg;
			$ret["permError"] = 1;

		}
		
		return $ret;	

	} else return false;

}

function modTreeMenu($module) {

	$arr = getRecursiveModInfo($module);

	$num = count($arr);

	for ($row=0;$row<$num;$row++) {
	
		$mod = &$arr[$row];

		//show the translation for our module name if it exists
		$langmod = "_MT_".strtoupper($_SESSION["siteModInfo"][$mod]["link_name"]);

		if (defined($langmod)) $modName = constant($langmod);
		else $modName = $_SESSION["siteModInfo"][$mod]["module_name"];

		$str .= "<a href=\"index.php?module=".$mod."\">".$modName."</a>";
		if ($row != ($num -1)) $str .= " --> ";	
	}

	return $str;

}

function showModLevel($path,$sort) {

	$newArray = array();

	if (!is_array($_SESSION["siteModList"]["owner_path"])) return false;
	
	if (!$sort) $sort = "module_name";

	//get the keys of all modules at this level
	$keys = array_keys($_SESSION["siteModList"]["owner_path"],$path);

	$fields = array_keys($_SESSION["siteModList"]);
	
	$arr = $_SESSION["siteModList"][$sort];
	asort($arr);
	
	$sortArray = array_keys($arr);
	$count = count($sortArray);
	$fieldCount = count($fields);
	
	for ($row=0;$row<$count;$row++) {
	
		$key = $sortArray[$row];
		
		if (in_array($key,$keys)) {
		
			for ($i=0;$i<$fieldCount;$i++) {
				
				$field = $fields[$i];
				$newArray[$field][] = $_SESSION["siteModList"][$field][$key];
					
			}		
		
		}
		
	}

	return $newArray;

}
/************************************************************
	This function generates a page with all sub
	modules and their descriptions.
************************************************************/

function showModTableAlt($path,$sort = null,$altStyle,$decorStyle) {

	if (!$path) return false;

	$siteModList = showModLevel($path,$sort);

	$string = "<table border=0 width=100%>
			<tr>\n";

	$counter = "0";

	$num = count($siteModList["module_name"]);
	$cell = "0";
	
	for ($row=0;$row<$num;$row++) {

		if ($cell=="2") {
			$string .= "</tr>\n<tr>";
			$cell = "0";
		}
		
		$hide = null;

		$permCheck = setPermCheck($siteModList["permissions"][$row]);
		if ($permCheck && !bitset_compare(BITSET,$permCheck,ADMIN)) $hide = 1;

		$customPermCheck = setPermCheck($siteModList["custom_perm"][$row]);
		if ($customPermCheck && !bitset_compare(CUSTOM_BITSET,$customPermCheck,null)) $hide = 1;

		if ($siteModList["hidden"][$row]==1) $hide=1;

		if (!$hide) {

			//show the translation for our module name if it exists
			$langmod = "_MT_".strtoupper($siteModList["link_name"][$row]);
			$langdesc = "_MTDESC_".strtoupper($siteModList["link_name"][$row]);

			//check for translations of the module name
			if (defined($langmod)) $modName = constant($langmod);
			else $modName = $siteModList["module_name"][$row];

			//check for translations of the module description
			if (defined($langdesc)) $modDesc = constant($langdesc);
			else $modDesc = $siteModList["module_description"][$row];

			$string .= "	<td width=50% valign=top style=\"padding-bottom:10px\">
					<div>
					<a $altStyle  
					href=\"index.php?module=".$siteModList["link_name"][$row]."\"
					>$decorStyle
					".$modName."
					</a>
					</div>
					<div style=\"width:80%;font: 1em Georgia,Arial,sans-serif;\">
					".$modDesc."
					</div>
					</td>\n";

			$cell++;

		}

		
	}

	$string .= "</tr></table>";

	return $string;
	
}


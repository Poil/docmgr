<?
/****************************************************************************
	xml.php
	
	Houses all our xml parsing functions which use simplexml when
	available.  This file can only be used with PHP 5
****************************************************************************/

/*****************************************************************************
	loadSiteStructure
	Finds all module.xml files, merges, and parses them.  If a 
	cache file exists, it uses that first.  The parsed xml
	is then turned into an associative array and returned
*****************************************************************************/
function loadSiteStructure($dir) {

        $arr = findModConfig($dir);

        $cacheFile = $dir."/module-cache.xml";

        //if a cached version of the structure exists, get it instead
        //of search through all of the directories
        if (file_exists($cacheFile)) {
                $str = file_get_contents($cacheFile);
                return return_modlist($str);
        }
    
        $str = outputXmlHeader();

        for ($row=0;$row<count($arr);$row++) {

        	$path = $arr[$row];

        	//dynamically add the path of the module.  This should be faster overall
        	$tmp = file_get_contents($arr[$row]);

        	$path = str_replace("module.xml","",$path);

        	$search = "<module>\n";
        	$replace = "<module>\n\t<module_path>".$path."</module_path>\n";
                $replace .= "\t<owner_path>".getOwnerPath($path)."</owner_path>\n";
                
        	$str .= str_replace($search,$replace,$tmp);

	}
	$str .= outputXmlFooter();

	return return_modlist($str);
  
}

/******************************************************************
	setPermDefines
	Loads and parses the permissions.xml file.  It then
	sets defines based on their bit position
******************************************************************/
function setPermDefines() {

	if (!$_SESSION["definePermArray"] || defined("DEV_MODE")) {

		if (defined("ALT_FILE_PATH")) $xmlFile = ALT_FILE_PATH."/config/permissions.xml";
		else $xmlFile = "config/permissions.xml";

		$str = file_get_contents("$xmlFile");
		$_SESSION["definePermArray"] = parseGenericXml("perm",$str);

	}

	$permArray = $_SESSION["definePermArray"];

	if ($permArray) {

		for ($row=0;$row<count($permArray["name"]);$row++) {

			$dn = $permArray["define_name"][$row];
			$bitVal = bitCal($permArray["bitpos"][$row]);
			define("$dn","$bitVal");

		}


	} else return false;

}

/******************************************************************
	setCustomPermDefines
	Loads and parses the customperm.xml file.  It then
	sets defines based on their bit position
******************************************************************/

function setCustomPermDefines() {

	if (!$_SESSION["defineCustomPermArray"] || defined("DEV_MODE")) {

		if (defined("ALT_FILE_PATH")) $xmlFile = ALT_FILE_PATH."/config/customperm.xml";
		else $xmlFile = "config/customperm.xml";

		if (file_exists("$xmlFile")) {

			$str = file_get_contents("$xmlFile");
			$_SESSION["defineCustomPermArray"] = parseGenericXml("perm",$str);

		} else $_SESSION["defineCustomPermArray"] = "ignore";

	}

	//skip if there are no perms to worry about
	if ($_SESSION["defineCustomPermArray"]=="ignore") return false;

	$permArray = $_SESSION["defineCustomPermArray"];

	if ($permArray) {

		for ($row=0;$row<count($permArray["name"]);$row++) {
	
			$dn = $permArray["define_name"][$row];
			$bitVal = bitCal($permArray["bitpos"][$row]);

			define("$dn","$bitVal");

		}

	} else return false;

}



/*********************************************************
	outputXmlHeader
	adds the appropriate header to our xml string
*********************************************************/
function outputXmlHeader() {

	$str .= "<data>\n";
      
	return $str;
    
}
  
  
/*********************************************************
	outputXmlFooter
	adds the appropriate footer to our xml string
*********************************************************/
function outputXmlFooter() {
  
	$str = "</data>\n\n";
	return $str;
  
}

/*********************************************************
	findModConfig
	returns an array of the relative paths to
	all our module.xml files
*********************************************************/
  
function findModConfig($directory,$resultArray=null) {

	if (!is_dir($directory)) return false;

	$arr = scandir($directory);

	foreach ($arr AS $file) {

		//skip directory markers
		if ($file=="." || $file==".." || $file==".svn") continue;

		//recurse into subdirectories
		if (is_dir($directory.$file)) $resultArray = findModConfig($directory.$file."/",$resultArray);
		elseif ($file=="module.xml") $resultArray[] = $directory.$file;

	}

	sort($resultArray);

	return $resultArray;

}

/*********************************************************
	return_modlist
	creates an associative array from our combined
	module.xml string
*********************************************************/
function return_modlist($str) {

	$info = array();
	$list = array();
	
	$xml = simplexml_load_string($str);

	$i = 0;

	foreach ($xml -> module AS $modArr) {

		$fields = null;
		$key = (string)$modArr -> link_name;

		//if this value has already been set, then there are duplicate modules
		//I.E., two modules with the same link name
		if (is_array($info[$key]))
			die("Module Error!  Two modules have the link name <b>\"".$key."\"</b>.");
		
		foreach ($modArr -> children() AS $field => $val) {

			if ($field == "count") continue;	

			//check to make sure we do not have show_module and hide_module in this entry
			if (is_array($list["show_module"][$i]) && is_array($list["hide_module"][$i]))
				die("Cannot set \"show_module\" and \"hide_module\" simultaneously in \"".$key."\" module.xml");

			//these have multiple values, handle that
			if (	$field=="permissions" || 
				$field=="custom_perm" ||
				$field=="show_module" ||
				$field=="hide_module") 	{
					$info["$key"][$field][] = (string)$val;
					$list[$field][$i][] = (string)$val;
			}
			else {				
				$info["$key"][$field] = (string)$val;
				$list[$field][$i] = (string)$val;
			}
			
		}

		$i++;

	}		

	$arr["info"] = $info;
	$arr["list"] = arrayMultiSort($list,"sort_order");

	return $arr;

}

/*********************************************************
	parseGenericXML
	parses a generic xml string into an associative
	array.  It does not handle multiple entries
	of a tag within an element.
*********************************************************/
function parseGenericXml($obj,$data,$multi = null) {

	$list = array();
	
	$xml = simplexml_load_string($data);

	$i = 0;

	foreach ($xml -> $obj AS $arr) {

		$fields = null;
		
		foreach ($arr -> children() AS $field => $val) {

			if ($field == "count") continue;	

			if (@in_array($field,$multi)) $list[$field][$i][] = (string)$val;
			else $list[$field][$i] = (string)$val;
			
		}

		$i++;

	}		

	return $list;

}

function xml2array($str) {

	$xml = simplexml_load_string($str);
	return simplexml2array($xml);	

}

//taken directly from the php website
function simplexml2array($xml) {

	if (get_class($xml) == 'SimpleXMLElement') {
		$attributes = $xml->attributes();
		foreach($attributes as $k=>$v) {
			if ($v) $a[$k] = (string) $v;
		}
		$x = $xml;
		$xml = get_object_vars($xml);
	}
	if (is_array($xml)) {
		if (count($xml) == 0) return (string) $x; // for CDATA
		foreach($xml as $key=>$value) {
			$r[$key] = simplexml2array($value);
		}
		if (isset($a)) $r['@'] = $a;// Attributes
		return $r;
	}
	return (string) $xml;
}

//this function creates an xml header with typeid so we can process and associate
//the proper response handler with the returned data
function createXmlHeader($type) {

	header("Content-Type: text/xml");

	//use a default dbencoding
	if (!defined("VIEW_CHARSET")) define("VIEW_CHARSET","ISO-8859-1");

	$str = "<?xml version=\"1.0\" encoding=\"".VIEW_CHARSET."\" standalone=\"yes\"?>\n";
	$str .= "<data>\n";
	$str .= "\t<typeid>".$type."</typeid>\n";

	return $str;

}

//puts a footer on the end of our xml data
function createXmlFooter() {

	return "</data>\n";

}

//encases the data in its xml tags and CDATA declaration
function xmlEntry($key,$data,$ignore = null) {

	$str = "<".$key.">";
	if ($data!=NULL) {

		//convert our db data to the proper encoding if able
		if (defined("DB_CHARSET") && defined("VIEW_CHARSET")) $data = charConv($data,DB_CHARSET,VIEW_CHARSET);

		if ($ignore) $str .= $data;
		else $str .= "<![CDATA[".$data."]]>";
	}

	$str .= "</".$key.">\n";

	return $str;

}

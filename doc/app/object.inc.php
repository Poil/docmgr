<?

/********************************************************************************************

	Filename:
		object.inc.php
      
                        
*********************************************************************************************/

function loadObjectList() {

	//find our object modules if that hasn't been done already
	if (!$_SESSION["siteObjectList"] || defined("DEV_MODE")) {

	  	$siteModList = $_SESSION["siteModList"];
	  	$objArr = array();
  
	  	//get the keys of all modules that are objects
	  	if (!is_array($siteModList["object"])) return false;

	  	//get our objects and our fields  
	  	$keys = array_keys($siteModList["object"],1);
	  	$fields = array_keys($_SESSION["siteModList"]);

	  	//create a module array containing just our objects
	  	foreach ($keys AS $key) {
	  		foreach ($fields AS $field) $objArr[$field][] = $siteModList[$field][$key];
		}

		//store for later
		$_SESSION["siteObjectList"] = $objArr;

                return $objArr;

	} else return $_SESSION["siteObjectList"];


}

//load any object-specific javascript files
function loadObjectJs() {

        //load our objects into our session variable
        $objArr = loadObjectList();

	$num = count($objArr["link_name"]);
	for ($i=0;$i<$num;$i++) {
	
		$modPath = $objArr["module_path"][$i];
		$js_path = $modPath."object/javascript.js";
		if (file_exists($js_path)) includeJavascript($js_path);

	}

}

//this function loads all objects and their function files
function loadObjects($clear = null) {

        //get out if the objects have already been loaded
        if (!$_SESSION["objectsLoaded"] || $clear) {

          //load our objects into our session variable
          $objArr = loadObjectList();
          $num = count($objArr["link_name"]);
          for ($i=0;$i<$num;$i++) {

                //if clear is set, we are also in a situation where we need to append alt_file_path to 
                //the beginning of the url
		$modPath = $objArr["module_path"][$i];
                if ($clear) $modPath = ALT_FILE_PATH.$modPath;
  		$objfunc_path = $modPath."object/function.php";
  		$objcom_path = $modPath."object/common.php";
  		if (file_exists($objfunc_path)) include($objfunc_path);	
  		if (file_exists($objcom_path)) include($objcom_path);

          }

          //mark that we've loaded objects so they don't get loaded again
          if (!$clear) $_SESSION["objectsLoaded"] = $num;

        }
       
        define("OBJECT_NUMBER",$_SESSION["objectsLoaded"]);

}


//this function loads all helper modules and their function files
function loadHelpers($info,$module,$bitset) {

	//find our object modules if that hasn't been done already for this object type
	if (!$_SESSION["siteHelperList"][$module] || defined("DEV_MODE")) {

	  	$siteModList = $_SESSION["siteModList"];
	  	$helpArr = array();
  
	  	//get the keys of all modules that are objects
	  	if (!is_array($siteModList["helper"])) return false;

	  	//get our objects and our fields  
	  	$keys = array_keys($siteModList["helper"],$module);
	  	$fields = array_keys($_SESSION["siteModList"]);

	  	//create a module array containing just our objects
	  	foreach ($keys AS $key) {
	  		foreach ($fields AS $field) $helpArr[$field][] = $siteModList[$field][$key];
		}

		//so they are displayed in the proper order
		if (is_array($helpArr["helper_sort"])) $helpArr = arrayMultiSort($helpArr,"helper_sort");

		$_SESSION["siteHelperList"][$module] = $helpArr;

	}

	$helpArr = $_SESSION["siteHelperList"][$module];
	$num = count($helpArr["link_name"]);
	$iconArr = array();

	for ($i=0;$i<$num;$i++) {

		$modPath = $helpArr["module_path"][$i];
		$modName = $helpArr["link_name"][$i];
		$skipPerm = $helpArr["helper_noperm"][$i];

		//check permissions against this object.  If they don't match (permError is returned), then don't show the icon
		$permError = null;
		if (!$skipPerm) {
			$ret = checkCustomModPerm($modName,$bitset);
			extract($ret);
			if ($permError) continue;
		}
				
		//for common objects, we just load their object_function file, their javascript, and their
		//css file.  The rest are loaded when that module is actually visited	
		$helpfunc_path = $modPath."helper/function.php";
		if (file_exists($helpfunc_path)) include_once($helpfunc_path);

		$className = $modName."Helper";
		$c = loadClassMethod($className,"loadHelper");
		if (is_object($c)) {
		    $id = $modName.$info["id"];
		    $ret = $c -> loadHelper($info,$bitset);
		    if ($ret) {
  		      $ret["id"] = $id;
		      $iconArr[] = $ret;
                    }
                }

	}

	$iconStr = null;

	//show up to 5 icons, otherwise add the dropdown
	if (count($iconArr) > 5) {

	  $iconStr .= "<div class=\"objIcons\"><div style=\"float:left\">\n";

	  //add the first four icons
	  for ($i=0;$i<4;$i++) {
	    $ret = $iconArr[$i];
            $iconStr .= "<img id=\"".$ret["id"]."\" src=\"".$ret["icon"]."\" onClick=\"".$ret["link"]."\" title=\"".$ret["title"]."\">&nbsp;&nbsp;";
	  }

	  $iconStr .= "</div>\n";


	  //add the dropdown icon
	  $moreid = "more".$info["id"];
	  $imgid = "moreimg".$info["id"];
	  $iconStr .= "<div class=\"makeMenu\" onMouseLeave=\"hideObjMenu('".$moreid."');\">
	                <img id=\"".$imgid."\" src=\"".THEME_PATH."/images/fileicons/more.png\" onMouseEnter=\"showObjMenu('".$moreid."');\">&nbsp;&nbsp;";
	  $iconStr .= "<div id=\"".$moreid."\">";

	  for ($i=4;$i<count($iconArr);$i++) {
	  
	    if (BROWSER=="ie") 
	      $str = "class=\"ieSubRow\" onMouseOver=\"colorRow(event);\" onMouseOut=\"uncolorRow(event)\";"; 
	  
	    $ret = $iconArr[$i];
	    $iconStr .= "<li ".$str." onClick=\"".$ret["link"].";\">\n";
            $iconStr .= "<img id=\"".$ret["id"]."\" src=\"".$ret["icon"]."\" >&nbsp;&nbsp;";
            $iconStr .= $ret["title"];
            $iconStr .= "</li>\n";
	  }

	  $iconStr .= "</div></div><div class=\"cleaner\">&nbsp;</div></div>\n";

	} else {
	  $iconStr = "<div class=\"objIcons\">\n";
  	  foreach ($iconArr AS $ret)
            $iconStr .= "<img id=\"".$ret["id"]."\" src=\"".$ret["icon"]."\" onClick=\"".$ret["link"].";hideObjMenu('".$moreid."');\" title=\"".$ret["title"]."\">&nbsp;&nbsp;";
          $iconStr .= "</div>\n";
        }
        
	if (!$iconStr) $iconStr = "&nbsp;";
	return $iconStr;

}

//this function creates a dropdown of all the objects we want to create
function createObjectDropdown($parentId) {

        //load our objects into our session variable
        $objArr = loadObjectList();

	$num = count($objArr["link_name"]);

	//create our form for creating new objects in the current collection
	$str = "<select name=\"createObject\" id=\"createObject\" 	
	                onChange=\"createNewObject('".$parentId."');\"
	                size=1
	                class=\"dropdownSmall\">
                        <option value=\"0\">"._ADD_NEW."...\n";

	for ($i=0;$i<$num;$i++) {

	  $link = &$objArr["link_name"][$i];
	  $show = &$objArr["show_obj_create"][$i];    

	  if (!$show || $show=="no" || $show=="false") continue;

	  //use the translated module title if available
	  $txt = "_MT_".strtoupper($link);
          if (defined($txt)) $name = constant($txt);	  
          else $name = &$objArr["module_name"][$i];
          
	  $str .= "<option value=\"".$link."\">".$name."\n";

	}
	
	$str .= "</select>\n";
	
	return $str;

}


//this function requires that loadObjects() has already been called by the parent module
function displayObject($info,$mode,$style,$catInfo = null) {

	//get the function to display our object.  This will return arr["link"] and arr["icon"]
	if ($mode=="thumb") return displayThumb($info);
	else return displayList($info,$style,$catInfo);
	
}

/***********************************************************
  If a class an method exists, this returns the class 
  object.  Otherwise it returns false;
***********************************************************/
function loadClassMethod($className,$methodName) {

  if (!$className) return false;
  if (!$methodName) return false;

  if (!class_exists($className)) return false;
  $c = new $className;

  if (!method_exists($c,$methodName)) return false;
  else return $c;  

}

/***********************************************************
	Display our objects as a list
***********************************************************/
function displayList($info,$style,$catInfo) {

	//return the object type and load
	$objectType = $info["object_type"];
	if (!$objectType) return false;

	$className = $objectType."Object";
	$c = loadClassMethod($className,"listDisplay");
	if (is_object($c)) $optArr = $c->listDisplay($info);
	else return false;

	//get permissions so we can display our checkbox
	$bitset = $info["bitset"];
	if (!$bitset) $bitset = OBJ_EDIT;
	if (bitset_compare(BITSET,ADMIN,null)) $bitset = OBJ_ADMIN;
	if ($info["object_owner"]==USER_ID) $bitset = OBJ_ADMIN;

	//get our helper icons
	$iconStr = loadHelpers($info,$objectType,$bitset);

	$datemod = date_view($info["status_date"]);
	if (!$datemod) $datemod = "&nbsp;";

	//show our file size
	if ($objectType=="file" || $objectType=="document") $size = displayFileSize($info["filesize"],1);
	else $size = "0 KB";

	$rank = null;
	$summary = "<div>".$info["summary"]."</div>\n";
	
	//show the breadcrumbs to the file
	if ($_REQUEST["module"]=="find" || $_REQUEST["module"]=="searchview") {

		$breadcrumbs = simpleNavList(null,$info["parent_id"],$catInfo)."\n";

		if (defined("TSEARCH2_INDEX") && $_SESSION["showRank"])
			$rank = "<td class=\"".$style."\" align=center>".intVal($info["rank"] * 100)."%</td>\n";
	
	}

	$string = "<tr>";

	if (bitset_compare($bitset,OBJ_MANAGE,OBJ_ADMIN))
		$string .= "	<td align=center class=\"".$style."\">
				<input type=checkbox name=\"objectAction[]\" id=\"objectAction[]\" value=\"".$info["id"]."\">
				</td>\n";
	else
		$string .= "<td class=\"".$style."\">&nbsp;</td>\n";

	//show the icon returned from our object function
	$string .= "	<td align=center width=25 class=\"".$style."\">
			<img src=\"".$optArr["icon"]."\" border=0>
			</td>\n";

        if ($optArr["target"]) $target = "target=\"".$optArr["target"]."\"";
        else $target = null;

        if (count($info["related"]) > 0) {
          $related = "<div class=\"objectRelated\">"._RELATED_OBJECTS.": ";
          foreach ($info["related"] AS $r) {

            //the file properties module doesn't follow the naming scheme of the other objects
            $mod = &$r["object_type"];
            if ($mod=="file") $modprop = "fileproperties";
            else if ($mod=="collection") $modprop = "colprop";
            else if ($mod=="document") $modprop = "docprop";
            else $modprop = $mod."prop";
            $related .= "<a href=\"index.php?module=".$mod."&includeModule=".$modprop."&objectId=".$r["id"]."\">".$r["name"]."</a>, ";
          }
          $related = substr($related,0,strlen($related)-2);
          $related .= "</div>\n";
        }

	//assemble our object name with the link returned from our object function
	$string .= "<td class=\"".$style."\"><a href=\"".$optArr["link"]."\" ".$target.">
				".stripslashes($info["name"])."
				</a>".$d."
				<div class=\"objectDescription\">
				".stripslashes($summary)."
				</div>
                                ".$related."
                                ".$breadcrumbs."
                              </td>
				";


	if ($rank) $string .= $rank;	
	$string .= "<td class=\"".$style."\" align=center>".$size."</td>\n";
	$string .= "<td class=\"".$style."\" align=center>".$datemod."</td>\n";
	$string .= "<td class=\"".$style."\" style=\"white-space:nowrap\">".$iconStr."</td>\n";
	$string .= "</tr>\n";

	return $string;

}


/*****************************************************
	display our objects as thumbnails
*****************************************************/
function displayThumb($info) {

	//return the object type and load
	$objectType = $info["object_type"];
	if (!$objectType) return false;

	$className = $objectType."Object";
	$c = loadClassMethod($className,"thumbDisplay");
	if (is_object($c)) $optArr = $c->thumbDisplay($info);
	else return false;

	//set our permissions for object related functions
	if (!$bitset && bitset_compare(BITSET,INSERT_OBJECTS,ADMIN)) $bitset = OBJ_EDIT;
	if (bitset_compare(BITSET,ADMIN,null)) $bitset = OBJ_ADMIN;
	if ($info["object_owner"]==USER_ID) $bitset = OBJ_ADMIN;

	//get our helper icons
	$iconStr = loadHelpers($info,$objectType,$bitset);
	
	if (bitset_compare($bitset,OBJ_MANAGE,OBJ_ADMIN))
		$checkbox = "<input type=checkbox name=\"objectAction[]\" id=\"objectAction[]\" value=\"".$info["id"]."\">";
	else
		$checkbox = "&nbsp;\n";

	//use an href if passed from our obj options, otherwise create the link
	$link = $optArr["link"];
	$thumb = $optArr["icon"];

	if ($optArr["class"]) $class = $optArr["class"];
	else $class = "fileImage";

        if ($optArr["target"]) $target = "target=\"".$optArr["target"]."\"";
        else $target = null;
	
	$string = "	<div class=\"thumbContainer\">
			<center>
			<div style=\"padding-left:5px;padding-right:5px;\">
			".$iconStr."
			</div>
			<div style=\"width:100px;height:105px;\">
			<a href=\"".$link."\" ".$target.">
			<img src=\"".$thumb."\" class=\"".$class."\" border=0>
			</a>
			</div>
			<a href=\"".$link."\" ".$target.">
			".$info["name"]."
			</a>
			<br>
			".$checkbox."
			</center>
			</div>
			";
		

	return $string;

}

//get the user's bitset for the object
function returnObjBitset($objId,$arr) {

  if (!is_array($arr)) return false;

  //return all permissions that pertain to this user and this object

  //first, narrow it down to the object
  $keys = @array_keys($arr["object_id"],$objId);

  if (count($keys) == 0) return false;

  $groupArray = @explode(",",USER_GROUPS);
  $bitset = null;

  //now loop through our keys and start stacking up permissions for this user or their groups
  foreach ($keys AS $key) {

    //first get the account_id
    if ($arr["account_id"][$key]==USER_ID) $bitset |= $arr["bitset"][$key];

    if ($arr["group_id"][$key]) {
      if (@in_array($arr["group_id"][$key],$groupArray)) $bitset |= $arr["bitset"][$key];
    }

  }

  return $bitset;

}


/******************************************************************
	output all our files in the desired format
******************************************************************/
function showObjects($conn,$data,$view_parent,$getCol = null)	{

	//get our collections if necessary
	if ($getCol) {
		$sql = "SELECT DISTINCT id,name,parent_id,object_type FROM dm_view_collections";
		$catInfo = total_result($conn,$sql);
	}

	//if the user isn't an admin, requery the database and get permissions for our current objects
	if ($data["count"] > 0 && !bitset_compare(BITSET,ADMIN,null)) {
		$idArr = array();
		foreach ($data AS $curObj) if ($curObj["id"]) $idArr[] = $curObj["id"];
		$sql = "SELECT * FROM dm_object_perm WHERE object_id IN (".implode(",",$idArr).");";
		$permArr = total_result($conn,$sql);
	}


	//process how we'll sort our files
	if ($_REQUEST["sortField"]) $sortField = $_REQUEST["sortField"];
	if ($_REQUEST["sortDir"]) $sortDir = $_REQUEST["sortDir"];
        
	$display = "<div style=\"width:100%;\">\n";

	//just show the select_all icon for thumbnails
	if ($_SESSION["pageView"]=="thumb") {

		$display .= "
			<table width=100% cellpadding=0 cellspacing=0 align=center border=0 class=\"searchResultsTable\">
			<tr><td class=\"searchResultsLeftTitle\" width=100% >
				&nbsp;&nbsp;<img src=\"".THEME_PATH."/images/icons/selectall.png\" onClick=\"selectObjects('all');\" border=0 height=15 title=\""._SELECT_ALL_OBJECTS."\">
			</td><td class=\"searchResultsRightTitle\">
				&nbsp;
			</td></tr>
			<tr><td>
			";

	//show the selectall and the column headers for the list
	} else {

		if ($data["count"] > 0 || $_REQUEST["module"]!="find") {

			if (defined("TSEARCH2_INDEX") && $_SESSION["showRank"]) 
				$rankText = "	<td class=\"searchResultsTitle\" align=center width=10%>
								<a href=\"javascript:changeSort('rank');\">"._RANK."</a>
								</td>";
			else $rankText = null;

			//display our results
			$display .= "		
				<table width=100% cellpadding=0 cellspacing=0 align=center border=0 class=\"searchResultsTable\">
				<tr><td class=\"searchResultsLeftTitle\" width=30 align=center>
					<img src=\"".THEME_PATH."/images/icons/selectall.png\" onClick=\"selectObjects('all');\" border=0 height=15 title=\""._SELECT_ALL_OBJECTS."\">
				</td><td class=\"searchResultsTitle\" colspan=2 align=center>
					<a href=\"javascript:changeSort('name');\">"._NAME."</a>
				</td>
					".$rankText."
				<td class=\"searchResultsTitle\" align=center width=10%>
					<a href=\"javascript:changeSort('size');\">"._SIZE."</a>
				</td><td class=\"searchResultsTitle\" align=center width=11%>
					<a href=\"javascript:changeSort('edit');\">"._EDITED."</a>
				</td><td class=\"searchResultsRightTitle\" align=center width=\"25%\" style=\"white-space:nowrap\">
					"._OPTIONS."
				</td></tr>
				";


		}
	}

	//stop here if there's nothing to display
	if ($data["count"]=="0") {
		$display .= "<tr><td colspan=5>
					<div class=\"errorMessage\" style=\"padding:10px;\">
					"._NO_RESULTS."
					</div>
					</td></tr>";
		return $display;
	}

	/*
	//do we have two instances of a file with different permissions for this user.
	//searchCount is distinct on the id, so it is an accurate unique rep of our returned
	//ids.  count is distinct on all fields, so our dups could show up if a user
	//has one setting while the user's group has the other.  Currently we take the highest
	if ($_SESSION["searchCount"] < $data["count"]) {
		$dupPermFile = 1;
		$dupArr = array();
	}
	else $dupPermFile = null;
	*/

	$counter = 0;

	//spit out our search results
	for ($row=0;$row<$data["count"];$row++) {

		//get the bitset for this object if the user isn't an admin
		if (!bitset_compare(BITSET,ADMIN,null)) 
			$data[$row]["bitset"] = returnObjBitset($data[$row]["id"],$permArr);
		
		//style for the list
		$test = $counter % 2;
		if ($test == 1) $style = "searchResultsEven";
		else $style = "searchResultsOdd";

		$display .= displayObject($data[$row],$_SESSION["pageView"],$style,$catInfo);
		$counter++;

	}


	if ($_SESSION["pageView"]=="thumb") $display .= "</td></tr>\n";
	$display .= "</table>\n</div>\n\n";

	return $display;

}


function createViewSwitch() {

	if (defined("THUMB_SUPPORT")) {
	
		if ($_SESSION["pageView"]=="thumb")
			$string .= "<a href=\"javascript:switchView('list');\" class=main>"._VIEW_LIST."</a>";
		else
			$string .= "<a href=\"javascript:switchView('thumb');\" class=main>"._VIEW_THUMB."</a>";

	} else $string .= null;

	return $string;

}

function createFindNav($searchString,$searchCount,$timeCount,$curPage,$limitResults) {

		if ($searchCount > 0) {

			//searchCount, curPage, limitResults,
			if ($searchCount > 0) {
				$functionBar = "&nbsp;&nbsp;";
				$functionBar .= "<a href=\"javascript:void(0);\" onClick=\"return moveObject();\">"._MOVE."</a>";
				$functionBar .= "&nbsp;&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;&nbsp;";

				if (!defined("RESTRICTED_DELETE") || bitset_compare(BITSET,ADMIN,null)) {
					$functionBar .= "<a href=\"javascript:deleteObjects();\" >"._DELETE."</a>";
					$functionBar .= "&nbsp;&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;&nbsp;";
				}

			}

			if ($limitResults!==NULL) $numPages = $searchCount / $limitResults;
			else $numPages = 1;

			if ($curPage===NULL) $curPage = 1;

			$first = (($curPage - 1) * $limitResults) + 1;
			$second = $curPage * $limitResults;

			//show result string correctly when showing less than our increment
			if ($second > $searchCount) $second = $searchCount;

			$toolBar = _RESULTS." $first - $second "._OF." ".$searchCount." "._FOR." \"".stripsan($searchString)."\".";
			$toolBar .= "&nbsp;&nbsp;"._SEARCH_TOOK." ".$timeCount." "._SECONDS.".";

			if ($second>$searchCount) {
				$num = $second - $searchCount;
				$diff = $limitResults - $num;
				$second = $first + $diff - 1;
			}


			if (strstr($numPages,".")) $numPages = intVal($numPages) + 1;

			//if we have more pages than the config limit
			if ($numPages > PAGE_RESULT_LIMIT) {

				$pageHalf = intVal(PAGE_RESULT_LIMIT / 2);
	
				if ($curPage>$pageHalf) $pageBegin = $curPage - $pageHalf;
				else $pageBegin = 1;

				$pageEnd = $curPage + $pageHalf;

				//show num count on the last page
				if ($pageEnd>$numPages) $pageEnd = $numPages;

				//only show up to the limit if we have less pages than our increment count
				if ($pageEnd < PAGE_RESULT_LIMIT) $pageEnd = PAGE_RESULT_LIMIT;

			//if we have less pages than our limit
			} else {

				$pageBegin = 1;
				$pageEnd = $numPages;

			}
			
			$pageBar = null;
			$nextPage = $curPage + 1;
			$prevPage = $curPage - 1;

			if ($curPage>1 && $numPages > 1) {
				$pageBar .= "	<a href=\"javascript:jumpPage('1');\">
						<img src=\"".THEME_PATH."/images/active_firstpage.gif\" border=0></a>
						&nbsp;
						<a href=\"javascript:jumpPage('".$prevPage."');\">
						<img src=\"".THEME_PATH."/images/active_prevpage.gif\" border=0></a>
						&nbsp;
						";

			} else {
				$pageBar .= "	<img src=\"".THEME_PATH."/images/inactive_firstpage.gif\" border=0></a>
						&nbsp;
						<img src=\"".THEME_PATH."/images/inactive_prevpage.gif\" border=0></a>
						&nbsp;
						";
			}

			//create the toolbar for jumping from page to page
			for ($row=$pageBegin;$row<=$pageEnd;$row++) {

				if ($row==$curPage) $pageBar .= "&nbsp;<b>".$row."</b>&nbsp;";
				else $pageBar .= "&nbsp;<a href=\"javascript:jumpPage('".$row."');\" class=main>".$row."</a>&nbsp;";

			}
			
			$pageBar .= "&nbsp;";
			
			if ($numPages > 1 && $curPage < $numPages) {
				$pageBar .= "	<a href=\"javascript:jumpPage('".$nextPage."');\">
						<img src=\"".THEME_PATH."/images/active_nextpage.gif\" border=0></a>
						&nbsp;
						<a href=\"javascript:jumpPage('".$numPages."');\">
						<img src=\"".THEME_PATH."/images/active_lastpage.gif\" border=0></a>
						&nbsp;
						";
			} else {
				$pageBar .= "	<img src=\"".THEME_PATH."/images/inactive_nextpage.gif\" border=0></a>
						&nbsp;
				                <img src=\"".THEME_PATH."/images/inactive_lastpage.gif\" border=0></a>
						&nbsp;
						";
			}


		}


		$arr = array();
		$arr["function"] = $functionBar;
		$arr["page"] = $pageBar;
		$arr["toolbar"] = $toolBar;

		return $arr;
		
}


/****************************************************************************
  Object creation functions
****************************************************************************/

/*************************************************************
  createObject
    The parent function for creating objects within docmgr

    $opt contains the data for the objects. 
    Variables from the array:
      conn -> db connection resource
      name -> name of the object
      summary -> summary of the object
      parentId -> id or array of ids this object will belong to
      objectType -> module name of the object (file,collection...)
      objectOwner -> id of the account owning the object

    The objectId variable will be set and added to the opt
    array to be passed on to any function registered 
    for the object creation (like fileCreate or urlCreate)

*************************************************************/

function createObject($opt) {

    extract($opt);

    //check for required fields
    if (!$objectType) return false;
    if (!$name) return false;

    //make sure there isn't already an object with this name
    if (!checkObjName($conn,$name,$parentId)) return false;


    //make sure we can add objects at the root level
    if (!$parentId && defined("ADMIN_ROOTLEVEL") && !bitset_compare(BITSET,ADMIN,null)) return false;
    //beginTransaction($conn);

    //insert into the main object table
    $option = null;
    $option["name"] = $name;
    $option["summary"] = $summary;
    $option["version"] = 1;
    $option["create_date"] = date("Y-m-d H:i:s");
    $option["status_date"] = date("Y-m-d H:i:s");
    $option["status"] = "0";
    $option["object_type"] = $objectType;
    $option["object_owner"] = $objectOwner;

    //insert the collection
    if ($objectId = dbInsertQuery($conn,"dm_object",$option)) {

      //figure out the directory levels for this object.  We do this here for all objects 
      //until I think of a way that doesn't involve sequences
      $level1 = db_increment_seq($conn,"level1");
      $level2 = db_increment_seq($conn,"level2");
      storeObjLevel($conn,$objectId,$level1,$level2);
      $opt["objDir"] = $level1."/".$level2;

      //log that the object was created
      logEvent($conn,OBJ_CREATED,$objectId);

      //turn parentId into an array if not for the inserts
      if (!is_array($parentId)) $parentId = array($parentId);

      //setup the parent link for the collection
      $sql = null;
      foreach ($parentId AS $parent)
		$sql .= "INSERT INTO dm_object_parent (object_id,parent_id) VALUES ('$objectId','$parent');";
      if ($sql) db_query($conn,$sql);

      //inherit the parent's permissions
      inheritParentPerms($conn,$objectId,null);

      //add objectId to our parameter array to pass along
      $opt["objectId"] = $objectId;

      //now hand off to our other functions for the object-specific processing
      $className = $objectType."Object";
      $c = loadClassMethod($className,"runCreate");
      if (is_object($c)) {
        if (!$c->runCreate($opt)) {
          deleteObject($conn,$objectId);
          return false;
        }
      }

      //index this object
      indexObject($conn,$objectId,$objectOwner,$notifyUser);

      //thumbnail/preview this object
      thumbObject($conn,$objectId,$thumbForeground);

      //send out an alert.  We send the alert out for the object, even though the
      //parent triggers it.  This allows a user to see the new file right away
      foreach ($parentId AS $parent) sendEventNotify($conn,$objectId,OBJ_CREATE_ALERT,$parent);
      
    } 

    //endTransaction($conn);
	
    if ($objectId) return $objectId;
    else return false;

}


/****************************************************************************
  Object creation functions
****************************************************************************/

/*************************************************************
  updateObject
    The parent function for updating objects within docmgr

    $opt contains the data for the objects. 
    Variables from the array:
      conn -> db connection resource
      name -> name of the object
      summary -> summary of the object
      objectId -> id of the object we're updating

*************************************************************/

function updateObject($opt) {

    extract($opt);

    //check for required fields
    if (!$objectType) return false;
    if (!$name) return false;
    if (!$objectId) return false;

    //make sure we are not renaming to something already there
    if (!checkObjName($conn,$name,null,$objectId)) return false;

    //if it's "No summary available", blank it
    if ($summary==_NO_SUMMARY_AVAIL) $summary = null;

    //add in the object path
    $opt["objDir"] = returnObjPath($conn,$objectId);

    //insert into the main object table
    $option = null;
    $option["name"] = $name;
    $option["summary"] = $summary;
    $option["where"] = "id='$objectId'";

    //insert the collection
    if (dbUpdateQuery($conn,"dm_object",$option)) {

      //now hand off to our other functions for the object-specific processing
      $className = $objectType."Object";
      $c = loadClassMethod($className,"runUpdate");
      if (is_object($c)) {
        if (!$c->runUpdate($opt)) return false;
      }

      //index this object
      $arr = array();
      $arr["name"] = $name;
      $arr["summary"] = $summary;
      indexObject($conn,$objectId,$objectOwner,null,$arr);

      //thumbnail/preview this object
      thumbObject($conn,$objectId,$thumbForeground);

      //log the event
      logEvent($conn,OBJ_PROP_UPDATE,$objectId);

    } 

    //endTransaction($conn);
	
    return true;

}


//this function handles the removal of any object from the system.  It passes off to each
//object's own deletion function for things not handled here
function deleteObject($conn,$objectId,$parentId = null) {

	beginTransaction($conn);

	//if parentId is passed, make sure this object doesn't exist in more than one collection
        //if it does, just remove the link to that collection and exit
        if ($parentId) {
        
          $sql = "SELECT object_id FROM dm_object_parent WHERE object_id='$objectId'";
          $info = list_result($conn,$sql);

          //more than one result, delete our current reference and runaway
          if ($info["count"] > 1) {
            $sql = "DELETE FROM dm_object_parent WHERE object_id='$objectId' AND parent_id='$parentId'";
            if (db_query($conn,$sql)) return true;
            else return false;
          }
        
        }

	//figure out what kind of object this is
	$sql = "SELECT name,object_type,status FROM dm_object WHERE id='$objectId';";
	$info = single_result($conn,$sql);
	
	//if we can't find the obj, there's been an error.  Get out!!!	
	if (!$info) return false;

	//get out if the object is checked out
	if ($info["status"]==1) {
	  define("ERROR_MESSAGE",$name." "._CHECKED_OUT);
	  return false;
        }

	$objectType = $info["object_type"];

	/******************************************************
		perform common processing for all objects
	******************************************************/

	//now hand off to our other functions for the object-specific processing
	$className = $objectType."Object";

	$c = loadClassMethod($className,"runDelete");
	if (is_object($c)) $c->runDelete($conn,$objectId);

	//if there's an error message, get out
	if (defined("ERROR_MESSAGE")) return false;

        $sql = null;

        //pending indexing removal from the queue	
	$sql .= "DELETE FROM dm_index_queue WHERE object_id='".$objectId."';";
        
	//permission removal
	$sql .= "DELETE FROM dm_object_perm WHERE object_id='$objectId';";

	//parent collection removal
	$sql .= "DELETE FROM dm_object_parent WHERE object_id='$objectId';";

	//log removal (people want logs to remain)
	//$sql .= "DELETE FROM dm_object_log WHERE object_id='$objectId';";

	//indexed content removal
	$sql .= "DELETE FROM dm_index WHERE object_id='$objectId';";

	//keywords content removal
	$sql .= "DELETE FROM dm_keyword WHERE object_id='$objectId';";

	//alert removal
	$sql .= "DELETE FROM dm_alert WHERE object_id='$objectId';";

	//bookmark removal
	$sql .= "DELETE FROM dm_bookmark WHERE object_id='$objectId';";

	//discussion removal
	$sql .= "DELETE FROM dm_discussion WHERE object_id='$objectId';";

	//subscription
	$sql .= "DELETE FROM dm_subscribe WHERE object_id='$objectId';";

	//related files
	$sql .= "DELETE FROM dm_object_related WHERE object_id='$objectId' OR related_id='$objectId';";

	//primary object removal and associated table entry removal
	$sql .= "DELETE FROM dm_object WHERE id='$objectId'; ";

	//run the query
	if (!db_query($conn,$sql)) return false;

	endTransaction($conn);

	//send out an alert
	sendEventNotify($conn,$objectId,OBJ_REMOVE_ALERT);

	//if we get this far, return true;
	return true;

}



//check for an existing object with the new object's name
function checkObjName($conn,$name,$parentId,$objectId = null) {

  //first check to see if all our characters are valid
  if (defined("DISALLOW_CHARS")) {
    //treat both strings as arrays, make sure no characters in name are in our checkstr array  
    //yes, I know strings are arrays.  I did it this way for cleaner code
    $checkArr = strtoarray(DISALLOW_CHARS);
    $nameArr = strtoarray($name);

    $len = strlen($name);
    for ($i=0;$i<$len;$i++) {
      if (in_array($nameArr[$i],$checkArr)) {
        define("ERROR_MESSAGE",_INVALID_CHAR_IN_NAME." ".DISALLOW_CHARS);
        return false;
      }
    }
    
  }  
    
  //if we have an object with no parents, get the parents
  if ($parentId==NULL && $objectId) {
  
    $sql = "SELECT parent_id FROM dm_view_objects WHERE id='$objectId'";
    $list = total_result($conn,$sql);
    
    $parentId = $list["parent_id"];
  
  }

  //make sure parentId is an array before we continue
  if ($parentId==NULL) $parentId = "0";
  if (!is_array($parentId)) $parentId = array($parentId);

  $sql = "SELECT id FROM dm_view_objects WHERE name='".$name."' AND parent_id IN (".implode(",",$parentId).")";
  
  //if objectId is passed, we are doing an update and want to make sure the updated name doesn't
  //exist with another object
  if ($objectId) $sql .= " AND id!='$objectId'";

  $exists = num_result($conn,$sql);
  if ($exists > 0) {
    //get the name of the parents for the error message
    if (!$parentId[0])
      $parentName = _HOME;
    else {
      
      $sql = "SELECT name FROM dm_object WHERE id IN (".implode(",",$parentId).")";
      $info = total_result($conn,$sql);
      $parentName = implode("\" "._OR." \"",$info["name"]);

    }
    
    $msg = _OBJ_WITH_NAME." \"".$name."\" "._ALREADY_EXISTS_IN." \"".$parentName."\"";
    define("ERROR_MESSAGE",$msg);
    return false;
       
  }

  return true;
       
}


/**********************************************************
  checkObjType
    Here we verify the module we are working on
    in designed to operate on the object being
    passed to it.
**********************************************************/
function checkObjType($conn,$module,$objectId) {

  //sanity checking
  if (!$module) return false;
  if (!$objectId) return false;

  $modPath = $_SESSION["siteModInfo"][$module]["module_path"];
  $modArr = explode("/",$modPath);
  
  //remove modules/center/ from the array
  array_shift($modArr);
  array_shift($modArr);

  //get the object type for this object
  $sql = "SELECT object_type FROM dm_object WHERE id='$objectId'";
  $info = single_result($conn,$sql);

  //get out if something didn't work right
  if (!$info) return false;
    
  //loop through our modules in our path and see if our object_type is in there
  //if it is, then we are in a subdirectory of the object's owning module
  foreach ($modArr AS $mod) {
    if ($mod==$info["object_type"]) return true;  
  }
  
  //if we make it this far, we didn't find a match so return false;
  return false;

}


/************************************************************
  returns true if a module is also an object
************************************************************/
function isObject($module) {

  $objArr = loadObjectList();
  if (in_array($module,$objArr["link_name"])) return true;
  else return false;  

}

function returnObjectViewer($module) {

  $modViewer = $_SESSION["siteModInfo"][$module]["viewer"];  

  if ($modViewer) return $modViewer;
  else return false;

}

//storeObjLevel inserts a record in the database with the two level ids
//the object will use when writing files to the filesystem
function storeObjLevel($conn,$objId,$level1,$level2) {

  //this should never change for an object, but we'll pass a delete query just to be safe"
  $sql = "DELETE FROM dm_dirlevel WHERE object_id='$objId';
          INSERT INTO dm_dirlevel (object_id,level1,level2) VALUES ('$objId','$level1','$level2');";
  if (db_query($conn,$sql)) return true;
  else return false;
  
}

//returnObjPath returns the directory path to our object based on
//it's stored level values.  This does not include the FILE_DIR/DATA_DIR/DOC_DIR... bit
function returnObjPath($conn,$objId) {

  //get the values for this object
  $sql = "SELECT level1,level2 FROM dm_dirlevel WHERE object_id='$objId'";
  $info = single_result($conn,$sql);
  
  //get out if nothings found
  if (!$info) return false;

  //merge into a dir structure and return  
  return $info["level1"]."/".$info["level2"];
  
}


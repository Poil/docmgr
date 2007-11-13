<?

function removeDoubleSpaces($string) {

	while(strstr($string,"  ")) $string = str_replace("  "," ",$string);

	return $string;
}


function createDateFilter($opt) {

	extract($opt);

	$date1 = dateProcess($date1)." 00:00:00";
	$date2 = dateProcess($date2)." 23:59:59";

	//set whether we are searching by creation date or last modified date
	if ($mod_option=="enter") $date_string="create_date";
	else $date_string="status_date";

	if ($date_option=="single") {
		$date_string = "date_trunc('day', ".$date_string.")";
		$sqlDate.=" AND $date_string = '$date1'";
	}
	elseif ($date_option=="period") 
		$sqlDate.=" AND ($date_string >='$date1' AND $date_string <= '$date2')";
	
	elseif ($date_option=="before") 
		$sqlDate.=" AND $date_string <= '$date1'";	

	elseif ($date_option=="after") 
		$sqlDate.=" AND $date_string >= '$date1'";

	$sqlDate = substr($sqlDate,4);
	
	$sql = "SELECT id FROM dm_object WHERE $sqlDate";
	
	return $sql;
	
}

function createCollectionFilter($opt) {

	//get all our options
	extract($opt);

	//make an array if we only have a singular column value
	if (strstr(",",$limitColValue)) $colArr = explode(",",$limitColValue);
	else $colArr = array($limitColValue);

	//merge all selected collections and their children into one array
	for ($i=0;i<count($cv);$i++) 
		$colArr = array_merge($colArr,returnColChildren($conn,$cv[$i]));
	
	$colArr = array_values(array_unique($colArr));
	
	$sql = "SELECT object_id AS id FROM dm_object_parent WHERE parent_id IN (".implode(",",$colArr).")";

	return $sql;

}

function createTypeSql($string) {

	$arr = array();

	$pos = strpos($string,"objtype:");
	if ($pos!==FALSE) {
	
		$pos+=8;	//get past "objtype:"
		$str = substr($string,$pos);
		$pos2 = strpos($str," ");	//find the space after the type

		if ($pos2===FALSE) $type = $str;
		else $type = substr($str,0,$pos2);

		$arr["sql"] = "SELECT id FROM dm_object WHERE object_type='$type';";

		//remove our object type from what we search for
		$string = str_replace("objtype:".$type,"",$string);
	
	}

	$arr["string"] = $string;

	return $arr;

}

function execSearch($opt) {

	//get all our options
	extract($opt);

	//store our criteria in an array
	$sqlArr = array();
	
	/***********************************************************
		process our search filters
	***********************************************************/
	//our date filter
	if ($date_option!="any") $sqlArr[] = createDateFilter($opt);

	//handle any "objtype:" in the sql statement
	$arr = createTypeSql($string);
	$opt["string"] = $arr["string"];
	if ($arr["sql"]) $sqlArr[] = $arr["sql"];

	//what type of objects do we display
	if ($showObjects && count($showObjects) < OBJECT_NUMBER) 
		$sqlArr[] = "SELECT id FROM dm_object WHERE object_type IN ('".implode("','",$showObjects)."')";	

	//our account filter
	if ($limitAccountValue) 
		$sqlArr[] = "SELECT id FROM dm_object WHERE object_owner IN (".$limitAccountValue.")";

	//our collection filter
	if ($limitColValue) $sqlArr[] = createCollectionFilter($opt);

	//perm string filter
	if (!bitset_compare(BITSET,ADMIN,null)) {
		$permStr = permString($conn);
		$sqlArr[] = $sql = "SELECT id FROM dm_view_perm WHERE ".$permStr;
	}

	/************************************************
		put together our query string	
	************************************************/
	if ($opt["searchType"]=="keyword") $sql = createMetaSql($opt);
	else {
	
		if (defined("TSEARCH2_INDEX")) {
			$arr = createTsearch2Sql($opt);
			$sql = $arr["sql"];
			$rank = $arr["rank"];
		}
		else {
			$sql = createNormalSql($opt);
			$rank = null;
		}

	}

	//merge our query arrays	
	if (count($sqlArr) > 0) $sql .= " INTERSECT ".implode(" INTERSECT ",$sqlArr);
	
	/********************************************************
		layout our sorting
	********************************************************/
	if ($sortField) {
	
		if ($sortField == "edit") $sortField = "status_date";
		else if ($sortField == "size") $sortField = "filesize::numeric";
		else if ($sortField == "rank") $sortField = "rank";
		
		//we do this to make sure nothing funky can be passed by the url
	        if ($sortDir == "descending") $sortDir = "DESC";
		else $sortDir = "ASC";

	}

	$time1 = getmicrotime();

	//run our query to return ids only.  We'll query for details later
	if (!$_SESSION["searchCount"] || $sortField) {

		$list = total_result($conn,$sql);
		$_SESSION["searchCount"] = $list["count"];
		$idArr = $list["id"];
		$_SESSION["searchResultArray"] = $idArr;

	}
	else $idArr = $_SESSION["searchResultArray"];
	
	//limit our array to the current page
	if (count($idArr) > 0) {

		//merge in our file info
		$results = mergeFileInfo($conn,$idArr,$sortField,$sortDir,$limit,$offset,$rank);

		//transpose our array to have only the currently displayed ids
		for ($i=0;$i<$results["count"];$i++) $curArr[] = $results[$i]["id"];

		//merge collection information
		$results = mergeCollectionInfo($conn,$results,$curArr);
		
		//merge the permission values in for these
		if (!bitset_compare(BITSET,ADMIN,null)) 
			$results = mergePermInfo($conn,$results,$curArr,$permStr);

		//merge in active discussion information
		$results = mergeDiscussionInfo($conn,$results,$curArr);

		//merge in related file information
		$results = mergeRelatedInfo($conn,$results,$curArr);
		
	}
	else {
		$results = array();
		$results["count"] = 0;
	}
	
	$time2 = getmicrotime();
	$diff = $time2 - $time1;

	$diff = floatValue($diff,2);
	$results["timeCount"] = $diff;

	return $results;

}

//this function pulls the file information for our ids
function mergeFileInfo($conn,$idArr,$sortField,$sortDir,$limit,$offset,$rank) {

	if ($rank && !$sortField) $dbrank = " ORDER BY rank DESC";
	else if ($sortField) $dbrank = " ORDER BY $sortField $sortDir ";
	else $dbrank = null;
	
	$fields = "id,name,summary,object_type,create_date,object_owner,status,status_date,status_owner,filesize,level1,level2";
	if ($rank) $fields .= ",".$rank;

	//now get the info for these files
	$sql = "SELECT DISTINCT $fields FROM dm_view_search WHERE id IN (".implode(",",$idArr).")";
	if ($dbrank) $sql .= $dbrank;

	if ($limit !== NULL && $offset !== NULL) $sql .= " LIMIT $limit OFFSET $offset ";
	$list = list_result($conn,$sql);

	//resort our results to match the original order of idArr if not using rank
	if (!$dbrank) $results = sortSearchResults($idArr,$list);
	else $results = $list;

	return $results;
		
}

//this pulls the owning collections for our ids
function mergeCollectionInfo($conn,$results,$curArr) {

	//merge the collection values in for these
	$sql = "SELECT object_id,parent_id FROM dm_object_parent WHERE object_id IN (".implode(",",$curArr).")";
	$colarr = total_result($conn,$sql);

	//merge our collection values in
	for ($i=0;$i<$results["count"];$i++) {
		$key = @array_search($results[$i]["id"],$colarr["object_id"]);
		$results[$i]["parent_id"] = $colarr["parent_id"][$key];
	}

	return $results;

}

//this pulls the owning collections for our ids
function mergeRelatedInfo($conn,$results,$curArr) {

	//merge the collection values in for these
	$sql = "SELECT object_id,related_id,name,object_type FROM dm_view_related WHERE object_id IN (".implode(",",$curArr).")";
	$colarr = total_result($conn,$sql);

	//merge our collection values in
	for ($i=0;$i<$results["count"];$i++) {
		$keys = @array_keys($colarr["object_id"],$results[$i]["id"]);
		if (count($keys) > 0) {
			$c = 0;
			foreach ($keys AS $key) {
				$results[$i]["related"][$c]["id"] = $colarr["related_id"][$key];			
				$results[$i]["related"][$c]["name"] = $colarr["name"][$key];			
				$results[$i]["related"][$c]["object_type"] = $colarr["object_type"][$key];			
				$c++;
			}
		}
	}

	return $results;

}

//this pulls the owning collections for our ids
function mergeDiscussionInfo($conn,$results,$curArr) {

	//merge the collection values in for these
	$sql = "SELECT object_id FROM dm_discussion WHERE object_id IN (".implode(",",$curArr).")";
	$discArr = total_result($conn,$sql);

	//merge our collection values in
	for ($i=0;$i<$results["count"];$i++) {
		$keys = @array_keys($discArr["object_id"],$results[$i]["id"]);
		$num = count($keys);
		if ($num > 0) $results[$i]["discussion"] = count($keys);
	}

	return $results;

}

//this pulls permission settings for our owning ids
function mergePermInfo($conn,$results,$curArr,$permStr) {

	//the sort order will allow the highest permission to be set for an object and the user/group
	$sql = "SELECT object_id,bitset FROM dm_view_perm WHERE 
		object_id IN (".implode(",",$curArr).") AND (".$permStr.") ORDER BY object_id,bitset ASC";
	$permarr = total_result($conn,$sql);

	//merge our collection values in
	for ($i=0;$i<$results["count"];$i++) {
		$key = @array_search($results[$i]["id"],$permarr["object_id"]);
		$results[$i]["bitset"] = $permarr["bitset"][$key];
	}

	return $results;

}


/*******************************************************
	search for files via keyword
*******************************************************/
function createMetaSql($opt) {

	//get all our options
	extract($opt);

	//allow for wildcards if present
	if (strstr($string,"*")) $string = str_replace("*","%",$string);

	//compare against all keyword fields
	if ($metaOption=="_allkeywords") {

		$criteria = "(";
		
		//extract the column names of our keywords and create our query
		$keyArr = returnKeywords();
		foreach ($keyArr AS $keyword) $criteria .= $keyword["name"]." ILIKE '".$string."' OR ";		
		$criteria = substr($criteria,0,strlen($criteria)-4);
		$criteria .= ")";

	//compare against the selected one
	} else {	

		//create our query criteria
		$criteria = $metaOption." ILIKE '".$string."'";

	}

	$sql = "SELECT object_id AS id FROM dm_keyword WHERE $criteria";
	return $sql;

}

function createTsearch2Sql($opt) {

	extract($opt);

	//show ranks in the table
	$_SESSION["showRank"] = 1;

	//create our criteria for the query
	$criteria = null;
	$rank = null;

	/********************************************
		create a search with wildcards
	*********************************************/
	if (strstr($string,"*")) {

		if (in_array("file_name",$search_option)) 
		$nameCriteria = "(".formatSqlString("name",$string).") OR ";

		if (in_array("summary",$search_option)) 
		$sumCriteria = "(".formatSqlString("summary",$string).") OR ";

		if (in_array("file_contents",$search_option)) {

			$string = str_replace("%","",$string);

			//translate our string into the corresponding word ids
			$wordString = formatTsearch2String($string,array("file_contents"));

			//there was an error processing the string, exit
			if (!$wordString) return false;

			$contCriteria = "(idxfti @@ to_tsquery('".TSEARCH2_PROFILE."','$wordString')) OR ";
			$rank = "rank(idxfti,to_tsquery('".TSEARCH2_PROFILE."','$wordString'))";
		
		}

		$criteria = $nameCriteria.$sumCriteria.$contCriteria;
		$criteria = substr($criteria,0,strlen($criteria)-3);

		$sql = "SELECT DISTINCT id FROM dm_view_search WHERE $criteria "; 

	}
	/******************************************
		process our regular search
	******************************************/
	else {

		//translate our string into the corresponding word ids
		$wordString = formatTsearch2String($string,$search_option);
		$criteria = "idxfti @@ to_tsquery('".TSEARCH2_PROFILE."','$wordString') ";

		$rank = "rank(idxfti,to_tsquery('".TSEARCH2_PROFILE."','$wordString'))";
		$sql = "SELECT DISTINCT object_id AS id FROM dm_index WHERE $criteria "; 

	}

	$arr = array();
	$arr["sql"] = $sql;
	$arr["rank"] = $rank;

	return $arr;

}

function createNormalSql($opt) {

	extract($opt);

	//translate our string into the corresponding word ids
	$string = trim(strtolower($string));
	$word_array = explode(" ",$string);

	$criteria = "(".formatSqlString("idxtext",$string).")";
	$nameCriteria = "(".formatSqlString("name",$string).")";
	$sumCriteria = "(".formatSqlString("summary",$string).")";

	$time1 = getmicrotime();

	//create our main query
	$sql = "SELECT DISTINCT id FROM (";	

	//we are trying this without weights.  I'm assuming a union clauses are joined in the same order they 
	//are queried by.  Testing seems to show this is true, which means I don't need weights.
	if (in_array("file_name",$search_option)) $sql .= "(SELECT id FROM dm_view_search WHERE $nameCriteria) UNION ";
	if (in_array("summary",$search_option)) $sql .= "(SELECT id FROM dm_view_search WHERE $sumCriteria) UNION ";
	if (in_array("file_contents",$search_option)) $sql .= "(SELECT id FROM dm_view_search WHERE $criteria) UNION ";

	$sql = substr($sql,0,strlen($sql)-6);

	$sql .= ") AS mytable ";

	return $sql;

}

function formatTsearch2String($string,$option) {

	$wordString = str_replace("*","",trim(strtolower($string)));

	$wordString = removeDoubleSpaces($wordString);

	$str = "[^".REGEXP_OPTION." ]";

	//remove anything not indexed, based on our config criteria
	$wordString = eregi_replace("$str","",$wordString);

	//remove any more doublespaces left from invalid content removal
	$wordString = removeDoubleSpaces($wordString);

	$wordString = str_replace(" and not ","&!",$wordString);
	$wordString = str_replace(" or not ","|!",$wordString);
	$wordString = str_replace(" and ","&",$wordString);
	$wordString = str_replace(" not ","&!",$wordString);	//we use AND NOT by default.  The user can always use OR NOT
	$wordString = str_replace(" or ","|",$wordString);
	$wordString = str_replace(" ","&",$wordString);

	//if our string is reduced to nothing, get out
	if (!strlen($wordString)) return false;

	//if file_name,summary, or content are checked, this appends weights to the end of our strings
	if (count($option)<3) {

		$num = count($option);
		
		//$arr = explode(" ",trim(ereg_replace("[^".REGEXP_OPTION." ]","",$string)));
		$arr = explode(" ",$wordString);
		$lnum = count($arr);
		$skip = array("and","or","not");

		for ($row=0;$row<$num;$row++) {

			$tempString = $wordString;

			$cur = &$option[$row];

			if ($cur=="file_name") $weight = "A";
			elseif ($cur=="summary") $weight = "B";
			else $weight = "D";

			$tempString = "(".$tempString;

			for ($i=0;$i<$lnum;$i++) {

				if (in_array($arr[$i],$skip)) continue;

				$tempString = str_replace($arr[$i],$arr[$i].":".$weight,$tempString);

			}

			$tempString .= ")|";

			$finalString .= $tempString;
		}

		$wordString = substr($finalString,0,strlen($finalString)-1);

	}
	
	return $wordString;

}

function formatSqlString($field,$string) {

	$skipArray = array("and","or","not");

	$string = removeDoubleSpaces($string);

	$arr = explode(" ",$string);

	$st = 1;
	$join = null;
	
	for ($row=0;$row<count($arr);$row++) {

		if (!$arr[$row]) continue;

		if ($field=="idxtext") $comp = " LIKE ";
		else $comp = " ILIKE ";

		//if there is a wildcard, only place % where the * is, otherwise wrap the word with them
		if (strstr($arr[$row],"*")) $term = str_replace("*","%",$arr[$row]);
		else $term = "%".$arr[$row]."%";
		
		if (in_array($arr[$row],$skipArray)) {

			$st = null;
			$join = $arr[$row];
			if ($join=="not") {
				if ($arr[$row-1]=="or") $join = "or not";			
				else $join = "and not";
			}

		} else $st = 1;

		if ($st) {

			if ($st && (!$join && $row>0)) $join = " AND ";
		
			$str .= $join." ".$field." ".$comp." '".$term."' ";		
		
		}

	}

	return $str;

}


function sortSearchResults($idArr,$resArr) {

	$c = 0;
	$results = array();
	$results["count"] = $resArr["count"];
	
	foreach ($idArr AS $id) {

		for ($i=0;$i<$resArr["count"];$i++) {

			if ($resArr[$i]["id"]==$id) {
				$results[$c] = $resArr[$i];
				$c++;
				break;
			}
		}		
	
	}

	return $results;

}

function execCategory($conn,$category_value,$sortField = "name",$sortDir = "ascending",$curPage = null) {

	//setup the sort field
	if ($sortField == "edit") $sortField = "status_date";
	else if ($sortField == "size") $sortField = "filesize::numeric";
	else $sortField = "name";

	//we do this to make sure nothing funky can be passed by the url
	if ($sortDir == "descending") $sortDir = "DESC";
	else $sortDir = "ASC";

	$offset=null;
	$limit=null;

	if (defined("PAGE_BROWSE_RESULTS")) {
		$limit = RESULTS_PER_PAGE;
		if (!$curPage || $curPage==1) $offset = 0;
		else $offset = ($curPage-1) * $limit;
	}

	if ($category_value!=null) {

		//first get the count
		if (!$_SESSION["searchCount"]) {
			$sql = "SELECT DISTINCT id FROM dm_view_objects WHERE parent_id='$category_value'";
			if (!bitset_compare(BITSET,ADMIN,null)) $sql .= " AND ".permString($conn);
			$ids = total_result($conn,$sql);
			$_SESSION["searchCount"] = $ids["count"];
			$_SESSION["searchId"] = $ids["id"];
		}

		if ($_SESSION["searchCount"]) {

			//merge in our file info
			$results = mergeFileInfo($conn,$_SESSION["searchId"],$sortField,$sortDir,$limit,$offset,null);

			//transpose our array to have only the currently displayed ids
			for ($i=0;$i<$results["count"];$i++) $curArr[] = $results[$i]["id"];

			//merge in discussion counts for each object
			$results = mergeDiscussionInfo($conn,$results,$curArr);

			//merge in related file information
			$results = mergeRelatedInfo($conn,$results,$curArr);

		} else {
		
			$results["count"] = "0";
			
		}

		return $results;

	}

	return false;

}

function execWebdav($conn,$category_value) {

	if ($category_value!=null) {

		$fields = "id,name,summary,object_type,create_date,object_owner,status,status_date,status_owner,filesize::numeric,version";

		$sql = "SELECT DISTINCT $fields FROM dm_view_objects WHERE parent_id='$category_value' AND (object_type='file' OR object_type='collection')";
		if (!bitset_compare(BITSET,ADMIN,null)) $sql .= " AND ".permString();
		$sql .= " ORDER BY object_type ASC, name ASC";

		return list_result($conn,$sql);

	}

	return false;

}



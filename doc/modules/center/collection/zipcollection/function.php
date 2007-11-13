<?
function zipProcessFile($conn,$obj,$dir) {

	$sql = "SELECT name,version FROM dm_object WHERE id='".$obj["id"]."'";
	$objInfo = single_result($conn,$sql);
	$version=$objInfo["version"];

	$sql = "SELECT id FROM dm_file_history WHERE object_id='".$obj["id"]."' AND version='$version'";
	$info = single_result($conn,$sql);

	//copy the file to the temp directory with the correct name
	$filename = $dir."/".$obj["name"];
	$source = DATA_DIR."/".returnObjPath($conn,$obj["id"])."/".$info["id"].".docmgr";

	if (file_exists("$source")) copy("$source","$filename");

	//log the event since this means the file will be viewed
	logEvent($conn,OBJ_VIEWED,$obj["id"]);
	
}

function zipProcessCol($conn,$obj,$passDir) {

	$sql = "SELECT * FROM dm_view_objects WHERE parent_id='".$obj["id"]."'";

	//add perm string filter if not admin
	if (!bitset_compare(BITSET,ADMIN,null)) $sql .= " AND ".permString();

	$list = list_result($conn,$sql);
	
	//first, create a directory with this column.
	$dir = $passDir."/".$obj["name"];

	//remove the directory if it is there
	if (is_dir("$dir")) `rm -r "$dir"`;
	mkdir("$dir");

	for ($row=0;$row<$list["count"];$row++) {

		//only add files and collections to the archive	
		if ($list[$row]["object_type"]=="collection") zipProcessCol($conn,$list[$row],$dir);
		else if ($list[$row]["object_type"]=="file") zipProcessFile($conn,$list[$row],$dir);
	
	}

	//return the directory we created
	return $dir;
}

//this function creates a zipped file and allows the user to download it
function zipCollection($conn,$colId) {

	//get out if no collection id was passed
	if (!$colId) return false;

	//our temp directory for the user
	$dir = TMP_DIR."/".USER_LOGIN;

	//create the temp directory. otherwise empty any previous contents in that dir
	if (is_dir("$dir")) `rm -r "$dir"`;
	mkdir("$dir");

	$sql = "SELECT * FROM dm_view_collections WHERE id='$colId'";
	$info = single_result($conn,$sql);
	
	//create a folder which is a mirror of our collection
	$arcdir = zipProcessCol($conn,$info,$dir);	

	if (is_dir("$arcdir")) {

		$arr = explode("/",$arcdir);
		$arcsrc = array_pop($arr);

		//zip up our file
		$arc = $info["name"].".zip";	
		
		//create our archive
		$cmd = APP_CD." \"".$dir."\"; ".APP_ZIP." \"".$arc."\" \"".$arcsrc."\"";
		`$cmd`;

		//return the path of the archive with zip on the end
		return $dir."/".$arc;

	} else return false;
	
}

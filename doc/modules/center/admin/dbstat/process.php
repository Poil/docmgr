<?

function getDataSize() {

	$size = "0";
	$arr = recursiveList(DATA_DIR);
	
	foreach($arr AS $file) {
		$size += filesize($file);
	}

	$size = displayFileSize($size);

	return $size;

}


function db_statistics($conn) {

	$arr = array();

	//get the filesystem size;
	$arr["fileSize"] =  getDataSize();

	//get the number of files include history.
	$sql = "SELECT id FROM dm_file_history";
	$arr["fileNum"] = num_result($conn,$sql);

	//get the number of categories.
	$sql = "SELECT id FROM dm_object WHERE object_type='collection'";
	$arr["catNum"] = num_result($conn,$sql);

	//get the number of users.
	$info = returnAccountList(array("conn"=>$conn));
	$arr["usersNum"] = count($info["id"]);
	
	return $arr;

}

$toolBar = modTreeMenu($module);

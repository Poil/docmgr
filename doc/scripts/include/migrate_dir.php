#!/usr/local/bin/php

<?

echo "Now migrating directory structure\n";


echo "Creating new directories\n";
//make our directories in data and thumbnail
for ($i=1;$i<=LEVEL1_NUM;$i++) {

	$level1Data = DATA_DIR."/".$i;
	$level1Thumb = THUMB_DIR."/".$i;
	$level1Doc = DOC_DIR."/".$i;

	@mkdir($level1Data);
	@mkdir($level1Thumb);
	@mkdir($level1Doc);

	for ($c=1;$c<=LEVEL2_NUM;$c++) {
	
		$level2Data = $level1Data."/".$c;
		$level2Thumb = $level1Thumb."/".$c;	
		$level2Doc = $level1Doc."/".$c;
		
		@mkdir($level2Data);
		@mkdir($level2Thumb);
		@mkdir($level2Doc);
	
	}

}

echo "Assiging subdirectory to each object and migrating its files\n";
$sql = "SELECT * FROM dm_object WHERE object_type='file' OR object_type='document'";
$list = list_result($conn,$sql);

for ($i=0;$i<$list["count"];$i++) {

	//get our next level values and store them in the db
	$sql = "SELECT NEXTVAL('level1');";
	$info = single_result($conn,$sql);
	$l1 = $info[0];

	$sql = "SELECT NEXTVAL('level2');";
	$info = single_result($conn,$sql);
	$l2 = $info[0];

	$opt = null;
	$opt["object_id"] = $list[$i]["id"];
	$opt["level1"] = $l1;
	$opt["level2"] = $l2;
	dbInsertQuery($conn,"dm_dirlevel",$opt);	

	//now, move the files depending on type
	if ($list[$i]["object_type"]=="file") {

		$sql = "SELECT id FROM dm_file_history WHERE object_id='".$list[$i]["id"]."'";
		$files = list_result($conn,$sql);
		
		for ($c=0;$c<$files["count"];$c++) {
			$src = DATA_DIR."/".$files[$c]["id"].".docmgr";
			$dest = DATA_DIR."/".$l1."/".$l2."/".$files[$c]["id"].".docmgr";

			if (file_exists($src)) rename($src,$dest);		
		}	
	
	} else if ($list[$i]["object_type"]=="document") {

		$sql = "SELECT id FROM dm_document WHERE object_id='".$list[$i]["id"]."'";
		$files = list_result($conn,$sql);
		
		for ($c=0;$c<$files["count"];$c++) {
			$src = DOC_DIR."/".$files[$c]["id"].".docmgr";
			$dest = DOC_DIR."/".$l1."/".$l2."/".$files[$c]["id"].".docmgr";
			if (file_exists($src)) rename($src,$dest);		
		}	
	
	}

	//now the thumbs
	$src = THUMB_DIR."/".$list[$i]["id"].".docmgr";
	$dest = THUMB_DIR."/".$l1."/".$l2."/".$list[$i]["id"].".docmgr";
	if (file_exists($src)) rename($src,$dest);		
	

}




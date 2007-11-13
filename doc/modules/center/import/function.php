<?

/************************************************************************
	Process the recursive directory import
************************************************************************/

function importObjects($option) {

	//get all our variables
	extract($option);

	//gives us filePath,conn,parentId
	if (!is_array($filePath)) return false;
	
	//set the max execution time based on the number of files, right now 60 secs per file.  
	$timeout = EXECUTION_TIME * $fileCount;
	ini_set("max_execution_time","$timeout");	//putting this here is an experiment

	foreach ($filePath AS $file) {

		$colId = $parentId;
		$file = stripsan($file);

		//if we are importing a directory, create it in the system, then import
		//its files by recalling this function
		if (is_dir($file)) {

			$arr = explode("/",$file);
			$name = addslashes(array_pop($arr));
			
			$o = null;
			$o["conn"] = $conn;
			$o["name"] = $name;
			$o["parentId"] = $colId;
			$o["objectType"] = "collection";
			$o["objectOwner"] = USER_ID;
			$colId = createObject($o);

			if (!$colId) return false;

			//get the files in this directory
			$files = listDirectory($file,null,null);

			//import the files in this directory			
			if (count($files) > 0) {

				//add the path to the files.  We reassemble our original file path array
				//with the new file at the end
				for ($row=0;$row<count($files);$row++) 
					$files[$row] = $file."/".$files[$row];

				$opt = null;
				$opt["conn"] = $conn;
				$opt["filePath"] = $files;
				$opt["parentId"] = $colId;
				if (!importObjects($opt)) return false;

			}
			
			//delete the dir if requested by the user
			if ($_POST["delete_files"]=="yes") dirdel($file);

			//set permissions if we are not inheriting them
			if (!$_POST["permInherit"]) {

				//get our permissions from the form
				$viewEditArr = explode(",",$_POST["view_edit_value"]);
				$viewArr = explode(",",$_POST["view_value"]);

				//remove the empty end value
				@array_pop($viewEditArr);
				@array_pop($viewArr);

				permUpdate($conn,$colId,$viewEditArr,$viewArr); 

			}

		} else {

			//extract the name from the file
			$arr = explode("/",$file);
			$name = sanitizeString(array_pop($arr));

			//set all our options into the array with corresponding keys.  These will
			//be passed to the file_insert function, which handles inserting the file into the system
			$opt = null;
			$opt["conn"] = $conn;
			$opt["filepath"] = $file;
			$opt["name"] = $name;
			$opt["objectType"] = "file";
			$opt["delete_files"] = "no";
			$opt["parentId"] = $parentId;
			$opt["objectOwner"] = USER_ID;
			if (!$objId = createObject($opt)) {

	           		//output an error message from the file_insert function
	           		if (defined("ERROR_MESSAGE")) {
		           		$str = $file.": ".ERROR_MESSAGE;
		           		define("IMPORT_ERROR_MESSAGE",$str);
				}
				return false;
			}

			//delete the file if requested by the user
			if ($_POST["delete_files"]=="yes") @unlink($file);

			//set permissions if we are not inheriting them
			if (!$_POST["permInherit"]) {

				//get our permissions from the form
				$viewEditArr = explode(",",$_POST["view_edit_value"]);
				$viewArr = explode(",",$_POST["view_value"]);

				//remove the empty end value
				@array_pop($viewEditArr);
				@array_pop($viewArr);

				permUpdate($conn,$objId,$viewEditArr,$viewArr); 

			}

		}


        }

        return true;
			
}


function showFiles($path,$listArray) {

	//there's nothing to display
	if (count($listArray)==0) return "<div class=\"errorMessage\">"._FILE_DISPLAY_ERROR."</div>";

	for ($row=0;$row<count($listArray);$row++) {

		$form_value = $path."/".$listArray[$row];
		$string .= "<div>";


		if (is_dir($form_value)) {
			$file_name = "<a 	href=\"javascript:selectPath('".addslashes($form_value)."');\" 
							class=main>
							".stripsan($listArray[$row])."
							</a>";
		}
		else {
			$file_name = stripsan($listArray[$row]);
		}

		//just show the file name if we don't have permission to read it
		if (!is_readable($form_value)) {
			$string .= "<div style=\"padding-left:24px\">".$file_name."</div>";		
			continue;		
		}


		$string .= "	<input type=\"checkbox\"
			 		CHECKED
					name=\"filePath[]\" 
					id=\"filePath[]\" 
					value=\"".$form_value."\"> ";

		$string .= $file_name;

		$string .= "</div>";

	}

	return $string;

}


function list_dir($dirname)
{
	static $result_array=array();
	$handle=opendir($dirname);
	while ($file = readdir($handle))
	{
		if($file=='.'||$file=='..')	{
			continue;
		}
		else	{
			if (!stristr($file,"~") && !is_dir($dirname."/".$file)) {
				$result_array[]=$dirname."/".$file;
			}
		}
	}
	closedir($handle);
	return $result_array;

}
function dir_array($dirname) {

	static $dir_array=array();
	$handle=opendir($dirname);
	while ($file = readdir($handle))
	{
		if($file=='.'||$file=='..')	{
			continue;
		}
		else	{
			if (!stristr($file,"~")) {
				if (is_dir($dirname."/".$file)) $dir_array[]=$dirname."/".$file;
			}
		}
	}
	closedir($handle);
	return $dir_array;

}

function createImportCategory($conn) {

	$sql = "SELECT id FROM dm_view_collections WHERE name='Imported' AND parent_id='0'";
	$info = single_result($conn,$sql);

	if (!$info) {
		$option = null;
		$option["conn"] = $conn;
		$option["name"] = "Imported";
		$option["objectType"] = "collection";
		$option["objectOwner"] = 1;	//set to the admin user
		$option["parentId"] = "0";
		$importId = createObject($option);

	}
	else $importId=$info["id"];

	if (!$importId) return false;
	
	//see if the folder for the date_user exists
	$sql = "SELECT * FROM dm_view_collections WHERE name='".USER_LOGIN."' AND parent_id='$importId' AND object_type='collection'";
	$info = single_result($conn,$sql);

	if (!$info) {

		$option = null;
		$option["conn"] = $conn;
		$option["name"] = USER_LOGIN;
		$option["parentId"] = $importId;
		$option["objectType"] = "collection";
		$option["objectOwner"] = USER_ID;
		$parentId = createObject($option);

	}
	else $parentId = $info["id"];

	return $parentId;

}


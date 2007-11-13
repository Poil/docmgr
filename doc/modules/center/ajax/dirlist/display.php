<?

$path = stripsan($_REQUEST["dirPath"]);

$listArray = list_dir($path);
$backPath = null;

//die if the import directory is not the first part of the path
if (!$path || strpos($path,IMPORT_DIR)) die;

if ($path!=IMPORT_DIR) {

	$temp = explode("/",$path);
	array_pop($temp);
	$backPath = implode("/",$temp);

}

$num = count($listArray);

$str = createXmlHeader("listdir");

$str .= "<backlink>".$backPath."</backlink>\n";

//dump our file info into xml
for ($row=0;$row<$num;$row++) {

	$filePath = $path."/".$listArray[$row];

	if (is_dir($filePath)) $objType = "directory";
	else $objType = "file";

	if (is_readable($filePath)) $readable = "yes";
	else $readable = "no";

	$str .= "<object>\n";
	$str .= xmlEntry("name",$listArray[$row]);
	$str .= xmlEntry("path",$filePath);
	$str .= xmlEntry("objtype",$objType);
	$str .= xmlEntry("readable",$readable);
	$str .= "</object>\n";

}

$str .= createXmlFooter();

$siteContent = $str;


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
			if (!strstr($file,"~")) $result_array[]=$file;
		}
	}
	closedir($handle);
	return $result_array;

}


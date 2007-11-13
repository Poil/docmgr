#!/usr/local/bin/php
<?

include("../lib/xml.php");
include("../lib/modules.php");

$cache = createXmlFile("../modules/center/");
$cache = createXmlFile("../modules/left/");
$cache = createXmlFile("../modules/right/");

function createXmlFile($dir) {

	$arr = findModConfig($dir);
	$num = count($arr);

	$str = outputXmlHeader();

	for ($row=0;$row<$num;$row++) {

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

	$str = str_replace("../","",$str);

	$file = $dir."module-cache.xml";

	file_put_contents($file,$str);


}

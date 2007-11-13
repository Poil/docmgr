<?

function document2pdf($conn,$str) {

	$arr = explode("<img",$str);

	for ($i=0;$i<count($arr);$i++) {
	
	  //if not a docmgr lnk, skip
	  if (!strstr($arr[$i],"[DOCMGR_SESSION_MARKER]")) continue;

	  //get our url and put the rest in temp
	  $srcpos = strpos($arr[$i],"src=\"");

	  $temp = substr($arr[$i],$srcpos+5);
	  $url = substr($temp,0,strpos($temp,"\""));

	  //get the objectId
	  $pos = strpos($url,"fileId=");
	  $temp = substr($url,$pos+7);
	  $end = strpos($temp,"&");
	
	  //extract the objectid
	  $id = substr($temp,0,$end);

	  //create the new url
	  $sql = "SELECT object_id,(SELECT name FROM dm_object WHERE id=object_id) AS name FROM dm_file_history WHERE id='$id'";
	  $info = single_result($conn,$sql);

	  $newurl = SITE_URL."index.php?module=fileview&objectId=".$info["object_id"]."&login=".USER_LOGIN."&password=".USER_PASSWORD;	  

	  //put a fake extension on the end
	  $newurl .= "&extension=.".return_file_extension($info["name"]);
	  $newurl = str_replace("&","&amp;",$newurl);

	  $arr[$i] = str_replace($url,$newurl,$arr[$i]);
	  
	}
	
	$str = implode("<img",$arr);
	
	return $str;
	
}

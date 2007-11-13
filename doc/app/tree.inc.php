<?

//just show a single level of collections
function expandSingleCol($conn,$curValue) {

  //somehow, this is faster than the two table query method
  if (bitset_compare(BITSET,ADMIN,null)) {
  $sql = "SELECT DISTINCT id,name,parent_id,
           (SELECT count(id) FROM 
             (SELECT id,parent_id FROM dm_view_collections) AS mytable
             WHERE parent_id=dm_view_collections.id) AS child_count
             FROM dm_view_collections WHERE parent_id='$curValue' ORDER BY name
              ";
  }
  else { 
    $permStr = permString();
    //a little fancy query footwork
    $sql = "SELECT DISTINCT id,name,parent_id,
             (SELECT count(id) FROM 
               (SELECT id,parent_id FROM dm_view_collections WHERE ".$permStr.") AS mytable
               WHERE parent_id=dm_view_collections.id) AS child_count
             FROM dm_view_collections WHERE parent_id='$curValue' AND ".$permStr." ORDER BY name
              ";
  }  

  $list = list_result($conn,$sql);

  $str = null;
  
  for ($i=0;$i<$list["count"];$i++) {

    //first, get the info for this file
    $str .= "<collection>\n";
    $str .= xmlEntry("id",$list[$i]["id"]);
    $str .= xmlEntry("name",$list[$i]["name"]);
    $str .= xmlEntry("parent_id",$list[$i]["parent_id"]);
    $str .= xmlEntry("child_count",$list[$i]["child_count"]);
    $str .= "</collection>\n";

  }

  return $str;
  
}


function expandValueCol($conn,$curValue) {

  //get all collections that need to be displayed
  $sql = "SELECT DISTINCT id,name,parent_id FROM dm_view_collections";  
  if (!bitset_compare(BITSET,ADMIN,null)) $sql .= " WHERE ".permString($conn);
  $sql .= " ORDER BY name ";
  $catInfo = total_result($conn,$sql);

  $arr = array();

  if ($curValue) {

    if (!is_array($curValue)) $curValue = array($curValue);
  
    $num = count($curValue);

    for ($i=0;$i<$num;$i++) 
      $arr = array_merge($arr,returnCatOwner($catInfo,$curValue[$i],null));

    $arr = array_values(array_unique($arr));

  }

  //get the parent of the current, then pass it on to return info for all files
  //which are on the same level.  we also pass our value array so we can 
  //decent to the next level at a hit
  if ($catInfo["count"] > 0) {
    $keys = array_keys($catInfo["parent_id"],"0");
    $xml = showColXml($catInfo,$keys,$arr);
  }
  else $xml = null;
  
  return $xml;

}


function showColXml($catInfo,$keys,$arr) {

  if (!$keys) return false;

  foreach ($keys AS $childKey) {
  
    //first, get the infor for this file
    $str .= "<collection>\n";
    $str .= xmlEntry("id",$catInfo["id"][$childKey]);
    $str .= xmlEntry("name",$catInfo["name"][$childKey]);
    $str .= xmlEntry("parent_id",$catInfo["parent_id"][$childKey]);

    //now show the children
    $keys = array_keys($catInfo["parent_id"],$catInfo["id"][$childKey]);

    //show the children count
    $str .= xmlEntry("child_count",count($keys));

    //if in the expansion array show the children
    if (@in_array($catInfo["id"][$childKey],$arr)) {

      $str .= "<children>\n";
      $str .= showColXml($catInfo,$keys,$arr);
      $str .= "</children>\n";

    }

    $str .= "</collection>\n";
  
  }



  return $str;

}


//just show a single level of collections
function expandSingleObj($conn,$curValue) {

  //somehow, this is faster than the two table query method
  if (bitset_compare(BITSET,ADMIN,null)) {
  $sql = "SELECT DISTINCT id,name,object_type,parent_id,
           (SELECT count(id) FROM 
             (SELECT id,parent_id FROM dm_view_objects) AS mytable
             WHERE parent_id=dm_view_objects.id) AS child_count
             FROM dm_view_objects WHERE parent_id='$curValue' ORDER BY name
              ";
  }
  else { 
    $permStr = permString();
    //a little fancy query footwork
    $sql = "SELECT DISTINCT id,name,object_type,parent_id,
             (SELECT count(id) FROM 
               (SELECT id,parent_id FROM dm_view_objects WHERE ".$permStr.") AS mytable
               WHERE parent_id=dm_view_objects.id) AS child_count
             FROM dm_view_objects WHERE parent_id='$curValue' AND ".$permStr." ORDER BY name
              ";
  }  

  $list = list_result($conn,$sql);

  $str = null;
  
  for ($i=0;$i<$list["count"];$i++) {

    //first, get the info for this file
    $str .= "<object>\n";
    $str .= xmlEntry("id",$list[$i]["id"]);
    $str .= xmlEntry("name",$list[$i]["name"]);
    $str .= xmlEntry("icon",returnObjIcon($list[$i]["name"],$list[$i]["object_type"]));
    $str .= xmlEntry("parent_id",$list[$i]["parent_id"]);
    $str .= xmlEntry("child_count",$list[$i]["child_count"]);
    $str .= "</object>\n";

  }

  return $str;
  
}


function expandValueObj($conn,$curValue) {

  //get all objects that need to be displayed
  $sql = "SELECT DISTINCT id,name,object_type,parent_id FROM dm_view_objects";  
  if (!bitset_compare(BITSET,ADMIN,null)) $sql .= " WHERE ".permString($conn);
  $sql .= " ORDER BY name ";
  $objInfo = total_result($conn,$sql);

  $arr = array();

  if ($curValue) {

    if (!is_array($curValue)) $curValue = array($curValue);
  
    $num = count($curValue);

    for ($i=0;$i<$num;$i++) 
      $arr = array_merge($arr,returnCatOwner($objInfo,$curValue[$i],null));

    $arr = array_values(array_unique($arr));

  }

  //get the parent of the current, then pass it on to return info for all files
  //which are on the same level.  we also pass our value array so we can 
  //decent to the next level at a hit
  if ($objInfo["count"] > 0) {
    $keys = array_keys($objInfo["parent_id"],"0");
    $xml = showObjXml($objInfo,$keys,$arr);
  }
  else $xml = null;
  
  return $xml;

}


function showObjXml($objInfo,$keys,$arr) {

  if (!$keys) return false;

  foreach ($keys AS $childKey) {
  
    //first, get the infor for this file
    $str .= "<object>\n";
    $str .= xmlEntry("id",$objInfo["id"][$childKey]);
    $str .= xmlEntry("name",$objInfo["name"][$childKey]);
    $str .= xmlEntry("parent_id",$objInfo["parent_id"][$childKey]);

    
    //now show the children
    $keys = array_keys($objInfo["parent_id"],$objInfo["id"][$childKey]);

    $str .= xmlEntry("icon",returnObjIcon($objInfo["name"][$childKey],$objInfo["object_type"][$childKey]));

    //show the children count
    $str .= xmlEntry("child_count",count($keys));

    //if in the expansion array show the children
    if (@in_array($objInfo["id"][$childKey],$arr)) {

      $str .= "<children>\n";
      $str .= showObjXml($objInfo,$keys,$arr);
      $str .= "</children>\n";

    }

    $str .= "</object>\n";
  
  }



  return $str;

}

function returnObjIcon($name,$objType) {

    //list the icon that will be used for this object
    if ($objType=="file") {

      //use the file's type icon if it exists
      $type = return_file_type($name);
      if (file_exists(THEME_PATH."/images/fileicons/".$type.".png"))
        $img = THEME_PATH."/images/fileicons/".$type.".png";
      else
        $img = THEME_PATH."/images/fileicons/file.png";
    
    } else {
      //use the icon for the type if it exists, otherwise use the standard file icon
      $img = THEME_PATH."/images/fileicons/".$objType.".png";
      if (!file_exists($img)) THEME_PATH."/images/fileicons/file.png";
    }

    return $img;
   


}


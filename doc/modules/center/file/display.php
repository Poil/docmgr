<?

//show links for all options here
$mainContent .= showModLinks($siteModInfo["file"]["module_path"],"file");

$src = THUMB_DIR."/".returnObjPath($conn,$objectId)."/".$objectId.".docmgr";

$sessId = session_id();

if (file_exists($src))  {
  $src = "app/showthumb.php?objectId=".$objectId."&objDir=".OBJECT_DIR."&sessId=".$sessId;
  $class = "class=\"previewImage\"";
} else {

  $ext = return_file_extension($fileInfo["name"]);
  $thmb = THEME_PATH."/images/thumbnails/".$ext.".png";

  if (file_exists("$thmb")) $src = $thmb;
  else $src = THEME_PATH."/images/thumbnails/file.png";

  $class = null;

} 

$thumbnail = "<div style=\"text-align:center;margin-top:10px;\">\n";
$thumbnail .= "<img src=\"".$src."\" border=0 ".$class."><br><br>\n";
$thumbnail .= "<a href=\"index.php?module=fileview&objectId=".$objectId."\" class=\"boldLink\">"._VIEW_FILE."</a>\n";

//allow the user to checkout from here if perms are right
if ($fileInfo["status"]=="0" && bitset_compare(BITSET,INSERT_OBJECTS,ADMIN) && (bitset_compare(CUSTOM_BITSET,OBJ_EDIT,OBJ_ADMIN) || bitset_compare(CUSTOM_BITSET,OBJ_MANAGE,null))) {

  $thumbnail .= "<br><br><a href=\"index.php?module=filecheckout&objectId=".$objectId."\" class=\"boldLink\">"._CHECKOUT_FILE."</a>\n";

  // display "edit" link if file is a textfile
  if (return_file_mime($fileInfo["name"]) == "txt") {
	$thumbnail .= "<br><br><a href=\"javascript:openModuleWindow('fileedit','".$objectId."');\" class=\"boldLink\">"._EDIT."</a>\n";
  }

}  
  
$thumbnail .= "<br><br><a href=\"javascript:openModuleWindow('fileemail','".$objectId."');\" class=\"boldLink\">"._EMAIL."</a>\n";

$thumbnail .= "</div>\n";
                                                                                          
$langtext = "_MT_".strtoupper($siteModInfo[$includeModule]["link_name"]);
if (defined($langtext)) $includeHeader = constant($langtext);
else $includeHeader = $siteModInfo[$includeModule]["module_name"];

$pageContent = "

<div class=\"leftColumn\">
  <div class=\"pageHeader\">
    ".$fileInfo["name"]."
  </div>
  <div class=\"leftColumn\">
    ".$mainContent."
  </div><div class=\"rightColumn\" style=\"width:40%\">
    ".$thumbnail."
  </div>
</div><div class=\"rightColumn\">

  <img src=\"".returnModImage($siteModInfo[$includeModule]["link_name"])."\" border=0 align=left>
  <div class=\"subModuleHeader\">
  ".$includeHeader."
  </div>
  <div class=\"subModuleContent\">
  ";
    
  //there was a perm error accessing the sub module.  Display the error and stop
  if ($includePermError) $pageContent .= "<div class=\"errorMessage\">You are not allowed to access this module</div>\n";
  else {

    //determine our process file and our display file
    $style_path = $siteModInfo["$includeModule"]["module_path"]."stylesheet.css";
    $js_path = $siteModInfo["$includeModule"]["module_path"]."javascript.js";
    $display_path = $siteModInfo["$includeModule"]["module_path"]."display.php";
    $css_path = THEME_PATH."/modcss/".$includeModule.".css";
    
    //these get called by our body.inc.php file
    if (file_exists("$style_path")) includeStylesheet("$style_path");
    if (file_exists("$css_path")) includeStylesheet("$css_path");
    if (file_exists("$js_path")) includeJavascript("$js_path");
  
    //define our display module if there is one
    if (file_exists("$display_path")) include("$display_path");;

  }

$pageContent .= "
  </div>
</div>
";

$opt = null;
$opt["hideHeader"] = 1;
$opt["content"] = $pageContent;
$siteContent = sectionDisplay($opt);


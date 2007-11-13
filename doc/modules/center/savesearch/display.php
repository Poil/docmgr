<?
//show links for all options here
$mainContent .= showModLinks($siteModInfo["savesearch"]["module_path"],"savesearch");

$langtext = "_MT_".strtoupper($siteModInfo[$includeModule]["link_name"]);
if (defined($langtext)) $includeHeader = constant($langtext);
else $includeHeader = $siteModInfo[$includeModule]["module_name"];

$pageContent = "

<div class=\"leftColumn\">
  <div class=\"pageHeader\">
  ".$colInfo["name"]."
  </div>
  ".$mainContent."
</div>
<div class=\"rightColumn\">

  <img src=\"".returnModImage($siteModInfo[$includeModule]["link_name"])."\" border=0 align=left>
  <div class=\"subModuleHeader\">
  ".$includeHeader."
  </div>
  <div class=\"subModuleContent\">
  ";

  //there was a perm error accessing the sub module.  Display the error and stop
  if ($permErrorMessage) $siteContent .= "<div class=\"errorMessage\">".$permErrorMessage."</div>\n";
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
";

$opt = null;
$opt["hideHeader"] = 1;
$opt["content"] = $pageContent;
$siteContent = sectionDisplay($opt);

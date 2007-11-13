<?

$langtext = "_MT_".strtoupper($siteModInfo[$showMod]["link_name"]);
if (defined($langtext)) $includeHeader = constant($langtext);
else $includeHeader = $siteModInfo[$showMod]["module_name"];

$siteContent = "

  <table width=100% cellpadding=0 cellspacing=0>
  <tr><td>
    <img src=\"".returnModImage($siteModInfo[$showMod]["link_name"])."\" border=0>
  </td><td width=95%>
  <div class=\"pageHeader\">
  ".$includeHeader."
  </div>
  </td></tr>
  </table>
  ";
  
  //there was a perm error accessing the sub module.  Display the error and stop
  if ($permErrorMessage) $siteContent .= "<div class=\"errorMessage\">".$permErrorMessage."</div>\n";
  else {

    //determine our process file and our display file
    $style_path = $siteModInfo["$showMod"]["module_path"]."stylesheet.css";
    $js_path = $siteModInfo["$showMod"]["module_path"]."javascript.js";
    $display_path = $siteModInfo["$showMod"]["module_path"]."display.php";
  
    //these get called by our body.inc.php file
    if (file_exists("$style_path")) includeStylesheet("$style_path");
    if (file_exists("$js_path")) includeJavascript("$js_path");
  
    //define our display module if there is one
    if (file_exists("$display_path")) include("$display_path");;

  }


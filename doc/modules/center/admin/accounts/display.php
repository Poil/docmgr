<?
//someone tried to pass something from the url they shouldn't.  Display an error and exist here
if ($siteErrorMessage) {
  $errorMessage = $siteErrorMessage;
  return false;
}

$mainContent = null;

if (bitset_compare(BITSET,MANAGE_USERS,ADMIN)) {

  $mainContent .= "
                <form name=\"searchForm\" method=post>
                <input type=hidden name=pageAction id=pageAction value=\"search\">
                <div class=\"formHeader\">
                "._SEARCH_FOR."
                </div>
                <input type=text name=\"searchString\" id=\"searchString\" value=\"".$_REQUEST["searchString"]."\">
                <input type=submit value=\""._SEARCH."\">
                <br><br>
                <a href=\"javascript:createAccount();\" class=\"boldLink\">"._CREATE_NEW_ACCOUNT."</a>
                </form><br>
                ";                

  if ($pageAction=="search") {

    $mainContent .= "<br><br>
                  <div class=\"areaHeader\">
                  "._RESULTS_FOR." \"".$searchString."\"
                  </div>
                  <br>
                  <div class=\"selectList\" style=\"width:220px;height:250px;\">
                  ";    

    for ($row=0;$row<$searchResults["count"];$row++) {

      $id = &$searchResults["id"][$row];
      $name = &$searchResults["login"][$row];
    
      $mainContent .= "<li>";
      $mainContent .= "<a href=\"index.php?module=accounts&accountId=".$id."\">";
      $mainContent .= $name;
      $mainContent .= "</a></li>\n";
    
    }
                  
    $mainContent .= "</div><br><br>";          
  
  }

}

/*****************************************************
  This section shows the options for the account
*****************************************************/
if ($accountId!=NULL) {

  
  $mainContent .= "<div class=\"pageHeader\">
                "._NOW_EDITING." \"".$accountInfo["login"]."\"
                </div>
                ";

  //show links for all options here
  $mainContent .= showModLinks($siteModInfo["accounts"]["module_path"],"accounts");

}


$siteContent = "

<div class=\"leftColumn\">
  ".$mainContent."
</div>
<div class=\"rightColumn\">
  ";
  
  //there was a perm error accessing the sub module.  Display the error and stop
  if ($permErrorMessage) $siteContent .= "<div class=\"errorMessage\">".$permErrorMessage."</div>\n";
  else {

    //determine our process file and our display file
    $style_path = $siteModInfo["$includeModule"]["module_path"]."stylesheet.css";
    $js_path = $siteModInfo["$includeModule"]["module_path"]."javascript.js";
    $display_path = $siteModInfo["$includeModule"]["module_path"]."display.php";
  
    //these get called by our body.inc.php file
    if (file_exists("$style_path")) includeStylesheet("$style_path");
    if (file_exists("$js_path")) includeJavascript("$js_path");
  
    //define our display module if there is one
    if (file_exists("$display_path")) include("$display_path");;

  }

$siteContent .= "
</div>
";

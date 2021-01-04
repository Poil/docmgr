<?php
/*****************************************************************************************************

	body.inc.php

	This file displays the site including all side columns and logos

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">

*****************************************************************************************************/
?>
<!DOCTYPE HTML>
<html>
<head>

<title>
DocMGR Installation
</title>

<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<?php
/****************************************************
	our stylesheets and javascript files
****************************************************/

if (BROWSER=="ie")
{
  $css = THEME_PATH."/css-ie/core.css;";
  $css .= THEME_PATH."/css-ie/sidebar.css;";
  $css .= THEME_PATH."/css-ie/modal.css;";
  $css .= THEME_PATH."/css-ie/toolbar.css;";
  $css .= THEME_PATH."/css-ie/records.css;";
  $css .= THEME_PATH."/css-ie/eform.css;";
}
else
{
  $css = THEME_PATH."/css/core.css;";
  $css .= THEME_PATH."/css/sidebar.css;";
  $css .= THEME_PATH."/css/modal.css;";
  $css .= THEME_PATH."/css/toolbar.css;";
  $css .= THEME_PATH."/css/records.css;";
  $css .= THEME_PATH."/css/eform.css;";
}

includeStylesheet($css);

$js = null;
$js .= "jslib/mootools-core-1.4.4-full-nocompat.js;";
$js .= "jslib/mootools-more-1.4.0.1.js;";
$js .= "jslib/core.js;";
$js .= "jslib/sitenavigation.js;";
$js .= "jslib/sidebar.js;";
$js .= "jslib/toolbar.js;";
$js .= "jslib/pulldown.js;";
$js .= "jslib/modal.js;";
$js .= "jslib/eform.js;";
$js .= "jslib/records.js;";
$js .= "jslib/record_filters.js;";
$js .= "jslib/pager.js;";
$js .= "lang/en/client.js;";

$js .= "jslib/xml.js;";
$js .= "jslib/query.js;";
$js .= "jslib/proto.js;";
$js .= "jslib/string.js;";
$js .= "jslib/sitemenu.js;";
$js .= "jslib/notifications.js;";
$js .= "javascript/common.js;";

includeJavascript($js);

?>
</head>

<body style="overflow:auto">

<div id="siteModal"></div>

<div id="sitePage">

  <div id="siteHeader">
      <div id="siteHeaderImageContainer">
        <img id="siteHeaderImage" src="<?php echo THEME_PATH;?>/images/logo.png" border="0"/>
      </div>
      <div id="siteToolbar" class="siteToolbar">
        <div class="siteToolbarGroup left">
          <div <?php echo $backStyle;?> class="siteToolbarButton siteToolbarButtonBegin" onclick="document.pageForm.action.value='back';document.pageForm.submit();">Back</div>
          <div class="siteToolbarButton siteToolbarButtonEnd" onclick="document.pageForm.action.value='next';document.pageForm.submit();">Next</div>
        </div>
      </div>
  </div>

  <div id="siteBody">

    <div id="siteContent">
      <?php echo $siteContent;?>
    </div>
    
  </div>
                                                                
</div>


</body>
</html>

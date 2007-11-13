<?

//provide an edit link it the user is allowed
if (bitset_compare(CUSTOM_BITSET,OBJ_EDIT,null) || bitset_compare(CUSTOM_BITSET,OBJ_MANAGE,OBJ_ADMIN)) 
  $siteContent = "<div style=\"float:right;margin-right:5px\">
                  <img title=\""._VIEW_DISCUSS."\" onClick=\"location.href='index.php?module=document&includeModule=docdiscussion&objectId=".$objectId."'\" src=\"".THEME_PATH."/images/icons/talk.gif\" border=0>
                  <img title=\""._EDIT_FILE."\" onClick=\"openModuleWindow('docedit','$objectId',800,600);\" src=\"".THEME_PATH."/images/icons/edit.png\" border=0 style=\"padding-left:3px\">
                  <img title=\""._VIEW_AS_PDF."\" onClick=\"location.href='index.php?module=docpdf&objectId=".$objectId."';\" src=\"".THEME_PATH."/images/icons/pdf.png\" border=0 style=\"padding-left:3px\">
                  </div>";

$siteContent .= "<div>".returnNavList($conn,$objParent,null,$objName)."</div><br>";

$opt = null;
$opt["hideHeader"] = 1;

//encapsulating in a table forces the div to the proper width in safari and konqueror
$opt["content"] = "<table style=\"width:100%\" cellpadding=0 cellspacing=0><tr><td>".$xhtml."</td></tr></table>";

$siteContent .= sectionDisplay($opt);

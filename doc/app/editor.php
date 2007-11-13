<?

function loadEditor($textName,$textValue,$parentId) {

  return loadFckEditor($textName,$textValue,$parentId);

}

function loadFckEditor($textName,$textValue,$parentId) {

  include("fckeditor/fckeditor.php") ;

  $siteUrl = SITE_URL;

  if (!$toolbar) $toolbar = "docmgr";

  $sBasePath = $siteUrl."fckeditor/";
  $oFCKeditor = new FCKeditor($textName) ;
  $oFCKeditor->BasePath	= $sBasePath ;
  $oFCKeditor->Value = $textValue;
  if (BROWSER=="ie") $oFCKeditor->Height = "500px";	//use fixed height for i.e.
  else $oFCKeditor->Height = "87%";
  $oFCKeditor->Config['EditorAreaCSS'] = $siteUrl."themes/default/css/site.css";
  $oFCKeditor->Config['SkinPath'] = $sBasePath . 'editor/skins/silver/' ;
  $oFCKeditor->ToolbarSet = $toolbar;
  $oFCKeditor->Config['parentId'] = $parentId;
  $oFCKeditor->Config['sessionId'] = session_id();
  $oFCKeditor->Config['site_url'] = SITE_URL;
  $oFCKeditor->Config['theme_path'] = THEME_PATH;
  $oFCKeditor->Config['DefaultLanguage'] = LANG_I18N; 
  $oFCKeditor->Config['langFile'] = SITE_URL."lang/".LANG_I18N.".js"; 
  $oFCKeditor->Config['objDir'] = OBJECT_DIR;
  return $oFCKeditor->CreateHtml() ;
}


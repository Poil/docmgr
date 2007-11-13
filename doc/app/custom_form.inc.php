<?
/********************************************************************
  contains functions for creating custom forms 
  specific to docmgr
  
  created:  08-04-2005
  
********************************************************************/

function keywordText($info,$curValue = null) {

  extract($info);	//gives us title,name, size and type

  if (!$size) $size = "20";

  if ($style) $style = "style=\"".$style."\"";
  else $style = null;

  $str = "<div class=\"formHeader\">
          ".$title."
          </div>
          <input type=text ".$style." name=\"".$name."\" id=\"".$name."\" size=\"".$size."\" value=\"".$curValue."\">
          ";
          

  return $str;

}

function keywordDropdown($info,$curValue = null) {

  extract($info);	//gives us title,name,type,size and option

  if (!$size) $size = 1;

  if ($style) $style = "style=\"".$style."\"";
  else $style = null;
  
  $str = "<div class=\"formHeader\">
          ".$title."
          </div>
          <select ".$style." name=\"".$name."\" id=\"".$name."\" size=\"".$size."\">\n";
          
  foreach ($option AS $opt) {          

    if ($curValue==$opt) $select = " SELECTED ";
    else $select = null;

    $str .= "<option ".$select." value=\"".$opt."\">".$opt."\n";

  }

  $str .= "</select>\n";
  
  return $str;

}




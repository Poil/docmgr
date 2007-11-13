<? 

include("app/editor.php");

if ($successMessage) {
    
    $content = "<br><div class=\"successMessage\">".$sucessMessage."</div>\n";

} 

    $content = "

        <form  name=\"pageForm\" method=\"post\">
        
        <input type=hidden name=\"objectId\" id=\"objectId\" value=\"".$objectId."\">
        <input type=hidden name=\"pageAction\" id=\"pageAction\" value=\"\">
        <input type=hidden name=\"filename\" id=\"filename\" value=\"".$info["name"]."\">
        
        
        <br>
        ".loadEditor("editorContent",$text,$parentId)."
        
        </form>

        ";

$btnstr = "
        <input class=\"submitSmall\" type=\"button\" onClick=\"submitEditForm();\" name=\"saveText\" value=\""._SUBMIT_CHANGES."\">
        <input class=\"submitSmall\" type=\"button\" onClick=\"rejectEditForm();\" name=\"saveText\" value=\""._CLOSE_WINDOW."\">
        ";

$header = "<div style=\"float:right\">".$btnstr."</div>
          "._EDITFILE." \"".$info["name"]."\"";

$opt = null;
$opt["leftHeader"] = $header;
$opt["rightHeader"] = $btnstr;
$opt["content"] = $content;
$siteContent .= sectionDisplay($opt);

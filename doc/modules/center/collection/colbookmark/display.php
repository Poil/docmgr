<? 

$content = "

        <form  name=\"pageForm\" method=\"post\">
        
        <input type=hidden name=\"objectId\" id=\"objectId\" value=\"".$objectId."\">
        <input type=hidden name=\"module\" id=\"module\" value=\"colbookmark\">
        
        
        <br>
        <div class=\"formHeader\" style=\"padding-bottom:5px\">"._ENTER_BOOKMARK_NAME."</div>
        <input type=text name=\"bookmarkName\" id=\"bookmarkName\" value=\"".$info["name"]."\">
        <input type=submit name=\"bookmarkObject\" value=\""._SUBMIT."\">
        </form>
        ";        

$opt = null;
$opt["leftHeader"] = _BOOKMARK_THIS_COLLECTION;
$opt["content"] = $content;
$siteContent .= sectionDisplay($opt);

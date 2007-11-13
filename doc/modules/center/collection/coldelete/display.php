<? 

if ($successMessage) ajaxSelfClose();

$content = "

        <form  name=\"pageForm\" method=\"post\">
        
        <input type=hidden name=\"objectId\" id=\"objectId\" value=\"".$objectId."\">
        <input type=hidden name=\"module\" id=\"module\" value=\"coldelete\">
        <input type=hidden name=\"pageAction\" id=\"pageAction\" value=\"\">
        
        <div class=\"modalText\">"._DELETE_CONFIRM."?</div>
        <div class=\"modalButtons\">
        <input type=button onClick=\"self.close();\" value=\""._CANCEL."\">
        &nbsp;&nbsp;&nbsp;&nbsp;
        <input type=submit name=\"deleteObject\" value=\""._OKAY."\">
        </div>
        </form>
        ";        

$opt = null;
$opt["hideHeader"] = 1;
$opt["content"] = $content;
$siteContent .= sectionDisplay($opt);

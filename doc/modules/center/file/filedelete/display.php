<? 

if ($successMessage) ajaxSelfClose();

$pageContent .= "

        <form  name=\"pageForm\" method=\"post\">
        
        <input type=hidden name=\"objectId\" id=\"objectId\" value=\"".$objectId."\">
        <input type=hidden name=\"module\" id=\"module\" value=\"filedelete\">
        <input type=hidden name=\"pageAction\" id=\"pageAction\" value=\"\">
        
        <br>
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
$opt["content"] = $pageContent;
$siteContent = sectionDisplay($opt);
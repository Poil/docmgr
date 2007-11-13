<? 

if ($successMessage) ajaxSelfClose();

$content = "

        <form  name=\"pageForm\" method=\"post\">
        
        <input type=hidden name=\"objectId\" id=\"objectId\" value=\"".$objectId."\">
        <input type=hidden name=\"objectAction\" id=\"objectAction\" value=\"\">
        
        
        <br>
        <div class=\"modalText\">"._DELETE_CONFIRM."?</div>
        <div class=\"modalButtons\">
        <input type=button onClick=\"self.close();\" value=\""._CANCEL."\">
        &nbsp;&nbsp;&nbsp;&nbsp;
        <input type=submit name=\"deleteObject\" value=\""._OKAY."\" onClick=\"getObjIds()\">
        </div>
        </form>
        ";        

$opt = null;
$opt["hideHeader"] = 1;
$opt["content"] = $content;
$siteContent .= sectionDisplay($opt);

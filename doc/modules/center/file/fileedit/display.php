<? 

if ($successMessage) {
    
    $pageContent .= "<br><div class=\"successMessage\">".$sucessMessage."</div>\n";

} 

    $pageContent .= "

        <form  name=\"pageForm\" method=\"post\">
        
        <input type=hidden name=\"objectId\" id=\"objectId\" value=\"".$objectId."\">
        <input type=hidden name=\"module\" id=\"module\" value=\"fileedit\">
        <input type=hidden name=\"pageAction\" id=\"pageAction\" value=\"\">
        <input type=hidden name=\"includeModule\" id=\"includeModule\" value=\"fileedit\">
        <input type=hidden name=\"filename\" id=\"filename\" value=\"".$info["name"]."\">
        
        <br>
        <div class=\"formHeader\">"._FILE.":</div>
        ";
    
    if (!$error) {
        $pageContent .= "<textarea name=\"text\" id=\"text\">".$text."</textarea>";
    } else {
        $pageContent .= "<br><div class=\"errorMessage\">".$error."</div>\n";
        $disabled = " disabled";
    }

    
    $pageContent .= "</form>\n";
    
$btnstr = "
        <input type=\"button\" class=\"submitSmall\" onClick=\"submitEditForm();\" name=\"saveText\" value=\""._SAVE."\"".$disabled.">
        <input type=\"button\" class=\"submitSmall\" onClick=\"rejectEditForm();\" name=\"saveText\" value=\""._CLOSE_WINDOW."\">
        ";
        

$header = "<div style=\"float:right\">".$btnstr."</div>
          "._EDITFILE." ".$info["name"];

$opt = null;
$opt["leftHeader"] = $header;
$opt["content"] = $pageContent;
$siteContent .= sectionDisplay($opt);

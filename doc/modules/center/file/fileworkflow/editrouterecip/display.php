<?

//if workflow is owned by this user, then show the option to be notified
//when workflow is complete.
if ($curaccount==USER_ID && defined("EMAIL_SUPPORT")) {

  if ($curnotify=="t") $check = " CHECKED ";
  else $check = null;

  $emailNotify = "<div style=\"padding-top:5px;padding-bottom:5px;\">
                  <input type=checkbox ".$check." onClick=\"setEmailNotify()\" name=\"emailNotify\" id=\"emailNotify\" value=\"1\">
                  "._EMAIL_WHEN_WF_COMPLETE."
                  </div>
                  ";

} else $emailNotify = null;
                

$onPageLoad = "loadPage()";
$content =  jsCalLoad()."
            <form name=\"pageForm\" method=\"post\">
            <input type=hidden name=\"stage\" id=\"stage\" value=\"1\">            
            <input type=hidden name=\"pageAction\" id=\"pageAction\" value=\"\">
            <input type=hidden name=\"routeId\" id=\"routeId\" value=\"".$routeId."\">
            <div id=\"ctrlDiv\">
              <a href=\"javascript:loadTemplateList()\">["._SHOW_TEMPLATES."]</a>
              &nbsp;|&nbsp;
              <a href=\"javascript:saveTemplate()\">["._SAVE_TEMPLATE."]</a>
              &nbsp;|&nbsp;
              <a href=\"javascript:addStage()\">["._CREATE_STAGE."]</a>
              ".$emailNotify."
            </div>
            
            <div class=\"cleaner\">&nbsp;</div>
            <div id=\"stagecol\"></div>
            </form>
            ";

           
$btnstr = "<input type=button onClick=\"javascript:beginCloseWindow()\" value=\""._BEGIN_ROUTE_DIST."\" class=\"submitSmall\">
           <input type=button onClick=\"javascript:closeWindow()\" value=\""._CLOSE_WINDOW."\" class=\"submitSmall\">
           ";

$opt = null;
$opt["leftHeader"] = "<div style=\"float:right\">".$btnstr."</div>Edit Workflow Recipients";
$opt["content"] = $content;
$siteContent = sectionDisplay($opt);


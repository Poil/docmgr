<?

$onPageLoad = "document.loginForm.login.focus()";

$siteContent .= "<!--EDEV LOGIN FORM-->\n";
$siteContent .= "<div class=\"login_form\">\n";

//display any errors/warnings/messages
if ($_GET["timeout"] == "true") $siteContent .= "<p class=\"errorMessage\">"._SESSION_TIMED_OUT."</p>\n";
else { if ($show_login_form=="incorrect") $siteContent .= "<p class=\"errorMessage\">"._LOGIN_ERROR."</p>\n";}

if (defined ("WARNING_BANNER")) $siteContent .= "<p class=\"errorMessage\">".WARNING_BANNER."</p>";

//display the main login form
$siteContent .= "<h1>"._LOGIN_INTRO."</h1>\n";
$siteContent .= "<form name=\"loginForm\" method=\"post\">\n";
$siteContent .= "<input type=\"hidden\" name=\"module\" value=\"".$module."\">\n";
$siteContent .= "<input type=\"hidden\" name=\"queryString\" id=\"queryString\" value=\"".$queryString."\">\n";
$siteContent .= "<p class=\"form_input\">"._USERNAME.": <input type=\"text\" id=\"login\" name=\"login\" class=\"input_text\"></p>\n";
$siteContent .= "<p class=\"form_input\">&nbsp;"._PASSWORD.": <input type=\"password\" name=\"password\" class=\"input_text\"></p>\n";

//allow the user to save their session info in a cookie, if allowed in the site's config
if (defined("USE_COOKIES")) $siteContent .= "<p class=\"form_input\"><input type=\"checkbox\" name=\"savePassword\" id=\"savePassword\" value=\"yes\">"._LOGIN_SAVE."</p>\n";

//show the submit button
$siteContent .= "<p class=\"form_submit\"><input type=\"submit\" name=\"submitlogin\" value=\""._DO_LOGIN."\"></p>\n";

//additional content from the database
if ($loginTextValue) $siteContent .= $loginTextValue;

//allow the user to request a new account, if allowed in the site's config
if (defined("REQUEST_ACCOUNT")) $siteContent .= "<a class=\"login_request_account\" href=\"index.php?module=accountapply\">Apply for a User Name</a></b>";

//close the section out and put the focus on the username textbox
$siteContent .= "</form></div>\n";

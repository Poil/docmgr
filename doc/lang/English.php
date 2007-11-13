<?
/*****************************************************************************
	This file is broken down by module, and common text is put
	in its own section.  To alter module names and descriptions,
	you'll need to see the site_properties table.  As for the
	permission descriptions, you'll want the auth_permissions table
*****************************************************************************/

//language charset for the browser
define("LANG_CHARSET","ISO-8859-1");

//the i18n abbr for this language
define("LANG_I18N","en");	

/************ 0.58 defines **************/
define("_VIEW_AS_PDF","View As PDF");
define("_DOC_EMPTY_PDF_ERROR","The document is empty.  Cannot create PDF File");
define("_ADMINPERM_UPDATE_ERROR","You are not allowed to manipulate administrative permissions");

define("_LT_OBJ_WORKFLOW_BEGIN","Workflow route started for object"); 
define("_LT_OBJ_WORKFLOW_END","Workflow route completed for object"); 
define("_LT_OBJ_WORKFLOW_VIEW","Object workflow view completed"); 
define("_LT_OBJ_WORKFLOW_EDIT","Object workflow edit completed"); 
define("_LT_OBJ_WORKFLOW_APPROVE","Object workflow approval completed"); 
define("_LT_OBJ_WORKFLOW_REJECT","Object workflow entry rejected"); 
define("_LT_OBJ_WORKFLOW_CLEAR","Object workflow routes cleared"); 

/************ 0.57 defines **************/
define("_PREVIEW_FILE","Preview File");
define("_EMAIL_DOC","Email Document");
define("_RELATE_SELF_ERROR","An object can not be related to itself");
define("_RELATED_OBJECTS","Related");
define("_ALL_KEYWORDS","All Keywords");
define("_REVERSE_RELATE","Make relation go both ways");
define("_MY_ROUTED_DOCS","My Routed Documents");
define("_OBJ_VIEW_PERM_ERROR","You do not have permissions to view this object");
define("_HIDDEN","Hidden");

/************ 0.56 defines **************/

define("_CLEAR_ALL","Clear All");
define("_VIEW_DISCUSS","View object discussion");
define("_MT_DOCDISCUSSION","Discussion");
define("_MTDESC_DOCDISCUSSION","Participate in discussion of this document");

/**********************************************************************************/

define("_OBJ_MOVE_PERM_ERROR","You do not have permissions to move this object");
define("_MT_NEWDOC","Create Document");
define("_MT_DOCUMENT","Document");
define("_DOC_CONTENT","Document Content");
define("_SAVE_ROUTE","Save This Route");
define("_SELECT_ROUTE","Select Route");
define("_SEND_FILE_ON_UPDATE","Email file with an update notification");
define("_SEND_FILE_ON_CREATE_UPDATE","Email file with a create/update notification");
define("_VIEW_OBJ_PROPERTIES","You can view the object by clicking the following link");
define("_TEMPLATE_EDIT_PERM_ERROR","You do not have permission to edit this template");
define("_MT_HOMEDIR","Home Directory");
define("_MTDESC_HOMEDIR","Edit user's home directory");
define("_SHOW_TEMPLATES","Show Templates");
define("_SAVE_TEMPLATE","Save Template");
define("_CREATE_STAGE","Create New Stage");
define("_EMAIL_WHEN_WF_COMPLETE","Email me when workflow is complete");
define("_WORKFLOW_COMPLETE","A DocMGR workflow is complete");
define("_WORKFLOW_COMPLETE_MSG","The pending workflow created by you for this file is complete.");
define("_INVALID_CHAR_IN_NAME","There is an invalid character in the object name.  The name cannot contain");

define("_FILE_VERSION","File Revision");
define("_MY_PROFILE","My Profile");
define("_USE_LANG","Use This Language");
define("_EMAIL_COLLECTION","Email link to collection");
define("_SUBSCRIBE_COL","Manage collection subscription settings");
define("_NOTIFY_ME_COLEVENT","Notify me of the following events for members of this collection");
define("_TASK_NOTES","Task Notes");
define("_OBJ_TYPE_ERROR","You cannot open this object with this module");
define("_EMAIL_SUCCESS","Object emailed successfully");
define("_EMAIL_ERROR","Object could not be emailed");
define("_CLICK_VIEW_PROP","Click To View");
define("_CHECK_CLEAR_ALERT","Check To Clear");
define("_LAST_SUCCESSFUL_LOGIN","Last successful login");
define("_FAILED_ATTEMPTS","Failed attempt(s)");

define("_SELF_PARENT_ERROR","An object can not be its own parent.  Update failed."); 
define("_SELECT_NEW_COLLECTION","Select new collection"); 
define("_MOVE_OBJECT","Move Objects"); 
define("_BEGIN_ROUTE_DIST","Begin Route"); 
define("_CLOSE_WINDOW","Close Window"); 
define("_TASK_ASSIGN_ERROR","A task has been assigned to this user.  You cannot delete the entry"); 
define("_UPDATE_SELECTION","Update Selection"); 
define("_PDF_SUPPORT","PDF Support"); 
define("_IMAGE_TIFF_SUPPORT","Image & Tiff Support"); 
define("_MISC_SUPPORT","Miscellaneous Support"); 
define("_TASK","Task"); 
define("_DUE","Due"); 
define("_MT_FILE","Uploaded File"); 
define("_NO_PENDING_TASKS_ASSIGNED","No pending tasks assigned to you"); 
define("_GROUP_EDIT_PERM_ERROR","You are not allowed to edit this group"); 
define("_OPENING_COLLECTION","Opening Collection"); 
define("_OBJ_WITH_NAME","A different object with the name");	
define("_ALREADY_EXISTS_IN","already exists in"); 
define("_OR","or"); 
define("_PLEASE_WAIT","Please wait..."); 
define("_SPECIFY_NAME_ERROR","You must specify a name"); 
define("_SPECIFY_ADDR_ERROR","You must specify an address"); 
define("_SHOW_MATCHING","Show Matching");
define("_FILES","Files");
define("_COLLECTIONS","Collections");
define("_URLS","Urls");
define("_SAVESEARCH","Saved Searches");

/* begin 0.53.1 */

define("_NO_SUBJECT_ERROR","No subject was entered");

/* end new defines */

define("_DELETE_OBJECT","Delete This Object");
define("_OKAY","Okay");
define("_CANCEL","Cancel");
/************************************
  File Editing
************************************/
  
define("_EDITFILE", "Edit");
define("_SAVE", "Save file");
define("_EDIT_REVNOTE","edited online");
define("_FILE_TOO_LARGE","file too large for online editing!");
define("_EDIT_FILE","Edit file");



//find module
define("_SAVE_THIS_SEARCH","Save This Search");
define("_SEARCH_AGAIN","Search Again");
define("_SEARCH_TYPE","Search Type");
define("_OWNED_BY_USER","Owned By User");
define("_IN_COLLECTION","In Collection");
define("_NORMAL","Normal");
define("_KEYWORD","Keyword");

//save search module
define("_MT_SAVESEARCH","Saved Search");
define("_MT_SAVESEARCHCRITERIA","Saved Search Criteria");
define("_MT_SAVESEARCHPARENT","Saved Search Parents");
define("_MT_SAVESEARCHPROP","Saved Search Properties");
define("_MT_SAVESEARCHPERM","Saved Search Permissions");
define("_MTDESC_SAVESEARCHCRITERIA","Edit the criteria of this query");
define("_MTDESC_SAVESEARCHPARENT","Edit the owning collections of this object");
define("_MTDESC_SAVESEARCHPROP","Edit the generic properties of this query");
define("_MTDESC_SAVESEARCHPERM","Edit user and group access to this object");

//create save search module
define("_BOOKMARK","Bookmark");
define("_SELECT_DESTINATION","Select A Destination");
define("_CREATE_QUERY","Create Saved Query");

/**************************************************
  anonaccess module
**************************************************/
define("_LINK_EXPIRED","The link has expired.  Please email the sender and request a new link to the file"); 
define("_PIN_INVALID","The PIN number you entered is invalid.  Please verify the PIN in your email and try again."); 
define("_FILE_VIEW_ERROR","There was an error viewing this file"); 
define("_ENTER_PIN","Please enter your PIN to access"); 
define("_LINK_EXPIRES_ON","This link expires on"); 
define("_OPEN_FILE","Open File"); 


/*************************************
  Login Form
*************************************/
define("_LOGIN_ERROR","Your Username and/or Password was incorrect");
define("_LOGIN_INTRO","Please enter your Username and Password");
define("_USERNAME","Username");
define("_PASSWORD","Password");
define("_LOGIN_SAVE","Remember My Login Information");
define("_DO_LOGIN","Login"); 
define("_SESSION_TIMED_OUT","Your session has timed out, please log in again");


/*************************************
  Left column modules
*************************************/

//browse collections
define("_BROWSE_COL","Browse Collections");

//messages
define("_DOCMGR_MESSAGES","DocMGR Messages");
define("_WELCOME","Welcome");
define("_PROFILE","Profile");
define("_CHANGE_LANGUAGE","Change Language");

//search
define("_SEARCH_FOR_FILES","Search For Files");
define("_SEARCH","Search");
define("_ADVANCED","Advanced");
define("_WITHIN_COLLECTION","Within This Collection");


/************************************
  Center Column Modules
************************************/

/************************************
  Home Module
************************************/
define("_MT_HOME","Home");
define("_RECENT_ADD_FILES","Recently Added Files");
define("_BOOKMARK_COLLECTION","Bookmarked Collections");
define("_CLICK_OPEN","Click To Open");
define("_MY_TASKS","My Tasks");
define("_CLICK_MANAGE","Click To Manage");
define("_MY_SUBSCRIPTIONS","My Subscriptions");
define("_MY_CHECKED_OUT_FILES","My Checked Out Files");
define("_CLICK_CHECKIN","Click To Checkin");
define("_BOOKMARK_REMOVE_CONFIRM","Are you sure you want to remove this bookmark");
define("_NO_FILES_DISPLAY","No Files To Display");
define("_NO_ALERTS_DISPLAY","No Alerts To Display");
define("_NO_BOOKMARKS_DISPLAY","No Bookmarks To Display");
define("_REMOVE","Remove");
define("_DATE","Date"); 
define("_RECIPIENT","Recipient"); 
define("_FILE_REMOVED","File Removed From System"); 
define("_UNKNOWN","Unknown"); 
define("_FILE_VIEW_NOTIFY","File View Notification"); 
define("_VIEWED_BY","was viewed by"); 
define("_VIEWED_DATE","on"); 
define("_VIEWED_BY_IP","from IP address:"); 
define("_CLICK_DETAILS","Click For More Details"); 
define("_OBJ_VIEWED_BY","Object viewed by"); 
define("_OBJ_EMAILED_TO","Object emailed to"); 

/***********************************
  Import Module
***********************************/
define("_MT_IMPORT","Import");
define("_BACK","Back");
define("_IMPORT_OBJECTS","Import Objects");
define("_DELETE_AFTER_IMPORT","Delete Files After Import");
define("_SELECT_DEFAULT_COLLECTION","Select Default Collection");
define("_FILE_PREF","File Preferences");
define("_GROUPS","Groups");
define("_INHERIT_PERM_PARENT","Inherit Permissions From Parent");
define("_FILE_DISPLAY_ERROR","There are no files to display");

/***********************************
  Permissions Selector
***********************************/
define("_ACCOUNT_GROUP","Account/Group");
define("_MANAGE","Manage");
define("_EDIT","Edit");
define("_VIEW","View");
define("_APPROVE","Approve");

/***********************************
  Find Module
***********************************/
define("_MT_FIND","Find");
define("_OBJECT_SELECT_ERROR","You must select at least one object first");
define("_DELETE_ALL_CONFIRM","Are you sure you want to delete all selected objects");
define("_DELETE_CONFIRM","Are you sure you want to delete this object");
define("_SEARCH_ERROR","You didn't enter anything to search for");
define("_DISPLAYING","Displaying");
define("_NAME","Name");
define("_DESCRIPTION","Description");
define("_EDITED","Edited");
define("_OPTIONS","Options");
define("_RANK","Rank");
define("_ADVANCE_DOC_SEARCH","Advanced Document Search");
define("_SEARCH_FOR_WILDCARD","Search For (Use \"*\" For Wildcards)");
define("_WHEN_FILE_WAS","When File Was");
define("_LAST_MOD","Last Modified");
define("_ENTER_INTO_SYSTEM","Entered Into System");
define("_DURING","During");
define("_ANY_DATE","Any Date");
define("_ON_DATE","On Date");
define("_TIME_PERIOD","Time Period");
define("_BEFORE","Before");
define("_AFTER","After");
define("_FROM","From");
define("_ON","On");
define("_SEARCH_TOOK","Search Took");
define("_SECONDS","Seconds");
define("_TO","To");
define("_SEARCH_IN","Search In");
define("_FILE_CONTENTS","File Contents");
define("_FILE_NAME","File Name");
define("_FILE_SUMMARY","File Summary");
define("_SHOW_MATCH_COLLECTION","Show Matching Collections");
define("_RESULT_PER_PAGE","Results Per Page");
define("_LIMIT_TO","Limit To");
define("_YES","Yes");
define("_NO","No");
define("_SEARCH_FILES","Search Files");
define("_SEARCH_FOR","Search For");
define("_KEYWORD_SEARCH","Keyword Search");
define("_MOVE","Move");
define("_DELETE","Delete");
define("_RESULTS","Results"); //(Results X - X For "searchstring")
define("_OF","Of");
define("_FOR","For");
define("_RESET_FORM","Reset Form");
define("_ENTER_BOOKMARK_NAME","Please type your bookmark name");
define("_AVAIL","Available");

/************************************
  Browse Module
************************************/
define("_NO_SUMMARY_AVAIL","No Summary Available");
define("_MT_BROWSE","Browse");
define("_LOCATION","Location");
define("_VIEW_THUMB","View As Thumbnails");
define("_VIEW_LIST","View As List");
define("_ADD_NEW","Add New");
define("_COLLECTION","Collection");
define("_UPLOADED_FILE","Uploaded File");
define("_PROPERTIES","Properties");
define("_DELETE_COLLECTION","Delete This Collection");
define("_ZIP_COLLECTION","Zip and Download This Collection");
define("_BOOKMARK_THIS_COLLECTION","Bookmark Collection");
define("_CHECKOUT_FILE","Checkout File");
define("_UPDATE_FILE","Update File");
define("_DELETE_FILE","Delete This File");
define("_EMAIL_FILE","Email File");
define("_SUBSCRIBE_FILE","Manage File Subscription Settings");
define("_DELETE_URL","Delete This URL");
define("_SUBSCRIBE_URL","Manage URL Subscription Settings");
define("_SELECT_ALL_OBJECTS","Select All Objects");

/*************************************
  Browse and Find file processing
*************************************/
define("_COLLECTION_PARENT_ERROR","You cannot make a collection the parent of itself");
define("_OBJECT_MOVE_SUCCESS","Object moved successfully");
define("_OBJECT_MOVE_ERROR","Object could not be moved");
define("_OBJECT_REMOVE_SUCCESS","Object removed successfully");
define("_OBJECT_REMOVE_ERROR","Object removal failed");
define("_COLLECTION_BOOKMARK_SUCCESS","Collection bookmarked successfully");
define("_COLLECTION_BOOKMARK_ERROR","Collection bookmark failed");

/*************************************
  Other main level links
*************************************/
define("_HOME","Home");
define("_LOGOUT","Logout");


/*************************************
  Admin Module
*************************************/
define("_MT_ADMIN","Admin");

/*************************************
  Account Manager
*************************************/
define("_MT_ACCOUNTS","Account Manager");
define("_MTDESC_ACCOUNTS","Use this tool to edit existing user accounts, or to create new ones.");
define("_CREATE_NEW_ACCOUNT","Create New Account");
define("_RESULTS_FOR","Results For");
define("_NOW_EDITING","Now Editing");

/************************************
  Account Profile
************************************/
define("_MT_ACCOUNTPROFILE","Profile");
define("_MTDESC_ACCOUNTPROFILE","Update account profile settings");
define("_PROFILE_INFO","Profile Information");
define("_FIRST_NAME","First Name");
define("_LAST_NAME","Last Name");
define("_LOGIN","Login");
define("_EMAIL","Email");
define("_PHONE","Phone");

/***********************************
  Account Password
***********************************/
define("_MT_ACCOUNTPASSWORD","Change Password");
define("_MTDESC_ACCOUNTPASSWORD","Update or reset your password");
define("_PASSWORD_NOMATCH","The passwords did not match");
define("_MUST_ENTER_PASSWORD","You must enter a password");
define("_ENTER_NEW_PASSWORD","Enter your new password");
define("_AGAIN_TO_CONFIRM","Again To Confirm");
define("_RESET_PASSWORD","Reset Password");

/***********************************
  Account Permissions
***********************************/
define("_MT_ACCOUNTPERM","Permissions");
define("_MTDESC_ACCOUNTPERM","Modify account permissions for this app");
define("_BASIC_ACCOUNT_PERM","Basic Account Permissions");
define("_ENABLE_ACCOUNT","Enable this account");

/***********************************
  Account Groups
***********************************/
define("_MT_ACCOUNTGROUPS","Account Groups");
define("_MTDESC_ACCOUNTGROUPS","Manage groups this account belongs to");
define("_GROUP_EXISTS","A group with this name already exists");

/***********************************
  Account Remove
***********************************/
define("_MT_ACCOUNTREMOVE","Delete This Account");
define("_MTDESC_ACCOUNTREMOVE","Remove this account from the system");
define("_ACCOUNT_REMOVE_CONFIRM","Are you sure you want to delete the account");
define("_ACCOUNT_REMOVE_CONFIRM_ERROR","You must confirm you want to delete this account before removing it");
define("_REMOVE_ACCOUNT","Delete Account");


/*************************************
  Database statistics
*************************************/
define("_MT_DBSTAT","Database Statistics");
define("_MTDESC_DBSTAT","View DocMGR's filesystem and database statistics");
define("_FILESYSTEM","Filesystem");
define("_NUMBER_FILES","Number of files");
define("_NUMBER_USERS","Number of Users");
define("_NUMBER_COLLECTIONS","Number of Collections");
  

/*************************************
  Group Admin
*************************************/
define("_MT_GROUPADMIN","Group Administration");
define("_MTDESC_GROUPADMIN","This utility allows for management of account groups");
define("_SELECT_GROUP","Select A Group");
define("_ADD_GROUP","Add New Group");
define("_UPDATE_GROUP","Update Group");
define("_DELETE_GROUP","Delete Group");
define("_GROUP_ENTER_ERROR","You must enter a name to update this group");


/*************************************
  External Applications
*************************************/
define("_MT_EXTERNAPP","External Applications");
define("_MTDESC_EXTERNAPP","For viewing optional applications detected by DocMGR");
define("_SUPPORT_STATUS","Support Status");
define("_SUPPORT_DETAILS","Support Details");
define("_RELATED_BINARIES","Related Binaries");
define("_SUPPORT","Support");
define("_SUPPORT_ENABLED","support is enabled");
define("_SUPPORT_DISABLED","support is disabled");
define("_BINARY_FOUND","binary found");
define("_BINARY_NOTFOUND","binary cannot be found in");
define("_DISABLED_IN_CONFIG","Disabled in config file");
define("_SUPPORT_NOT_COMPILED","support is not compiled into PHP");
define("_ENABLED","Enabled");
define("_DISABLED","Disabled");


/**************************************
  collection module
**************************************/
define("_MT_COLLECTION","Collection");
define("_MT_COLPROP","Collection Properties");
define("_MT_NEWCOLLECTION","Create New Collection");
define("_MT_COLPERM","Collection Permissions");
define("_MT_COLPARENT","Collection Parents");
define("_MTDESC_COLPROP","Edit the generic properties of this collection");
define("_MTDESC_COLPERM","Edit user and group access to this collection");
define("_MTDESC_COLPARENT","Edit the owning collections of this object");
define("_RESET_CHILD_PERM","Reset permissions on all objects below this collection");

/**************************************
  url module
**************************************/
define("_MT_URL","URL");
define("_MT_URLPROP","URL Properties");
define("_MT_URLPERM","URL Permissions");
define("_MT_URLPARENT","Collections");
define("_MTDESC_URLPROP","Edit the generic properties of this url");
define("_MTDESC_URLPERM","Edit user and group access to this url");
define("_MTDESC_URLPARENT","Edit the owning collections of this object");
define("_MT_NEWURL","Create New URL Link");
define("_URL_ADDR","URL Address");
define("_URL","URL");
define("_REDOWNLOAD_URL","Check here to download and reindex the page");
define("_DOWNLOAD_URL","Check here to download and index the page");


/***********************************
  File Module
***********************************/
define("_MT_FILEPROPERTIES","File Properties");
define("_MT_FILEPERM","File Permissions");
define("_MT_FILEPARENT","Collections");
define("_MT_FILEHISTORY","Revision History");
define("_MT_FILECHECKIN","File Update");
define("_MT_FILELOGS","Logs");
define("_MT_FILEDISCUSSION","Discussion");
define("_MT_FILEWORKFLOW","Document Routing");
define("_MT_FILETASK","My File Tasks");
define("_VIEW_FILE","View File");

/************************************
  File Properties
************************************/
define("_NONE","None");
define("_SUMMARY","Summary");
define("_VERSION","Version");
define("_SIZE","Size");
define("_OWNER","Owner");
define("_CREATED","Created By");
define("_LAST_MODIFIED","Last Modified");
define("_FILE_STATUS","File Status");
define("_AVAIL_EDIT","Available For Editing");
define("_CHECKED_OUT","Checked Out");
define("_CHECKOUT_BY","Checked Out By");
define("_CHECKOUT_ON","Checked Out On");
define("_LATEST_REVISION_NOTES","Latest Revision Notes");
define("_CLEAR_CHECKOUT_STATUS","Clear Checkout Status");
define("_CLEAR_CHECKOUT_WARNING","Warning!  This will unlock the object and allow others to edit it");

/************************************
  File Upload
************************************/
define("_KEEP_WINDOW_OPEN","Keep Window Open After Upload");
define("_FILE_UPLOAD_SUCCESS","File uploaded successfully");
define("_FILE_UPLOAD_ERROR","File upload failed");
define("_FILE_UPLOAD_SELECT_ERROR","There was an error with file selection");
define("_VIRUS_WARNING","Virus Warning"); 
define("_INVALID_MD5SUM_WARNING","Warning!  The md5sum for the file you wish to download does not match the value stored in the database for this revision of the file"); 

/************************************
  File Update
************************************/
define("_NOTES_FOR_REVISION","Notes For New Revision");
define("_UPLOAD","Upload");
define("_FILE","File");

/************************************
  Revision History
************************************/
define("_REVISION_NOTES","Revision Notes");
define("_MODIFIED","Modified");
define("_VIEW_THIS_VERSION","View This Version");
define("_PROMOTE_LATEST_VERSION","Promote To Latest Version");
define("_ENTRY","Entry");
define("_USER","User");
define("_REMOVE_THIS_VERSION","Remove"); 
define("_REVISION_REMOVE_CONFIRM","Are you sure you want to remove this revision"); 

/************************************
  File Discussion
************************************/
define("_POST_TOPIC","Post New Topic");
define("_BACK_TOPIC_LIST","Back To Topic List");
define("_REPLY_TOPIC","Reply To Topic");
define("_TOPIC","Topic");
define("_REPLIES","Replies");
define("_LAST_COMMENT","Last Comment");
define("_STARTED_BY","Started By");
define("_NO_DISCUSS_MESSAGES","There are no messages in the discussion board, yet");
define("_SUBJECT","Subject");
define("_MESSAGE","Message");
define("_AUTHOR","Author");
define("_POSTED","Posted");
define("_EDIT_POST","Edit This Post");
define("_DELETE_POST","Delete This Post");
define("_NEW_POST","New Post");
define("_DISCUSS_MSG_REMOVE","Are you sure you want to remove this message?  If this is the first post in a topic, it will remove the post and all replies to it!");

/************************************
  Document Workflow
************************************/
define("_CREATE_NEW_ROUTE","Create New Route");
define("_ROUTING_HISTORY","Routing History");
define("_ROUTING_STATUS","Routing Status");
define("_STATUS","Status");
define("_BEGIN","Begin");
define("_NO_COMMENT_POSTED","No comment posted");
define("_NO_RESULTS","No Results To Display");
define("_DATE_CREATED","Date Created");
define("_DATE_COMPLETED","Date Completed");
define("_NOT_DISTRIBUTED","Not Distributed");
define("_PENDING","Pending");
define("_COMPLETED","Completed");
define("_NOT_COMPLETE","Not Complete");
define("_RECIPIENTS","Recipients");
define("_NO_RECIPIENTS_DISPLAY","No recipients to display");
define("_DUE","Due");
define("_VIEW_DOCUMENT","View Document");
define("_EDIT_DOCUMENT","Edit Document");
define("_APPROVE_DOCUMENT","Approve Document");
define("_CLOSE_WINDOW","Close Window"); 
define("_PENDING_WORKFLOW_ERROR","A pending workflow entry exists for this object"); 
define("_REJECTED","Rejected"); 
define("_FORCE_COMPLETE","Force Complete"); 


/***********************************
  Workflow recipient editor
***********************************/
define("_ADD_RECIPIENT","Add Recipient");
define("_TASK_TYPE","Task Type");
define("_ADD_TO_STAGE","Add To Stage");
define("_CREATE_NEW_STAGE","Create New Stage");
define("_DUE_DATE","Due Date");
define("_ASSIGNED_TO","Assigned To");
define("_ROUTING_STAGES","Routing Stages");
define("_STAGE","Stage");
define("_IN_PROGRESS","In Progress");
define("_REJECTED_BY","Rejected By");
define("_DATE_SELECT_ERROR","You did not select a date");
define("_ACCOUNT_SELECT_ERROR","You did not assign the task to a user");
define("_ENTRY_STAGE_ALREADY_EXISTS","An entry for this account already exists at Stage");

/***********************************
  File Logs
***********************************/
define("_LAST_TEN_ENTRIES","Last 10 Entries"); 
define("_MY_ENTRIES","My Entries"); 
define("_VIRUS_SCANS","Virus Scans"); 
define("_EMAILS","Emails"); 
define("_FILE_VIEWS","File Views"); 
define("_CHECKINS_CHECKOUTS","Checkins/Checkouts"); 
define("_ALL_ENTRIES","All Entries"); 
define("_DATA","Data"); 
define("_ANONYMOUS","Anonymous Recipient");

/************************************
  File Emailing
************************************/
define("_EMAILING","Emailing");
define("_YOUR_EMAIL","Your Email Address");
define("_TO","To");
define("_COMMENTS","Comments");
define("_SEND_EMAIL","Send Email");
define("_EMAIL_TO_ERROR","You must enter a recipient for your email");
define("_EMAIL_FROM_ERROR","You must enter your email address as the sender");
define("_EVENT_NOTIFICATION","Event Notification For");
define("_TASK_NOTIFICATION","Task Notification For");
define("_FOLLOWING_EVENT_OCCURED","The following event occurred for");
define("_VIEW_FILE_TASK","You can view your task by clicking the following link");
define("_SEND_TO_ANON_RECIP","Send file to anonymous recipient"); 
define("_VALID_LINK_TIME","Length of time this link is valid"); 
define("_NO_EMAIL_ERROR","There is no email set in your profile."); 
define("_WEEK","Week"); 
define("_WEEKS","Weeks"); 
define("_HOURS","Hours"); 
define("_MONTH","Month"); 
define("_MINUTES","Minutes"); 
define("_DAYS","Days"); 
define("_NOTIFY_FILE_VIEW","Email me when file is viewed"); 

/************************************
  File Subscriptions
************************************/
define("_NOTIFY_ME_EVENT","Notify me for any of the following events");
define("_SEND_EMAIL_NOTIFY","Send email notification");
define("_EMAIL_PROFILE_ERROR","You do not have an email address set in your profile.
                              You must do this before you can receive email notifications
                              of events");
define("_UPDATE_SETTINGS","Update Settings");
define("_SUBSCRIPTION","Subscription");
define("_SETTING_UPDATE_SUCCESS","Settings updated successfully");
define("_SETTING_UPDATE_ERROR","Settings update failed");

/************************************
  File Tasks
************************************/
define("_YOUR_PENDING_TASK","Your Pending Task");
define("_INSTRUCTIONS","Instructions");
define("_ACCEPT","Accept");
define("_REJECT","Reject");
define("_VIEW_COMPLETE","View Complete");
define("_EDIT_COMPLETE","Edit Complete");
define("_APPROVE_TEXT","You may view this file by clicking \"View File\" to the left.
                        After reviewing the file, you may accept or reject the
                        approval by using the buttons below.
                        ");

define("_EDIT_TEXT","You may edit this file by clicking \"Checkout File\" to the left.
                        After editing and checking in your updated file, you may acknowledge you have
                        completed your changes by using the button below.
                        ");

define("_VIEW_TEXT","You may view this file by clicking \"View File\" to the left.
                        After editing and checking in the file, you may acknowledge you are
                        finished by using the button below.
                        ");



/************************************
  object log entries
************************************/
define("_LT_OBJ_CREATED","Object Created");
define("_LT_OBJ_PROP_UPDATE","Object Properties Updated");
define("_LT_OBJ_PERM_UPDATE","Object Permissions Updated");
define("_LT_OBJ_VIEWED","Object Viewed");
define("_LT_OBJ_CHECKED_OUT","Object Checked Out");
define("_LT_OBJ_CHECKED_IN","Object Checked In");
define("_LT_OBJ_VERSION_PROMOTE","Object Version Promoted");
define("_LT_OBJ_MOVED","Object Moved");
define("_LT_OBJ_EMAILED","Object Emailed");
define("_LT_OBJ_ANON_EMAILED","Object Emailed To Anonymous Recipient"); 
define("_LT_OBJ_ANON_VIEWED","Object Viewed By Anonymous Recipient"); 
define("_LT_OBJ_VIRUS_PASS","No viruses found in scan"); 
define("_LT_OBJ_VIRUS_FAIL","Viruses found in scan"); 
define("_LT_OBJ_VIRUS_ERROR","Error scanning the file"); 
define("_LT_OBJ_CHECKSUM_VERIFY_PASS","Digital signature verified"); 
define("_LT_OBJ_CHECKSUM_VERIFY_FAIL","Digital signature verification failed"); 


/************************************
  object alert entries
************************************/
define("_AT_OBJ_VIEW_ALERT","Object waiting for you to view");
define("_AT_OBJ_EDIT_ALERT","Object waiting for you to edit");
define("_AT_OBJ_COMMENT_ALERT","Object waiting for comment");
define("_AT_OBJ_COMMENT_POST_ALERT","Comment posted for object");
define("_AT_OBJ_APPROVAL_ALERT","Object awaiting approval");
define("_AT_OBJ_CHECKOUT_ALERT","Object checked out");
define("_AT_OBJ_CHECKIN_ALERT","Object checked in");
define("_AT_OBJ_TASK_ALERT","There is a pending task awaiting you for this object");


/************************************
  common messages and text
************************************/
define("_UPDATE_SUCCESS","The update was successful");
define("_UPDATE_ERROR","There was an error processing the update");
define("_CREATE_SUCCESS","The object was created successfully");
define("_CREATE_ERROR","There was an error creating the object");
define("_DELETE_SUCCESS","The object was removed successfully");
define("_DELETE_ERROR","There was an error removing the object");
define("_UPDATE","Update");
define("_SUBMIT","Submit");
define("_DELETE","Delete");
define("_CLEAR_FORM","Clear Form");
define("_SUBMIT_CHANGES","Submit Changes");
define("_AT","At");
define("_BY","By");
define("_WITH","With");

/***********************************
  Permissions Text
***********************************/
define("_PERM_ADMIN","Administrator");
define("_PERM_MANAGE_USERS","Can manage other users");
define("_PERM_MANAGE_GROUP","Can manage groups");
define("_PERM_INSERT_OBJECTS","Can insert objects into the system");
define("_PERM_EDIT_PROFILE","Can alter own profile");
define("_PERM_EDIT_PASSWORD","Can alter password"); //new


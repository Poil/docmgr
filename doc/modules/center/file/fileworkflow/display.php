<?

//return our account list for later
$opt = null;
$opt["conn"] = $conn;
$accountList = returnAccountList($opt);

/***************************************************************
	Display past workflow history for this object
****************************************************************/
$histString = createWorkflowHistory($conn,$accountList,$objectId);
$routeString = null;
$showCreateRoute = null;

//if there is no route id, check for an active one
if (!$routeId) {

	$sql = "SELECT id,status FROM dm_workflow WHERE object_id='$objectId' AND (status='pending' OR status='nodist');";
	$info = single_result($conn,$sql);
	
	if ($info) $routeId = $info["id"];
	else $showCreateRoute = 1;
	
	
} else {
	
	$sql = "SELECT status FROM dm_workflow WHERE id='$routeId'";
	$info = single_result($conn,$sql);
		
	if ($info["status"]!="pending" && $info["status"]!="nodist") $showCreateRoute = 1;

}

//show our route creation link
if ($showCreateRoute) 
	$routeString = "<a href=\"javascript:submitForm('createRoute');\" class=\"boldLink\">"._CREATE_NEW_ROUTE."</a><br><br>";

/******************************************************************
	If we are editing or creating a workflow instance,
	display the form and accompanying information below
******************************************************************/
if ($routeId) {

	$routeString .= createRouteStatus($conn,$accountList,$routeId);

}


//output our page content
$pageContent .= "
	<form name=\"pageForm\" method=\"post\">
	<input type=hidden name=\"pageAction\" id=\"pageAction\" value=\"\">
	<input type=hidden name=\"routeId\" id=\"routeId\" value=\"".$routeId."\">
	<input type=hidden name=\"includeModule\" id=\"includeModule\" value=\"fileworkflow\">
	<table width=100% border=0>
	<tr><td>
		".$routeString."
	</td></tr>
	<tr><td>
		<div class=\"formHeader\">
		"._ROUTING_HISTORY."
		</div>
		<div style=\"padding-left:10px;padding-top:5px\">
		<table width=450px>
		<tr><td width=150px style=\"font-weight:bold\">
			"._STATUS."
		</td><td width=150px style=\"font-weight:bold\">
			"._DATE_CREATED."
		</td><td width=150px style=\"font-weight:bold\">
			"._DATE_COMPLETED."
		</td></tr>
		</table>
		<div style=\"height:200px;width:90%;border:1px solid #DCDCDC;overflow:auto\"> 
		".$histString."
		</div>
		</div>
	</td></tr>
	</table>	

";


<?

function createWorkflowHistory($conn,$accountList,$objectId) {

	/***************************************************************
		Display past workflow history for this object
	****************************************************************/
	//get our history of workflow for this object
	$sql = "SELECT * FROM dm_workflow WHERE object_id='$objectId' ORDER BY id DESC";
	$list = list_result($conn,$sql);

	$histString = "<table width=100% cellpadding=0 cellspacing=0>\n";

	if ($list["count"]=="0") $histString .= "<tr><td class=\"errorMessage\">"._NO_RESULTS."</td></tr>\n";
	else {

		//loop through and output our results
		for ($i=0;$i<$list["count"];$i++) {
	
			$entry = &$list[$i];

			$histString .= "<tr >\n";


			//status
			$histString .= "<td style=\"border-bottom:1px solid #DCDCDC\" width=150px>";
			$histString .= "<a href=\"index.php?module=file&includeModule=fileworkflow&routeId=".$entry["id"]."\">";
			$histString .= displayStatus($entry["status"]);
			$histString .= "</a>";
			$histString .= "</td>\n";

			//the date of creation
			$histString .= "<td style=\"border-bottom:1px solid #DCDCDC\" width=150px>
					".date_view($entry["date_create"])."
					</td>\n";

			//date of completion
			if ($entry["date_complete"]) $histString .= "<td style=\"border-bottom:1px solid #DCDCDC\" width=150px>".date_view($entry["date_complete"])."</td>\n";
			else $histString .= "<td style=\"border-bottom:1px solid #DCDCDC\">"._NOT_COMPLETE."</td>\n";
	
			//get the user's login	
			//$key = array_search($entry["account_id"],$accountList["id"]);
			//$histString .= "<td style=\"border-bottom:1px solid #DCDCDC\">".$accountList["login"][$key]."</td>\n";

			$histString .= "</tr>\n";
		
		}

	}

	$histString .= "</table>\n";

	return $histString;	

}


function createRouteStatus($conn,$accountList,$routeId) {

	//get the basic info for this route
	$sql = "SELECT * FROM dm_workflow WHERE id='$routeId';";
	$routeInfo = single_result($conn,$sql);
    
	//get recip info for this route
	$sql = "SELECT * FROM dm_workflow_route WHERE workflow_id='$routeId' ORDER BY sort_order,id;";
	$recip = total_result($conn,$sql);

	if ($recip["count"] > 0) {

		$recipString = "<table width=100%>\n";

		for ($row=0;$row<$recip["count"];$row++) {

			$key = array_search($recip["account_id"][$row],$accountList["id"]);
			$recipName = $accountList["login"][$key];
			
			if ($recip["comment"][$row]) $recipComment = $recip["comment"][$row];
			else $recipComment = _NO_COMMENT_POSTED;
			
			$recipStatus = displayStatus($recip["status"][$row]);

			$recipString .= "<div style=\"padding-bottom:5px\">\n";
			$recipString .= "<div style=\"font-weight:bold\">".$recipName."\n";
			$recipString .= "<span style=\"font-weight:normal\">(".$recipStatus.")</span>\n";
			$recipString .= "</div>\n";
			$recipString .= "<div style=\"padding-left:5px\">".$recipComment."</div>\n";
			$recipString .= "</div>\n";
		}		

		$recipString .= "</table>\n";
				
		
	} else $recipString = "<div class=\"errorMessage\">"._NO_RECIPIENTS_DISPLAY."</div>\n";

	//if the route is complete
	if ($routeInfo["status"]=="complete") {

		$dateString = "<div class=\"formHeader\">
				"._DATE_COMPLETED."
				</div>
				".date_view($routeInfo["date_complete"])."\n";	

		$status = _COMPLETED;
		
	//if it's still going
	} 
	//if the route is forced complete
	else if ($routeInfo["status"]=="forcecomplete") {

		$dateString = "<div class=\"formHeader\">
				"._DATE_COMPLETED."
				</div>
				".date_view($routeInfo["date_complete"])."\n";	

		$status = _FORCE_COMPLETE;
		
	//if it's still going
	}	else if ($routeInfo["status"]=="pending") {

		//format the date due
		$dateDue = date_view($routeInfo["date_due"]);

		$dateString = "<div class=\"formHeader\">
				"._DATE_DUE."
				</div>
				".$dateDue."\n";

		$status = _IN_PROGRESS." <a href=\"javascript:forceComplete();\">["._FORCE_COMPLETE."]</a>\n";

	//if it's rejected by a user
	} else if ($routeInfo["status"]=="rejected") {

		//find the user that rejected this entry
		$key = array_search("reject",$recip["status"]);
		$accountId = $recip["account_id"][$key];
		$comment = $recip["comment"][$key];

		$key = array_search($accountId,$accountList["id"]);
		$login = $accountList["login"][$key];

		$dateDue = date_view($routeInfo["date_due"]);

		$dateString = "<div class=\"formHeader\">
				"._DATE_DUE."
				</div>
				".$dateDue."\n";

		$status = _REJECTED_BY." ".$login."<br>".$comment;

	}
	else {

		$status = _NOT_DISTRIBUTED." &nbsp;&nbsp; ";
		
		//show begin if there are recipients setup
		if ($recip["count"] > 0) $status .= "<a href=\"javascript:beginDist()\">["._BEGIN."]</a>\n";

	}

	//setup our edit recip link
	if ($routeInfo["status"]=="nodist" || $routeInfo["status"]=="pending") 
		$editString = "<a href=\"javascript:editRecipients();\" class=\"boldLink\">["._EDIT."]</a>";
	else 
		$editString = null;
	
	$routeString =	"<table width=100% cellpadding=0 cellspacing=0>
			<tr><td width=50% valign=top>
				<div class=\"formHeader\">
				"._ROUTING_STATUS."
				</div>
				".$status."			
			</td><td width=50% valign=top>
				<div class=\"formHeader\">
				"._DATE_CREATED."
				</div>
				".date_view($routeInfo["date_create"])."
			</td></tr>
			<tr><td colspan=2 >&nbsp;</td></tr>
			<tr><td colspan=2>
				<div class=\"formHeader\" style=\"padding-bottom:5px\">
				"._RECIPIENTS."
				&nbsp;
				".$editString."		
				</div>
				<div style=\"padding-left:10px\">
				".$recipString."			
				</div>
			</td></tr>
			</table>
			<br><br>
			";

	return $routeString;
			
}

function displayStatus($str) {

	if ($str=="nodist") return _NOT_DISTRIBUTED;
	elseif ($str=="pending") return _PENDING;
	elseif ($str=="complete") return _COMPLETED;
	elseif ($str=="rejected") return _REJECTED;	
	elseif ($str=="forcecomplete") return _FORCE_COMPLETE;
}


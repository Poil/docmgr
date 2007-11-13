createObjectHandler["file"] = "uploadFile";

function uploadFile(id) {

        parm = centerParms(450,475) + ",width=450,height=500,scrollbars=no,status=yes";
        url = "index.php?module=upload&parentId=" + id;

        window.open(url,'_new',parm);

}

function submitForm(action) {

	document.pageForm.pageAction.value = action;
	document.pageForm.submit();

}


function removeFile(id) {

        if (confirm(I18N["revision_remove_confirm"])) {

	        document.pageForm.fileId.value = id;
	        submitForm('delete');

	}

}

function viewFile(id) {

	document.pageForm.fileId.value = id;
	submitForm('view');

}

function promoteFile(id) {

	document.pageForm.fileId.value = id;
	submitForm('promote');

}


function editRecipients() {

	var routeId = document.pageForm.routeId.value;
	var url = "index.php?module=editrouterecip&routeId=" + routeId;

	param = centerParms(800,600) + ",width=800,height=600";

	window.open(url,"_new",param);

}

function forceComplete() {

	document.pageForm.pageAction.value = "clearRoute";
	document.pageForm.submit();

}

function beginDist() {

	submitForm('beginDist');

}

function submitForm(action) {

	document.pageForm.pageAction.value = action;
	document.pageForm.submit();

}


function submitEditForm() {
    submitForm('save');
}

function rejectEditForm() {

	var lh = window.opener.location.href;

	//if the parent is the docview module, relaod the page
	if (lh.indexOf("docview")!=-1) window.opener.location.href = lh;

    	self.close();
}

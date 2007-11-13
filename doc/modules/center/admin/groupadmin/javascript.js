function submitForm(action) {

	var delconfirm = deletemsg + "?";
	var entererror = entermsg;

	if (action=="delete" && !confirm(delconfirm)) return false;
	else if (action=="update" || action=="add") {
	
		if (document.pageForm.name.value=="") {
			alert(entererror);
			return false;
		}

	}
	document.pageForm.pageAction.value = action;
	document.pageForm.submit();

}

function checkName() {

	var nf = document.pageForm.name;

	if (nf.value.length==0) {
		alert(I18N["specify_name_error"]);
		nf.focus();
		return false;
	}

}
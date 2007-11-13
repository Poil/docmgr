function checkName() {

	var nf = document.pageForm.name;
	var af = document.pageForm.url;

	if (nf.value.length==0) {
		alert(I18N["specify_name_error"]);
		nf.focus();
		return false;
	}

	if (af.value.length==0) {
		alert(I18N["specify_addr_error"]);
		af.focus();
		return false;
	}

}
function updateAccount() {

	var al = document.getElementById("accountList");

	len = al.options.length;

	var valArr = new Array();
	var nameArr = new Array();
	var c = 0;

	for (i=0;i<len;i++) {

		if (al[i].selected == true) {

			nameArr[c] = al.options[i].text;
			valArr[c] = al.options[i].value;
			c++;
			
		}

	}

	var valStr = valArr.join(",");
	var nameStr = nameArr.join(",");

	var destValField = window.opener.document.getElementById("accountFilterId");
	var destNameField = window.opener.document.getElementById("accountFilter");

	if (destValField) {
		destValField.value = valStr;
		destNameField.value = nameStr;
		self.close();
	} else {
		alert("Destination field does not exist");
	}	

}
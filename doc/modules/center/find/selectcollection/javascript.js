function updateCol() {

	//get all checkboxes
	var arr = document.getElementById("searchcol").getElementsByTagName("input");

	len = arr.length;

	var colVal = new Array();
	var colName = new Array();
	var c = 0;

	for (i=0;i<len;i++) {

		if (arr[i].type!="checkbox") continue;

		if (arr[i].checked==true) {
			colVal[c] = arr[i].value;
			colName[c] = arr[i].title;
			c++;
		}

	}

	var valStr = colVal.join(",");
	var nameStr = colName.join(",");

	var destValField = window.opener.document.getElementById("colFilterId");
	var destNameField = window.opener.document.getElementById("colFilter");

	if (destValField) {
		destValField.value = valStr;
		destNameField.value = nameStr;
		self.close();
	} else {
		alert("Destination field does not exist");
	}	

}

//get the ids of the objects we want to move
function getObjIds() {

	var boxes = window.opener.document.getElementsByTagName("input");
	var str = "";

	for (i=0;i<boxes.length;i++) {

		if (boxes[i].name!="objectAction[]") continue;

		if(boxes[i].checked == true) {

			str += boxes[i].value + "|";

		}

	}

	document.getElementById("objectAction").value = str;

}

function selectCat() {

	var formName = document.pageForm;

	var boxes = formName.getElementsByTagName("input");

	for (i=0;i<boxes.length;i++) {

		if(boxes[i].checked == true) {

			var id=boxes[i].value;
			break;
		}

	}

	if (id) {

		window.opener.document.pageForm.pageAction.value = "moveObject";
		window.opener.document.pageForm.newCategory.value = id;
		window.opener.document.pageForm.submit();
		self.close();

	} else {

		alert(I18N["object_select_error"]);

	}


}

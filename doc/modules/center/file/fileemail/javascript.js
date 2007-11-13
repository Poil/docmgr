
function submitForm(action) {

	document.pageForm.pageAction.value = action;
	document.pageForm.submit();

}


function submitEmailForm() {

        if ((document.getElementById("emailTo").value == "") && (document.getElementById("emailSystemUsers").value == "")) {
                alert(I18N["email_to_error"]);
                return false;
        }

    submitForm("send");


}


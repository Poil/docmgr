
function submitForm(action) {

	document.pageForm.pageAction.value = action;
	document.pageForm.submit();

}


function submitEmailForm() {

        if (document.getElementById("emailTo").value == "") {
                alert(I18N["email_to_error"]);
                document.getElementById("emailTo").focus();
                return false;
        } else if (document.getElementById("emailFrom").value == "") {
                alert(I18N["email_from_error"]);
                document.getElementById("emailFrom").focus();
                return false;
        }

    submitForm("send");


}



function submitForm(action) {

	document.pageForm.pageAction.value = action;
	document.pageForm.submit();

}


function submitEditForm() {
    submitForm('save');
}

function rejectEditForm() {
    self.close();
}

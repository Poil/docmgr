function submitForm(action) {

	document.pageForm.pageAction.value = action;
	document.pageForm.submit();

}

function clearAlert(id) {

	document.pageForm.objectId.value = id;
	submitForm('clearAlert');

}

function removeBookmark(id) {

        if (confirm(I18N["bookmark_remove_confirm"] + "?")) {
          document.pageForm.objectId.value = id;
          submitForm('removeBookmark');
        }

}

function clearAllAlerts() {

	if (confirm(I18N["clear_all_confirm"] + "?")) submitForm('clearAllAlerts');

}

function removeFile(id) {

        if (confirm(I18N["revision_remove_confirm"])) {

                document.pageForm.docId.value = id;
                submitForm('delete');

        }

}

function viewFile(id) {

	var url = "index.php?module=dochistory&docId=" + id + "&pageAction=view";
	parms = centerParms(800,600,1);
	window.open(url,"_dochistory",parms);

}

function promoteFile(id) {

        document.pageForm.docId.value = id;
        submitForm('promote');

}

function submitForm(action) {

	document.pageForm.pageAction.value = action;
	document.pageForm.submit();

}

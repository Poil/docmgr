function showPerm() {

	var view = document.getElementById("catPermissions").style.visibility;

	if (view=="hidden") showObject("catPermissions");
	else hideObject("catPermissions");

}

function formSubmit(action) {

	//permissionSubmit();

     	document.pageForm.pageAction.value = action;
     	document.pageForm.submit();
                                                                                                                                            
}

function selectRadio() {

	var myList = document.getElementById("fileList");

	var myRadio = myList.getElementsByTagName("radio");

	for (i=0;i<myRadio.count;i++) {

		id = myRadio[i].id;

		document.getElementById(id).checked = true;

	}

}

function selectPath(val) {

	document.pageForm.path.value = val;
	document.pageForm.submit();

}

function hideRadio() {

	var myList = document.getElementById("fileList");

	var myRadio = myList.getElementsByTagName("radio");

	for (i=0;i<myRadio.length;i++) {

		id = myRadio[i].id;
		document.getElementById(id).checked = false;

	}

}

function loadDir(path) {

	document.getElementById("fileList").innerHTML = "<div class=\"successMessage\">" + I18N["please_wait"]+ "</div>\n";

	url = "index.php?module=dirlist&dirPath=" + escape(path);
	loadXMLReq(url);

	//we need to update the path of the page header
	//pathDisplay
	document.getElementById("pathDisplay").innerHTML = path;

}

function writeDirList(resp) {

	data = parseXML(resp);

	//we need to show available files, a back link to move up a directory, 
	//and the import button
	
	//name
	//path
	//objtype -> file,directory
	//readable

	if (data.object) var objlen = data.object.length;
	else var objlen = 0;

	//create the backlink
	if (data.backlink) {
		var backlnk = document.createElement("a");
		backlnk.setAttribute("href","javascript:loadDir(\"" + data.backlink + "\")");
		backlnk.appendChild(document.createTextNode("<-- Back"));
	}

	//create the import button
	if (objlen > 0) {
		if (document.all) var subbtn = document.createElement("<input type=submit name=\"importFiles\">");
		else {
			var subbtn = document.createElement("input");
			subbtn.setAttribute("type","submit");
			subbtn.setAttribute("name","importFiles");
		}
		subbtn.setAttribute("value","Import Files");
	}

	//store the import and backlink
	var ctrldiv = document.createElement("div");
	setFloat(ctrldiv,"right");
	if (backlnk) {
		ctrldiv.appendChild(backlnk);
		ctrldiv.appendChild(document.createElement("br"));
		ctrldiv.appendChild(document.createElement("br"));
	}
	if (subbtn) ctrldiv.appendChild(subbtn);

	//create our import list
	var ilist = document.createElement("ul");
	ilist.style.listStyleType="none";

	for (i=0;i<objlen;i++) {

		curobj = data.object[i];

		var row = document.createElement("li");
		
		//if the file or directory is readable, create a checkbox
		if (curobj.readable=="yes") {

			if (document.all) var chkbox = document.createElement("<input type=checkbox CHECKED name=\"filePath[]\" id=\"filePath[]\">");
			else {
				var chkbox = document.createElement("input");
				chkbox.setAttribute("type","checkbox");
				chkbox.setAttribute("name","filePath[]");
				chkbox.setAttribute("id","filePath[]");
				chkbox.setAttribute("checked",true);
			}
			chkbox.setAttribute("value",curobj.path);
			row.appendChild(chkbox);

		}

		var txtspan = document.createElement("span");

		//if it's a directory, create a link
		if (curobj.objtype=="directory") {
			var dirlink = document.createElement("a");
			dirlink.setAttribute("href","javascript:loadDir(\"" + curobj.path + "\")");
			dirlink.appendChild(document.createTextNode(curobj.name));
			txtspan.appendChild(dirlink);
		} else {
			txtspan.appendChild(document.createTextNode(curobj.name));
		}

		//pad appropriately
		if (curobj.readable=="yes") txtspan.style.paddingLeft = 3;
		else txtspan.style.paddingLeft = 24;

		row.appendChild(txtspan);

		ilist.appendChild(row);

	}	

	var fl = document.getElementById("fileList");
	fl.innerHTML = "";
	fl.appendChild(ctrldiv);

	if (objlen > 0) fl.appendChild(ilist);
	else {
		var errdiv = document.createElement("div");
		errdiv.appendChild(document.createTextNode(I18N["file_display_error"]));
		setClass(errdiv,"errorMessage");
		fl.appendChild(errdiv);
	}

}


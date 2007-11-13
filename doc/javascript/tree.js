/**********************************************************************************
	this code is way complicated and bigger than it should be, but it works.
	It desperately needs to be consolidated and split out to work better
**********************************************************************************/

ajaxRH["listdir"] = "writeDirList";
ajaxRH["coltree"] = "writeColTree";
ajaxRH["singlecoltree"] = "expandColTree";
ajaxRH["browsecollection"] = "writeBrowseResults";

//this function creates the tree from scratch
function writeColTree(resp) {

        //convert to an array
        data = parseXML(resp);
	if (!data.mode) data.mode = "link";

	//get out if no div to write to
	if (!data.divName) return false;

        var mydiv = document.getElementById(data.divName);
        mydiv.innerHTML = "";

        //nothing to display if the list is empty
        if (!data.collection) {
                mydiv.innerHTML = "<div class=\"errorMessage\">No results to display</div>";
                return false;
        }
	else mydiv.innerHTML = "<div class=\"successMessage\">Loading Tree...</div>";

	//if no form is passed, create a link tree
	if (data.mode=="link") tree = createLinkTree(data);
	else tree = createFormTree(data);

	if (tree) {
		mydiv.innerHTML = "";
		mydiv.appendChild(tree);
	} else {
		mydiv.innerHTML = "<div class=\"errorMessage\">Error loading tree</div>";
	} 

}

function expandCollection(id,keepOpen,formName,mode) {

	if (formName)
		tgtdiv = formName + "_" + id;
	else
		tgtdiv = "_subcol" + id;

	var pb = "box" + tgtdiv;
	var pf = "folder" + tgtdiv;
	if (!mode) mode = "link";

	var td = document.getElementById(tgtdiv);
	boximg = document.getElementById(pb);
	foldimg = document.getElementById(pf);

	//reset all folders to closed on this level
	if (td.parentNode.parentNode) {
		var fi = td.parentNode.parentNode.getElementsByTagName("img");
		var len = fi.length;
		for (i=0;i<len;i++) {	
			if (fi[i].src.indexOf("open_folder") != -1) 
					fi[i].src = "themes/default/images/closed_folder.png";
		}
	}

	//ie work around for the extra space it puts at the bottom of the div
	if (document.all) td.style.borderBottom = "1px solid white";

	//this is if we are processing a child that's already open, and it's
	//not part of the initial display of the tree
	if (td.firstChild && !keepOpen) { 

		if (boximg) boximg.src = "themes/default/images/plusbox.gif";				
		if (mode=="link" && foldimg) foldimg.src = "themes/default/images/closed_folder.png";

		//this seems to work better than removing the childNodes
		td.innerHTML = "";
		return false;
	}
	else {

		if (boximg) boximg.src = "themes/default/images/dashbox.gif";				
		if (mode=="link" && foldimg) foldimg.src = "themes/default/images/open_folder.png";
		url = "index.php?module=coltree&curValue=" + id + "&expandSingle=" + tgtdiv;

		//tack on the formname and mode for parsing forms
		if (formName) {
			url += "&formName=" + formName;
			url += "&mode=" + mode;
		}

		loadXMLReq(url);
	
	}

}

//this function expands a single level of a tree
function expandColTree(resp) {

	if (!resp) return false;

	var curchild = parseXML(resp);
	var i = 0;
	var subdiv = document.getElementById(curchild.expandSingle);
	
	subdiv.innerHTML = "";
	if (curchild.formName) subdiv.appendChild(createFormTree(curchild));
	else subdiv.appendChild(createLinkTree(curchild));

}

//this function returns all collections at a level.  If there are some
//below the level, it calls itself again to display them
function createFormTree(data) {

	var i = 0;
	var container = document.createElement("div");
	var formName = data.formName;
	var checkName = formName + "TreeVal";
	var mode = data.mode;
	var curVal = data.curValue;

	var checkForm = document.getElementById(checkName);

	//our values are stored in a comma delimited format in a hidden field.
	if (checkForm) {
		var checkVal = checkForm.value;
		var checkArr = checkVal.split(",");
	} 

	//get out if there's nothing to report
	if (!data.collection) return container;
	var len = data.collection.length;

	for (i=0;i<len;i++) {

		var curcol = data.collection[i];

		//create our div, images, and text
		var curdiv = document.createElement("div");
		var idName = formName + "_" + curcol.id;

		//the children div
		if (curcol.children) {
			//make sure the form name gets passed again
			curcol.children.formName = formName;
			curcol.children.curValue = curVal;
			curcol.children.mode = mode;
			var childTree = createFormTree(curcol.children);
		}
		else var childTree = document.createElement("div");

		childTree.setAttribute("id",idName);
		childTree.style.marginLeft=13;

		//the form
		if (mode=="radio") formType = "radio";
		else if (mode=="checkbox") formType = "checkbox";
		else return false;

		var formChecked = null;

		//is the form checked
		if (checkArr) {
			var test = arraySearch(curcol.id,checkArr);
			if (test != "-1") formChecked = "yes";
		}

		//just don't ask...
		if (document.all) {
			if (formChecked) var fStr = "<input name=\"" + formName + "\" CHECKED>";
			else var fStr = "<input name=\"" + formName + "\">";
			var curform = document.createElement(fStr);
		}
		else {
			var curform = document.createElement("input");
			curform.setAttribute("name",formName);
			if (formChecked) curform.setAttribute("checked","true");
		}

		curform.setAttribute("type",formType);
		curform.setAttribute("id",formName);
		curform.setAttribute("value",curcol.id);
		curform.setAttribute("title",curcol.name);

		//the plus box
		if (curcol.child_count > 0) {

			var pbox = document.createElement("img");
			pbox.setAttribute("id","box" + idName);			
			pbox.style.marginRight = 3;
			setClick(pbox,"expandCollection('" + curcol.id + "','','" + formName + "','" + mode + "')");

			if (curcol.children) 		
				pbox.setAttribute("src","themes/default/images/dashbox.gif");
			else 
				pbox.setAttribute("src","themes/default/images/plusbox.gif");

		}			
		else {
			var pbox = null;
			if (document.all) curform.style.marginLeft = 12;
			else curform.style.marginLeft = 16;
		}

		//the collection name and link
		var txt = document.createTextNode(curcol.name);

		//put it all together
		if (pbox) curdiv.appendChild(pbox);
		curdiv.appendChild(curform);
		curdiv.appendChild(txt);
		curdiv.appendChild(childTree);
		container.appendChild(curdiv);

	}	

	return container;

}


//this function returns all collections at a level.  If there are some
//below the level, it calls itself again to display them
function createLinkTree(data) {

	var i = 0;
	var container = document.createElement("div");

	//get out if there's nothing to report
	if (!data.collection) return container;
	var len = data.collection.length;

	for (i=0;i<len;i++) {

		var curcol = data.collection[i];

		//create our div, images, and text
		var curdiv = document.createElement("div");
		var idName = "_subcol" + curcol.id;

		//the folder
		var folder = document.createElement("img");
		folder.setAttribute("id","folder" + idName);
		folder.style.marginRight = 3;
		setClick(folder,"browseCollection('" + curcol.id + "')");

		//the children div
		if (curcol.children) var childTree = createLinkTree(curcol.children);
		else var childTree = document.createElement("div");

		childTree.setAttribute("id",idName);
		childTree.style.marginLeft=13;

		if (curcol.children) 		
			folder.setAttribute("src","themes/default/images/open_folder.png");
		else 
			folder.setAttribute("src","themes/default/images/closed_folder.png");

		//the plus box
		if (curcol.child_count > 0) {

			var pbox = document.createElement("img");
			pbox.setAttribute("id","box" + idName);			
			pbox.style.marginRight = 3;
			setClick(pbox,"expandCollection('" + curcol.id + "')");

			if (curcol.children) 		
				pbox.setAttribute("src","themes/default/images/dashbox.gif");
			else {
				pbox.setAttribute("src","themes/default/images/plusbox.gif");
			}

		}			
		else {
			var pbox = null;
			folder.style.marginLeft = 12;
		}

		//the collection name and link
		var txt = document.createTextNode(curcol.name);
		var link = document.createElement("a");
		link.setAttribute("href","javascript:browseCollection('" + curcol.id + "')");		
		link.appendChild(txt);

		//put it all together
		if (pbox) curdiv.appendChild(pbox);
		curdiv.appendChild(folder);
		curdiv.appendChild(link);
		curdiv.appendChild(childTree);

		container.appendChild(curdiv);

	}	

	return container;

}

function browseCollection(id) {

	//throw in our sort fields if available
	sf = document.getElementById("sortField");
	sd = document.getElementById("sortDir");


	//load it normally if ajax browsing is disabled
	if (enableAjax==0) {

		url = "index.php?module=browse&view_parent=" + id;

		//add sorting if set by user
		if (sf && sd) url += "&sortField=" + sf.value + "&sortDir=" + sd.value;

		location.href = url;
		return false;
	}

	showStatus(I18N["updating"]);

	//check to see if the div is accessible.  If not, force a page reload
	var dn = "_subcol" + id;
	var cd = document.getElementById(dn);
	if (!cd) {
		url = "index.php?module=browse&view_parent=" + id;

		//add sorting if set by user
		if (sf && sd) url += "&sortField=" + sf.value + "&sortDir=" + sd.value;

		location.href = url;
		return false;
	}

    stat = document.getElementById("browseStatus");
    if (stat) stat.innerHTML = "<div class=\"errorMessage\">" + I18N["opening_collection"] + "</div>";

    if (id > 0) {
        showObject("searchCollection");
        document.getElementById("limitCol").value = 1;
    } else {
        hideObject("searchCollection");
        document.getElementById("limitCol").value = "";
    }

	if (id!=0) expandCollection(id,1);

    url = "index.php?module=browse&forceEmpty=1&view_parent=" + id;

	//add sorting if set by user
	if (sf && sd) url += "&sortField=" + sf.value + "&sortDir=" + sd.value;

	loadXMLReq(url);

}

function writeBrowseResults(txt) {

	document.getElementById("siteCenterColumn").innerHTML = txt;
	hideStatus();

}



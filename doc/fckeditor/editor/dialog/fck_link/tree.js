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

        if (data.divname) {
		var mydiv = document.getElementById(data.divname);
	}
	else var mydiv = document.getElementById("pageTree");

        mydiv.innerHTML = "";

        //nothing to display if the list is empty
        if (!data.object) {
                mydiv.innerHTML = "<div class=\"errorMessage\">No results to display</div>";
                return false;
        }
	else mydiv.innerHTML = "<div class=\"successMessage\">Loading Tree...</div>";

	//if no form is passed, create a link tree
	tree = createLinkTree(data);

	if (tree) {
		mydiv.innerHTML = "";

                //create a home link
                var hdiv = document.createElement("div");
                var hlink = document.createElement("a");
                hlink.setAttribute("href","javascript:browseCollection('0')");
                hlink.appendChild(document.createTextNode("Home"));
                hdiv.appendChild(hlink);
                mydiv.appendChild(hdiv);

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
					fi[i].src = siteurl + sitetheme + "/images/closed_folder.gif";
		}
	}

	//ie work around for the extra space it puts at the bottom of the div
	if (document.all) td.style.borderBottom = "1px solid white";

	//this is if we are processing a child that's already open, and it's
	//not part of the initial display of the tree
	if (foldimg.src.indexOf("file.gif") == -1) {

	if (td.firstChild && !keepOpen) { 

		if (boximg) boximg.src = siteurl + sitetheme + "/images/plusbox.gif";				
		if (mode=="link" && foldimg) foldimg.src = siteurl + sitetheme + "/images/closed_folder.gif";

		//this seems to work better than removing the childNodes
		td.innerHTML = "";
		return false;
	}
	else {

		if (boximg) boximg.src = siteurl + sitetheme + "/images/dashbox.gif";				
		if (mode=="link" && foldimg) foldimg.src = siteurl + sitetheme + "/images/open_folder.gif";
		loadPageTree(id,tgtdiv);

	}

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
function createLinkTree(data) {

	var i = 0;
	var container = document.createElement("div");

	/*
	if (!data.expandSingle) {

		//create a home link
		var hdiv = document.createElement("div");
		var hlink = document.createElement("a");
		hlink.setAttribute("href","javascript:browseCollection('browse','0')");
		hlink.appendChild(document.createTextNode("Home"));
		hdiv.appendChild(hlink);
		container.appendChild(hdiv);
	
	}
	*/
	//get out if there's nothing to report
	if (!data.object) return container;
	var len = data.object.length;

	for (i=0;i<len;i++) {

		var curcol = data.object[i];

		//create our div, images, and text
		var curdiv = document.createElement("div");
		var idName = "_subcol" + curcol.id;

		//the folder
		var folder = document.createElement("img");
		folder.setAttribute("id","folder" + idName);
		folder.style.marginRight = 3;
		setClick(folder,"browseCollection('" + curcol.viewmod + "','" + curcol.id + "')");

		//the children div
		if (curcol.children) var childTree = createLinkTree(curcol.children);
		else var childTree = document.createElement("div");

		childTree.setAttribute("id",idName);
		childTree.style.marginLeft=13;

		if (curcol.children) 		
			folder.setAttribute("src",siteurl + sitetheme + "/images/open_folder.gif");
		else {
			if (curcol.viewmod=="browse") 
				folder.setAttribute("src",siteurl + sitetheme + "/images/closed_folder.gif");
			else
				folder.setAttribute("src",siteurl + sitetheme + "/images/file.gif");
		}

		//the plus box
		if (curcol.child_count > 0) {

			var pbox = document.createElement("img");
			pbox.setAttribute("id","box" + idName);			
			pbox.style.marginRight = 3;
			setClick(pbox,"expandCollection('" + curcol.id + "')");

			if (curcol.children) 		
				pbox.setAttribute("src",siteurl + sitetheme + "/images/dashbox.gif");
			else {
				pbox.setAttribute("src",siteurl + sitetheme + "/images/plusbox.gif");
			}

		}			
		else {
			var pbox = null;
			folder.style.marginLeft = 12;
		}

		//the collection name and link
		var txt = document.createTextNode(curcol.name);
		var link = document.createElement("a");
		link.setAttribute("href","javascript:browseCollection('" + curcol.viewmod + "','" + curcol.id + "')");		
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




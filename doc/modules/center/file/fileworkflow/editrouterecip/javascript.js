/**********************************************************************
	This file is not terribly efficient.  We do whole screen
	redrawes where section redrawes will do.  We also don't
	reference our objects as efficiently as we could.  Assigning
	IDs to each entry would probably help.  A naming convention
	for what's a route, what's an entry, etc would help as well.
	It will probably get an overhaul at some point

	Okay, the collection of all entries is a "route"
	The collection of entries at one level (simultaneous distribution)
	is a "stage".
	A single distribution is an "entry"

**********************************************************************/

ajaxRH["accountlist"] = "writeAccountList";
ajaxRH["entrylist"] = "writeEntryList";
ajaxRH["wftemplate"] = "writeTemplateList";

//our globals
var stagediv;
var stagenum;


//create the page from scratch
function loadPage() {

	//load the calender language file
	loadScript(callang_file);

	var i;

	//if no stages, default to one
	if (!stagenum) stagenum = 1;
	for (i=1;i<=stagenum;i++) createStage(i);

	//load our current entries
	var curroute = document.getElementById("routeId").value;
	url = "index.php?module=modentry&pageAction=view&routeId=" + curroute;
	loadXMLReq(url);
	
}


/*********************************************************************
	handles most of our processing of data from the modentry
	module.  That module is called when we obtain a list of entries
	for a route, we modify or delete an entry
*********************************************************************/
function writeEntryList(resp) {

	var data = parseXML(resp);

	//use the first entry for our pageAction value
	var pa = data.pageAction;

	//alert the error if there is one
	if (data.error) {
		alert(data.error);
		return false;
	}

	if (pa=="edit") {

		//load the recipient form with our current data
		addEntry(data.route[0].sort_order,data.route[0]);

	} else if (pa=="delete") {

		//remove the entry we just deleted
		//get the stage
		var curstage = document.getElementById("stage" + data.route[0].sort_order);
		var divarr = curstage.getElementsByTagName("div");
		var condiv = divarr[3];

		var recips = condiv.getElementsByTagName("div");
		len = recips.length;
		var i;

		for (i=0;i<len;i++) {

			var entryname = "entry" + data.route[0].id;
			if (recips[i].id==entryname) {	
				condiv.removeChild(recips[i]);
				break;
			}
		}

	} else if (pa=="loadtemplate" || pa=="updateEntry") {

		//we have to redraw the whole damn thing.
		var scol = document.getElementById("stagecol");

		document.getElementById("stage").value = "1";
		
		scol.innerHTML = "";
		createStage(1);

		if (!data.route) alert(I18N["data_load_error"]);
		else {

			var len = data.route.length;
			var c;
			for (c=0;c<len;c++) createEntryDiv(data.route[c]);

		}

	} else if (pa=="removetemplate") {

		loadTemplateList();

	} else if (pa=="editrecip") {

		writeEditEntry(data);

	} else if (data.route) {

		var len = data.route.length;
		var c;
		for (c=0;c<len;c++) createEntryDiv(data.route[c]);

		cancelEntry();

	}

}

/**********************************************************************
	Entry manipulation functions
**********************************************************************/
//actually creates the html containing the information for an entry
function createEntryDiv(curroute) {

	var rdiv = document.createElement("div");
	rdiv.setAttribute("id","entry" + curroute.id);
	setClass(rdiv,"entryDiv");

	//get our stage id
	var stagename = "stage" + curroute.sort_order;
	var curstage = document.getElementById(stagename);

	if (!curstage) {
		addStage();
		curstage = document.getElementById(stagename);
	}
	
	//the fourth div is the route container
	var divs = curstage.getElementsByTagName("div");
	var condiv = divs[3];

	//now, assemble our stage info into it's div
	var adiv = document.createElement("div");
	adiv.appendChild(document.createTextNode(curroute.account_name));

	var ddiv = document.createElement("div");
	ddiv.appendChild(document.createTextNode(curroute.date_due));

	var linkdiv = document.createElement("div");

	var editlink = document.createElement("a");
	editlink.setAttribute("href","javascript:editEntry('" + curroute.id + "');");
	editlink.appendChild(document.createTextNode("[" + I18N["edit"] + "]"));

	var dellink = document.createElement("a");
	dellink.setAttribute("href","javascript:deleteEntry('" + curroute.id + "');");
	dellink.appendChild(document.createTextNode("[" + I18N["delete"] + "]"));

	linkdiv.appendChild(editlink);
	linkdiv.appendChild(document.createTextNode(" "));
	linkdiv.appendChild(dellink);

	rdiv.appendChild(adiv);
	rdiv.appendChild(ddiv);
	rdiv.appendChild(linkdiv);

	condiv.appendChild(rdiv);

}

//delete the selected route entry
function deleteEntry(id) {

	if (confirm(I18N["entry_del_confirm"] + "?")) {
		url = "index.php?module=modentry&entryId=" + id + "&pageAction=delete";
		loadXMLReq(url);
	}
}

//open a popup window with a form for creating a new workflow recipient
function addEntry(num,curdata) {

	var stagediv = document.getElementById("stagecol");

	if (!curdata) var curdata;

	var stageid = "stage" + num;
	var curstage = document.getElementById(stageid);
	document.getElementById("stage").value = num;

	var addform = document.createElement("div");
	addform.setAttribute("id","popupWin");
	setClass(addform,"addForm");

	//create route type form
	var typediv = createTypeForm(curdata);
	addform.appendChild(typediv);
	
	//create route date form
	var datediv = createDateForm(curdata);

	//create route recipient list.  we do this one differently 
	//because we have to go to the server for the list.  So, we'll
	//just create the div and write to it when the server responds
	var recipdiv = createRecipDiv(curdata);

	//create notes list
	var noteform = createNoteForm(curdata);

	//create buttons
	var funcbtns = createFuncBtns();

	addform.appendChild(datediv);
	addform.appendChild(recipdiv);
	addform.appendChild(noteform);
	addform.appendChild(funcbtns);

	stagediv.appendChild(addform);

	//load the recipient list
	url = "index.php?module=accountlist";
	loadXMLReq(url);

        Calendar.setup({
                inputField      :    "dateDue",
		ifFormat        :    date_format,
                button          :    "setDateDue",   // trigger for the calendar (button ID)
                singleClick     :    true,           // double-click mode
                step            :    1                // show all years in drop-down boxes (instead of every other year as default)
        }
	);
}

//open a popup window with a form for creating a new workflow recipient
function editEntry(num) {

	var url = "index.php?module=modentry&pageAction=editrecip&entryId=" + num;
	loadXMLReq(url);

}

//create a form for editing a current Entry
function writeEditEntry(data) {

	var d = data.route[0];

	var stagediv = document.getElementById("stagecol");
	var num = d.sort_order;

	var stageid = "stage" + d.sort_order;

	var curstage = document.getElementById(stageid);
	document.getElementById("stage").value = d.sort_order;

	var addform = document.createElement("div");
	addform.setAttribute("id","popupWin");
	setClass(addform,"editForm");

	//create route type form
	var typediv = createTypeForm(d.task_type);
	addform.appendChild(typediv);
	
	//create route date form
	var datediv = createDateForm(d.date_due);

	//create notes list
	var noteform = createNoteForm(d.task_notes);

	//create buttons
	var funcbtns = createFuncBtns(d.id);

	addform.appendChild(datediv);
	addform.appendChild(noteform);
	addform.appendChild(funcbtns);

	stagediv.appendChild(addform);

	//load the recipient list
	url = "index.php?module=accountlist";
	loadXMLReq(url);

        Calendar.setup({
                inputField      :    "dateDue",
		ifFormat        :    date_format,
                button          :    "setDateDue",   // trigger for the calendar (button ID)
                singleClick     :    true,           // double-click mode
                step            :    1                // show all years in drop-down boxes (instead of every other year as default)
        }
	);
}


/******************************************************************
	Functions used to create an editEntry or addEntry form
******************************************************************/

//create our buttons in the addEntry form (Ok, Cancel)
function createFuncBtns(curval) {

	var btndiv = document.createElement("div");
	setClass(btndiv,"funcdiv");

	var saveEntry;

	if (curval) saveEntry = "saveEntry('" + curval + "')";
	else saveEntry = "saveEntry()";

	var ok = createForm("input");
	ok.setAttribute("type","button");
	ok.setAttribute("value",I18N["save"]);
	setClass(ok,"funcbtns");
	setClick(ok,saveEntry);

	var cancel = createForm("input");
	cancel.setAttribute("type","button");
	cancel.setAttribute("value",I18N["cancel"]);
	setClass(cancel,"funcbtns");
	setClick(cancel,"cancelEntry()");

	btndiv.appendChild(ok);
	btndiv.appendChild(cancel);

	return btndiv;

}

//create the list of accounts we can select as recipients
function createRecipDiv() {

	var curdiv = document.createElement("div");
	setClass(curdiv,"inputForm");

	var reciplist = document.createElement("div");
	reciplist.setAttribute("id","recipDiv");

	var recipheader = document.createElement("div");
	setClass(recipheader,"formHeader");
	recipheader.appendChild(document.createTextNode(I18N["task_recip"]));

	curdiv.appendChild(recipheader);
	curdiv.appendChild(reciplist);

	return curdiv;

}

//actually write out our list
function writeAccountList(resp) {

	var recipdiv = document.getElementById("recipDiv");

	if (recipdiv) {

		data = parseXML(resp);
		
		len = data.account.length;
		var i;

		for (i=0;i<len;i++) {

			var entry = createAccountEntry(data.account[i]);
			recipdiv.appendChild(entry);		

		}

	}

}

//create the line with the checkbox and account name
function createAccountEntry(account) {

	var line = document.createElement("li");
	var cb = createForm("input","accountId[]");
	cb.setAttribute("type","checkbox");
	cb.setAttribute("value",account.id);	

	var accountName = account.first_name + " " + account.last_name;

	line.appendChild(cb);
	line.appendChild(document.createTextNode(accountName));

	return line;

}

//creates the form for task type selection
function createTypeForm(curdata) {

	var typediv = document.createElement("div");
	setClass(typediv,"inputForm");

	var typeheader = document.createElement("div");
	setClass(typeheader,"formHeader");
	typeheader.appendChild(document.createTextNode(I18N["task_type"]));

	var typeform = createSelect("taskType");
	typeform[0] = new Option(I18N["view"],"view");
	typeform[1] = new Option(I18N["edit"],"edit");
	typeform[2] = new Option(I18N["approve"],"approve");
	if (curdata) typeform.value = curdata;

	typediv.appendChild(typeheader);
	typediv.appendChild(typeform);

	return typediv;

}

//creates the form for date selection
function createDateForm(curdata) {

	var datediv = document.createElement("div");
	setClass(datediv,"inputForm");

	var dateheader = document.createElement("div");
	setClass(dateheader,"formHeader");
	dateheader.appendChild(document.createTextNode(I18N["date_due"]));

	var dateform = createForm("input","dateDue");
	dateform.setAttribute("type","text");
	if (curdata) dateform.setAttribute("value",curdata);
	dateform.style.width = "85px";

	var datebtn = createForm("input","setDateDue");
	datebtn.setAttribute("value","...");
	datebtn.setAttribute("type","button");

	datediv.appendChild(dateheader);
	datediv.appendChild(dateform);
	datediv.appendChild(datebtn);
	
	return datediv;

}

//creates the form for note entry
function createNoteForm(curdata) {

	var notediv = document.createElement("div");
	setClass(notediv,"inputForm");

	var noteheader = document.createElement("div");
	setClass(noteheader,"formHeader");
	noteheader.appendChild(document.createTextNode(I18N["notes"]));

	var noteform = createForm("textarea","taskNotes");
	setClass(noteform,"noteEntry");	
	if (curdata) noteform.setAttribute("value",curdata);

	notediv.appendChild(noteheader);
	notediv.appendChild(noteform);
	
	return notediv;

}


function closeWindow() {

	//refresh the parent window
	url = window.opener.location.href;
	url += "&includeModule=fileworkflow";

	window.opener.location.href = url;

	//close this window
	self.close();

}

function beginCloseWindow() {

	//set the form to begin the distribution on the parent
	window.opener.document.pageForm.pageAction.value = "beginDist";
	window.opener.document.pageForm.submit();

	//close this window
	self.close();

}


/*****************************************************************
	Stage manipulation functions
*****************************************************************/

//figure out the data for our next stage then create it
function addStage() {

	num = document.getElementById("stage").value;

	var stageid = "stage" + num;
	var curstage = document.getElementById(stageid);

	var newnum = parseInt(num) + 1;
	document.getElementById("stage").value = newnum;
	createStage(newnum);
	
}

//actually create the stage div
function createStage(num) {

	var stagediv = document.getElementById("stagecol");
	var stageid = "stage" + num;

	//create the stage
	var sdiv = document.createElement("div");
	setClass(sdiv,"stage");
	sdiv.setAttribute("id",stageid);

	//create our recipient link
	var addrecip = document.createElement("div");
	var reciplink = document.createElement("a");
	reciplink.setAttribute("href","javascript:addEntry('" + num + "');");
	reciplink.appendChild(document.createTextNode("[" + I18N["add_recipient"] + "]"));
	addrecip.appendChild(reciplink);
	setClass(addrecip,"addLink");

	//show the stage name
	var stagename = document.createElement("div");
	setClass(stagename,"stageTitle");
	stagename.appendChild(document.createTextNode(I18N["stage"] + num));

	//create our container for our recipients
	var recipcon = document.createElement("div");
	setClass(recipcon,"recipContainer");

	//a cleaner
	var myclean = document.createElement("div");
	setClass(myclean,"cleaner");

	//put it all together
	sdiv.appendChild(stagename);
	sdiv.appendChild(addrecip);
	sdiv.appendChild(myclean);
	sdiv.appendChild(recipcon);
	sdiv.appendChild(myclean.cloneNode(true));	

	//add our stage to the main div
	stagediv.appendChild(sdiv);

}


/******************************************************************
	Popup window button functions
******************************************************************/

//cancel whatever's going on and close the popup window
function cancelEntry() {

	var stagediv = document.getElementById("stagecol");
	var divs = stagediv.getElementsByTagName("div");
	var len = divs.length;

	for (i=0;i<len;i++) {

		if (divs[i].id=="popupWin") {

			stagediv.removeChild(divs[i]);
			break;

		}

	}

}

//post our new recipient data to the modentry module
function saveEntry(id) {

	if (id) document.pageForm.pageAction.value = "updateEntry";
	else document.pageForm.pageAction.value = "update";

	//make sure a date entry is entered
	dd = document.pageForm.dateDue;
	if (!dd.value) {
		alert(I18N["date_entry_error"]);
		return false;
	}

	//if creating a new entry, make sure a recipient is selected
	if (!id) {
		var arr = document.pageForm.getElementsByTagName("input");
		var len = arr.length;
		var i;
		var pass;

		for (i=0;i<len;i++) {
			if (arr[i].id=="accountId[]" && arr[i].checked==true) pass = 1;
		}

		//throw an error if no recipients are selected
		if (!pass) {
			alert(I18N["recip_entry_error"]);
			return false;
		}
	}

	//post our form
	url = "index.php?module=modentry";
	if (id) url += "&entryId=" + id;
	postAjaxForm(document.pageForm,url);

}


/*********************************************************************
	Template manipulation functions
*********************************************************************/

//creates a form for saving a route set as a template
function saveTemplate() {

	var stagediv = document.getElementById("stagecol");

	var addform = document.createElement("div");
	addform.setAttribute("id","popupWin");
	setClass(addform,"addForm");

	//create form
	var saveform = document.createElement("div");
	setClass(saveform,"saveForm");

	//create the Save As box
	var saveheader = document.createElement("div");
	setClass(saveheader,"formHeader");
	saveheader.appendChild(document.createTextNode(I18N["save_new_template"]));

	var savetext = createForm("text","saveName");
	var savebtn = createForm("button","saveBtn");
	savebtn.style.marginLeft="5px";
	savebtn.setAttribute("value","Save");
	setClick(savebtn,"postTemplate()");

	saveform.appendChild(saveheader);
	saveform.appendChild(savetext);
	saveform.appendChild(savebtn);

	//now create the list of current ones.  They can pick from those to
	var savelist = document.createElement("div");
	setClass(savelist,"saveList");

	var listheader = document.createElement("div");
	setClass(listheader,"formHeader");
	listheader.appendChild(document.createTextNode(I18N["update_exist_template"]));	

	var templist = document.createElement("ul");	
	setClass(templist,"templateList");
	templist.setAttribute("id","templateList");	

	//assemble our list components
	savelist.appendChild(listheader);
	savelist.appendChild(templist);

	//our close window button
	var closediv = document.createElement("div");
	setClass(closediv,"closeList");

	var closebtn = createForm("button");
	setClick(closebtn,"cancelEntry()");
	closebtn.value = I18N["close"];
	closediv.appendChild(closebtn);

	addform.appendChild(saveform);
	addform.appendChild(savelist);	
	addform.appendChild(closediv);
	stagediv.appendChild(addform);	

	//load our template list
	var url = "index.php?module=wftemplate";
	loadXMLReq(url);

}

//handles data coming back from the wftemplate module
function writeTemplateList(resp) {

	data = parseXML(resp);

	if (data.pageAction=="templatelist") createTemplateList(data);
	else if (data.pageAction=="save") {
		//if there is an error, show it.  otherwise close the window
		if (data.error) alert(data.error);
		else cancelEntry();
	} else if (data.pageAction=="loadsavelist") writeSaveTemplateList(data);

}

//create a list of available templates for the current user
function createTemplateList(data) {

	if (!data.template) return false;

	var list = document.getElementById("templateList");
	var len = data.template.length;
	var i;

	for (i=0;i<len;i++) {

		var curtemp = data.template[i];

		var row = document.createElement("li");
		var link = document.createElement("a");

		link.setAttribute("href","javascript:postTemplate('" + curtemp.id + "');");
		link.appendChild(document.createTextNode(curtemp.name));

		row.appendChild(link);
		list.appendChild(row);
	}
	
}

//Saves the current workflow as a template.  If an existing template is selected,
//it's data is overwritten, otherwise it's saved with a new name
function postTemplate(id) {

	var rid = document.getElementById("routeId").value;
	url = "index.php?module=wftemplate&routeId=" + rid + "&pageAction=save";
	if (id) url += "&templateId=" + id;
	else url += "&saveName=" + document.getElementById("saveName").value;
	loadXMLReq(url);	

}

//load our list of current saved templates
function loadTemplateList() {

	var stagediv = document.getElementById("stagecol");

	var addform = document.createElement("div");
	addform.setAttribute("id","popupWin");
	setClass(addform,"saveTemplate");

	//now create the list of current ones.  They can pick from those to
	var savelist = document.createElement("div");
	setClass(savelist,"saveList");

	var listheader = document.createElement("div");
	setClass(listheader,"formHeader");
	listheader.appendChild(document.createTextNode(I18N["select_template_below"]));	

	var templist = document.createElement("ul");	
	setClass(templist,"templateList");
	templist.setAttribute("id","templateList");	

	//assemble our list components
	savelist.appendChild(listheader);
	savelist.appendChild(templist);

	//our close window button
	var closediv = document.createElement("div");
	setClass(closediv,"closeList");

	var closebtn = createForm("button");
	setClick(closebtn,"cancelEntry()");
	closebtn.value = I18N["close"];
	closediv.appendChild(closebtn);

	addform.appendChild(savelist);	
	addform.appendChild(closediv);
	stagediv.appendChild(addform);	

	//load our template list
	var url = "index.php?module=wftemplate&pageAction=loadsavelist";
	loadXMLReq(url);

}

//write the list of saved templates as returned from the wftemplate module
function writeSaveTemplateList(data) {

	if (!data.template) return false;

	var list = document.getElementById("templateList");
	var len = data.template.length;
	var i;

	for (i=0;i<len;i++) {

		var curtemp = data.template[i];

		var row = document.createElement("li");
		var link = document.createElement("a");
		var img = document.createElement("img");

		link.setAttribute("href","javascript:loadTemplate('" + curtemp.id + "');");
		link.appendChild(document.createTextNode(curtemp.name));

		img.setAttribute("src","themes/default/images/xbox.gif");
		setClick(img,"removeTemplate('" + curtemp.id + "');");
		setFloat(img,"right");

		row.appendChild(img);
		row.appendChild(link);
		list.appendChild(row);
	}

}

function removeTemplate(id) {

	var curroute = document.getElementById("routeId").value;

	if (confirm("Are you sure you want to remove this template?")) {
		url = "index.php?module=modentry&pageAction=removetemplate&templateId=" + id + "&routeId=" + curroute;
		loadXMLReq(url);
	}

}

//load a selected template.  Sets the current workflow set to use our routes as
//created from a template
function loadTemplate(id) {

	var curroute = document.getElementById("routeId").value;

	url = "index.php?module=modentry&pageAction=loadtemplate&templateId=" + id + "&routeId=" + curroute;
	loadXMLReq(url);

}

function setEmailNotify() {

	var curroute = document.getElementById("routeId").value;
	var curval = document.getElementById("emailNotify");
	var sendval;

	if (curval.checked==true) sendval = "t";
	else sendval = "f";

	url = "index.php?module=modentry&pageAction=modnotify&routeId=" + curroute + "&emailNotify=" + sendval;
	loadXMLReq(url);

}

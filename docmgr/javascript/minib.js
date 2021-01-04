MINIB = new DOCMGR_MINIB();

function DOCMGR_MINIB() 
{

	this.filter;
	this.mode;
	this.typeFilter;
	this.path;
	this.ceiling;
	this.editor;
	this.selected;
	this.bookmarks;
	this.objectName;
	this.handler;
	this.backHandler;
	this.ctrlpressed = false;
	
	//reference to our window
	this.list;				//file/folder list
	this.tableRow;		//row containing list columns
	this.pathdiv;			//div containing path form
	this.controls;		//buttons

	/****************** methods ****************/

	this.reset = function()
	{
		MINIB.filter = "*";
		MINIB.mode = "open";
		MINIB.typeFilter = "*";
		MINIB.path = "/Users/" + USER_LOGIN;
		MINIB.ceiling = "/Users/" + USER_LOGIN;
		MINIB.editor = "dmeditor";
		MINIB.selected = new Array();
	};

	this.keydown = function(e)
	{
  	//check if Ctrl or Command key are pressed   
	  if (e.keyCode=="91" || e.keyCode=="17") MINIB.ctrlpressed = true;
	};

	this.keyup = function(e)
	{
  	//check if Ctrl or Command key were released
  	if (e.keyCode=="91" || e.keyCode=="17") MINIB.ctrlpressed = false;
	};

	this.registerKeys = function()
	{
		window.onkeyup = this.keyup;
		window.onkeydown = this.keydown;
	}

	this.open = function(mode,handler,path,typeFilter,backHandler)
	{

		this.reset();
		this.registerKeys();

		if (mode) this.mode = mode;
		if (handler) this.handler = handler;
		if (typeFilter) this.typeFilter = typeFilter;
		if (path) this.path = path;

		if (backHandler) this.backHandler = backHandler;
		else this.backHandler = "";

		MODAL.open(640,480,_I18N_DOCMGR_FILE_CHOOSER);

		if (backHandler)
		{
			MODAL.clearNavbarRight();
			MODAL.addNavbarButtonRight(_I18N_BACK,backHandler);
		}
		
		//setup the divs
		this.setupContainer();
		this.setupToolbar();
		this.setupPathDiv();
		this.setupTypeDiv();
		this.setupControls();
		this.setupList();


	};

	this.setInitialPath = function()
	{

		var workingRoot = "";

		if (MINIB.path.indexOf(MINIB.ceiling)==-1)
		{

			for (var i=0;i<MINIB.bookmarks.length;i++)
			{
				//this will at least use the highest level bookmark
				if (MINIB.path.indexOf(MINIB.bookmarks[i].object_path)!=-1)
				{
					workingRoot = MINIB.bookmarks[i].object_path;
					break;
				}
			}

			MINIB.ceiling = workingRoot;

		}
		else
		{
			workingRoot = MINIB.ceiling;

		}

		MINIB.setupBlocks(workingRoot);

		//we've got something to work with
		if (workingRoot.length > 0)
		{

			var arr = MINIB.path.split("/");
			var p = "";

			if (workingRoot=="/") var begin = "0";
			else var begin = 1;
		
			for (var i=begin;i<arr.length;i++)
			{
				if (p=="/") p += arr[i];
				else p += "/" + arr[i];

				//skip until we are passed the root
				if (p.length < workingRoot.length) continue;

				//browse to this location
				MINIB.browse(p);

			}

		}

	};


	/**************** setup functions ****************/

	this.setupContainer = function()
	{
		MODAL.container.appendChild(ce("div","","mbObjectList"));
		MODAL.container.appendChild(ce("div","","mbPathDiv"));
		MODAL.container.appendChild(ce("div","","mbTypeDiv"));
		MODAL.container.appendChild(ce("div","","mbPathDisplay"));
	};

	this.setupToolbar = function() 
	{

		//make our dropdown for ceiling switching
		this.ceilSwitch = createSelect("ceilingSwitch");
		setChange(this.ceilSwitch,"MINIB.switchCeiling()");

    //assemble the xml
		var p = new PROTO();
    p.add("command","docmgr_bookmark_search");   
		p.post(API_URL,createMethodReference(this,"writeBookmarks"));

		//add the form for jumping bookmarks
		MODAL.toolbarLeft.appendChild(this.ceilSwitch);

		//add a select button
		MODAL.addToolbarButtonRight(_I18N_SELECT,"MINIB.selectObject()");

	};

	this.writeBookmarks = function(data) 
	{

		if (data.error) alert(data.error);
		else if (!data.record) alert(_I18N_NORESULTS_FOUND);
		else 
		{

			//save for later
			MINIB.bookmarks = data.record;

			MINIB.ceilSwitch[0] = new Option(_I18N_BOOKMARKS + "...","-1");

			for (var i=0;i<data.record.length;i++) 
			{
				MINIB.ceilSwitch[i+1] = new Option(data.record[i].name,data.record[i].object_path);
			}

			//figure out which bookmark we should use
			var opts = MINIB.ceilSwitch.options;
			MINIB.ceilSwitch.selectedIndex = 1;
	
			for (var i=0;i<opts.length;i++)
			{
				if (MINIB.path.indexOf(opts[i].value)!=-1)
				{
					MINIB.ceilSwitch.selectedIndex = i;
					break;
				}
			}

			if (MINIB.ceiling != MINIB.path) MINIB.setInitialPath();
			else MINIB.switchCeiling();

		}

	};

	/*******************************************************************
		FUNCTION: switchCeiling
		PURPOSE:	switches the current path to the selected ceiling
	*******************************************************************/
	this.switchCeiling = function() 
	{

		var cs = ge("ceilingSwitch");
		
		if (cs.value!=-1)
		{
			//reset the browse window
			MINIB.ceiling = cs.value;
			MINIB.path = cs.value;

			MINIB.clearBlocks();
			MINIB.setupBlocks(this.path);
			MINIB.browse(cs.value);
		}
		
	};

	/*******************************************************************
		FUNCTION: setupList
		PURPOSE:	sets up the div containing our columns of object lists
	*******************************************************************/
	this.setupList = function() 
	{

		this.list = ge("mbObjectList");

		var tbl = ce("table","","mbObjectTable");
		tbl.setAttribute("cellpadding","0");
		tbl.setAttribute("cellspacing","0");

		var tbd = ce("tbody");
		this.tableRow = ce("tr","","mbObjectTableRow");
		tbd.appendChild(this.tableRow);
		tbl.appendChild(tbd);
		this.list.appendChild(tbl);

	};

	/*******************************************************************
		FUNCTION: setupPathDiv
		PURPOSE:	sets up the div containing path textbox for the user
							to see path of selected object
	*******************************************************************/

	this.setupPathDiv = function() 
	{
		
		var pd = ge("mbPathDiv");

		var fileheader = ce("div","currentHeader","",_I18N_NAME + ": ");
		var info = ce("div","currentInfo");

		var filetb = createTextbox("fileName");

		if (this.mode=="open") 
		{
			if (document.all) filetb.disabled = true;
			else filetb.readonly = true;
		}

		pd.appendChild(fileheader);
		pd.appendChild(filetb);
		pd.appendChild(createCleaner());

	};

	this.clearBlocks = function()
	{

		var remove = ge("mbObjectTable").getElementsByTagName("td");

		for (var i=0;i<remove.length;i++)
		{
			remove[i].parentNode.removeChild(remove[i]);
		}

	};

	this.cleanupBlocks = function(cellpath)
	{

		var cells = ge("mbObjectTable").getElementsByTagName("td");
		var remove = new Array();

			//loop through the cells and remove what we don't need
			for (var c=0;c<cells.length;c++)
			{

				var cp = cells[c].getAttribute("id");

				if (!cp) remove.push(cells[c])
				else
				{

					if (cp!="/") cp += "/";

					//this cell's path isn't in what we're browsing, kill it
					if (cellpath.indexOf(cp)==-1)
					{
						remove.push(cells[c]);
					}
					else if (MINIB.ceiling!="/" && cp=="/")
					{
						remove.push(cells[c]);
					}

				}

			}

		for (var i=0;i<remove.length;i++)
		{
			remove[i].parentNode.removeChild(remove[i]);
		}

	}

	this.setupBlocks = function(path)
	{

		if (!path) path = "/";

		MINIB.cleanupBlocks(path);

		var cells = ge("mbObjectTable").getElementsByTagName("td");
		var patharr = path.split("/");
		var used = new Array();

		//now add all cells that aren't there				
		for (var i=0;i<patharr.length;i++)
		{

			used.push(patharr[i]);
			var cellpath;

			//start at the ceiling
			if (!patharr[i]) cellpath = "/";
			else cellpath = used.join("/");

			//make sure we are within our ceiling
			if (cellpath.indexOf(MINIB.ceiling)==-1) continue;

			var exists = false;
			var len = cells.length;
			var ref = ge(cellpath);

			if (!ref)
			{

				//if it doesn't exist, make it
				var ref = ce("td","mbObjectCell");
				ref.setAttribute("id",cellpath);
				ref.setAttribute("valign","top");

				MINIB.tableRow.appendChild(ref);

			}

		}

		MINIB.padBlock();

	};

	this.padBlock = function()
	{

		//since we are using a table, we need at least 3 columns or it doesn't look right
		var arr = MINIB.tableRow.getElementsByTagName("td");

		if (arr.length < 3)
		{

			for (var i=arr.length;i<3;i++)
			{
					var ref = ce("td","mbObjectCell");
					ref.setAttribute("id","");
					MINIB.tableRow.appendChild(ref);
			}

		}

	}
	
	this.fetchBlock = function(path)
	{

		if (!path) path = "/";
		return ge(path);

	}

	this.setupTypeDiv = function() {

		//if we are in save mode
		if (this.mode=="save")
		{
			var p = new PROTO();
			p.setProtocol("XML");
			p.get("config/extensions.xml",createMethodReference(this,"writeTypeDiv"));
		}

	};

	this.writeTypeDiv = function(data)
	{

		//figure out if any are set
		var arr = new Array();

		for (var i=0;i<data.object.length;i++)
		{
			if (data.object[i].allow_dmsave==1) arr.push(new Array(data.object[i].proper_name,data.object[i].extension));
		}

		var pd = ge("mbTypeDiv");

		//we have additional save as file options available.  create a dropdown listing them
		if (arr.length > 0)
		{

			var fileheader = ce("div","currentHeader","",_I18N_TYPE + ": ");

			var sel = createSelect("fileType");
			sel[0] = new Option("DocMGR " + _I18N_DOCUMENT,"docmgr");

			for (var i=0;i<arr.length;i++)
			{
				sel[i+1] = new Option(arr[i][0],arr[i][1]);
			}

			pd.appendChild(fileheader);
			pd.appendChild(sel);
			pd.appendChild(createCleaner());

			//set default save type
			if (DMEDITOR_DEFAULT_SAVE!="DMEDITOR_DEFAULT_SAVE")
			{
				sel.value = DMEDITOR_DEFAULT_SAVE;
			}

		}
		else
		{

			//no options available, just save as docmgr
			var hid = createHidden("fileType","docmgr");
			pd.appendChild(hid);

		}

	};

	this.setupControls = function() 
	{

		var mydiv = ge("mbObjectControls");

		if (this.filter=="*" || !this.filter) var filtertxt = _I18N_FILTER + ": " + _I18N_NONE;
		else filtertxt = _I18N_FILTER + " (" + this.filter + ")";
		var filterdiv = ce("div","filterDesc","",filtertxt);

		var selbtn = createBtn("selbtn",_I18N_SELECT,"MINIB.selectObject();return false");

	};
	
	this.selectObject = function()
	{

		//make sure there's a filename mattching our app
		if (!MINIB.checkFileName()) return false;

		if (MINIB.handler)
		{

			//save the file as this type
			if (this.mode=="save") 
			{

				if (MINIB.selected.length > 0)
				{

					var arr = MINIB.selected[0];
					arr.savetype = ge("fileType").value;

					//if they changed the name, don't return the id of the object
					if (ge("fileName").value != arr.name) arr.id = null;

					//make sure we pass the typed one
					arr.name = ge("fileName").value;
					
					//they've picked a collection and typed a name
					if (arr.type=="collection")
					{
						arr.parent = arr.path;
						arr.path += "/" + arr.name;
					}
					
					MINIB.selected = new Array(arr);

				}
				//just use a bookmark as the path
				else
				{
					var arr = new Array();
					arr.parent = MINIB.path;
					arr.savetype = ge("fileType").value;
					arr.name = ge("fileName").value;

					MINIB.selected = new Array(arr);			

				}

				//fallback for object type
				if (!MINIB.selected[0].type)
				{
					if (MINIB.selected[0].savetype=="docmgr") MINIB.selected[0].type = "document";
					else MINIB.selected[0].type = "file";
				}

			}

			//call the save function in the opening window.
			var func = eval(MINIB.handler);
			func(MINIB.selected);

			//we were loaded as a popup, not pushed onto an existing one
			if (!MINIB.backHandler) MODAL.hide();
			else 
			{
				var func = eval(MINIB.backHandler);
				func();
			}

		}
		else
		{
			MODAL.hide();
		}
	};

	/**************** everything below here needs to be called and call others statically (MINIB.funcname)**********************/

	/*******************************************************************
		FUNCTION: browse
		PURPOSE:	makes call to get objects in the requested column
	*******************************************************************/
	this.browse = function(path)
	{

		updateSiteStatus(_I18N_PLEASEWAIT);

		//setup our containers for the results
		MINIB.setupBlocks(path);

		MINIB.path = path;

		//assemble the xml
		var p = new PROTO();
		p.add("command","docmgr_query_browse");
		p.add("path",path);
		p.add("no_paginate","1");
		p.post(API_URL,"MINIB.browseResults");

	};

	/******************************************************************
		FUNCTION: browseResults
		PURPOSE:	result handler for column list data
	*******************************************************************/
	this.browseResults = function(data) 
	{

		clearSiteStatus();

		if (data.error) alert(data.error);
		else 
		{

			if (!data.current_object_path) data.current_object_path = "/";

			var ref = MINIB.fetchBlock(data.current_object_path);

			//get rid of existing data	
			clearElement(ref);

			//nothing found
			if (!data.record) 
			{
				ref.appendChild(ce("div","errorMessage","",_I18N_NORESULTS_FOUND));
			} 
			else 
			{

				//a container for our data that will scroll	
				var cont = ce("div","mbObjectCellContainer");
				var added = false;

				for (var i=0;i<data.record.length;i++) 
				{
	
					var obj = data.record[i];

					//check the type filter.  if not match, skip it
					if (!MINIB.checkTypeFilter(obj.object_type)) continue;

					//setup the row id
					if (data.current_object_path=="/") var curpath = "/" + obj.name;
					else var curpath = data.current_object_path + "/" + obj.name;
	
					//create the row and image
					var row = ce("div","mbObjectRow","row_" + curpath);
					row.setAttribute("path",curpath);
					row.setAttribute("object_id",obj.id);
					row.setAttribute("object_type",obj.object_type);
					row.setAttribute("object_extension",obj.extension);
					row.setAttribute("object_name",obj.name);

		 			var img = ce("img");
	  			img.setAttribute("src",THEME_PATH + "/images/object-icons/" + obj.icon);
	
					//put them together
					row.appendChild(img);
					row.appendChild(ctnode(obj.name.substr(0,24)));
	
					if ((obj.object_type=="file" && MINIB.checkFilter(obj.extension)) || 
							obj.object_type=="document" || obj.object_type=="collection")
					{
						setClick(row,"MINIB.handleClick(event)");
						setDblClick(row,"MINIB.handleDblClick(event)");
					}
					else
					{
						setClass(row,"mbObjectRowDisabled");
					}

					cont.appendChild(row);			
					added = true;
	
				}

				//no objects matched the filter
				if (added==false) 
				{
					cont.appendChild(ce("div","errorMessage","",_I18N_NORESULTS_FOUND));
				}

				ref.appendChild(cont);


			}

			MINIB.updateDisplay();

		}
	
	};

	this.gatherInfo = function(e)
	{

		var ref = getEventSrc(e);
		if (ref.tagName.toLowerCase()=="img") ref = ref.parentNode;

		var arr = new Array();
		arr.type = ref.getAttribute("object_type");
		arr.extension = ref.getAttribute("object_extension");
		arr.id = ref.getAttribute("object_id");
		arr.path = ref.getAttribute("path");
		arr.name = ref.getAttribute("object_name");
		arr.parent = MINIB.path;

		if (MINIB.ctrlpressed==true)
		{
			MINIB.selected.push(arr);
		}
		else
		{
			MINIB.selected = new Array(arr);
		}

		return arr;

	};

	this.handleClick = function(e)
	{

		var arr = MINIB.gatherInfo(e);
		MINIB.updateSelectedClass()

		if (arr.type=="collection")
		{
			MINIB.browse(arr.path);
		}
		else if (arr.type=="file" || arr.type=="document")
		{
			MINIB.updateSelectedPath(arr.path,arr.type);
		}

	};

	this.handleDblClick = function(e)
	{

		var arr = MINIB.gatherInfo(e);

		MINIB.updateSelectedClass()

		if (arr.type=="file" || arr.type=="document")
		{
			MINIB.selectObject();
		}

	};

	this.updateDisplay = function() 
	{

			//only look for stale elements after the table has been initially loaded
			MINIB.updatePathDisplay();

			//update selected class and scrollbar
			MINIB.updateSelectedClass()
			MINIB.updateScroll();

			MINIB.updateSelectedPath(MINIB.path,"collection");


	};	


	/********************** utilities ****************************/

	this.updatePathDisplay = function()
	{

		var ref = ge("mbPathDisplay");
		clearElement(ref);

		ref.appendChild(ce("div","currentHeader","",_I18N_PATH + ": "));
		ref.appendChild(ce("div","currentInfo","",MINIB.path));
		ref.appendChild(createCleaner());

	};

	this.checkFilter = function(ext) {

		//if everything, just return true
		if (MINIB.filter=="*" || !MINIB.filter) return true;

		var check = "," + this.filter + ",";

		if (check.indexOf("," + ext + ",")==-1) return false;
		else return true;

	};

	this.checkTypeFilter = function(objtype) {

		//if everything, just return true
		if (MINIB.typeFilter=="*" || !MINIB.typeFilter) return true;

		var arr = MINIB.typeFilter.split(",");
		var key = arraySearch(objtype,arr);

		if (key==-1) return false;
		else return true;

	};



	/******************************************************************
		FUNCTION: updateScroll
		PURPOSE:	scrolls the list columns to see the most recent element
	*******************************************************************/
	this.updateScroll = function() {

		cells = MINIB.tableRow.getElementsByTagName("td");
		var c = 0;

		MINIB.list.scrollLeft = cells.length * 185;

	};

	/******************************************************************
		FUNCTION: updateSelectedPath
		PURPOSE:	stores the current path we are viewing
	*******************************************************************/
	this.updateSelectedPath = function(path,objtype) {

		//update the class for what we just cliecked
		MINIB.updateSelectedClass()

		if (path.length > 4) 
		{

			//if viewing fiels and not documents, do this
			if (objtype=="document" || objtype=="file") 
			{

				var arr = path.split("/");
				var fn = arr.pop();
				ge("fileName").value = fn;

				//set the path to the parent of this one
				this.path = arr.join("/");

			} 
			else ge("fileName").value = "";

		} 

	};

	/**
		shows which objects in the current path are selected by the user
		*/
	this.updateSelectedClass = function()
	{

		//stop here if nothing to work with
		if (MINIB.selected.length==0) return false;

		var path = MINIB.selected[0].path;
		var ref = ge("row_" + path);
		if (!ref) return false;
	
		var arr = ref.parentNode.getElementsByTagName("div");

		//reset the classes on all objects in this directory	
		for (var i=0;i<arr.length;i++) 
		{
			var cn = getObjClass(arr[i]);
			if (cn!="mbObjectRowDisabled") setClass(arr[i],"mbObjectRow");
		}

		//now show all current objects as selected
		for (var i=0;i<MINIB.selected.length;i++)
		{

			var path = MINIB.selected[i].path;
			var ref = ge("row_" + path);
			if (!ref) return false;

			setClass(ref,"mbObjectRowSel");

		}

	};

	this.updateColumnScroll = function(path) 
	{

		var cells = MINIB.tableRow.getElementsByTagName("td");

		for (var i=0;i<cells.length;i++)
		{

			var path = cells[i].getAttribute("id");

			MINIB.updateSelectedClass(path);

			//get all entries in our cell
			var divs = cells[i].getElementsByTagName("div");

			//set the scrolltop so we can always see the selected folder
			for (var c=0;c<divs.length;c++) 
			{

				if (divs[c].id.indexOf("row_")==-1) continue;

				if (divs[c].className=="mbObjectRowSel") 
				{
					cells[i].getElementsByTagName("div")[0].scrollTop = (c * 20);
					break;
				}

			}

		}

	};

	/******************************************************************
		FUNCTION: checkFileName
		PURPOSE:	makes sure we have the correct extension on the file
							we are saving
	*******************************************************************/
	this.checkFileName = function() 
	{

		if (MINIB.mode=="save") {

			var ref = ge("fileName");
			if (!ref) return false;

			//did they enter the name
			if (ref.value.length==0) 
			{
				alert(_I18N_ENTER_NAME_FILE_ERROR);
				ref.focus();
				return false;
			}		

		}

		return true;

	};
	
}

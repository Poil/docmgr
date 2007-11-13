ajaxRH["filelist"] = "writeFileList";
ajaxRH["fileedit"] = "writeFileEdit";
ajaxRH["pagetree"] = "writePageTree";

var oEditor     = window.parent.InnerDialogLoaded() ;
var FCK         = oEditor.FCK ;
var FCKLang     = oEditor.FCKLang ;
var FCKConfig   = oEditor.FCKConfig ;
var sessId	= FCKConfig.sessionId;
var parentId	= FCKConfig.parentId;
var siteurl 	= FCKConfig.site_url;
var sitetheme   = FCKConfig.theme_path;

//load our language file
eval(loadScript(FCKConfig.langFile));

var uploadStat;

// Get the selected image (if available).
var oImage = FCK.Selection.GetSelectedElement() ;

if ( oImage && oImage.tagName != 'IMG' && !( oImage.tagName == 'INPUT' && oImage.type == 'image' ) )
	oImage = null ;

// Get the active link.
var oLink = FCK.Selection.MoveToAncestorNode( 'A' ) ;

var oImageOriginal ;

function setUrl(url) {

	var tu = document.getElementById("txtUrl");
	var mode = document.getElementById("switchMode");

	//make the image propertie field visible if it's not
	if (mode.value!="imagePropDiv") {
		mode.value = "imagePropDiv";
		changeMode();
	}

	//set our value
	tu.value = url;
	
	updatePreviewImg(url);

}

function updatePreviewImg(url) {

	var ip = document.getElementById("imgpreview");
	
	//update our preview window
	ip.innerHTML = "";

	var pimg = document.createElement("img");
	pimg.setAttribute("src",url);
	if (document.all) {
		pimg.setAttribute("height","148");
		pimg.setAttribute("width","198");
	}
	ip.appendChild(pimg);

}

function changeMode() {

	var newmode = document.getElementById("switchMode").value;

	hideObject("pageTreeDiv");
	hideObject("uploadFileDiv");
	hideObject("imagePropDiv");

	showObject(newmode);

}


function loadImagePage() {


	//resize our window
	if (document.all) {
		window.parent.dialogHeight=42;
		window.parent.dialogWidth=58;
	        var newleft = 20;
	        var newtop = 10;
		window.parent.dialogLeft = newleft;
		window.parent.dialogTop = newtop;

	}
	else {

		window.parent.resizeTo(830,630) ;
		//reposition our window
	        var newleft = (screen.width - 830) / 2;
	        var newtop = (screen.height - 630) / 2;
		window.parent.moveTo(newleft,newtop);
	}

	 // Activate the "OK" button.
        window.parent.SetOkButton( true ) ;

	createUploadDiv();
	createImageProp();
	createBrowseHeader();
	LoadSelection();
	loadFileList();
	loadPageTree();
	loadModeSelector();

}

function loadFileList() {
	
	//load our file list
	var mydiv = document.getElementById("fileList");
	var url;

	mydiv.innerHTML = "<div class=\"successMessage\">" + I18N["please_wait"] + "</div>\n";	
		
	//use the appropriate url for our mode
	url = siteurl + "app/filelist.php?parentId=" + parentId + "&sessionId=" + sessId;
	loadXMLReq(url);

}

function loadModeSelector() {

	var mydiv = document.getElementById("selectMode");
	var mode = FCKConfig.imageMode;

	//load our select box for changing modes
	var sel = document.createElement("select");
	sel.setAttribute("name","switchMode");
	sel.setAttribute("id","switchMode");
	setClass(sel,"dropdownSmall");
	setChange(sel,"changeMode()");
	sel[0] = new Option(I18N["image_prop"],"imagePropDiv");
	sel[1] = new Option(I18N["upload_file"],"uploadFileDiv");
	sel[2] = new Option(I18N["browse_page"],"pageTreeDiv");
	
	mydiv.appendChild(document.createTextNode(I18N["switch_to"]+ ": "));
	mydiv.appendChild(sel);

}

function loadPageTree(id,expandSingle) {

	//load our file list
	var url;

	if (id) parentId = id;

	//load our page list
	url = siteurl + "app/pagetree.php?divName=pageTree&parentId=" + parentId + "&sessionId=" + sessId;
	if (expandSingle) url += "&expandSingle=" + expandSingle;
	loadXMLReq(url);

}

function writeFileList(resp) {

	var mydiv = document.getElementById("fileList");
	mydiv.innerHTML = "";

	//get our display mode
	var dm = document.getElementById("displayMode").value;

	var data = parseXML(resp);
	var filecount = data.filecount;
	var i;

	if (!filecount) {
		curdiv = document.createElement("div");
		setClass(curdiv,"errorMessage");
		curdiv.appendChild(document.createTextNode(I18N["no_files_found"]));
	} else {
		
		curdiv = document.createElement("div");
		for (i=0;i<filecount;i++) {
			if (dm=="list") curdiv.appendChild(createFileList(data.file[i]));
			else curdiv.appendChild(createFileThumb(data.file[i]));
		}

	}

	mydiv.appendChild(curdiv);

}

function createFileThumb(file) {

	//the parent container
	var thumbdiv = document.createElement("div");
	setClass(thumbdiv,"thumbcontainer");

	//the file container
	var filediv = document.createElement("div");
	setClass(filediv,"filecontainer");

	var thumbimg = document.createElement("img");
	setClass(thumbimg,"thumbnail");
	thumbimg.setAttribute("src",file.thumburl);

	if (document.all) {
		thumbimg.setAttribute("height","75");
		thumbimg.setAttribute("width","100");
	}
	filediv.appendChild(thumbimg);

	imgclick = "setUrl('" + file.fileurl + "')";
	setClick(thumbimg,imgclick);

	//the name
	var fndiv = document.createElement("div");
	setClass(fndiv,"filename");
	fndiv.appendChild(document.createTextNode(file.filename));

	//the options
	var optdiv = document.createElement("div");
	setClass(optdiv,"fileopt");
	
	if (file.allowedit) {
		var rlimg = document.createElement("img");
		rlimg.setAttribute("src",siteurl + sitetheme + "/images/rotate_left.gif");
		setClick(rlimg,"rotateImage('" + file.objectId + "','" + file.fileId + "','left')");

		var rrimg = document.createElement("img");
		rrimg.setAttribute("src",siteurl + sitetheme + "/images/rotate_right.gif");
		setClick(rrimg,"rotateImage('" + file.objectId + "','" + file.fileId + "','right')");

		optdiv.appendChild(rlimg);
		optdiv.appendChild(rrimg);

		var delimg = document.createElement("img");
		delimg.setAttribute("src",siteurl + sitetheme + "/images/trash.gif");
		setClick(delimg,"removeImage('" + file.objectId + "')");
		optdiv.appendChild(delimg);

	}

	thumbdiv.appendChild(filediv);
	thumbdiv.appendChild(fndiv);
	thumbdiv.appendChild(optdiv);

	return thumbdiv;	

}

function createFileList(file) {

	//the parent container
	var listdiv = document.createElement("li");
	setClass(listdiv,"listcontainer");

	//the name
	var fndiv = document.createElement("div");
	setClass(fndiv,"listname");

	var fnlink = document.createElement("a");
	imgclick = "javascript:setUrl('" + file.fileurl + "')";
	fnlink.setAttribute("href",imgclick);
	fnlink.appendChild(document.createTextNode(file.filename));
	fndiv.appendChild(fnlink);

	//the options
	var optdiv = document.createElement("div");
	setClass(optdiv,"listopt");
	
	if (file.allowedit) {
		var rlimg = document.createElement("img");
		rlimg.setAttribute("src",siteurl + sitetheme + "/images/rotate_left.gif");
		setClick(rlimg,"rotateImage('" + file.filepath + "','left')");

		var rrimg = document.createElement("img");
		rrimg.setAttribute("src",siteurl + sitetheme + "/images/rotate_right.gif");
		setClick(rrimg,"rotateImage('" + file.filepath + "','right')");

		optdiv.appendChild(rlimg);
		optdiv.appendChild(rrimg);
	}

	var delimg = document.createElement("img");
	delimg.setAttribute("src",siteurl + sitetheme + "/images/trash.gif");
	setClick(delimg,"removeImage('" + file.filepath + "')");
	optdiv.appendChild(delimg);

	listdiv.appendChild(optdiv);
	listdiv.appendChild(fndiv);

	return listdiv;	

}

function rotateImage(objId,fileId,dir) {

	var mydiv = document.getElementById("fileList");

	mydiv.innerHTML = "<div class=\"successMessage\">" + I18N["please_wait"] + "</div>\n";	
	url = siteurl + "app/fileedit.php?objectId=" + objId + "&fileId=" + fileId + "&sessionId=" + sessId + "&editfile=rotate&dir=" + dir;
	loadXMLReq(url);

}

function removeImage(objId) {

	var mydiv = document.getElementById("fileList");

	if (confirm("Are you sure you want to remove this file?")) {
		mydiv.innerHTML = "<div class=\"successMessage\">" + I18N["please_wait"] + "</div>\n";	
		url = siteurl + "app/fileedit.php?objectId=" + objId + "&sessionId=" + sessId + "&editfile=delete";		
		loadXMLReq(url);
	}

}

function writeFileEdit(resp) {

	loadFileList();

	//append epoch so the image isn't cached
	var thisdate = new Date();
	var epoch = thisdate.getTime();

	sUrl = GetE('txtUrl').value + "&epoch=" + epoch;
	updatePreviewImg(sUrl);

}



function LoadSelection()
{
	if ( ! oImage ) return ;

	var sUrl = GetAttribute( oImage, 'src', '' ) ;

	if (sUrl) updatePreviewImg(sUrl);

	// TODO: Wait stable version and remove the following commented lines.
//	if ( sUrl.startsWith( FCK.BaseUrl ) )
//		sUrl = sUrl.remove( 0, FCK.BaseUrl.length ) ;

	GetE('txtUrl').value    = sUrl ;
	GetE('txtAlt').value    = GetAttribute( oImage, 'alt', '' ) ;
	GetE('txtVSpace').value	= GetAttribute( oImage, 'vspace', '' ) ;
	GetE('txtHSpace').value	= GetAttribute( oImage, 'hspace', '' ) ;
	GetE('txtBorder').value	= GetAttribute( oImage, 'border', '' ) ;
	GetE('cmbAlign').value	= GetAttribute( oImage, 'align', '' ) ;

	if ( oImage.style.pixelWidth > 0 )
		GetE('txtWidth').value  = oImage.style.pixelWidth ;
	else
		GetE('txtWidth').value  = GetAttribute( oImage, "width", '' ) ;

	if ( oImage.style.pixelHeight > 0 )
		GetE('txtHeight').value  = oImage.style.pixelHeight ;
	else
		GetE('txtHeight').value = GetAttribute( oImage, "height", '' ) ;

        if ( oLink )
        {
                GetE('txtLnkUrl').value         = oLink.getAttribute('href',2) ;
                //GetE('cmbLnkTarget').value      = oLink.target ;
        }

}

//#### The OK button was hit.
function Ok()
{
	if ( GetE('txtUrl').value.length == 0 )
	{
		window.parent.SetSelectedTab( 'Info' ) ;
		GetE('txtUrl').focus() ;

		alert( FCKLang.DlgImgAlertUrl ) ;

		return false ;
	}

	var bHasImage = ( oImage != null ) ;

	if ( !bHasImage )
		oImage = FCK.CreateElement( 'IMG' ) ;
	else
		oEditor.FCKUndo.SaveUndoStep() ;
	
	UpdateImage( oImage ) ;

	var sLnkUrl = GetE('txtLnkUrl').value.trim() ;

	if ( sLnkUrl.length == 0 )
	{
		if ( oLink )
			FCK.ExecuteNamedCommand( 'Unlink' ) ;
	}
	else
	{
		if ( oLink )	// Modifying an existent link.
			oLink.href = sLnkUrl ;
		else			// Creating a new link.
		{
			if ( !bHasImage )
				oEditor.FCKSelection.SelectNode( oImage ) ;

			oLink = oEditor.FCK.CreateLink( sLnkUrl ) ;

			if ( !bHasImage )
			{
				oEditor.FCKSelection.SelectNode( oLink ) ;
				oEditor.FCKSelection.Collapse( false ) ;
			}
		}

		//SetAttribute( oLink, 'target', GetE('cmbLnkTarget').value ) ;
	}

	self.close();
	return true ;
}

function UpdateImage( e, skipId )
{
	e.src = GetE('txtUrl').value ;
	SetAttribute( e, "alt"   , GetE('txtAlt').value ) ;
	SetAttribute( e, "width" , GetE('txtWidth').value ) ;
	SetAttribute( e, "height", GetE('txtHeight').value ) ;
	SetAttribute( e, "vspace", GetE('txtVSpace').value ) ;
	SetAttribute( e, "hspace", GetE('txtHSpace').value ) ;
	SetAttribute( e, "border", GetE('txtBorder').value ) ;
	SetAttribute( e, "align" , GetE('cmbAlign').value ) ;

}

var bLockRatio = true ;

function SwitchLock( lockButton )
{
	bLockRatio = !bLockRatio ;
	lockButton.className = bLockRatio ? 'BtnLocked' : 'BtnUnlocked' ;
	lockButton.title = bLockRatio ? 'Lock sizes' : 'Unlock sizes' ;

	if ( bLockRatio )
	{
		if ( GetE('txtWidth').value.length > 0 )
			OnSizeChanged( 'Width', GetE('txtWidth').value ) ;
		else
			OnSizeChanged( 'Height', GetE('txtHeight').value ) ;
	}
}

// Fired when the width or height input texts change
function OnSizeChanged( dimension, value )
{
	// Verifies if the aspect ration has to be mantained
	if ( oImageOriginal && bLockRatio )
	{
		if ( value.length == 0 || isNaN( value ) )
		{
			GetE('txtHeight').value = GetE('txtWidth').value = '' ;
			return ;
		}

		if ( dimension == 'Width' )
			GetE('txtHeight').value = value == 0 ? 0 : Math.round( oImageOriginal.height * ( value  / oImageOriginal.width ) ) ;
		else
			GetE('txtWidth').value  = value == 0 ? 0 : Math.round( oImageOriginal.width  * ( value / oImageOriginal.height ) ) ;
	}

	UpdatePreview() ;
}

// Fired when the Reset Size button is clicked
function ResetSizes()
{
	if ( ! oImageOriginal ) return;

	GetE('txtWidth').value  = oImageOriginal.width;
	GetE('txtHeight').value = oImageOriginal.height;

	UpdatePreview();
}

//add a file to our queue
function addFile() {

	//now create a text indicator to show the file
	newfile = document.createElement("li");	

	//get our form containing the file we want to upload
	filediv = document.getElementById("uploadFileForm");
	var ufarr = filediv.getElementsByTagName("input");
	var uf = ufarr[0];

	if (!uf || !uf.value) return false;

	//add a new hidden form element and the 
	var txtdiv = document.getElementById("uploadFileText");
	var curform = document.pageForm;

	//this is kind of awkward, but I couldn't get a file input to clone
	//properly in i.e.  Basically we move the file object used to select files
	//down and change it's name, then create a new one in it's place

	//copy the current one and change its name
	uf.setAttribute("name","fileUpload[]");
	uf.style.visibility="hidden";
	uf.style.position="absolute";
	uf.style.left="0";
	uf.style.top="0";
	newfile.appendChild(uf);	

	//create a new one
	var uploadfile = document.createElement("input");
	uploadfile.type = "file";
	setChange(uploadfile,"addFile()");
	filediv.appendChild(uploadfile);

	//the link for clearing the file
	var cleardiv = document.createElement("div");
	setFloat(cleardiv,"right");
	cleardiv.style.textAlign="right";

	var clearStr = escapeBackslash(uf.value);

	var clearlink = document.createElement("a");
	clearlink.setAttribute("href","javascript:clearUpload('" + clearStr + "')");
	clearlink.appendChild(document.createTextNode("[Remove]"));
	cleardiv.appendChild(clearlink);
	newfile.appendChild(cleardiv);

	var lbldiv = document.createElement("div");
	if (uf.value.indexOf("/") != -1) var stArr = uf.value.split("/");
	else var stArr = uf.value.split("\\");
	var len = stArr.length - 1;
	lbldiv.appendChild(document.createTextNode(stArr[len]));
	newfile.appendChild(lbldiv);


	//add to the parent
	txtdiv.appendChild(newfile);	

	//empty out the original
	//uf.value = "";

}

function uploadFiles() {

	document.pageForm.parentId.value = parentId;
	document.pageForm.sessionId.value = sessId;
	document.pageForm.submit();

	//every second load our frame and see if it's done
	var mydiv = document.getElementById("fileList");
	mydiv.innerHTML = "<div class=\"successMessage\">" + I18N["please_wait"] + "</div>\n";	
	uploadStat = setInterval("checkUploadForm()","1000");


}

function checkUploadForm() {

	txt = window.frames["uploadframe"].document.body.innerHTML;

	//some error reporting
	if (txt.length > 10) {
		alert(txt);
		txt = "done";
	}

	if (txt=="done") {
		clearInterval(uploadStat);
		document.getElementById("uploadFileText").innerHTML = "";
		loadFileList();
	}

}

function clearUpload(fp) {

       	//get the filter select box value
        cd = document.getElementById("searchCriteria");

	//get all bullets in our area
	var txtdiv = document.getElementById("uploadFileText");
	var liarr = txtdiv.getElementsByTagName("li");

	var num = liarr.length;
	var i;

	//cycle thru the bullets
	for (i=0;i<num;i++) {

		//find the hidden input file field.  If it's value matches our file pointer
		//then remove it from the list
		var curli = liarr[i];
		if (curli) {
			var filearr = curli.getElementsByTagName("input");
			var curfile = filearr[0];		

			//we have a match, remove this node
			if (curfile.value==fp) txtdiv.removeChild(curli);
		}
	}
}

function browseCollection(pid) {

	var mydiv = document.getElementById("fileList");

	//change our parentId for browsing and uploading
	parentId = pid;

	mydiv.innerHTML = "<div class=\"successMessage\">" + I18N["please_wait"] + "</div>";

	if (pid > 0) expandCollection(pid);

        //check to see if the div is accessible.  If not, force a page reload
	url = siteurl + "app/filelist.php?parentId=" + parentId + "&sessionId=" + sessId;
	loadXMLReq(url);

}




function createUploadDiv() {

	/***************************************
		create our upload form
	***************************************/

	// the main div
	var updiv = document.getElementById("uploadFileDiv");

	//title header
	var upheader = document.createElement("div");
	setClass(upheader,"formHeader");
	upheader.appendChild(document.createTextNode(I18N["select_upload"]));
	
	//our file input form
	var upform = document.createElement("div");
	upform.setAttribute("id","uploadFileForm");

	var upfile = createForm("file");
	setClass(upfile,"textboxSmall");
	setChange(upfile,"addFile()");

	upform.appendChild(upfile);
	
	//div for storing virtual file list
	var upfiletxt = document.createElement("div");
	upfiletxt.setAttribute("id","uploadFileText");

	//the button
	var upbtn = createForm("button");
	setClass(upbtn,"submitSmall");
	setClick(upbtn,"uploadFiles()");
	upbtn.value = I18N["upload_files"];

	//assemble
	updiv.appendChild(upheader);
	updiv.appendChild(upform);
	updiv.appendChild(upfiletxt);
	updiv.appendChild(upbtn);

	return updiv;

}

function createImageProp() {

	//container
	var ipdiv = document.getElementById("imagePropDiv");

	//header	
	var ipheader = document.createElement("div");
	setClass(ipheader,"formHeader");
	ipheader.appendChild(document.createTextNode(I18N["selected_image"]));

	//preview area
	var imgprev = document.createElement("div");
	imgprev.setAttribute("id","imgpreview");	

	//properties area
	var propheader = document.createElement("div");
	setClass(propheader,"formHeader");
	propheader.appendChild(document.createTextNode(I18N["image_prop"]));

	//create a parent div to clone later
	var con = document.createElement("div");
	setClass(con,"imgprop");
	var conheader = document.createElement("div");
	var conform = createForm("text");
	conform.setAttribute("size","30");
	con.appendChild(conheader);
	con.appendChild(conform);

	//our headers
	ipdiv.appendChild(ipheader);
	ipdiv.appendChild(imgprev);
	ipdiv.appendChild(propheader);

	//our prop form
	ipdiv.appendChild(createImgPropForm(con,I18N["image_url"],"txtUrl","30"));	
	ipdiv.appendChild(createImgPropForm(con,I18N["link_url"],"txtLnkUrl","30"));	
	ipdiv.appendChild(createImgPropForm(con,I18N["alt_txt"],"txtAlt","30"));	
	ipdiv.appendChild(createImgPropForm(con,I18N["width"],"txtWidth","3"));	
	ipdiv.appendChild(createImgPropForm(con,I18N["height"],"txtHeight","3"));	
	ipdiv.appendChild(createImgPropForm(con,I18N["border"],"txtBorder","3"));	
	ipdiv.appendChild(createImgPropForm(con,I18N["align"],"cmbAlign","30"));	
	ipdiv.appendChild(createImgPropForm(con,I18N["vert_spacing"],"txtVSpace","3"));	
	ipdiv.appendChild(createImgPropForm(con,I18N["horiz_spacing"],"txtHSpace","3"));	

	return ipdiv;

}

function createImgPropForm(con,header,txt,size) {

	var img = con.cloneNode(true);
	var imgname = img.getElementsByTagName("div")[0];
	var imgform = img.getElementsByTagName("input")[0];

	imgname.appendChild(document.createTextNode(header));

	imgform.setAttribute("name",txt);
	imgform.setAttribute("id",txt);
	if (size) imgform.setAttribute("size",size);	

	return img;

}

function createBrowseHeader() {

	var bh = document.getElementById("browseHeader");

	var sd = document.createElement("div");
	sd.setAttribute("id","selectDisplay");

	var dm = createSelect("displayMode","loadFileList()");
	setClass(dm,"selectSmall");
	dm[0] = new Option(I18N["view_thumb"],"thumbnail");
	dm[1] = new Option(I18N["view_list"],"list");

	sd.appendChild(dm);

	var sf = document.createElement("div");
	sf.appendChild(document.createTextNode(I18N["select_file"]));
	setClass(sf,"formHeader");

	bh.appendChild(sd);
	bh.appendChild(sf);

}

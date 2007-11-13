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
var oLink = FCK.Selection.GetSelectedElement() ;

if ( oLink && oLink.tagName != 'IMG' && !( oLink.tagName == 'INPUT' && oLink.type == 'image' ) )
	oLink = null ;

// Get the active link.
var oLink = FCK.Selection.MoveToAncestorNode( 'A' ) ;

function setUrl(url) {

	var tu = document.getElementById("txtUrl");

	//set our value
	tu.value = url;
	
}


function loadLinkPage() {

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

		window.parent.resizeTo(650,485) ;
		//reposition our window
	        var newleft = (screen.width - 650) / 2;
	        var newtop = (screen.height - 485) / 2;
		window.parent.moveTo(newleft,newtop);
	}

	 // Activate the "OK" button.
        window.parent.SetOkButton( true ) ;

	loadForm();
	LoadSelection();
	loadPageTree();

}

function loadPageTree(id,expandSingle) {

	//load our file list
	var url;

	if (id) parentId = id;

	//load our page list
	url = siteurl + "app/completetree.php?divName=pageTree&parentId=" + parentId + "&sessionId=" + sessId;
	if (expandSingle) url += "&expandSingle=" + expandSingle;
	loadXMLReq(url);

}

function loadForm() {

	var curform = document.getElementById("linkForm");

	var header = document.createElement("div");
	setClass(header,"formHeader");
	header.appendChild(document.createTextNode(I18N["link_prop"]));

	//create the url textbox
	var urldiv = document.createElement("div");
	setClass(urldiv,"linkprop");

	var urlheader = document.createElement("div");
	urlheader.appendChild(document.createTextNode(I18N["url"]));
	var urlform = createForm("text","txtUrl");
	urlform.setAttribute("size","50");

	urldiv.appendChild(urlheader);
	urldiv.appendChild(urlform);

	//create the destination dropdown
	var tgtdiv = document.createElement("div");
	setClass(tgtdiv,"linkprop");

	var tgtheader = document.createElement("div");
	tgtheader.appendChild(document.createTextNode(I18N["target"]));
	var tgtform = createSelect("cmbTarget");
	tgtform[0] = new Option(I18N["new_win"] + " (_new)","_new");	
	tgtform[1] = new Option(I18N["same_win"] + " (_self)","_self");	

	tgtdiv.appendChild(tgtheader);
	tgtdiv.appendChild(tgtform);

	curform.appendChild(urldiv);
	curform.appendChild(tgtdiv);

}

function LoadSelection()
{
	if ( ! oLink ) return ;

	var sUrl = GetAttribute( oLink, 'href', '' ) ;

        // Get Advances Attributes
	GetE('txtUrl').value			= sUrl;
	GetE('cmbTarget').value 		= oLink.target;

}

//#### The OK button was hit.
function Ok()
{

	var sUrl = GetE('txtUrl').value;

        if ( oLink )    // Modifying an existent link.
        {
                oEditor.FCKUndo.SaveUndoStep() ;
                oLink.href = sUrl ;
        }
        else                    // Creating a new link.
        {
                oLink = oEditor.FCK.CreateLink( sUrl ) ;
                if ( ! oLink )
                        return true ;
        }

	//target
	oLink.target = GetE('cmbTarget').value;
	//SetAttribute( oLink, 'target', GetE('cmbTarget').value) ;

	self.close();

        return true ;

}


function browseCollection(mod,pid) {

	parentId = pid;

	if (pid > 0) expandCollection(pid);

        //check to see if the div is accessible.  If not, force a page reload
	var url = siteurl + "app/filelist.php?parentId=" + parentId + "&sessionId=" + sessId;
	loadXMLReq(url);

	var seturl = siteurl + "index.php?module=" + mod + "&objectId=" + pid;

	GetE('txtUrl').value = seturl;

}


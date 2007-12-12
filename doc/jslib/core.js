/*******************************************************
	core.js
	Common functions used in our apps
	Created: 04/20/2006
*******************************************************/

var BROWSER;

//set browser
if (navigator.userAgent.indexOf("Safari")!=-1) BROWSER = "safari";
else if (document.all) BROWSER = "ie";
else BROWSER = "mozilla";

//returns a string for opening a window in the center of the screen.
//bases position on width and height of the window
function centerParms(width,height,complete) {

	xPos = (screen.width - width) / 2;
	yPos = (screen.height - height) / 2;

	string = "left=" + xPos + ",top=" + yPos;

	//return the width & height portions too
	if (complete) string += ",width=" + width +",height=" + height;

	return string;
}

//return the key of the array which matches our needle
function arraySearch(str,arr) {

        arrlen = arr.length;

        for (c=0;c<arrlen;c++) {

                if (arr[c]==str) return c;

        }

        return -1;
}


//reduce an array to just those keys that has values.  The keys are
//resequenced as well
function arrayReduce(arr) {

    var newarr = new Array();
    var len = arr.length;
    var c = 0;

    for (i=0;i<len;i++) {

        if (arr[i].length > 0) {
            newarr[c] = arr[i];
            c++;
        }

    }

    return newarr;

}

//close a window and refresh the parent.
function selfClose() {

	var url = window.opener.location.href;
	window.opener.location.href = url;
	window.opener.focus();
	self.close();

}


function openModuleWindow(module,objectId,width,height) {

        if (!width) width = "600";
        if (!height) height = "500";

        parm = centerParms(width,height,1) + ",status=yes,scrollbars=yes";

        url = "index.php?module=" + module + "&objectId=" + objectId;
        nw = window.open(url,"_modulewin",parm);
        nw.focus();

}

function openModalWindow(module,objectId,width,height) {

        if (!width) width = "600";
        if (!height) height = "500";

        winparm = "toolbar=0,location=0,status=0,menubar=0,scrollbars=0,resizable=0";
        parm = centerParms(width,height,1) + "," + winparm;
        url = "index.php?module=" + module + "&objectId=" + objectId;
        nw = window.open(url,"_modalwin",parm);
        nw.focus();

}


//pause script execution for the specified milliseconds
function pause(numberMillis) {
    var now = new Date();
    var exitTime = now.getTime() + numberMillis;
    while (true) {
        now = new Date();
        if (now.getTime() > exitTime)
            return;
    }
}

//show our current site status
function updateSiteStatus(msg) {
	ge("siteStatus").style.display = "block";
	ge("siteStatus").innerHTML = msg;
}

//clear the site status
function clearSiteStatus() {
	ge("siteStatus").style.display = "none";
	ge("siteStatus").innerHTML = "";
}


//sort a multi dimensional array by desired key
function arrayMultiSort(arr,sort_key) {

	var newarr = new Array();
	var sortkey = new Array();

	//split our array into those w/ keys and w/o keys
	var fullsort = new Array();
	var emptysort = new Array();

	for (var i=0; i<arr.length; i++) {

		if (arr[i][sort_key]) {
			fullsort.push(arr[i]);
			sortkey.push(arr[i][sort_key]);
		}
		else emptysort[i].push(arr[i]);

	}

	//sort the key array
	sortkey.sort();

	//recreate our new array with the sort elements
	for (var i=0;i<sortkey.length;i++) {

		//assemble in the correct order
		for (c=0;c<fullsort.length;c++) {

			if (fullsort[c][sort_key]==sortkey[i]) {
				newarr.push(fullsort[c]);
				break;
			}
	
		}

	}

	//now add the elements w/o keys
	for (var i=0; i<emptysort.length;i++) newarr.push(emptysort[i]);
	
	return newarr;

}

function bitset_compare(bit1,bit2,admin) {

    auth = null;

    if ( parseInt(bit1) & parseInt(bit2) ) auth = 1;

    if (admin) {

        if ( parseInt(bit1) & parseInt(admin) ) auth = 1;

    }

    if (!auth) return false;
    else return true;

}

/*******************************************
	these two functions require mootools.js
*******************************************/
function getWinWidth() {
	return window.getWidth();
}

function getWinHeight() {
	return window.getHeight();
}

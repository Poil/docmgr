/*******************************************************
	core.js
	Common functions used in our apps
	Created: 04/20/2006
*******************************************************/

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


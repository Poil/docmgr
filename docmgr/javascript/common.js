/*************************************************
	FILENAME: common.js
	PURPOSE:  contains miscellaneous common
						site-specific javascript functions
*************************************************/

var logintimer;
var docmgrtimer;
var siteFileUpload = 0;			//set this when uploading a file to prevent other background processes from running

function startLoginTimer() {

	logintimer = setInterval("loginTimer()","60000");

}

function loginTimer() {

	//if uploading a file, bail
	if (siteFileUpload==1) return false;

	var url = "index.php?module=logintimer";
	protoReq(url,"writeLoginTimer");

}

function writeLoginTimer(data) {

	if (data.time_left <= 0) logoutWarning();

}

function killScreen() {

	var ds = ge("screenKiller");
	var w = getWinWidth();
	var h = getWinHeight();

	if (document.all) h += 200;

	ds.style.width = w + "px";
	ds.style.height = h + "px";
	setClick(ds,"void(0)");

}

function liveScreen() {

	var ds = ge("screenKiller");
	ds.style.width = "0px";
	ds.style.height = "0px";

}

function logoutWarning() {

	//just kick us back to the login screen
	runTimeout();

}

function runTimeout() {

	location.href = "index.php?timeout=true";

}

function fileExtension(fn) {

	var ext = "";

	var pos = fn.lastIndexOf(".");

	if (pos!=-1) ext = fn.substr(pos+1).toLowerCase();

	return ext;

}


/********************************************************************
	Functions for creating or manipulating DOM objects

	created: 04/20/2006

********************************************************************/

//set a floatStyle for an object
function setFloat(myvar,floatVal) {

        if (document.all) myvar.style.styleFloat = floatVal;
        else myvar.setAttribute("style","float:" + floatVal);

        return myvar;
}

//set a className value for an object
function setClass(myvar,classVal) {

        if (document.all) myvar.setAttribute("className",classVal);
        else myvar.setAttribute("class",classVal);

        return myvar;
}

//set an onclick event for an object
function setClick(myvar,click) {

        if (document.all) myvar.onclick = new Function(" " + click + " ");
        else myvar.setAttribute("onClick",click);

        return myvar;

}

//create a new form
function createForm(formType,formName,checked) {

	var curform;

	if (checked) formCheck = " CHECKED ";
	else formCheck = "";

	//just don't ask...
	if (document.all) {
		fStr = "<input type=\"" + formType + "\" name=\"" + formName + "\" id=\"" + formName + "\" " + formCheck + ">";
		curform = document.createElement(fStr);
	}
	else {
		var curform = document.createElement("input");
		curform.setAttribute("name",formName);
		curform.setAttribute("type",formType);
		curform.setAttribute("id",formName);
		if (checked) curform.checked = true;
	}

	return curform;

}

function createBtn(formName,val,oc) {

	var btn = createForm("button",formName);
	btn.setAttribute("value",val);

	if (oc) setClick(btn,oc);

	return btn;

}

//create a new form
function createSelect(formName,change) {

	var curform;

	//just don't ask...
	if (document.all) {

		if (change) onChange = "onChange=\"" + change + "\"";
		else onChange = "";

		fStr = "<select name=\"" + formName + "\" id=\"" + formName + "\" " + onChange + ">";
		curform = document.createElement(fStr);
	}
	else {
		var curform = document.createElement("select");
		curform.setAttribute("name",formName);
		curform.setAttribute("id",formName);
		if (change) curform.setAttribute("onChange",change);
	}

	return curform;

}

//set an onclick event for an object
function setChange(myvar,click) {

        if (document.all) myvar.onchange = new Function(" " + click + " ");
        else myvar.setAttribute("onChange",click);

        return myvar;

}

//set an onclick event for an object
function setKeyUp(myvar,click) {

        if (document.all) myvar.onkeyup = new Function(" " + click + " ");
        else myvar.setAttribute("onkeyup",click);

        return myvar;

}


//shorthand for getElementbyId
function ge(element) {
	return document.getElementById(element);
}

//shorthand for creating an element
function ce(elementType) {
	return document.createElement(elementType);
}

function createCleaner() {

	var cleaner = document.createElement("div");
	setClass(cleaner,"cleaner");

	return cleaner;

}

//shorthand for creating a text node
function ctnode(str) {
	return document.createTextNode(str);
}
 
function changeClass(id,section) {
	document.getElementById(id).className = section;
}


//hide an object from view
function hideObject(obj) {

        document.getElementById(obj).style.position="absolute";
        document.getElementById(obj).style.visibility="hidden";
        document.getElementById(obj).style.zIndex="-10";

}

//show an object in the browser
function showObject(obj,zIndex) {

	if (!zIndex) zIndex = 1;

        document.getElementById(obj).style.position="static";
        document.getElementById(obj).style.visibility="visible";
        document.getElementById(obj).style.zIndex=zIndex;

}

//cycle between hide and show
function cycleObject(obj,zIndex) {

        var visib = document.getElementById(obj).style.visibility;

        if (visib=="visible") hideObject(obj);
        else showObject(obj,zIndex);

}

//calculates the left offset of an object
function calculateOffsetLeft(r){
        return Ya(r,"offsetLeft");
}

//calcuates teh top offset of an object
function calculateOffsetTop(r){
        return Ya(r,"offsetTop");
}

//does the legwork on offset calcuation
function Ya(r,attr) {
        var kb=0;
        while(r){
                kb+=r[attr];
                r=r.offsetParent;
        }
        return kb;
}

//returns the value of a radio form
function getRadioValue(obj) {

        if (!obj) return "";

        var len = obj.length;

        if (len == undefined && obj.checked) return obj.value;
        for (i=0;i<len;i++)
                if (obj[i].checked) return obj[i].value;

        return "";

}

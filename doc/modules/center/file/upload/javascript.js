
//switch our form
function switchMode() {

	var newmode = document.getElementById("uploadMode").value;
	hideObject("singleUpload");
	hideObject("multiUpload");
	showObject(newmode);

	document.getElementById("modeStatus").value = newmode;

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
	uf.setAttribute("id","fileUpload[]");
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
                var filearr = curli.getElementsByTagName("input");
                var curfile = filearr[0];

                //we have a match, remove this node
                if (curfile.value==fp) txtdiv.removeChild(curli);

        }
}


/*************************************************************
	generic code for processing ajax requests
*************************************************************/

//the number of simultaenous asynchronous requests we can have
ajaxQueueNum = 20;

//our handlers.  These match the typeid tag in our xml response
ajaxRH = new Array();

//store our requests in here
ajaxReq = new Array();
ajaxReq[0] = 0;

//registers a new handler function for an xml request return
function regHandler(resp,handler) {

	ajaxRH[resp] = handler;

}

//parses xml response and hands it off to the appropriate function if it exists
function parseResponse(req) {

	var respXML = req.responseXML;
	var respTXT = req.responseText;
	var z = 0;

	//determine if there's no xml info available.  This seems redundant, but the last 
	//check makes this work with opera
 	if (!respXML || (respXML && !respXML.hasChildNodes()) || respXML.childNodes.length=="0") {

		//if there's no text either, get out
		if (!respTXT) return false;

		//continue with txt processing
		//return the typeid value 

		//first, find the typeid hidden field
		pos = respTXT.indexOf("name=typeid");
		if (pos==-1) pos = respTXT.indexOf("name='typeid'");
		if (pos==-1) pos = respTXT.indexOf("name=\"typeid\"");
		if (pos==-1) return false;

		//extract the value from the field
		tmp = respTXT.substr(pos);
		valpos = tmp.indexOf("value=") + 6;
		endpos = tmp.indexOf(">");
		diff = endpos - valpos;
		tmp = tmp.substr(valpos,diff);
		elementName = tmp.replace(/[^a-zA-Z 0-9_-]+/g,'');

		//call the handling function
		if (ajaxRH[elementName]) {
			
			func = eval(ajaxRH[elementName]);
			func(respTXT);
			return true;

		}

		return false;

	}

	//carry on with xml processing
	var typeElement = respXML.getElementsByTagName("typeid");
	if (!typeElement[0]) return false;

	//get our function name for handling, and hand this off
	var elementName = typeElement[0].firstChild.nodeValue;

	// this is within a loop over all elements
	if (ajaxRH[elementName]) {

		func = eval(ajaxRH[elementName]);

		//only pass on an element to our handler function
		for (z=0;z<respXML.childNodes.length;z++) {
			if (respXML.childNodes[z].nodeType==1) {
				func(respXML.childNodes[z]);
				break;
			}
		}

		return true;

	//if there is no handler, return the text
	} else return false;

}

//handles our xml requests for getting data
function loadXMLReq(url,reqMode,noCache) {

	var xmlreq = null;
	var parms = null;
	var openIndex = 0;

	//default to GET if reqMode is not set
	if (!reqMode) reqMode = "GET";

	//find an opening in our queue to store the request information
	for (i=0;i<ajaxQueueNum;i++) {

		if (ajaxReq[i] == null) {
			openIndex = i;
			break;
		}

	}

	//our callback function for processing the return from our xml request
	callBackFunc = function xmlHttpChange() {
				
				for (i=0;i<ajaxQueueNum;i++) {

					req = ajaxReq[i];
					if (req==null) continue;

					if (req.readyState == 4) {

						switch (req.status) {
							
							case 200:
								//handle the response and empty this queue entry
								//we empty the queue first because a processing lag
								//seems to cause it to be called again
								ajaxReq[i] = null; 

								if (!parseResponse(req)) {

									//evaluate if there is no xml response, and there is a text response, contine and parse the text response
 									if (		(!req.responseXML || 
													(req.responseXML && !req.responseXML.hasChildNodes()) || req.responseXML.childNodes.length=="0")	&&
													req.responseText.length > 0) {
												
										//check for login message
										var loginCheck = req.responseText.indexOf("<!--EDEV LOGIN FORM-->");
										if (loginCheck >= 0) {

											alert("Your session has expired, you will now be redirected to the login page");
											location.href = "index.php?show_login_form=1";

										} else {

											//parse error check
											var peCheck = req.responseText.indexOf("Parse error:");

											//script warning check
											var wCheck = req.responseText.indexOf("Warning:");

											if ((peCheck >= 0 || peCheck < 10) || (wCheck >=0 || wCheck < 10)) {
												if (confirm("There was an error loading the page.  Do you wish to see the error text?")) {
													alert(req.responseText);
												}
											}
										}
				
									}
								}
								break;

							case 401:
								alert("Error 401 (Unauthorized):  You are not authorized to view this page");
								break;

							case 402:
								alert("Error 402 (Forbidden): You are forbidden to view this page");
								break;

							case 404:
								alert("Error 404 (Not Found): The requested page was not found");
								break;

						}

                			}

				}

			};

	//if it's a post, we need to strip the parameters out of the url
	//get a new request depending on ie or standard
	if (window.XMLHttpRequest) 			ajaxReq[openIndex] = new XMLHttpRequest();
	else if (window.ActiveXObject) 	ajaxReq[openIndex] = new ActiveXObject("Microsoft.XMLHTTP");

	if (ajaxReq[openIndex]) {

			//append a random string to the url to prevent caching in ie if we are loading
			//an xml file directly
			if (url.indexOf(".xml") != '-1') {
				var time = new Date().getTime();
				if (url.indexOf("?") != '-1') url += "&" + time;
				else url += "?" + time;
			}

			//if it's a post method, we need to split our url into parameters and the url destination itself
			if (reqMode=="POST") {
				var pos = url.indexOf("?");
				if (pos!=-1) {
					parms = url.substr(pos + 1);
					url = url.substr(0,pos);
				}
			}				

			//register our callback function
			ajaxReq[openIndex].onreadystatechange = callBackFunc;

			//open the connection
			ajaxReq[openIndex].open(reqMode, url, true);

			//if it's a post, send the proper header
			if (reqMode=="POST") {
				ajaxReq[openIndex].setRequestHeader("Content-type","application/x-www-form-urlencoded");
				ajaxReq[openIndex].setRequestHeader("Content-length",parms.length);
				ajaxReq[openIndex].setRequestHeader("Connection","close");
			}

			//prevent caching if set
			if (noCache) ajaxReq[openIndex].setRequestHeader("If-Modified-Since", "Sat, 1 Jan 2005 00:00:00 GMT");

			//send the parameters
			ajaxReq[openIndex].send(parms);

	}

}

//this function posts data from a form to a url
function postAjaxForm(docForm,url,reqMode) {

	query = form2Query(docForm);
	url += "&" + query;

	//just return the string if desired
	if (reqMode=="return") return url;
	else loadXMLReq(url,reqMode);

}


//this function converts a form's data to a query string to be passed to a server
//as a get or post.  The var docForm should be a reference to a <form>
function form2Query(docForm) {

	var strSubmitContent = '';
	var formElem;
	var strLastElemName = '';
	
	for (i = 0; i < docForm.elements.length; i++) {
		
		formElem = docForm.elements[i];

		switch (formElem.type) {

			// Text fields, hidden form elements
			case 'text':
			case 'hidden':
			case 'password':
			case 'textarea':
			case 'select-one':
				strSubmitContent += formElem.name + '=' + escape(formElem.value) + '&'
				break;
				
			// Radio buttons
			case 'radio':
				if (formElem.checked) {
					strSubmitContent += formElem.name + '=' + escape(formElem.value) + '&'
				}
				break;
				
			// Checkboxes
			case 'checkbox':
				if (formElem.checked) {
					strSubmitContent += formElem.name + '=' + escape(formElem.value) + '&';
				}
				break;
				
		}
	}
	
	// Remove trailing separator
	strSubmitContent = strSubmitContent.substr(0, strSubmitContent.length - 1);
	return strSubmitContent;

}


//checks to see if the element has a text value, or if there are children below it to go through
function hasChildNodes(obj) {

	if (document.all) {

		if (obj.firstChild && obj.firstChild.nodeValue) return false;
		else return true;

	} else {

		if (obj.childNodes.length==1) return false;
		else return true;

	}

}


/**********************************************
	parse our xml into an associative array
	takes the top node as a reference
	ex:
	resp = req.responseXML;
	arr = parseXML(resp.firstChild);
**********************************************/

function parseXML(dataNode) {

	var len = dataNode.childNodes.length;
	var arr = new Array();
	var keyarr = new Array();

	var n=0;
	var i = 0;

	while (dataNode.childNodes[i]) {

		var objNode = dataNode.childNodes[i];

		if (objNode.nodeType==1) {

			var keyname = objNode.nodeName;

			if (objNode.hasChildNodes()) {

				//if the key does not exist in our key array, added it and reset its counter
				if (!keyarr[keyname]) {
					keyarr[keyname] = 0;
					arr[keyname] = new Array();
				}

				n = keyarr[keyname];

				arr[keyname][n] = new Array();

				//store single length nodes here
				if (!hasChildNodes(objNode)) arr[keyname] = objNode.firstChild.nodeValue;
				else {

					var c = 0;
					while (objNode.childNodes[c]) {

						var curNode = objNode.childNodes[c];
						var curName = curNode.nodeName;

						//only continue on nodes that are elements
 						if (curNode.nodeType==1) {

							//there are nested tags here, get them
							if (hasChildNodes(curNode)) {

								if (document.all) arr[keyname][n][curName] = parseXML(curNode);
								else if (curNode.childNodes.length > 1) arr[keyname][n][curName] = parseXML(curNode);
		
							} else arr[keyname][n][curName] = curNode.firstChild.nodeValue;

						}

						c++;
					}

				}

				keyarr[keyname]++;

			}

		}

		i++;

	}

	return arr;

}

function loadXMLSync(fullUrl) {

        // Mozilla and alike load like this
        if (window.XMLHttpRequest) {
                req = new XMLHttpRequest();
                //FIXXXXME if there are network errors the loading will hang, since it is not done asynchronous since
                // we want to work with the script right after having loaded it
                req.open("GET",fullUrl,false); // true= asynch, false=wait until loaded
                req.send(null);
        } else if (window.ActiveXObject) {
                req = new ActiveXObject((navigator.userAgent.toLowerCase().indexOf('msie 5') != -1) ? "Microsoft.XMLHTTP" : "Msxml2.XMLHTTP");
                if (req) {
                        req.open("GET", fullUrl, false);
                        req.send();
                }
        }

        if (req!==false) {
                if (req.status==200) {
                        // eval the code in the global space (man this has cost me time to figure out how to do it grrr)
			if (req.responseXML) {
				//get the root node and return it
  				for (z=0;z<req.responseXML.childNodes.length;z++) {
    					if (req.responseXML.childNodes[z].nodeType==1) { 
      						return req.responseXML.childNodes[z];   
    					}
  				}  
			}
			else return req.responseText;
                } else if (req.status==404) {
                        // you can do error handling here
			alert("Page not found");
                }

        }

}

//this function will load an external javascript file and parse it.  Generally, this
//is done when a page originally loads, but this allows us to load external
//scripts on the fly
function loadScript(fullUrl) {

        // Mozilla and alike load like this
        if (window.XMLHttpRequest) {
                req = new XMLHttpRequest();
                //FIXXXXME if there are network errors the loading will hang, since it is not done asynchronous since
                // we want to work with the script right after having loaded it
                req.open("GET",fullUrl,false); // true= asynch, false=wait until loaded
                req.send(null);
        } else if (window.ActiveXObject) {
                req = new ActiveXObject((navigator.userAgent.toLowerCase().indexOf('msie 5') != -1) ? "Microsoft.XMLHTTP" : "Msxml2.XMLHTTP");
                if (req) {
                        req.open("GET", fullUrl, false);
                        req.send();
                }
        }

        if (req!==false) {
                if (req.status==200) {
                        // eval the code in the global space (man this has cost me time to figure out how to do it grrr)
			return req.responseText;
                } else if (req.status==404) {
                        // you can do error handling here
			alert("Page not found");
                }

        }

}

//this function converts all inputs/selects in a div to aquery string to be passed to a server
//as a get or post.  The var docForm should be a reference to an dom object.  "ignore" is
//an optional array containing the names of fields you may want to ignore
function div2Query(mydiv,ignore) {

	var str = "";
	var ignorestr = ",";

	//get our supported form types
	var sel = mydiv.getElementsByTagName("select");
	var input = mydiv.getElementsByTagName("input");
	var ta = mydiv.getElementsByTagName("textarea");
	var i;


	//convert ignore into a string
	if (ignore) for (i=0;i<ignore.length;i++) ignorestr += ignore[i] + ",";

	//process selects
	for (i=0; i<sel.length;i++) {
		//skip if it's in our ignore array
		if (ignorestr.indexOf("," + sel[i].name + ",")!=-1) continue;

		for (var c=0;c<sel[i].options.length;c++) {
			if (sel[i].options[c].selected==true) {
				str += sel[i].name + "=" + escape(sel[i].options[c].value) + "&";
			}
		}

	}

	//process textarea
	for (i=0;i<ta.length;i++) {
		//skip if it's in our ignore array
		if (ignorestr.indexOf("," + ta[i].name + ",")!=-1) continue;
		str += ta[i].name + "=" + escape(ta[i].value) + "&";
	}

	//process the rest
	for (i=0;i<input.length;i++) {

		//skip if it's in our ignore array
		if (ignorestr.indexOf("," + input[i].name + ",")!=-1) continue;

		//skip buttons for now
		if (input[i].type=="button") continue;
	
		//process radios and checkboxes
		if (input[i].type=="checkbox" || input[i].type=="radio") {
			if (input[i].checked) str += input[i].name + "=" + escape(input[i].value) + "&";
		}
		//everything else
		else {
			str += input[i].name + "=" + escape(input[i].value) + "&";		
		}
	

	}

	// Remove trailing separator
	str = str.substr(0, str.length - 1);
	return str;

}


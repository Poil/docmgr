
//this function extracts all numbers from a string
function returnNumbers(str) {

	var arr = new Array(0,1,2,3,4,5,6,7,8,9);
	var len = str.length;
	var newstr = "";

	for (i=0;i<len;i++) {

		if (arraySearch(str[i],arr)!=-1 && str[i]!=" ") newstr += str[i];

	}

	return newstr;
}

//generates a random string
function randomString() {
	var chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXTZabcdefghiklmnopqrstuvwxyz";
	var string_length = 8;
	var rstr = '';
	for (var i=0; i<string_length; i++) {
		var rnum = Math.floor(Math.random() * chars.length);
		rstr += chars.substring(rnum,rnum+1);
	}
	return rstr;
}

//escape backslashes in a string
function escapeBackslash(str) {

        var arr = str.split("\\");
        var newstr = arr.join("\\\\");

        return newstr;
}


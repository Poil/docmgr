
function submitForm(action) {

        document.pageForm.pageAction.value = action;
        document.pageForm.submit();

}

function insertTags(tagOpen, tagClose, sampleText) {

        var txtarea = document.pageForm.wikiContent;
        // IE
        if(document.selection  && document.all) {
                var theSelection = document.selection.createRange().text;
                if(!theSelection) { theSelection=sampleText;}
                txtarea.focus();
                if(theSelection.charAt(theSelection.length - 1) == " "){// exclude ending space char, if any
                        theSelection = theSelection.substring(0, theSelection.length - 1);
                        document.selection.createRange().text = tagOpen + theSelection + tagClose + " ";
                } else {
                        document.selection.createRange().text = tagOpen + theSelection + tagClose;
                }

        // Mozilla
        } else if(txtarea.selectionStart || txtarea.selectionStart == '0') {
                var startPos = txtarea.selectionStart;
                var endPos = txtarea.selectionEnd;
                var scrollTop=txtarea.scrollTop;
                var myText = (txtarea.value).substring(startPos, endPos);
                if(!myText) { myText=sampleText;}
                if(myText.charAt(myText.length - 1) == " "){ // exclude ending space char, if any
                        subst = tagOpen + myText.substring(0, (myText.length - 1)) + tagClose + " ";
                } else {
                        subst = tagOpen + myText + tagClose;
                }
                txtarea.value = txtarea.value.substring(0, startPos) + subst +
                  txtarea.value.substring(endPos, txtarea.value.length);
                txtarea.focus();

                var cPos=startPos+(tagOpen.length+myText.length+tagClose.length);
                txtarea.selectionStart=cPos;
                txtarea.selectionEnd=cPos;
                txtarea.scrollTop=scrollTop;

        // All others
        } else {
                var copy_alertText=alertText;
                var re1=new RegExp("\\$1","g");
                var re2=new RegExp("\\$2","g");
                copy_alertText=copy_alertText.replace(re1,sampleText);
                copy_alertText=copy_alertText.replace(re2,tagOpen+sampleText+tagClose);
                var text;
                if (sampleText) {
                        text=prompt(copy_alertText);
                } else {
                        text="";
                }
                if(!text) { text=sampleText;}
                text=tagOpen+text+tagClose;
                document.infoform.infobox.value=text;
                // in Safari this causes scrolling
                if(!is_safari) {
                        txtarea.focus();
                }
                noOverwrite=true;
        }
        // reposition cursor if possible
        if (txtarea.createTextRange) txtarea.caretPos = document.selection.createRange().duplicate();

}


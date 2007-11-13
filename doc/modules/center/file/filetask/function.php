<?

function returnApprovalText() {

  $inst = "<br><br><div>
            <div class=\"formHeader\">
            "._INSTRUCTIONS."
            </div>
            "._APPROVE_TEXT."
            <br><br>
            <div class=\"formHeader\">
            "._COMMENTS."
            </div>
            <textarea name=\"fileComment\" id=\"fileComment\" rows=4 cols=35></textarea>
            <br><br>
            <input type=button onClick=\"submitForm('taskComplete');\" value=\""._ACCEPT."\">
            &nbsp;&nbsp;&nbsp;&nbsp;
            <input type=button onClick=\"submitForm('rejectApproval');\" value=\""._REJECT."\">
            </div>
            ";
            
  return $inst;

}

function returnViewText() {

  $inst = "<br><br><div>
            <div class=\"formHeader\">
            "._INSTRUCTIONS."
            </div>
            "._VIEW_TEXT."
            <br><br>
            <div class=\"formHeader\">
            "._COMMENTS."
            </div>
            <textarea name=\"fileComment\" id=\"fileComment\" rows=4 cols=35></textarea>
            <br><br>
            <input type=button onClick=\"submitForm('taskComplete');\" value=\""._VIEW_COMPLETE."\">
            </div>
            ";

  return $inst;

}

function returnEditText() {

  $inst = "<br><br><div>
            <div class=\"formHeader\">
            "._INSTRUCTIONS."
            </div>
            "._EDIT_TEXT."
            <br><br>
            <div class=\"formHeader\">
            "._COMMENTS."
            </div>
            <textarea name=\"fileComment\" id=\"fileComment\" rows=4 cols=35></textarea>
            <br><br>
            <input type=button onClick=\"submitForm('taskComplete');\" value=\""._EDIT_COMPLETE."\">
            </div>
            ";

  return $inst;

}

/*
function returnCommentText() {

  $inst = "<br><br><div>
            <div class=\"formHeader\">
            "._INSTRUCTIONS."
            </div>
            You may view this file by clicking \"View File\" to the left.
            After viewing the file, you may acknowledge you have viewed the
            file by clicking \"View Complete\" belowl
            <br><br>
            <div class=\"formHeader\">
            "._COMMENTS."
            </div>
            <textarea name=\"fileComment\" id=\"fileComment\" rows=4 cols=35></textarea>
            <br><br>
            <input type=button onClick=\"submitForm('taskComplete');\" value=\"View Complete\">
            </div>
            ";

  return $inst;

}
*/
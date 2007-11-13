<?

//use this function to send emails w/ or w/o attachments to recipients
function send_email($to,$from,$subject,$message,$attachArray,$replyTo = null) {

	//trim returnAddr to try and prevent the ATTACK messages
	$from = trim($from);
	$to = trim($to);

	//add From: header
	$headers = "From: ".$from."\r\n";
	
	//add a reply to header if set
	if ($replyTo) $headers .= "Reply-To: ".$replyTo."\r\n";
	else if (defined("EMAIL_REPLYTO")) $headers .= "Reply-To: ".EMAIL_REPLYTO."\r\n";

	//specify MIME version 1.0
	$headers .= "MIME-Version: 1.0\r\n";

	//process for a plain-text message
	if (!is_array($attachArray)) $headers .= createEmailHeaders($message);

	//process for an email with an attachment	
	else {

		//unique boundary
		$boundary = uniqid("----=_NextPart_").".EW_----";

		//tell e-mail client this e-mail contains//alternate versions
		//note that enclosing the boundary in quotes allows MIME email
		//to work with AOL.
		$headers .= "Content-Type: multipart/mixed; boundary=\"".$boundary."\"\r\n";
		$headers .= "Content-Transfer-Encoding: 7bit\r\n\r\n";

		//message to people with clients who do not understand MIME
		$headers .= "This is a multi-part message in MIME format.\r\n";

		//add the text or html part of the message
		$headers .= "--$boundary\r\n";
		$headers .= createEmailHeaders($message);

		//loop through and add all attachments
		foreach ($attachArray AS $attach) {

			$attachPath = $attach["path"];
			$attachName = $attach["name"];
			$attachDesc = $attach["description"];
			$attachType = $attach["type"];

			//get the file as a string and encode
			$h = fopen($attachPath,"rb");
			$contents = fread($h,filesize($attachPath));
			fclose($h);

			$FILE = chunk_split(base64_encode($contents));

			$headers .= "--$boundary\r\n";
		  	$headers .= "Content-Type: application/octet-stream; name=\"".$attachName."\"\r\n";
		   	$headers .= "Content-Transfer-Encoding: base64\r\n";
			$headers .= "Content-Disposition: attachment; filename=\"".$attachName."\"\r\n\r\n";
			$headers .= $FILE;

		}

		$headers .= "\r\n--$boundary--\r\n";
		
	}
	

	$subject = stripslashes($subject);

	file_put_contents("/tmp/mail.txt",$headers);

	//use php_imap if available, otherwise use sendmail
	if (function_exists("imap_8bit")) {

		if (imap_mail("$to","$subject","","$headers","")) return true;
		else return false;

	} else {

		//prepend the destination and subject to the headers
		$to = "To: ".$to."\r\n";
		$subject = "Subject: ".$subject."\r\n";
		
		$headers = $to.$subject.$headers;

		//write our headers to a temp file for passing to sendmail	
		$file = TMP_DIR."/".rand().".eml";
	
		$fp = fopen($file,w);
		fwrite($fp,$headers);
		fclose($fp);
		
		//pass the file to sendmail
		`cat "$file" | sendmail -t -f "$from"`;

		//remove the temp file and exit
		unlink($file);
		return true;

	}
}

function createEmailHeaders($msg) {

	//encode the message for html
	//if (function_exists("imap_8bit")) $HTML = imap_8bit("$msg");
	$HTML = $msg;

	
	//get rid of all formatting in the message
	$msg = eregi_replace("<br>","\r\n",$msg);
	$TEXT = strip_tags($msg);

	$headers = null;
	
	if ($HTML) {
	
		if (!defined("VIEW_CHARSET")) define("VIEW_CHARSET","ISO-8859-1");
	
		//tell e-mail client this e-mail contains//alternate versions
		//note that enclosing the boundary in quotes allows MIME email
		//to work with AOL.
		$headers .= "Content-Type: text/html; charset=".VIEW_CHARSET."; format=flowed\r\n";
		$headers .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
		$headers .= $HTML;
		$headers .= "\r\n\r\n";
	
	} else {
	
		//tell e-mail client this e-mail contains//alternate versions
		//note that enclosing the boundary in quotes allows MIME email
		//to work with AOL.
		$headers .= "Content-Type: text/plain; charset=".VIEW_CHARSET."; format=flowed\r\n";
		$headers .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
		$headers .= $TEXT;
		$headers .= "\r\n\r\n";

	}

	return $headers;

}


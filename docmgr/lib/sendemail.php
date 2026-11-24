<?php
//$this->MIMEHeader, $this->MIMEBody;

require_once(SITE_PATH."/lib/phpmailer/src/Exception.php");
require_once(SITE_PATH."/lib/phpmailer/src/PHPMailer.php");
require_once(SITE_PATH."/lib/phpmailer/src/SMTP.php");

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

/**********************************************************************
	FILENAME:	email.php
	PURPOSE:	contains generic functions used for sending an email
	MODIFIED:	06/22/2007 -> descriptions added to functions
**********************************************************************/

class SENDEMAIL
{

	public $to;
	public $from;
	public $subject;
	public $message;
	public $attach;
	public $replyTo;
	public $headers;
	public $errorMessage;
	public $massEmail;
	public $mailer;
				
	function __construct($to=null,$from=null,$subject=null,$message=null)
	{

		$this->mailer = new PHPMailer(true);
		$this->mailer->IsSMTP(); // telling the class to use SMTP
		$this->mailer->CharSet = PHPMailer::CHARSET_UTF8;

		if ($to) 			$this->setTo($to);
		if ($from) 		$this->setFrom($from);
		if ($subject) $this->setSubject($subject);
		if ($message) $this->setMessage($message);

	}

	function error()
	{
		return $this->errorMessage;
	}

	function getError()
	{
		return $this->errorMessage;
	}
	
	function throwError($err)
	{
		$this->errorMessage = $err;
	}

	function setup()
	{
		$this->mailer->Host = SMTP_HOST;
		$this->mailer->Port = SMTP_PORT;
		//$this->mailer->SMTPDebug = 2;  		// enables SMTP debug information (for testing)

		if (defined("SMTP_AUTH"))
		{
			$this->mailer->SMTPAuth = true;		// enable SMTP authentication	
			$this->mailer->Username = SMTP_AUTH_LOGIN;
			$this->mailer->Password = SMTP_AUTH_PASSWORD;
			
		}
		
	}
	
	function setTo($str)
	{

		if ($str==null) return false;
		
		$str = str_replace(";",",",$str);

		try
		{

			$recips = arrayReduce(explode(",",$str));
			
			foreach ($recips AS $recip) 
			{
				$recip = trim($recip);
				if (!$recip) continue;
				
				$arr = $this->setupEmail($recip);
				$this->mailer->AddAddress($arr[0],$arr[1]);
			}			

		} 
		catch (PHPMailerException $e) 
		{
			$this->throwError($e->errorMessage()); //Pretty error messages from PHPMailer
		} 
		catch (Exception $e) 
		{
			$this->throwError($e->getMessage()); //Boring error messages from anything else!
		}
	
	}

	function setCC($str)
	{

		if ($str==null) return false;

		$str = str_replace(";",",",$str);

		try
		{

			$recips = arrayReduce(explode(",",$str));
			
			foreach ($recips AS $recip) 
			{
				$recip = trim($recip);
				if (!$recip) continue;

				$arr = $this->setupEmail($recip);
				$this->mailer->AddCC($arr[0],$arr[1]);
			}			

		} 
		catch (PHPMailerException $e) 
		{
			$this->throwError($e->errorMessage()); //Pretty error messages from PHPMailer
		} 
		catch (Exception $e) 
		{
			$this->throwError($e->getMessage()); //Boring error messages from anything else!
		}
	
	}

	function setBCC($str)
	{

		if ($str==null) return false;

		$str = str_replace(";",",",$str);

		try
		{

			$recips = arrayReduce(explode(",",$str));
			
			foreach ($recips AS $recip) 
			{
				$recip = trim($recip);
				if (!$recip) continue;

				$arr = $this->setupEmail($recip);
				$this->mailer->AddBCC($arr[0],$arr[1]);
			}			

		} 
		catch (PHPMailerException $e) 
		{
			$this->throwError($e->errorMessage()); //Pretty error messages from PHPMailer
		} 
		catch (Exception $e) 
		{
			$this->throwError($e->getMessage()); //Boring error messages from anything else!
		}
	
	}

	function setFrom($str)
	{

		if ($str==null) return false;

		try
		{
			//send the email							
			$arr = $this->setupEmail($str);
			$this->mailer->SetFrom($arr[0],$arr[1]);
		} 
		catch (PHPMailerException $e) 
		{
			$this->throwError($e->errorMessage()); //Pretty error messages from PHPMailer
		} 
		catch (Exception $e) 
		{
			$this->throwError($e->getMessage()); //Boring error messages from anything else!
		}
	
	}

	function setSubject($str)
	{
		$this->mailer->Subject = $str;
	}

	function setMessage($str)
	{
		$this->mailer->MsgHTML($str);
	}

	private function setupEmail($str)
	{
		$str = trim($str);
		$str = str_replace("<","",$str);
		$str = str_Replace(">","",$str);
		$arr = explode(" ",$str);
		
		$email = array_pop($arr);
		$name = "";
		
		if (count($arr) > 0) $name = implode(" ",$arr);
		
		return array($email,$name);
		
	}

	function setReplyTo($str)
	{
		$arr = $this->setupEmail($str);
		$this->mailer->AddReplyTo($arr[0],$arr[1]);
	}

	function setAttach($arr)
	{
		if (!$arr) return false;
		
		if (!is_array($arr)) $arr = array($arr);
		
		foreach ($arr AS $attach)
		{
			if (!empty($attach["cid"]))
			{
				$type = return_file_mime(basename($attach["path"]));
				$this->mailer->AddEmbeddedImage($attach["path"], $attach["cid"]);
				//, null, null, $type);
			}
			else
			{
				$this->mailer->AddAttachment($attach["path"]);
			}
		}
	}

	function setMassEmail($str)
	{
		$this->massEmail = $str;
	}

	function getContent()
	{
		//hasn't been sent yet
		if (!$this->mailer->getSentMIMEMessage()) $this->mailer->preSend();

		return $this->mailer->getSentMIMEMessage();	

	}

	function send()
	{

		try
		{
			//send the email							
			$this->setup();
			$this->mailer->Send();
		} 
		catch (PHPMailerException $e) 
		{
			$this->throwError($e->errorMessage()); //Pretty error messages from PHPMailer
		} 
		catch (Exception $e) 
		{
			$this->throwError($e->getMessage()); //Boring error messages from anything else!
		}
	
	}
	
}

<?php

require_once(dirname(__FILE__) . '/../ThirdParty/PHPMailer/PHPMailerAutoload.php');

class EmailSmtp
{
   private $mail;
   
   // $to can be an array
	function __construct($host, $port, $username, $password, $options = [])
	{
      $this->mail = new PHPMailer;
		
      // Use SMTP
      $this->mail->isSMTP();

      $this->mail->SMTPOptions = $options;

      // SMTP debugging
      // 0 = off (for production use)
      // 1 = client messages
      // 2 = client and server messages
      $this->mail->SMTPDebug = 0;
      // HTML-friendly debug output
      $this->mail->Debugoutput = 'html';

      $this->mail->Host = $host;
      $this->mail->Port = $port;
      if ($port == 465)
         $this->mail->SMTPSecure = "ssl";
      else if ($port == 587)
         $this->mail->SMTPSecure = "tls";

      $this->mail->SMTPAuth = true;
      $this->mail->Username = $username;
      $this->mail->Password = $password;
	}
	
	function __destruct()
	{
	}
   
   function SetFrom($email, $name = NULL)
   {
      $this->mail->setFrom($email, $name);
   }
   
   function AddReplyTo($email, $name = NULL)
   {
      $this->mail->addReplyTo($email, $name);
   }
   
   function AddTo($email, $name = NULL)
   {
      $this->mail->addAddress($email, $name);
   }

   function AddCC($email, $name = NULL)
   {
      $this->mail->addCC($email, $name);
   }
   
   function AddBcc($email, $name = NULL)
   {
      $this->mail->addBCC($email, $name);
   }
   
   function AddHeaders($headers)
   {
      foreach ($headers as $name => $value)
      {
         $this->mail->addCustomHeader($name, $value);
      }
   }
   
   function SetSubject($subject)
   {
      $this->mail->Subject = $subject;
   }
   
   function SetBody($textBody, $htmlBody = NULL)
   {
      if (empty($htmlBody))
      {
         // make sure we have an HTML body
         $htmlBody = Html::Encode($textBody);
      }
      if (strpos(strtolower($htmlBody), '<html') === false)
      {
         // make sure the HTML body is wrapped (sometimes just the content is provided)
         $htmlBody = 
"<!doctype html>
<html>
<head>
<meta charset=\"utf-8\">
<meta name=\"viewport\" content=\"width=device-width, initial-scale=1\">
</head>
<body>
$htmlBody
</body>
</html>";
      }
      if (empty($textBody))
      {
         $textBody = Html::HtmlToText($htmlBody);
      }
      
      $this->mail->Body = $htmlBody;
      $this->mail->AltBody = $textBody;
   }
   
   function AddAttachment($string, $filename, $encoding = 'base64', $type = '', $disposition = 'attachment')
   {
      $this->mail->addStringAttachment($string, $filename, $encoding, $type, $disposition);
   }
   
   function SetCalendar($string, $method='REQUEST')
   {
      $this->mail->Ical = $string;
      $this->mail->IcalRequest = $method;
   }
   
	function Send()
	{
		$message = '';
		
      if (!$this->mail->send())
      {
         $message = $this->mail->ErrorInfo;
         WriteCallStack("Sending email failed: " . $message);
      }
		
		return $message;
	}
};

if (0)
{
   $msg = Email::Send('support@nmboost.com', 'saltyfoam@gmail.com', 'Test 4', 'My test!');
   echo("Test: $msg");
}

?>
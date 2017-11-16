<?php

// NOTE: This file requires Error.php!

class EmailPop3
{
   private $connection;
   
	function __construct($host, $port, $user, $pass)
	{
      $folder = "INBOX";
      $ssl = $port == 995;
      
		$ssl = ($ssl == false) ? "/novalidate-cert" : "/ssl/novalidate-cert";
		$this->connection = NULL;
		try
		{
			$temp = imap_open("{"."$host:$port/pop3$ssl"."}$folder",$user,$pass,OP_SILENT);
			if ($temp !== false)
				$this->connection = $temp;
		}
		catch (Exception $e)
		{
			WriteException($e, 'Caught exception logging into POP3 server');
		}
	}
	
	function __destruct()
	{
      if ($this->connection)
   		imap_close($this->connection, CL_EXPUNGE);
	}
   
	function IsOpen()
	{
		return $this->connection != NULL;
	}
	
	function Status()
	{
		$check = imap_mailboxmsginfo($this->connection);
		return (array)$check;
	}
	
	function GetList($range = NULL)
	{
		$result = array();
		if (empty($range))
		{
			$MC = imap_check($this->connection);
			$range = "1:".$MC->Nmsgs;
			if ($MC->Nmsgs == 0)
				return $result;
		}
		$response = imap_fetch_overview($this->connection, $range);
		foreach ($response as $msg)
			$result[$msg->msgno] = (array)$msg;
		return $result;
	}

	function Delete($mid)
	{
		return imap_delete($this->connection, $mid);
	}
	
	function GetHeader($mid)
	{
		return imap_fetchheader($this->connection, $mid, FT_PREFETCHTEXT);
	}
	
	function GetMessage($mid)
	{
		$msg = imap_fetchstructure($this->connection, $mid);
		$mail = $this->mail_get_parts($mid, $msg, 0);
		
		$content = $mail[0]['data'];	// headers
		
		if (count($mail) == 1)
		{
			// single body

			$mail = array_merge($mail, $this->mail_get_parts($mid, $msg, 1));
			
			$content .= $mail[1]['data'];
		}
		else
		{
			// multipart
			
			$boundary = isset($mail[0]['boundary']) ? $mail[0]['boundary'] : '';
			unset($mail[0]);
			foreach ($mail as $item)
			{
				$mimetype = $item['mimetype'];
				$content .= "--$boundary\r\nContent-type: $mimetype\r\n\r\n";
				$content .= $item['data'] . "\r\n\r\n";
			}
			$content .= "--$boundary--\r\n";
		}
		
		return $content;
	}

	static function mail_parse_headers($headers)
	{
		$headers=preg_replace('/\r\n\s+/m', '',$headers);
		preg_match_all('/([^: ]+): (.+?(?:\r\n\s(?:.+?))*)?\r\n/m', $headers, $matches);
		foreach ($matches[1] as $key =>$value)
			$result[$value]=$matches[2][$key];
		return $result;
	}

	function mail_mime_to_array($mid, $parse_headers=false)
	{
		$mail = imap_fetchstructure($this->connection, $mid);
		$mail = $this->mail_get_parts($mid, $mail, 0);
		if ($parse_headers)
			$mail[0]["parsed"]=EmailPop3::mail_parse_headers($mail[0]["data"]);
		return $mail;
	}

   function mail_get_parts($mid, $part, $prefix)
	{    
		$attachments=array();
		$attachments[$prefix]=$this->mail_decode_part($mid, $part, $prefix);
		if (isset($part->parts)) // multipart
		{
			$prefix = (strcmp($prefix, "0") == 0)?"":"$prefix.";
			foreach ($part->parts as $number=>$subpart) 
				$attachments=array_merge($attachments, $this->mail_get_parts($mid, $subpart, $prefix.($number+1)));
		}
		return $attachments;
	}

	function mail_decode_part($mid, $part, $prefix)
	{
		$types = array(
			0 => 'text',
			1 => 'multipart',
			2 => 'message',
			3 => 'application',
			4 => 'audio',
			5 => 'image',
			6 => 'video',
			7 => 'other'
		);
		
		$attachment = array();
		
		if($part->ifdparameters)
		{
			foreach($part->dparameters as $object)
			{
				$attachment[strtolower($object->attribute)]=$object->value;
				if(strtolower($object->attribute) == 'filename')
				{
					$attachment['is_attachment'] = true;
					$attachment['filename'] = $object->value;
				}
			}
		}

		if($part->ifparameters)
		{
			foreach($part->parameters as $object)
			{
				$attachment[strtolower($object->attribute)]=$object->value;
				if(strtolower($object->attribute) == 'name')
				{
					$attachment['is_attachment'] = true;
					$attachment['name'] = $object->value;
				}
			}
		}

		$attachment['data'] = imap_fetchbody($this->connection, $mid, $prefix);
		if($part->encoding == 3) // 3 = BASE64
		{
			$attachment['data'] = Utilities::DecodeBase64($attachment['data']);
		}
		elseif($part->encoding == 4) // 4 = QUOTED-PRINTABLE
		{
			$attachment['data'] = Utilities::DecodeQuotedPrintable($attachment['data']);
		}

		$attachment['mimetype'] = $types[$part->type] . '/' . strtolower($part->subtype);
		if (isset($attachment['charset']))
			$attachment['mimetype'] .= '; charset=' . $attachment['charset'];
		if (isset($attachment['filename']))
			$attachment['mimetype'] .= '; filename=' . $attachment['filename'];

		return $attachment;
	}
};

if (0)
{
}

?>
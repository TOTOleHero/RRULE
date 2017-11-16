<?php

// ========================================================================
//        Copyright (c) 2012 Dominique Lacerte, All Rights Reserved.
// 
// Redistribution and use in source and binary forms are prohibited without 
//   prior written consent from Dominique Lacerte (internet@lacerte.org).
// ========================================================================

require_once(dirname(__FILE__) . '/File.php');
require_once(dirname(__FILE__) . '/Log.php');
require_once(dirname(__FILE__) . '/Utilities.php');

class Mime
{
    /**
	 * SPL-compatible autoloader.
	 *
     * @param string $className Name of the class to load.
     *
     * @return boolean
    public static function autoload($className)
    {
        if ($className != 'Mime')
		{
            return false;
		}
        return include str_replace('_', '/', $className) . '.php';
    }
     */

	function __construct()
	{
		$this->Headers = array();
		$this->HeaderCaseMappings = array();	// maps a lowercase header name to its mixed case equivalent in Headers
		$this->Segments = array();
		$this->originalMime = "";
	}
	
	function __destruct()
	{
	}

	function ContainsHeader($name)
	{
		// convert to the case of the actual header
		$_name = strtolower($name);
		if (isset($this->HeaderCaseMappings[$_name]))
		{
			$_name = $this->HeaderCaseMappings[$_name];
		}
		else
		{
			$_name = $name;
		}
		
		if (isset($this->Headers[$_name]) && 
			strlen(Utilities::RemoveSurroundingSpaces($this->Headers[$_name])) > 0)
		{
			return 1;
		}
		
		return 0;
	}

	function SetHeader($name, $value)
	{
		// convert to the case of the actual header
		$_name = strtolower($name);
		if (isset($this->HeaderCaseMappings[$_name]))
		{
			$_name = $this->HeaderCaseMappings[$_name];
		}
		else
		{
			// not found, use passed-in name and add it to the case mappings
			$this->HeaderCaseMappings[$_name] = $name;
			$_name = $name;
		}

		// convert a reference to an array of values into a string	
		if (is_array($value))
		{
			$value = join(",", $value);
		}
		
      if (is_null($value))
   		unset($this->Headers[$_name]);   // DRL FIXIT? Should also remove header case mapping?
      else
   		$this->Headers[$_name] = $value;
	}

	function SetToHeader($name, $item, $value)
	// item can be NULL in order to set the "default" item
	{
		$this->RemoveFromHeader($name, $item);
		
		// convert to the case of the actual header
		$_name = strtolower($name);
		if (isset($this->HeaderCaseMappings[$_name]))
		{
			$_name = $this->HeaderCaseMappings[$_name];
		}
		else
		{
			// not found, use passed-in name and add it to the case mappings
			$this->HeaderCaseMappings[strtolower($name)] = $name;
			$_name = $name;
		}
		
		$headers = &$this->Headers;
		
		$temp = "";
		if (isset($headers[$_name]))
		{
			$temp = $headers[$_name];
			if (is_array($temp))
			{
				WriteDie("Setting a header that has more than one value not supported for header '$name'!");
			}
		}

		if (!empty($item))
		{
			$temp .= "; $item=\"$value\"";		// DRL FIXIT? Not sure when to not add quotes around value?
		}
		else
		{
			$temp = $value . $temp;					// temp should already contain "; xyz"
		}

		$headers[$_name] = $temp;
	}

	function RemoveFromHeader($name, $item)
	// item can be NULL in order to remove the "default" item
	{
		// convert to the case of the actual header
		$_name = strtolower($name);
		if (isset($this->HeaderCaseMappings[$_name]))
		{
			$_name = $this->HeaderCaseMappings[$_name];
		}
		else
		{
			$_name = $name;	// not found, use passed-in name
		}
		
		$headers = &$this->Headers;
		
		$result = 0;
		if (isset($headers[$_name]))
		{
			$orig = $headers[$_name];
			if (is_array($orig))
			{
				WriteDie("Removing from a header that has more than one value not supported for header '$name'!");
			}
			
			// DRL FIXIT? I don't think this code takes into proper account separators appearing inside double quotes...
			
			$parts = explode(";", $orig);
			if (empty($item))
			{
				if (strpos($parts[0], "=") === false)
				{
					$parts[0] = "";
					$result++;
				}
			}
			else
			{
				$item = strtolower($item);
				
				for ($i = 0; $i < count($parts); $i++)
				{
					// DRL FIXIT! Perl version specified no limit so trailing empty fields weren't dropped.
					list($key) = explode("=", $parts[$i]);
					if (strcmp(strtolower(Utilities::RemoveSurroundingSpaces($key)), $item) == 0)
					{
						array_splice($parts, $i, 1);
						$result++;
						$i--;
					}
				}
			}
			if ($result > 0)
			{
				$headers[$_name] = join("; ", $parts);
			}
		}
		
		return $result;
	}

	function GetFromHeader($name, $item = NULL)
	// item can be NULL in order to get the "default" item
	{
		// convert to the case of the actual header
		$_name = strtolower($name);
		if (isset($this->HeaderCaseMappings[$_name]))
		{
			$_name = $this->HeaderCaseMappings[$_name];
		}
		else
		{
			$_name = $name;	// not found, use passed-in name
		}
		
		$headers = &$this->Headers;
		
		if (isset($headers[$_name]))
		{
			$orig = $headers[$_name];
			$values = NULL;
			if (is_array($orig))
			{
				$values = $orig;
			}
			else
			{
				$values = array($orig);
			}
			
			foreach ($values as $value)
			{
				
				// DRL FIXIT! This code doesn't take into account separators appearing inside double quotes...
				
				$result = 0;
				$parts = explode(";", $value);
				if (empty($item))
				{
					if (strpos($parts[0], "=") === false)
					{
						return $parts[0];
					}
				}
				else
				{
					$item = strtolower($item);
				
					for ($i = 0; $i < count($parts); $i++)
					{
						if (strpos($parts[$i], "=") !== false)
						{
							// DRL FIXIT! Perl version specified no limit so trailing empty fields weren't dropped.
							list($key, $value) = explode("=", $parts[$i]);
							if (strcmp(strtolower(Utilities::RemoveSurroundingSpaces($key)), $item) == 0)
							{
								return Mime::DecodeHeader(Utilities::RemoveQuotes($value), $this->originalMime);
							}
						}
					}
				}
			}
		}
		
		return NULL;
	}

	function RemoveHeader($name)
	{
		$headerCaseMappings = &$this->HeaderCaseMappings;
		
		$lc_name = strtolower($name);
		
		// convert to the case of the actual header
		if (isset($headerCaseMappings[$lc_name]))
		{
			$_name = $headerCaseMappings[$lc_name];
		}
		else
		{
			$_name = $name;	// not found, use passed-in name
		}
		
		$headers = &$this->Headers;
		
		if (isset($headers[$_name]))
		{
			unset($headers[$_name]);
			unset($headerCaseMappings[$lc_name]);
			return 1;
		}
		
		return 0;
	}

	function RemoveMatchingHeaders($pattern)
	{
		$count = 0;
		
		$headers = &$this->Headers;
		foreach ($headers as $name => $value)
		{
			if (preg_match("/$pattern/i", $name))
			{
				$count += $this->RemoveHeader($name);
			}
		}
		
		return $count;
	}

	function GetHeader($name, $defaultValue = NULL)
	{
		// convert to the case of the actual header
		$_name = strtolower($name);
		if (isset($this->HeaderCaseMappings[$_name]))
		{
			$_name = $this->HeaderCaseMappings[$_name];
		}
		else
		{
			return $defaultValue;
		}
		
      if (!isset($this->Headers[$_name]))
         return NULL;
         
		// this could return an array if more than one value was set
		$result = $this->Headers[$_name];
		if (is_array($result))
		{
			foreach ($result as &$item)
				$item = Mime::DecodeHeader($item, $this->originalMime);
		}
		else
			$result = Mime::DecodeHeader($result, $this->originalMime);
			
		return $result;
	}

	function Headers()
	{
		return $this->Headers;
	}

	function AddSegment($contentType, $filename, $content = NULL, $inline = NULL, $encode = NULL)
	{
		$segment = $this->_CreateSegment($contentType, $filename, $content, $inline, $encode);
		if (empty($segment)) { return false; }
		array_push($this->Segments, $segment);
		return true;
	}

	function SetSegment($index, $contentType, $filename, $content = NULL, $inline = NULL, $encode = NULL)
	{
		if ($index < 0 || $index >= count($this->Segments)) { return 0; }
		$segment = $this->_CreateSegment($contentType, $filename, $content, $inline, $encode);
		if (empty($segment)) { return false; }
		$this->Segments[$index] = $segment;
		return true;
	}

	function AddMimeSegment($mime)
	{
		if ($mime == NULL) { return false; }
		$temp = $mime->ToString();
		assert($temp !== NULL);
		array_push($this->Segments, $temp);
		return true;
	}

	function SetMimeSegment($index, $mime)
	{
		if ($mime == NULL) { return false; }
		$temp = $mime->ToString();
		assert($temp !== NULL);
		$this->Segments[$index] = $temp;
		return true;
	}

	function _CreateSegment($contentType, $filename, $content, $inline, $encode)
	{
		if (is_null($encode))
		{
			$encode = true;
		}
		if (is_null($contentType))
		{
			$contentType = MimeTypes::GetMimeTypeFromExtension(File::GetExtension($filename));
			if (empty($contentType))
			{
				WriteError("Unrecognized file type from extension: $filename");
				$contentType = "text/plain";
			}
		}
		if (is_null($content))
		{
			$content = "";
			
			// the content wasn't passed, load it using the filename given
			$file = File::OpenLocked($filename, "rb");
			if (!empty($file))
			{
				$buf = NULL;
				while($file->Read($buf, 1024))
				{ $content .= $buf; }
				$file->Close();
			}
			else
			{
				WriteError("Can't open content $filename");
				return NULL;
			}
		}

	//	// DRL I added this because if no filename is given some type (like text/plain) won't show in browser.
	//	if (empty($filename))
	//	{
	//		$filename = "Body." . MimeTypes::GetExtensionForMimeType($contentType);
	//	}

		$data = "Content-Type: $contentType";
		if (!empty($filename))
		{
			$data .= "; name=\"$filename\"";
		}
		$data .= "\n";
      if (is_null($inline) || strpos($contentType, 'multipart/') === false)
      {
         // nothing
      }
		else
      {
         if ($inline)
   		{
   			$data .= "Content-Disposition: inline";
   		}
   		else
   		{
   			$data .= "Content-Disposition: attachment";
   		}
   		if (!empty($filename))
   		{
   			$data .= "; filename=\"$filename\"";
   		}
   		$data .= "\n";
      }

		if ($encode && strpos($contentType, 'multipart/') === false)
		{
			if (strpos(strtolower($contentType), "text/") == 0)
			{
				// DRL FIXIT! Should probably use quoted-printable!
				$data .= "Content-Transfer-Encoding: 7bit\n";
			}
			else
			{
				$content = Utilities::EncodeBase64($content);
				$data .= "Content-Transfer-Encoding: base64\n";
			}
		}
		
      if (strpos($contentType, 'multipart/') === false)
   		$data .= "Content-Length: " . strlen($content) . "\n";
		
		$data .= "\n" . $content;
		
		return $data;
	}

	function RemoveSegment($index)
	{
		if (array_splice($this->Segments, $index, 1))
		{
			return true;
		}

		return false;
	}

   // returns MIME object array
	function Segments()
	{
      $result = array();
      for ($i = 0; $i < count($this->Segments); $i++)
      {
         $result[] = $this->GetSegment($i);
      }
		return $result;
	}

	function GetSegmentCount()
	{
		return count($this->Segments);
	}
   
   // returns MIME object or NULL
	function GetSegment($index)
	{
      assert($index < count($this->Segments));
      $data = $this->Segments[$index];

      $temp = NULL;
      $i = Mime::FindHeaderEnd($data, $temp);
      
      // we need to copy the Content-Xyz headers to the segment if they are not there
      $headers = '';
      if (strpos(strtolower(substr($data, 0, $i)), 'content-type') === false)
      {
         foreach ($this->Headers as $name => $value)
         {
            if (strpos(strtolower($name), 'content-') === 0)
            {
               // we can't copy the multipart type to the child, and since the child
               // doesn't have a type it can't be valid
               if (strtolower($name) == 'content-type' && strpos($value, 'multipart/') === 0)
                  return NULL;

               $headers .= $name . ': ' . $value . "\n";
            }
         }
      }
      
		$temp = Mime::FromString($headers . $data, false);
      assert($temp !== NULL);
      return $temp;
	}

	function GetSegmentContent($index, $decoded=true)
	{
		$data = $this->Segments[$index];

      $encoding = NULL;
      if ($decoded)
      {
         $encoding = $this->GetFromHeader('Content-Transfer-Encoding');
         if (empty($encoding) && strpos($data, 'Content-Transfer-Encoding') !== false)
         {
            $mime = Mime::FromString($data);
            $encoding = $mime->GetFromHeader('Content-Transfer-Encoding');
         }
      }
		
		$skiplen = NULL;
      $i = Mime::FindHeaderEnd($data, $skiplen);
		
		return Mime::DecodeData(substr($data, $i+$skiplen), $encoding);
	}

   // returns a Mime object
	function GetSegmentWithType($contentType)
	{
		$contentType = strtolower($contentType);

		$type = strtolower($this->GetFromHeader('Content-Type'));
		if (strcmp($type, $contentType) == 0)
      {
         return $this;
      }
		
		if (strpos($type, 'multipart/') === 0 || 
         empty($type))   // no type in the headers if it was moved to the segment
		{
			foreach ($this->Segments() as $content)
			{
// DRL FIXIT!			   assert($content !== NULL);
				$result = $content ? $content->GetSegmentWithType($contentType) : NULL;
				if (!is_null($result))
					return $result;
			}
		}
		
		return NULL;
	}

	function ToBinary()
	{
		return $this->ToString();
	}

	function ToString($stripBCC = NULL)
	{
		if (is_null($stripBCC))
		{
			$stripBCC = 0;
		}
		
		$result = "";

		if (!$this->ContainsHeader("MIME-Version"))
		{
			$result .= "MIME-Version: 1.0\n";
		}

		// if we have multiple parts we have to use a "multipart" content type
		$contentType = $this->GetHeader("Content-Type");
		$multipart = count($this->Segments) > 1 || strpos(strtolower($contentType), "multipart/") !== false;
		$boundary = NULL;
		if ($multipart)
		{
			if ((empty($contentType) || strpos(strtolower($contentType), "multipart/") === false))
			{
				$contentType = "multipart/mixed";
				$this->SetHeader("Content-Type", $contentType);
			}
			$boundary = Mime::_GetBoundary($contentType);
			if (strlen($boundary) == 0)
			{
            $num = Utilities::IntRand(10, 1000000);
				$this->SetToHeader("Content-Type", "boundary", "----=_Separation_" . $num . "_");
				$contentType = $this->GetHeader("Content-Type");
				$boundary = Mime::_GetBoundary($contentType);
			}
		}

		$headers = &$this->Headers;
		foreach ($headers as $name => $value)
		{
			if (!$stripBCC || strcmp(strtolower($name), "bcc") != 0)
			{
	// DRL I put the content type back in because for plain/text the content won't have the type
	//			// if we are not multipart we use the content type provided by the segment
	//			if ($multipart || strcmp(strtolower($name), "content-type") != 0)
				{
					if (is_array($value))
					{
						foreach ($value as $part)
						{
							$result .= "$name: $part\n";
						}
					}
					else
					{
						$result .= "$name: $value\n";
					}
				}
			}
		}

		if ($multipart)
		{
			$result .= "\nThis is a multi-part message in MIME format.\n";
		}

		foreach ($this->Segments as $attachment)
		{
			if ($multipart)
			{
				$result .= "\n--$boundary\n";
			}
			$result .= $attachment;
		}

		if ($multipart)
		{
			$result .= "\n--$boundary--\n";
		}
		
		return $result;
	}

	static function FromBinary($binary, $obj = NULL)
	{
		return Mime::FromString($binary, NULL, $obj);
	}

	static function FromString($string, $alwaysSeperateBody = NULL, $obj = NULL)
	{
		// this parameter is used to indicate whether the data part of the MIME message
		// should be returned as a seperate item even if it is the only one (default) - in 
      // other words should the segment contain the Content-Xyz headers or should the 
      // returned MIME object contain them?
		if (is_null($alwaysSeperateBody))
		{
			$alwaysSeperateBody = true;
		}

      $separator = "\r\n";
      $i = strpos($string, "\n");
		if ($i !== false && ($i == 0 || $string[$i-1] != "\r"))
      {
         $separator = "\n";
      }

      $i = strpos($string, $separator . $separator);

 		$lines = explode($separator, substr($string, 0, $i));
 		$buffer = substr($string, $i + strlen($separator));   // leave one separator prefixing the buffer
 		
		$headers = array();
		$headerCaseMappings = array();
		$header = '';
		
		$i = 0;
		while ($i < count($lines))
		{
			$line = $lines[$i];
			$i++;
			
			if (strlen($line) == 0)
			   continue;
			
			$firstChar = $line[0];
			if (strcmp($firstChar, " ") == 0 || strcmp($firstChar, "\t") == 0)
			{
				// this is a folded header line
				$header .= " " . Utilities::RemoveSurroundingSpaces($line);
			}
			else
			{
				if (!empty($header))
				{
					Mime::_ProcessHeader($header, $headers, $headerCaseMappings);
				}
				$header = $line;
			}
		}
		// process the last header if there was one
		if (!empty($header))
		{
			Mime::_ProcessHeader($header, $headers, $headerCaseMappings);
		}

		if (count($headers) == 0)
		   return NULL;

		$result = $obj ? $obj : new Mime();
		$result->Headers = $headers;
		$result->HeaderCaseMappings = $headerCaseMappings;
		$result->originalMime = $string;

		$contentType = $result->GetHeader("Content-Type", "text/plain");
      
      $boundary = NULL;
		if (strpos(strtolower($contentType), "multipart/") !== false)
		{
			// has sub-parts
			
			$boundary = "--" . Mime::_GetBoundary($contentType);

			if ($boundary == "--")
			{
			   $boundary = NULL; // if we don't find a boundary we'll act as if this message has no sub-parts
			   
				// no boundary specified, look for it

            $i = strpos($buffer, "$separator--");
            if ($i !== false)
            {
               $i += strlen($separator);
               $j = strpos($buffer, $separator, $i);
               if ($j !== false)
                  $boundary = substr($buffer, $i, $j - $i);
            }
			}
			
			if ($boundary == NULL)
         {
            WriteError("Multipart content-type header is missing boundary: " . $contentType);
//            WriteError("Original message: " . $string);
         }
         else
         {
            // skip to the first boundary, the stuff before is fluff
            $bndry = "$separator$boundary";
            $i = strpos($buffer, $bndry);
            while (true)
            {
               if ($i === false || $i + strlen($bndry) == strlen($buffer))
               {
                  WriteError("Multipart MIME is missing terminating boundary ($bndry)!");
                  WriteError("Original message: " . $string);
                  break;
               }
               $i += strlen($bndry);
               if (substr($buffer, $i, 2) == "--")
               {
                  break;
               }
               $i += strlen($separator);
   
               $j = strpos($buffer, $bndry, $i);
               if ($j !== false)
               {
                  array_push($result->Segments, substr($buffer, $i, $j - $i));
               }
   
               $i = $j;
            }
         }
		}
		if ($boundary == NULL)
		{
			// no sub-parts
			
			if ($alwaysSeperateBody)
			{
				// we have to move the content related headers from the
				// main message to this segment
            $temp = '';
				foreach (array_keys($headers) as $name)
				{
					if (strpos(strtolower($name), "content-") === 0)
					{
						$value = $headers[$name];
						$temp .= "$name: $value$separator";
						$result->RemoveHeader($name);
					}
				}
				$buffer = $temp . $buffer;
			}
	//		else
	//		{
	//			$buffer = $separator . $buffer;
	//		}

         assert($buffer !== NULL);
         array_push($result->Segments, $buffer);
		}
		
		return $result;
	}
	
	// a header could have encoding based on RFC 2047
	static function DecodeHeader($str, $originalMime = NULL)
	{
		try
		{
			$str = iconv_mime_decode("Subject: $str", 0, "utf-8");
			return substr($str, 9);
		}
		catch (Exception $e)
		{
			WriteError("Exception decoding MIME header '$str': " . $e->getMessage());
			if (!is_null($originalMime))
				WriteInfo("Original: " . $originalMime);
			return $str;
		}
	}

	static function DecodeData($str, $encoding)
	{
//		$str = iconv("windows-1252", "utf-8", $str);
		
		if (is_null($encoding))
			return $str;
		
		if (strcmp(strtolower($encoding), '7bit') == 0 ||
			strcmp(strtolower($encoding), '8bit') == 0)
		{
			// no decoding required
		}
		elseif (strcmp(strtolower($encoding), 'base64') == 0)
		{
			$str = Utilities::DecodeBase64($str);
		}
		elseif (strcmp(strtolower($encoding), 'quoted-printable') == 0)
		{
			$str = Utilities::DecodeQuotedPrintable($str);
		}
		else
		{
			WriteError("Unrecognized MIME encoding: $encoding");
		}
		
		return $str;
	}

   static function FindHeaderEnd($data, &$separatorLen)
   {
		// seperator is a blank line, could be \n\n or \r\n\r\n...
		
      $separatorLen = 2;
		$i = strpos($data, "\n\n");
		$j = strpos($data, "\r\n\r\n");

      // let's check for the case where there are no headers
      $k = false;
      $klen = 0;
      if (strlen($data) >= 1 && substr($data, 0, 1) == "\n")
      {
         $klen = 1;
         $k = 0;
      }
      else if (strlen($data) >= 2 && substr($data, 0, 2) == "\r\n")
      {
         $klen = 2;
         $k = 0;
      }

		if ($i === false || ($j !== false && $j < $i))
      {
         $separatorLen = 4;
         $i = $j;
      }
		if ($i === false || ($k !== false && $k < $i))
      {
         $separatorLen = $klen;
         $i = $k;
      }

		if ($i === false)
		{
			WriteError("MIME segment is missing LF LF separator!");
         $i = 0;
		}
      
      return $i;
   }

	private static function _ProcessHeader($header, &$ref_headers, &$ref_headerCaseMappings)
	{
//		$header = Utilities::ReplaceInString($header, "\n", "");	// folded lines would have contained LFs
//		$header = Utilities::ReplaceInString($header, "\r", "");	// and line endings may also include CRs
		
		$i = strpos($header, ":");
		if ($i === false)
		{
			WriteError("Unrecognized MIME header: " . $header);
			return;
		}
		
		$name = Utilities::RemoveSurroundingSpaces(substr($header, 0, $i));
		$value = Utilities::RemoveSurroundingSpaces(substr($header, $i + 1));
		
		if (isset($ref_headers[$name]))
		{
			$data = &$ref_headers[$name];
			if (is_array($data))
			{
				array_push($data, $value);
			}
			else
			{
				$ref_headers[$name] = array($data, $value);
			}
		}
		else
		{
			$ref_headers[$name] = $value;
			$ref_headerCaseMappings[strtolower($name)] = $name;
		}
	}

	private static function _GetBoundary($contentType)
	{
		foreach (explode(";", $contentType) as $section)
		{
			if (strpos($section, "boundary") !== false)
			{
				$value = substr($section, strpos($section, "=")+1);
				$value = Utilities::RemoveQuotes(Utilities::RemoveSurroundingSpaces($value));
				return $value;
			}
		}

		return "";
	}
}

if (0)
{
   $message = File::ReadTextFile(dirname(__FILE__) . '/Complex.eml', "r");
   $mime = Mime::FromString($message);
   $segment = $mime->GetSegmentWithType('text/calendar');
   if ($segment == NULL)
   {
      WriteDie("Error getting empty segment!");
   }

   $message = File::ReadTextFile(dirname(__FILE__) . '/Meetup1.eml', "r");
   $mime = Mime::FromString($message);
   $header = $mime->GetHeader("Subject");
   if (strcmp($header, "You're invited to Average pace walk around Rithet\xE2\x80\x99s Bog") != 0)
   {
      WriteDie("Error decoding encoded header!");
   }

   $text = "MIME-Version: 1.0
Content-Type: multipart/related; boundary=\"----=_Separation_807345_\"

This is a multi-part message in MIME format.

------=_Separation_807345_
MIME-Version: 1.0
Content-Type: multipart/alternative; boundary=\"----=_Separation_569035_\"

This is a multi-part message in MIME format.

------=_Separation_569035_--

------=_Separation_807345_--
";
   $mime = Mime::FromString($text);
   $segment = $mime->GetSegment(0);
	if ($segment->GetSegmentCount() != 0)
	{
		WriteDie("Error parsing empty multipart message!");
	}
   
	unlink(dirname(__FILE__) . '/MultipartTest2.eml');
	
	$message = File::ReadTextFile(dirname(__FILE__) . '/MultipartTest.eml', "r");
	$temp = strlen($message);
	if ($temp != /*130978*/ 132979)
	{
		WriteDie("Error reading multipart message!");
	}

	// decode top level message
	$mime = Mime::FromString($message);
	if (empty($mime))
	{
		WriteDie("Error converting multipart message!");
	}
	if (count($mime->Segments()) != 2)
	{
		WriteDie("Error converting to two root segments!");
	}
	
	$temp = $mime->GetHeader("CONTENT-type");
	if (strcmp($temp, "multipart/mixed; boundary=\"----=_NextPart_000_017D_01C79326.25C66DB0\"") != 0)
	{
		WriteDie("Error getting header!");
	}
	$mime->SetToHeader("beboo", "location", "var house");
	if (strcmp($mime->GetHeader("BEBOO"), "; location=\"var house\"") != 0)
	{
		WriteDie("Error adding to empty header or getting header of different case!");
	}
	if (strcmp($mime->GetFromHeader("BeBoo", "Location"), "var house") != 0)
	{
		WriteDie("Error getting header part!");
	}
	$mime->SetHeader("bebOO", "10 MAY 2007 ; location = Here");
	if (!$mime->ContainsHeader("BeBoo"))
	{
		WriteDie("Error checking for header!");
	}
	$mime->SetToHeader("beboO", "time", "17:10");
	if (!$mime->RemoveFromHeader("BEBOo", "LOCATION"))
	{
		WriteDie("Error removing from header!");
	}
	if (strcmp($mime->GetHeader("bebOO"), "10 MAY 2007 ;  time=\"17:10\"") != 0)
	{
		WriteDie("Error getting modified header!");
	}
	if (!$mime->RemoveFromHeader("BeBOo", NULL))
	{
		WriteDie("Error removing from header!");
	}
	if (strcmp($mime->GetHeader("BEBoo"), ";   time=\"17:10\"") != 0)
	{
		WriteDie("Error getting modified header!");
	}
	if (strcmp($mime->GetFromHeader("BeboO", "time"), "17:10") != 0)
	{
		WriteDie("Error getting header part!");
	}
	if (strcmp($mime->GetFromHeader("BeboO", NULL), "") != 0)
	{
		WriteDie("Error getting header part!");
	}
	if (!$mime->RemoveHeader("bebOo"))
	{
		WriteDie("Error removing header!");
	}
	if ($mime->ContainsHeader("beboo"))
	{
		WriteDie("Error checking for header!");
	}
	if (!$mime->ContainsHeader("X-Priority"))
	{
		WriteDie("Error in test??");
	}
	if ($mime->RemoveMatchingHeaders("^X-") != 6)
	{
		WriteDie("Error removing X- headers!");
	}
	if ($mime->ContainsHeader("X-Priority"))
	{
		WriteDie("Error removing matching for headers!");
	}
	
	// decode related parts (two alternative docs and a JPEG)
	$temp = $mime->Segments();
	$mime1 = $temp[0];
	if (empty($mime1))
	{
		WriteDie("Error converting multipart function-message!");
	}
	if (count($mime1->Segments()) != 2)
	{
		WriteDie("Error converting to two function-segments!");
	}

	// decode first related part, the alternative containing text and HTML parts
	$temp = $mime1->Segments();
	$mime11 = $temp[0];
	if (empty($mime11))
	{
		WriteDie("Error converting multipart function-function-message!");
	}
	if (count($mime11->Segments()) != 2)
	{
		WriteDie("Error converting to two function-function-segments!");
	}

	// decode text part
	$temp = $mime11->Segments();
	$mime111 = $temp[0];
	if (empty($mime111))
	{
		WriteDie("Error converting multipart function-function-function-message!");
	}
	if (count($mime111->Segments()) != 1)
	{
		WriteDie("Error converting to one function-function-function-segments!");
	}

	// decode HTML part, note it links to a JPEG found on the Web and also included as
	// one of the parts to this email
	$temp = $mime11->Segments();
	$mime112 = $temp[1];
	if (empty($mime112))
	{
		WriteDie("Error converting multipart function-function-function-message!");
	}
	if (count($mime112->Segments()) != 1)
	{
		WriteDie("Error converting to one function-function-function-segments!");
	}

	// decode JPEG which has a content-location URL matching what's in the HTML
	$temp = $mime1->Segments();
	$mime12 = $temp[1];
	if (empty($mime12))
	{
		WriteDie("Error converting multipart function-function-message!");
	}
	if (count($mime12->Segments()) != 1)
	{
		WriteDie("Error converting to two function-function-segments!");
	}
	
	// this last part is the PDF document attachment
	$temp = $mime12->Segments();
	$temp = $temp[0];
	
	// DRL FIXIT! Add testing here that creates a new email using the pieces
	// of the original in order to make sure our stuff really works!
	$result = $mime->ToString();

	// we can't do a straight comparison but you should be able to open this email
	// and it should look identical to the original
	File::WriteTextFile(dirname(__FILE__) . '/MultipartTest2.eml', $result);
}

?>
<?php

class Url
{
	static function GetDomain($url)
	{
      return parse_url($url, PHP_URL_HOST);
/*
		$iStart = strpos($url, "://");
		if ($iStart === false)
		{
			$iStart = 0;
		}
      else
      {
         $iStart += 3;
      }
      
		$iEnd = strpos($url, "/", $iStart);
		if ($iEnd === false)
		{
   		$iEnd = strpos($url, "?", $iStart);
   		if ($iEnd === false)
   		{
   			$iEnd = strlen($url);
   		}
		}
		
		return substr($url, $iStart, $iEnd - $iStart);
*/
	}

	static function StripProtocol($url)
	{
		$iStart = strpos($url, "://");
		if ($iStart === false)
		{
   		$iStart = strpos($url, ":");
   		if ($iStart === false)
   		{
            return $url;
   		}
         else
         {
            return substr($url, $iStart + 1);
         }
		}
      else
      {
         return substr($url, $iStart + 3);
      }
	}

	static function GetFullPath($url)
	{
      $url = Url::StripParams($url);
      
		$iStart = strpos($url, "://");
		if ($iStart === false)
		{
   		$iStart = 0;
		}
      else
      {
         $iStart += 3;
      }
      return substr($url, $iStart);
	}

   // leaves a terminating slash
	static function StripFilename($url)
	{
		$iStart = strrpos($url, "/");
		if ($iStart === false)
		{
         $iStart = strlen($url)-1;
		}
      return substr($url, 0, $iStart+1);
	}

	static function GetProtocol($url)
	{
		$iStart = strpos($url, "://");
		if ($iStart === false)
		{
   		$iStart = strpos($url, ":");
   		if ($iStart === false)
   		{
            return '';
   		}
		}
      
      return substr($url, 0, $iStart);
	}

	static function StripParams($url)
	{
		$iStart = strpos($url, "?");
		if ($iStart === false)
         return $url;
         
      return substr($url, 0, $iStart);
	}

   // returns everything after the ?
	static function GetParamsSection($url)
	{
		$iStart = strpos($url, "?");
		if ($iStart === false)
         return NULL;
         
      return substr($url, $iStart+1);
	}

	static function GetParam($url, $name)
	{
		$i = strpos($url, "$name=");
		if ($i === false)
		{
			return NULL;
		}
		
		$url = substr($url, $i+strlen($name)+1);
		
		$i = strpos($url, "&");
		if ($i === false)
		{
			$i = strlen($url);
		}
		
		return substr($url, 0, $i);
	}

   // value must NOT be encoded, if it is NULL then the parameter is removed completely
	static function SetParam($url, $name, $value)
	{
      $replacement = "";
		if ($value !== NULL)
      {
         $value = Url::EncodeURIComponent($value);
         $replacement = "$name=$value";
      }

		if (strpos($url, "$name=") === false)
		{
		   if (!empty($replacement))
         {
            if (strpos($url, "?") === false)
            {
               $url .= "?$replacement";
            }
            else
            {
               $url .= "&$replacement";
            }
         }
		}
		else
		{
			$url = preg_replace("/$name=[^&=]*/", $replacement, $url);
		}
		
		return $url;
	}

	static function RemoveParam($url, $name)
	{
		if (strpos($url, "$name=") !== false)
		{
			$url = preg_replace("/$name=[^&=]*/", '', $url);
		}
		
		return $url;
	}

	// this is intended to work exactly like the JavaScript encodeURIComponent()
	static function EncodeURIComponent($str, $encodeQuote=false)
	{
    	$revert = $encodeQuote ? 
          array('%21'=>'!', '%2A'=>'*', '%28'=>'(', '%29'=>')') : 
          array('%21'=>'!', '%2A'=>'*', '%28'=>'(', '%29'=>')', '%27'=>"'");
    	return strtr(rawurlencode($str), $revert);
	}
   
   static function CreateEmailLink($email, $subject, $body)
   {
      $subject = Url::EncodeURIComponent($subject, true);
      $body = Url::EncodeURIComponent($body, true);
      
      if (empty(Url::GetProtocol($email)))
         $email = 'mailto:' . $email;
      
      return $email . '?subject=' . $subject . '&body=' . $body;
   }
   
   static function CreateSmsLink($number, $content)
   {
      $content = Url::EncodeURIComponent($content, true);
      
      $number = Url::StripProtocol($number);
      $number = Utilities::RemoveNonAlphanumericCharacters($number);
      $number = 'sms:' . $number;
      
      $ios = Utilities::iOSVersion();
      if ($ios)
      {
         if ($ios < 8)
            return $number . ';body=' . $content;
         else
            return $number . '&body=' . $content;
      }
      
      return $number . '?body=' . $content;
   }
}

?>

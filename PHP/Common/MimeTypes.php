<?php


//require "../Common/File.pl";
//require "../Common/Utilities.pl";

class MimeTypes
{
	private static $MimeExtensions =
	array(
	// NOTE: default extension for a type should come last
	// This list is duplicated in MimeTypes.cpp!
		"hta" => "application/hta",
		"isp" => "application/x-internet-signup",
		"crd" => "application/x-mscardfile",
		"pmc" => "application/x-perfmon ",
		"spc" => "application/x-pkcs7-certificates",
		"sv4crc" => "application/x-sv4crc",
		"bin" => "application/octet-stream",
		"clp" => "application/x-msclip",
		"mny" => "application/x-msmoney",
		"p7r" => "application/x-pkcs7-certreqresp",
		"evy" => "application/envoy",
		"p7s" => "application/pkcs7-signature",
		"eps" => "application/postscript",
		"setreg" => "application/set-registration-initiation",
		"xlm" => "application/vnd.ms-excel",
		"cpio" => "application/x-cpio",
		"dvi" => "application/x-dvi",
		"p7b" => "application/x-pkcs7-certificates",
		"doc" => "application/msword",
		"dot" => "application/msword",
		"p7c" => "application/pkcs7-mime",
		"ps" => "application/postscript",
		"wps" => "application/vnd.ms-works",
		"csh" => "application/x-csh",
		"iii" => "application/x-iphone",
		"pmw" => "application/x-perfmon",
		"man" => "application/x-troff-man",
		"hdf" => "application/x-hdf",
		"mvb" => "application/x-msmediaview",
		"texi" => "application/x-texinfo",
		"setpay" => "application/set-payment-initiation",
		"stl" => "application/vndms-pkistl",
		"mdb" => "application/x-msaccess",
		"oda" => "application/oda",
		"hlp" => "application/winhlp",
		"nc" => "application/x-netcdf",
		"sh" => "application/x-sh",
		"shar" => "application/x-shar",
		"tcl" => "application/x-tcl",
		"ms" => "application/x-troff-ms",
		"ods" => "application/oleobject",
		"axs" => "application/olescript",
		"xla" => "application/vnd.ms-excel",
		"mpp" => "application/vnd.ms-project",
		"dir" => "application/x-director",
		"sit" => "application/x-stuffit",
		"*" => "application/octet-stream",
		"crl" => "application/pkix-crl",
		"ai" => "application/postscript",
		"xls" => "application/vnd.ms-excel",
		"wks" => "application/vnd.ms-works",
		"ins" => "application/x-internet-signup",
		"pub" => "application/x-mspublisher",
		"wri" => "application/x-mswrite",
		"spl" => "application/futuresplash",
		"hqx" => "application/mac-binhex40",
		"p10" => "application/pkcs10",
		"xlc" => "application/vnd.ms-excel",
		"xlt" => "application/vnd.ms-excel",
		"dxr" => "application/x-director",
		"js" => "application/x-javascript",
		"m13" => "application/x-msmediaview",
		"trm" => "application/x-msterminal",
		"pml" => "application/x-perfmon",
		"me" => "application/x-troff-me",
		"wcm" => "application/vnd.ms-works",
		"latex" => "application/x-latex",
		"m14" => "application/x-msmediaview",
		"wmf" => "application/x-msmetafile",
		"cer" => "application/x-x509-ca-cert",
		"zip" => "application/x-zip-compressed",
		"p12" => "application/x-pkcs12",
		"pfx" => "application/x-pkcs12",
		"der" => "application/x-x509-ca-cert",
		"pdf" => "application/pdf",
		"xlw" => "application/vnd.ms-excel",
		"texinfo" => "application/x-texinfo",
		"p7m" => "application/pkcs7-mime",
		"pps" => "application/vnd.ms-powerpoint",
		"dcr" => "application/x-director",
		"gtar" => "application/x-gtar",
		"sct" => "text/scriptlet",
		"fif" => "application/fractals",
		"exe" => "application/octet-stream",
		"ppt" => "application/vnd.ms-powerpoint",
		"sst" => "application/vndms-pkicertstore",
		"pko" => "application/vndms-pkipko",
		"scd" => "application/x-msschedule",
		"tar" => "application/x-tar",
		"roff" => "application/x-troff",
		"t" => "application/x-troff",
		"prf" => "application/pics-rules",
		"rtf" => "application/rtf",
		"pot" => "application/vnd.ms-powerpoint",
		"wdb" => "application/vnd.ms-works",
		"bcpio" => "application/x-bcpio",
		"dll" => "application/x-msdownload",
		"pma" => "application/x-perfmon",
		"pmr" => "application/x-perfmon",
		"tr" => "application/x-troff",
		"src" => "application/x-wais-source",
		"acx" => "application/internet-property-stream",
		"cat" => "application/vndms-pkiseccat",
		"cdf" => "application/x-cdf",
		"tgz" => "application/x-compressed",
		"sv4cpio" => "application/x-sv4cpio",
		"tex" => "application/x-tex",
		"ustar" => "application/x-ustar",
		"crt" => "application/x-x509-ca-cert",
		"class" => "application/x-java-vm",
		"ser" => "application/x-java-serialized-object",
		"jar" => "application/x-java-archive",
		"ra" => "audio/x-pn-realaudio ",
		"mid" => "audio/mid",
		"au" => "audio/basic",
		"snd" => "audio/basic",
		"wav" => "audio/wav",
		"aifc" => "audio/aiff",
		"m3u" => "audio/x-mpegurl",
		"ram" => "audio/x-pn-realaudio",
		"aiff" => "audio/aiff",
		"rmi" => "audio/mid",
		"aif" => "audio/x-aiff",
		"mp3" => "audio/mpeg",
		"gz" => "application/x-gzip",
		"z" => "application/x-compress",
		"tsv" => "text/tab-separated-values",
		"xml" => "text/xml",
		"323" => "text/h323",
		"htt" => "text/webviewhtml",
		"stm" => "text/html",
		"html" => "text/html",
		"xsl" => "text/xml",
		"htm" => "text/html",
		"txt" => "text/plain",
		"rtf" => "text/richtext",
		"cod" => "image/cis-cod",
		"ief" => "image/ief",
		"pbm" => "image/x-portable-bitmap",
		"tiff" => "image/tiff",
		"ppm" => "image/x-portable-pixmap",
		"rgb" => "image/x-rgb",
		"dib" => "image/bmp",
		"jpeg" => "image/jpeg",
		"cmx" => "image/x-cmx",
		"pnm" => "image/x-portable-anymap",
		"jpe" => "image/jpeg",
		"jfif" => "image/pjpeg",
		"tif" => "image/tiff",
		"jpg" => "image/jpeg",
		"xbm" => "image/x-xbitmap",
		"ras" => "image/x-cmu-raster",
		"gif" => "image/gif",
		"png" => "image/png",        
		
		"mpg" => "video/mpeg", 	
		"mpeg" => "video/mpeg", 	
		"mpe" => "video/mpeg",
		"qt" => "video/quicktime",
		"mov" => "video/quicktime",
		"avi" => "video/x-msvideo",
		"movie" => "video/x-sgi-movie",
		"mp4" => "video/mpeg", 	
	
		# added these for Microsoft products
		"asf" => "video/x-ms-asf",
		"asx" => "video/x-ms-asf",
		"wma" => "audio/x-ms-wma",
		"wax" => "audio/x-ms-wax",
		"wmv" => "video/x-ms-wmv",
		"wvx" => "video/x-ms-wvx",
		"wm" => "video/x-ms-wm",
		"wmx" => "video/x-ms-wmx",
		"wmz" => "application/x-ms-wmz",
		"wmd" => "application/x-ms-wmd",
	
		"eml" => "message/rfc822",
	
		"csv" => "text/csv",
		
		"vcf" => "text/x-vcard",
		"vcard" => "text/x-vcard",	# default extension for a type should come last
		
		"icalendar" => "text/calendar",
		"ics" => "text/calendar",
		"ifb" => "text/calendar",
		"ical" => "text/calendar"	# default extension for a type should come last
	);
	private static $MimeTypes = null;
	
	private static $ImageExtensions = array();
	
	static function IsImageFile($filename)
	{
		$mimeType = GetMimeTypeFromExtension(File::GetExtension($filename));
		if ($mimeType == null)
		{
			return 0;
		}
	
		return Utilities::StringContains($mimeType, "image/");
	}
	
	static function GetImageExtensions()
	{
		if (count(MimeTypes::$ImageExtensions) == 0)
		{
			foreach (MimeTypes::$MimeExtensions as $ext => $mimeType)
			{
				if (Utilities::StringContains($mimeType, "image/"))
				{
					MimeTypes::$ImageExtensions[] = $ext;
				}
			}
		}
		
		return MimeTypes::$ImageExtensions;
	}
	
	static function GetExtensionForMimeType($type)
	{
      // NOTE: this will remove some extensions that point to the same MIME type
		if (MimeTypes::$MimeTypes == NULL)
			MimeTypes::$MimeTypes = array_flip(MimeTypes::$MimeExtensions);
         
		return MimeTypes::$MimeTypes[strtolower($type)];
	}
	
	static function GetMimeTypeFromExtension($extension)
	{
		return MimeTypes::$MimeExtensions[strtolower($extension)];
	}
	
	static function MatchesMimePatterns($ref_mimePatterns, $mimeType)
	{
		$mimePatterns = $ref_mimePatterns;
		if (count($mimePatterns) == 0)
		{
			return 1;			# supports anything
		}
		
		if ($mimeType == null)
		{
			foreach ($mimePatterns as $pattern)
			{
				$pattern = Utilities::ReplaceInString($pattern, "*", ".+");
				if (strcmp($pattern, "*/*"))
				{
					return 1;	# supports anything
				}
			}
			return 0;
		}
		
		foreach ($mimePatterns as $pattern)
		{
			$pattern = Utilities::ReplaceInString($pattern, "*", ".+");
			if (preg_match($pattern, $mimeType) > 0)
			{
				return 1;		# supports this pattern
			}
		}
	
		return 0;
	}
	
	static function MatchesAnyMimePatterns($ref_mimePatterns1, $ref_mimePatterns2)
	{
		$mimePatterns1 = $ref_mimePatterns1;
		$mimePatterns2 = $ref_mimePatterns2;
		foreach ($mimePatterns1 as $pattern1)
		{
			$pattern1 = Utilities::ReplaceInString($pattern1, "*", ".+");
			foreach ($mimePatterns2 as $pattern2)
			{
				$pattern2 = Utilities::ReplaceInString($pattern2, "*", ".+");
				if (preg_match($pattern2, $pattern1) > 0 || preg_match($pattern1, $pattern2) > 0)
				{
					return 1;
				}
			}
		}
	
		return 0;
	}
}

?>

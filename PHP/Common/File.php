<?php

class File
{
	static function ReadTextFile($filename)
	{
		$file = fopen($filename, 'r');
		if (!$file)
			return null;
      $data = '';
		$len = filesize($filename);
		if ($len > 0)
		{
	 		$data = fread($file, $len);
		}
		fclose($file);
		return $data;
	}

	static function WriteTextFile($filename, $data)
	{
		$file = fopen($filename, 'w');
		if (!$file)
			return null;
 		fwrite($file, $data, strlen($data));
		fclose($file);
	}
	
	static function ReadBinaryFile($filename)
	{
		$file = fopen($filename, 'rb');
		if (!$file)
			return null;
		$data = '';
		$len = filesize($filename);
		if ($len > 0)
		{
	 		$data = fread($file, $len);
		}
		fclose($file);
		return $data;
	}

	static function WriteBinaryFile($filename, $data)
	{
		$file = fopen($filename, 'wb');
		if (!$file)
			return null;
 		fwrite($file, $data, strlen($data));
		fclose($file);
	}

	static function ConvertFromCSV($data)
	{
		$len = strlen($data);
			
		$rows = array();
		$inQuotes = false;
		$skipping = false;
		$rowStart = 0;
		$value = '';
		$row = array();
		for ($i = 0; $i < $len; $i++)
		{
			$c = $data[$i];
			if ($inQuotes)
			{
				if ($c == '"')
				{
					if ($i < $len-1 && $data[$i+1] == '"')
					{
						// two double quotes in a row converts to one
						$value .= $c;
						$i++;
					}
					else
					{
						$inQuotes = false;
						$skipping = true;
					}
				}
/*
				else if ($c == '\\')
				{
					if ($i < $len-1 && $data[$i+1] == '\\')
					{
						// two backslashes in a row converts to one
						$value .= $c;
						$i++;
					}
				}
*/
				else
				{
					$value .= $c;
				}
			}
			else
			{
				if ($c == '"')
				{
					if ($skipping)
					{
						$value = substr($data, $rowStart, $i - $rowStart);
						print("Unmatched double quotes in $filename near [$value]\r\n");
						exit(1);
					}
					
					$inQuotes = true;
					$value = '';
				}
				else if ($c == ',' || $c == "\r" || $c == "\n")
				{
					if (!$skipping)
						$value = trim($value);	// strings not surrounded by double quotes are trimmed

					if ($c == "\r" || $c == "\n")	// end of row
					{
						// skip empty rows
						if (count($row) > 0 || strlen($value) > 0)
						{
							array_push($row, $value);
							array_push($rows, $row);
						}
						
						$rowStart = $i+1;
						$row = array();
					}
					else
					{
						array_push($row, $value);
					}
					
					$inQuotes = false;
					$skipping = false;
					$value = '';
				}
				else if ($skipping)
				{
					if ($c != ' ' && $c != '\t')
					{
						$value = substr($data, $rowStart, $i - $rowStart);
						print("Unexpected character [$c] in ConvertFromCSV near [$value]\r\n");
						exit(1);
					}
				}
				else
				{
					$value .= $c;
				}
			}
		}
		
		if ($inQuotes)
		{
			$value = substr($data, $rowStart-10, 50);
			print("Unmatched double quotes in $filename near [$value]\r\n");
			exit(1);
		}
		
		return $rows;
	}
	
	static function ConvertToCSV($rows)
	{
		static $special_chars = array('"' => "\"\""/*, "\\" => "\\\\"*/);
		
		$data = "";
		
		foreach ($rows as $row)
		{
			for ($j = 0; $j < count($row); $j++)
			{
				$value = $row[$j];
				
				// escape special characters
				foreach ($special_chars as $char => $replace)
				{
					$value = str_replace($char, $replace, $value);
				}
				
				if ($j != 0)
					$data .= ',';
				$data .= "\"$value\"";
			}
			
			$data .= "\r\n";
		}
		
		return $data;
	}

	static function ReadCSVFile($filename)
	{
		return File::ConvertFromCSV(File::ReadBinaryFile($filename));
	}
	
	static function WriteCSVFile($filename, $rows)
	{
		File::WriteBinaryFile($filename, File::ConvertToCSV($rows));
	}

	static function Exists($filename)
	{
		return file_exists($filename);
	}
   
	// returns TRUE or FALSE
	static function Delete($filename)
	{
		return unlink($filename);
	}

   static function CreateIntermediateFolders($filename)
   {
		$path = pathinfo($filename)['dirname'];		// strip the name (go up one directory)
      
      return file_exists($path) || mkdir($path, 0777, true);
   }
   
	static function DeleteFolder($dirname)
	{
	   if (is_dir($dirname))
	      $dir_handle = opendir($dirname);
	   if (!$dir_handle)
	      return false;
	   while($file = readdir($dir_handle))
	   {
	      if ($file != "." && $file != "..")
		  {
	         if (!is_dir($dirname."/".$file))
	            unlink($dirname."/".$file);
	         else
	            delete_directory($dirname.'/'.$file);     
	     }
	   }
	   closedir($dir_handle);
	   rmdir($dirname);
	   return true;
	}	
   
   static function GetFilename($path)
   {
      return pathinfo($path, PATHINFO_BASENAME);
   }
   
   static function GetPath($path)
   {
      return pathinfo($path, PATHINFO_DIRNAME);
   }
   
   static function GetExtension($path)
   {
      return pathinfo($path, PATHINFO_EXTENSION);
   }
}

?>

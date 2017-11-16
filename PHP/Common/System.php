<?php

class System
{
	static $OperatingSystemWindows = 1;
	static $OperatingSystemUnix = 2;
	
	static $OS = 0;
	static $Locks = array();
	
	static function GetOperatingSystem()
	{
		if (System::$OS == 0)
		{
			$os = php_uname('s');
			if (stripos($os, 'WINNT') !== false ||
				stripos($os, 'WIN32') !== false ||
				stripos($os, 'Windows') !== false)
				System::$OS = System::$OperatingSystemWindows;
			elseif (stripos($os, 'BSD') !== false ||
				stripos($os, 'Unix') !== false ||
				stripos($os, 'Linux') !== false)
				System::$OS = System::$OperatingSystemUnix;
			else
				WriteDie("Unrecognized operating system $os");
		}
		
		return System::$OS;
	}
	
	// "name" should be any valid filename (with path and extension) that will
	// uniquely identify the group of processes you wish to lock out
	static function GrabMutex($name)
	{
		if (System::GetOperatingSystem() == System::$OperatingSystemWindows)
		{
			$fp = @fopen($name, 'x');
			if ($fp !== false)
			{
				System::$Locks[$name] = $fp;
				return true;
			}
		}
		else
		{
			$fp = fopen($name, 'w');
			
			// NOTE: LOCK_NB is not supported under Windows!
			if (flock($fp, LOCK_EX | LOCK_NB))
			{
				System::$Locks[$name] = $fp;
				return true;
			}
	
			fclose($fp);
		}
		
		return false;
	}

	static function ReleaseMutex($name)
	{
		if (!isset(System::$Locks[$name]))
			WriteDie("Attempting to unlock $name which was not locked!");

		$fp = System::$Locks[$name];
		
		if (System::GetOperatingSystem() == System::$OperatingSystemWindows)
		{
			fclose($fp);
			unlink($name);
		}
		else
		{
			flock($fp, LOCK_UN);
			fclose($fp);
		}
	}
}

if (0)
{
   $test1 = dirname(__FILE__) . "/../test1.lock";
	if (System::GrabMutex($test1))
	{
		WriteInfo('Got lock test1');
		if (System::GrabMutex($test1))
			WriteInfo('Error, got lock test1 while locked!');
		else
			WriteInfo('Success, didnt get lock test1, already locked');
		System::ReleaseMutex($test1);
	}
}

?>

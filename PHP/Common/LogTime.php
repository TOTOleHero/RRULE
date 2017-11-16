<?php

// ========================================================================
//        Copyright (c) 2010 Dominique Lacerte, All Rights Reserved.
// 
// Redistribution and use in source and binary forms are prohibited without 
//   prior written consent from Dominique Lacerte (internet@lacerte.org).
// ========================================================================

require_once(dirname(__FILE__) . '/DateAndTime.php');
require_once(dirname(__FILE__) . '/Log.php');

class LogTime
{
	private $start;
	private $name;
	private $minimumSeconds;
	
    /**
	 * SPL-compatible autoloader.
	 *
     * @param string $className Name of the class to load.
     *
     * @return boolean
    public static function autoload($className)
    {
        if ($className != 'LogTime')
		{
            return false;
		}
        return include str_replace('_', '/', $className) . '.php';
    }
     */

	function __construct($name, $minimumSeconds = 0.0)
	{
		$this->start = DateAndTime::Now();
		$this->name = $name;
		$this->minimumSeconds = $minimumSeconds;
	}
	
	function __destruct()
	{
		$this->End();
	}
		
	function AddString($str)
	{
		$this->name .= $str;
	}
		
	function PrintTime()
	{
		if (strlen($this->name) > 0)
		{
			$milli1 = $this->start->GetAsMilliseconds();
			$milli2 = DateAndTime::Now()->GetAsMilliseconds();
			$duration = (($milli2 - $milli1) / 1000);
			if ($duration >= $this->minimumSeconds)
			{
				WriteError('Duration of ' . $this->name . ': ' . $duration . "s");
			}
		}
	}
	
	function End()
	{
		if (strlen($this->name) > 0)
		{
			$this->PrintTime();
			$this->name = "";
		}
	}
}
?>

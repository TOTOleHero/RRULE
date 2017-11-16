<?php

// ========================================================================
//        Copyright (c) 2010 Dominique Lacerte, All Rights Reserved.
// 
// Redistribution and use in source and binary forms are prohibited without 
//   prior written consent from Dominique Lacerte (internet@lacerte.org).
// ========================================================================

require_once(dirname(__FILE__) . '/Log.php');
require_once(dirname(__FILE__) . '/Utilities.php');

class RecurrenceRuleIterator
{
	public $Values;
	
    /**
	 * SPL-compatible autoloader.
	 *
     * @param string $className Name of the class to load.
     *
     * @return boolean
    public static function autoload($className)
    {
        if ($className != 'RecurrenceRuleIterator')
		{
            return false;
		}
        return include str_replace('_', '/', $className) . '.php';
    }
     */

	function __construct()
	{
		$this->Values = array();
	}
	
	function __destruct()
	{
	}


	function AddDate($date)
	{
		array_push($this->Values, $date);
	}
	
	function HasNext()
	{
      return count($this->Values) > 0;
	}
	
	function GetNext()
	{
		if (count($this->Values) == 0)
		{
			return NULL;
		}
		
		$value = array_shift($this->Values);
		return array($value);
	}
	
	function GetCount()
	{
		return count($this->Values);
	}
	
	function Close()
	{
		$this->Values = array();
	}
}

?>

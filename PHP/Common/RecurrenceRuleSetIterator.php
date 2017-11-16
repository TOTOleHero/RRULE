<?php


// ========================================================================
//        Copyright (c) 2010 Dominique Lacerte, All Rights Reserved.
// 
// Redistribution and use in source and binary forms are prohibited without 
//   prior written consent from Dominique Lacerte (internet@lacerte.org).
// ========================================================================

require_once(dirname(__FILE__) . '/Log.php');
require_once(dirname(__FILE__) . '/Utilities.php');

class RecurrenceRuleSetIterator
{
	private $dt;
	private $gen;
	private $genlist;

	function __construct(&$genlist, $gen)
	{
      $this->dt = NULL;
		$this->gen = $gen;
      $this->genlist = NULL;
	
		// the caller expects us to have retrieved the initial value
		// and only add ourselves to the list if we have a value
		if ($this->GetNext())
		{
			array_push($genlist, $this);
			$this->genlist = &$genlist;
		}
	}
	
	function __destruct()
	{
	}
	
	function GetCurrent()
	{
		return $this->dt;
	}
	
	function HasNext()
	{
		if ($this->gen == NULL)
      {
         return false;
      }
      return $this->gen->HasNext();
	}
	
	function GetNext()
	{
		if ($this->gen == NULL)
      {
         return NULL;
      }
		$data = $this->gen->GetNext();
		if (!$data)
		{
			Utilities::RemoveFromArray($this->genlist, $this);

			$this->Close();
			
			return NULL;
		}
		
		$this->dt = $data[0];
		
		return $data;
	}
	
	function GetCount()
	{
		if ($this->gen == NULL)
      {
         return 0;
      }
      return $this->gen->GetCount();
	}
	
	function Close()
	{
		$this->dt = NULL;
		$this->gen = NULL;
//		$this->genlist = NULL;   This would set the referenced variable to NULL!
	}
	
	static function Equal($obj1, $obj2)
	{
		// turn them both into dates
		if (strcmp(get_class($obj1), "RecurrenceRuleSetIterator") == 0)
		{
			if (empty($obj1->dt)) { WriteDie("NULL date value"); }
			$obj1 = $obj1->dt;
		}
		if (strcmp(get_class($obj2), "RecurrenceRuleSetIterator") == 0)
		{
			if (empty($obj2->dt)) { WriteDie("NULL date value"); }
			$obj2 = $obj2->dt;
		}
		
		return $obj1 == $obj2;
	}
	
	static function NotEqual($obj1, $obj2)
	{
		// turn them both into dates
		if (strcmp(get_class($obj1), "RecurrenceRuleSetIterator") == 0)
		{
			if (empty($obj1->dt)) { WriteDie("NULL date value"); }
			$obj1 = $obj1->dt;
		}
		if (strcmp(get_class($obj2), "RecurrenceRuleSetIterator") == 0)
		{
			if (empty($obj2->dt)) { WriteDie("NULL date value"); }
			$obj2 = $obj2->dt;
		}
		
		return $obj1 != $obj2;
	}
	
	static function GreaterThan($obj1, $obj2)
	{
		// turn them both into dates
		if (strcmp(get_class($obj1), "RecurrenceRuleSetIterator") == 0)
		{
			if (empty($obj1->dt)) { WriteDie("NULL date value"); }
			$obj1 = $obj1->dt;
		}
		if (strcmp(get_class($obj2), "RecurrenceRuleSetIterator") == 0)
		{
			if (empty($obj2->dt)) { WriteDie("NULL date value"); }
			$obj2 = $obj2->dt;
		}
		
		return $obj1 > $obj2;
	}
	
	static function GreaterThanOrEqual($obj1, $obj2)
	{
		// turn them both into dates
		if (strcmp(get_class($obj1), "RecurrenceRuleSetIterator") == 0)
		{
			if (empty($obj1->dt)) { WriteDie("NULL date value"); }
			$obj1 = $obj1->dt;
		}
		if (strcmp(get_class($obj2), "RecurrenceRuleSetIterator") == 0)
		{
			if (empty($obj2->dt)) { WriteDie("NULL date value"); }
			$obj2 = $obj2->dt;
		}
		
		return $obj1 >= $obj2;
	}
	
	static function LessThan($obj1, $obj2)
	{
		// turn them both into dates
		if (strcmp(get_class($obj1), "RecurrenceRuleSetIterator") == 0)
		{
			if (empty($obj1->dt)) { WriteDie("NULL date value"); }
			$obj1 = $obj1->dt;
		}
		if (strcmp(get_class($obj2), "RecurrenceRuleSetIterator") == 0)
		{
			if (empty($obj2->dt)) { WriteDie("NULL date value"); }
			$obj2 = $obj2->dt;
		}
	
		return $obj1 < $obj2;
	}
	
	static function LessThanOrEqual($obj1, $obj2)
	{
		// turn them both into dates
		if (strcmp(get_class($obj1), "RecurrenceRuleSetIterator") == 0)
		{
			if (empty($obj1->dt)) { WriteDie("NULL date value"); }
			$obj1 = $obj1->dt;
		}
		if (strcmp(get_class($obj2), "RecurrenceRuleSetIterator") == 0)
		{
			if (empty($obj2->dt)) { WriteDie("NULL date value"); }
			$obj2 = $obj2->dt;
		}
		
		return $obj1 <= $obj2;
	}
	
	static function Compare($obj1, $obj2)
	{
		// turn them both into dates
		if (strcmp(get_class($obj1), "RecurrenceRuleSetIterator") == 0)
		{
			if (empty($obj1->dt)) { WriteDie("NULL date value"); }
			$obj1 = $obj1->dt;
		}
		if (strcmp(get_class($obj2), "RecurrenceRuleSetIterator") == 0)
		{
			if (empty($obj2->dt)) { WriteDie("NULL date value"); }
			$obj2 = $obj2->dt;
		}
		
		if ($obj1 < $obj2) return -1;
		if ($obj1 > $obj2) return 1;
		return 0;
	}
}

?>

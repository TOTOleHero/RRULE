<?php

// ========================================================================
//        Copyright (c) 2010 Dominique Lacerte, All Rights Reserved.
// 
// Redistribution and use in source and binary forms are prohibited without 
//   prior written consent from Dominique Lacerte (internet@lacerte.org).
// ========================================================================

require_once(dirname(__FILE__) . '/ArrayIterator.php');
require_once(dirname(__FILE__) . '/DbSchema.php');
require_once(dirname(__FILE__) . '/Log.php');
require_once(dirname(__FILE__) . '/RecurrenceRuleSetIterator.php');
require_once(dirname(__FILE__) . '/Utilities.php');

class RecurrenceRuleSet
{
	private $cache = NULL;
	private $rrule = array();
	private $rdate = array();
	private $exrule = array();
	private $exdate = array();

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
	
	function __construct($cache)
	{
		$this->cache = $cache;
	}
	
	function __destruct()
	{
	}
	
	function ToString()
	{
		$result = "";
		
		if (count($this->rdate))
		{
			if (strlen($result)) { $result .= "\n"; }
			$result .= "RDATE:";
			$first = 1;
			foreach ($this->rdate as $date)
			{
				if (!$first) { $result .= ","; } else { $first = 0; }
				$result .= $date->ToFormat(DateAndTime::$ISO8601BasicFormat);
			}
		}
		if (count($this->rrule))
		{
			if (strlen($result)) { $result .= "\n"; }
			$result .= "RRULE:";
			$first = 1;
			foreach ($this->rrule as $rule)
			{
				if (!$first) { $result .= ","; } else { $first = 0; }
				$result .= substr($rule->ToString(), 6);
			}
		}
		if (count($this->exdate))
		{
			if (strlen($result)) { $result .= "\n"; }
			$result .= "EXDATE:";
			$first = 1;
			foreach ($this->exdate as $date)
			{
				if (!$first) { $result .= ","; } else { $first = 0; }
				$result .= $date->ToFormat(DateAndTime::$ISO8601BasicFormat);
			}
		}
		if (count($this->exrule))
		{
			if (strlen($result)) { $result .= "\n"; }
			$result .= "EXRULE:";
			$first = 1;
			foreach ($this->exrule as $rule)
			{
				if (!$first) { $result .= ","; } else { $first = 0; }
				$result .= substr($rule->ToString(), 6);
			}
		}
		
		return $result;
	}
	
	function GetIterator()
	{
		$iter = new RecurrenceRuleIterator();
	
		$rlist = array();
		if (count($this->rdate))
		{
			$temp = $this->rdate;	// make a copy
			usort($temp, array('DateAndTime', 'Compare'));
			new RecurrenceRuleSetIterator($rlist, new MyArrayIterator($temp, DbSchema::FromString("Col1 datetime NOT NULL PRIMARY KEY (Col1)")));
		}
		foreach ($this->rrule as $rrule)
		{
			new RecurrenceRuleSetIterator($rlist, $rrule->GetIterator());
		}
		usort($rlist, array('RecurrenceRuleSet', 'Compare'));
	
		$exlist = array();
		if (count($this->exdate))
		{
			$temp = $this->exdate;	// make a copy
			usort($temp, array('DateAndTime', 'Compare'));
			new RecurrenceRuleSetIterator($exlist, new MyArrayIterator($temp, DbSchema::FromString("Col1 datetime NOT NULL PRIMARY KEY (Col1)")));
		}
		foreach ($this->exrule as $rrule)
		{
			new RecurrenceRuleSetIterator($exlist, $rrule->GetIterator());
		}
		usort($exlist, array('RecurrenceRuleSet', 'Compare'));
	
		$lastdt = NULL;
		while (count($rlist))   // items remove themselves from the array as they run out of items
		{
			if (!$lastdt || $lastdt != $rlist[0]->GetCurrent())
			{
				$lastdt = $rlist[0]->GetCurrent();
            
				while (count($exlist) && $exlist[0]->GetCurrent() != NULL && 
               DateAndTime::LessThan($exlist[0]->GetCurrent(), $lastdt))
				{
					$exlist[0]->GetNext();
					usort($exlist, array('RecurrenceRuleSet', 'Compare'));
				}
				if (!count($exlist) || $exlist[0]->GetCurrent() == NULL || 
               DateAndTime::NotEqual($exlist[0]->GetCurrent(), $lastdt))
				{
					$iter->AddDate($lastdt);
				}
			}
			$rlist[0]->GetNext();
			usort($rlist, array('RecurrenceRuleSet', 'Compare'));
		}
	
		return $iter;
	}
	
	function RRules()
	{
		return $this->rrule;
	}
	
	function AddRRule($rrule)
	{
		array_push($this->rrule, $rrule);
	}
	
	function ExRules()
	{
		return $this->exrule;
	}
	
	function AddExRule($exrule)
	{
		array_push($this->exrule, $exrule);
	}
	
	function RDates()
	{
		return $this->rdate;
	}
	
	function AddRDate($rdate)
	{
		array_push($this->rdate, $rdate);
	}
	
	function ExDates()
	{
		return $this->exdate;
	}
	
	function AddExDate($exdate)
	{
		array_push($this->exdate, $exdate);
	}
   
   function Start()
   {
      $start = NULL;
		foreach ($this->rrule as $rrule)
		{
			$temp = $rrule->Start();
         if ($temp != NULL && ($start == NULL || DateAndTime::LessThan($temp, $start)))
            $start = $temp;
		}
      return $start;
   }
   
   function Until()
   {
      $until = NULL;
		foreach ($this->rrule as $rrule)
		{
			$temp = $rrule->Until();
         if ($temp != NULL && ($until == NULL || DateAndTime::LessThan($temp, $until)))
            $until = $temp;
		}
      return $until;
   }
   
   function SetUntil($until)
   {
		foreach ($this->rrule as $rrule)
		{
			$rrule->SetUntil($until);
		}
   }
   
   static function Compare($rrs1, $rrs2)
   {
      if (!$rrs1->GetCurrent() || !$rrs2->GetCurrent())
      {
         WriteDie("NULL date in RecurrenceRuleSetIterator!");
      }
      return DateAndTime::Compare($rrs1->GetCurrent(), $rrs2->GetCurrent());
   }
}

?>

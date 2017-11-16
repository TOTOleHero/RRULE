<?php

// ========================================================================
//        Copyright (c) 2010 Dominique Lacerte, All Rights Reserved.
// 
// Redistribution and use in source and binary forms are prohibited without 
//   prior written consent from Dominique Lacerte (internet@lacerte.org).
// ========================================================================

require_once(dirname(__FILE__) . '/DateAndTime.php');
require_once(dirname(__FILE__) . '/Log.php');
require_once(dirname(__FILE__) . '/RecurrenceRuleConstants.php');
require_once(dirname(__FILE__) . '/Utilities.php');

class RecurrenceRuleParser
{

	// ===================================================================
	//
	//	Constants.
	//
	// ===================================================================
	
	private static $handlers =
	array(
		"INTERVAL"		=> '_handle_int',
		"COUNT"			=> '_handle_int',
		"BYSETPOS"		=> '_handle_int_list',
		"BYMONTH"		=> '_handle_int_list',
		"BYMONTHDAY"	=> '_handle_int_list',
		"BYYEARDAY"		=> '_handle_int_list',
		"BYEASTER"		=> '_handle_int_list',
		"BYWEEKNO"		=> '_handle_int_list',
		"BYHOUR"		=> '_handle_int_list',
		"BYMINUTE"		=> '_handle_int_list',
		"BYSECOND"		=> '_handle_int_list',
		"FREQ"			=> '_handle_FREQ',
		"UNTIL"			=> '_handle_UNTIL',
		"WKST"			=> '_handle_WKST',
		"BYDAY"			=> '_handle_BYWEEKDAY',
		"BYWEEKDAY"		=> '_handle_BYWEEKDAY'
	);
	
	
	// ===================================================================
	//
	//	Implementation.
	//
	// ===================================================================
	
	static function &ParseRecurrenceRule($line, $tzinfo)
	{
		$name;
		$value;
		if (strpos($line, ':') != FALSE)
		{
			list($name, $value) = explode(':', $line, 2);
			if (strcmp($name, "RRULE") != 0)
			{
				WriteError("unknown parameter (expected RRULE): $name");
				return NULL;
			}
		}
		else
		{
			$value = $line;
		}
		
		$rrkwargs = array();
		foreach (explode(';', $value) as $pair)
		{
			list($name, $value) = explode('=', $pair, 2);
			$name = strtoupper($name);
			$value = strtoupper($value);
			$handler = RecurrenceRuleParser::$handlers[$name];
			if (empty($handler))
			{
				WriteError("unknown parameter for name/value pair: $pair");
			}
			else
			{
				$handler($rrkwargs, $name, $value, $tzinfo);
			}
		}
		
		return $rrkwargs;
	}
}
	
	function _handle_int(&$rrkwargs, $name, $value, $tzinfo)
	{
		if (!Utilities::IsInteger($value))
		{
			$value = NULL;
			WriteError("not a legal integer for $name: $value");
		}
		else
		{
			$value = intval($value);
		}
		$rrkwargs[strtolower($name)] = $value;
	}
	
	function _handle_int_list(&$rrkwargs, $name, $value, $tzinfo)
	{
		$values = explode(',', $value);
		$result = array();
		foreach ($values as $value)
		{
			if (!Utilities::IsInteger($value))
			{
				WriteError("not a legal integer for $name: $value");
			}
			else
			{
				$value = intval($value);
				array_push($result, $value);
			}
		}
		$rrkwargs[strtolower($name)] = &$result;
	}
	
	function _handle_FREQ(&$rrkwargs, $name, $value, $tzinfo)
	{
		$rrkwargs["freq"] = RecurrenceRuleConstants::$FrequencyMap[$value];
	}
	
	function _handle_UNTIL(&$rrkwargs, $name, $value, $tzinfo)
	{
      // DRL FIXIT? I added this for some RRULEs that had this out of range date
      if ($value == '00010101T000000Z')
         $value = DateAndTime::$MINYEAR . '0101T000000Z';

		$date = DateAndTime::FromString($value, $tzinfo);	//, ignoretz, tzinfo)
		if (empty($date))
		{
			writeError("invalid until date: $value");
		}
		else
		{
			$rrkwargs["until"] = $date;
		}
	}
	
	function _handle_WKST(&$rrkwargs, $name, $value, $tzinfo)
	{
		$rrkwargs["wkst"] = RecurrenceRuleConstants::$WeekdayMap[$value];
	}
	
	function _handle_BYWEEKDAY(&$rrkwargs, $name, $value, $tzinfo)
	{
		$values = array();
		foreach (explode(',', $value) as $wday)
		{
			// each day value (MO, TU, etc.) may be preceded by a positive or negative integer
			$i;
			for ($i = 0; $i < strlen($wday) && Utilities::StringContains("+-0123456789", substr($wday, $i, 1)); $i++)
			{
			}
			$n;
			$w;
			if ($i > 0)
			{
				$n = intval(substr($wday, 0, $i));
				$w = substr($wday, $i);
			}
			else
			{
				$n = NULL;
				$w = $wday;
			}
			$temp =
			array(
				"weekday"	=> RecurrenceRuleConstants::$WeekdayMap[$w],
				"n"			=> $n
			);
			array_push($values, $temp);
		}
		$rrkwargs["byweekday"] = $values;
	}

?>

<?php

// ========================================================================
//        Copyright (c) 2010 Dominique Lacerte, All Rights Reserved.
// 
// Redistribution and use in source and binary forms are prohibited without 
//   prior written consent from Dominique Lacerte (internet@lacerte.org).
// ========================================================================

require_once(dirname(__FILE__) . '/Log.php');
require_once(dirname(__FILE__) . '/RecurrenceRuleConstants.php');
require_once(dirname(__FILE__) . '/Utilities.php');

class RecurrenceRuleIteratorInfo
{

	// ===================================================================
	//
	//	Constants.
	//
	// ===================================================================
	
	// Every mask is 7 days longer to handle cross-year weekly periods.
	private static $M366MASK =
	array(
		1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,
		2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,
		3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,
		4,4,4,4,4,4,4,4,4,4,4,4,4,4,4,4,4,4,4,4,4,4,4,4,4,4,4,4,4,4,
		5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,
		6,6,6,6,6,6,6,6,6,6,6,6,6,6,6,6,6,6,6,6,6,6,6,6,6,6,6,6,6,6,
		7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,
		8,8,8,8,8,8,8,8,8,8,8,8,8,8,8,8,8,8,8,8,8,8,8,8,8,8,8,8,8,8,8,
		9,9,9,9,9,9,9,9,9,9,9,9,9,9,9,9,9,9,9,9,9,9,9,9,9,9,9,9,9,9,
		10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,
		11,11,11,11,11,11,11,11,11,11,11,11,11,11,11,11,11,11,11,11,11,11,11,11,11,11,11,11,11,11,
		12,12,12,12,12,12,12,12,12,12,12,12,12,12,12,12,12,12,12,12,12,12,12,12,12,12,12,12,12,12,12,
		1,1,1,1,1,1,1,
	);
	private static $M365MASK;
	private static $MDAY366MASK;
	private static $MDAY365MASK;
	private static $NMDAY366MASK;
	private static $NMDAY365MASK;
	private static $M366RANGE = array(0, 31, 60, 91, 121, 152, 182, 213, 244, 274, 305, 335, 366);
	private static $M365RANGE = array(0, 31, 59, 90, 120, 151, 181, 212, 243, 273, 304, 334, 365);
	private static $WDAYMASK =
	array(
		0,1,2,3,4,5,6,0,1,2,3,4,5,6,0,1,2,3,4,5,6,0,1,2,3,4,5,6,0,1,2,3,4,5,6,0,1,2,3,4,5,6,0,1,2,3,4,5,6,0,1,2,3,4,5,6,0,1,2,3,4,5,6,0,1,2,3,4,5,6,
		0,1,2,3,4,5,6,0,1,2,3,4,5,6,0,1,2,3,4,5,6,0,1,2,3,4,5,6,0,1,2,3,4,5,6,0,1,2,3,4,5,6,0,1,2,3,4,5,6,0,1,2,3,4,5,6,0,1,2,3,4,5,6,0,1,2,3,4,5,6,
		0,1,2,3,4,5,6,0,1,2,3,4,5,6,0,1,2,3,4,5,6,0,1,2,3,4,5,6,0,1,2,3,4,5,6,0,1,2,3,4,5,6,0,1,2,3,4,5,6,0,1,2,3,4,5,6,0,1,2,3,4,5,6,0,1,2,3,4,5,6,
		0,1,2,3,4,5,6,0,1,2,3,4,5,6,0,1,2,3,4,5,6,0,1,2,3,4,5,6,0,1,2,3,4,5,6,0,1,2,3,4,5,6,0,1,2,3,4,5,6,0,1,2,3,4,5,6,0,1,2,3,4,5,6,0,1,2,3,4,5,6,
		0,1,2,3,4,5,6,0,1,2,3,4,5,6,0,1,2,3,4,5,6,0,1,2,3,4,5,6,0,1,2,3,4,5,6,0,1,2,3,4,5,6,0,1,2,3,4,5,6,0,1,2,3,4,5,6,0,1,2,3,4,5,6,0,1,2,3,4,5,6,
		0,1,2,3,4,5,6,0,1,2,3,4,5,6,0,1,2,3,4,5,6,0,1,2,3,4,5,6,0,1,2,3,4,5,6
	);

	// DRL SOme of these can't be private as they're used in the static methods declared 
	// at the bottom of this file.
	var $rrule = NULL;
	var $lastyear = NULL;
	var $lastmonth = NULL;
	var $yearlen = NULL;
	var $nextyearlen = NULL;
	var $yearordinal = NULL;
	var $yearweekday = NULL;
	var $mmask = NULL;
	var $mrange = NULL;
	var $mdaymask = NULL;
	var $nmdaymask = NULL;
	var $wdaymask = NULL;
	var $wnomask = NULL;
	var $nwdaymask = NULL;
	var $eastermask = NULL;
	
	// ===================================================================
	//
	//	Implementation.
	//
	// ===================================================================
	
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
	
	function __construct($rrule)
	{
      if (!RecurrenceRuleIteratorInfo::$M365MASK)
      {
   		RecurrenceRuleIteratorInfo::$M365MASK = RecurrenceRuleIteratorInfo::$M366MASK; array_splice(RecurrenceRuleIteratorInfo::$M365MASK, 59, 1);
   		$M29 = array(1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29);
   		$M30 = array(1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30);
   		$M31 = array(1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31);
   		RecurrenceRuleIteratorInfo::$MDAY366MASK = array_merge($M31,$M29,$M31,$M30,$M31,$M30,$M31,$M31,$M30,$M31,$M30,$M31,array(1,2,3,4,5,6,7));
   		RecurrenceRuleIteratorInfo::$MDAY365MASK = RecurrenceRuleIteratorInfo::$MDAY366MASK; array_splice(RecurrenceRuleIteratorInfo::$MDAY365MASK, 59, 1);
   		$M29 = array(-29,-28,-27,-26,-25,-24,-23,-22,-21,-20,-19,-18,-17,-16,-15,-14,-13,-12,-11,-10,-9,-8,-7,-6,-5,-4,-3,-2,-1);
   		$M30 = array(-30,-29,-28,-27,-26,-25,-24,-23,-22,-21,-20,-19,-18,-17,-16,-15,-14,-13,-12,-11,-10,-9,-8,-7,-6,-5,-4,-3,-2,-1);
   		$M31 = array(-31,-29,-28,-27,-26,-25,-24,-23,-22,-21,-20,-19,-18,-17,-16,-15,-14,-13,-12,-11,-10,-9,-8,-7,-6,-5,-4,-3,-2,-1);
   		RecurrenceRuleIteratorInfo::$NMDAY366MASK = array_merge($M31,$M29,$M31,$M30,$M31,$M30,$M31,$M31,$M30,$M31,$M30,$M31,array(-31,-29,-28,-27,-26,-25,-24));
   		RecurrenceRuleIteratorInfo::$NMDAY365MASK = RecurrenceRuleIteratorInfo::$NMDAY366MASK; array_splice(RecurrenceRuleIteratorInfo::$NMDAY365MASK, 31, 1);
      }
      
		$this->rrule = $rrule;
	}
	
	function __destruct()
	{
	}
	
	function rebuild($year, $month)
	{
		// Every mask is 7 days longer to handle cross-year weekly periods.
		$rr = $this->rrule;
		if ($year != $this->lastyear)
		{
			$this->yearlen = 365+DateAndTime::IsLeapYear($year);
			$this->nextyearlen = 365+DateAndTime::IsLeapYear($year+1);
			$firstyday = new DateAndTime($year, 1, 1);
			$this->yearordinal = $firstyday->ToOrdinalDay();
			$this->yearweekday = ($firstyday->DayOfWeek() + 6) % 7;	// convert to 0 as Monday
			
			$wday = new DateAndTime($year, 1, 1);
			$wday = ($wday->DayOfWeek() + 6) % 7;	// convert to 0 as Monday
			
			if ($this->yearlen == 365)
			{
				$this->mmask = &RecurrenceRuleIteratorInfo::$M365MASK;
				$this->mdaymask = &RecurrenceRuleIteratorInfo::$MDAY365MASK;
				$this->nmdaymask = &RecurrenceRuleIteratorInfo::$NMDAY365MASK;
				$this->mrange = &RecurrenceRuleIteratorInfo::$M365RANGE;
			}
			else
			{
				$this->mmask = &RecurrenceRuleIteratorInfo::$M366MASK;
				$this->mdaymask = &RecurrenceRuleIteratorInfo::$MDAY366MASK;
				$this->nmdaymask = &RecurrenceRuleIteratorInfo::$NMDAY366MASK;
				$this->mrange = &RecurrenceRuleIteratorInfo::$M366RANGE;
			}
			$this->wdaymask = RecurrenceRuleIteratorInfo::$WDAYMASK;	// make a copy
			array_splice($this->wdaymask, 0, $wday);
			
			if (!$rr->ByWeekNo())
			{
				$this->wnomask = NULL;
			}
			else
			{
				$this->wnomask = array();
				for ($i = 0; $i < $this->yearlen+7; $i++)
				{
					array_push($this->wnomask, 0);
				}
				#no1wkst = firstwkst = $this->{wdaymask.index($rr->{wkst)
				$no1wkst = (7 - $this->yearweekday + $rr->Wkst()) % 7;
				$firstwkst = $no1wkst;
				$wyearlen;
				if ($no1wkst >= 4)
				{
					$no1wkst = 0;
					// Number of days in the year, plus the days we got
					// from last year.
					$wyearlen = $this->yearlen + ($this->yearweekday - $rr->Wkst()) % 7;
				}
				else
				{
					// Number of days in the year, minus the days we
					// left in last year.
					$wyearlen = $this->yearlen - $no1wkst;
				}
				list($div, $mod) = array(Utilities::Div($wyearlen, 7), $wyearlen % 7);
				$numweeks = $div+Utilities::Div($mod, 4);
				foreach ($rr->ByWeekNo() as $n)
				{
					if ($n < 0)
					{
						$n += $numweeks+1;
					}
					if ($n > 0 && $n <= $numweeks)
					{
						$i;
						if ($n > 1)
						{
							$i = $no1wkst+($n-1)*7;
							if ($no1wkst != $firstwkst)
							{
								$i -= 7-$firstwkst;
							}
						}
						else
						{
							$i = $no1wkst;
						}
						for ($j = 0; $j < 7; $j++)
						{
							$this->wnomask[$i] = 1;
							$i += 1;
							if ($this->wdaymask[$i] == $rr->Wkst())
							{
								break;
							}
						}
					}
				}
				if (Utilities::ArrayContains($rr->ByWeekNo(), 1))
				{
					// Check week number 1 of next year as well
					// TODO: Check -numweeks for next year.
					$i = $no1wkst+$numweeks*7;
					if ($no1wkst != $firstwkst)
					{
						$i -= 7-$firstwkst;
					}
					if ($i < $this->yearlen)
					{
						// If week starts in next year, we
						// don't care about it.
						for ($j = 0; $j < 7; $j++)
						{
							$this->wnomask[$i] = 1;
							$i += 1;
							if ($this->wdaymask[$i] == $rr->Wkst())
							{
								break;
							}
						}
					}
				}
				if ($no1wkst)
				{
					// Check last week number of last year as
					// well. If no1wkst is 0, either the year
					// started on week start, or week number 1
					// got days from last year, so there are no
					// days from last year's last week number in
					// this year.
					$lnumweeks;
					if (!Utilities::ArrayContains($rr->ByWeekNo(), -1))
					{
						$lyearweekday = new DateAndTime($year-1, 1, 1);
						$lyearweekday = ($lyearweekday->DayOfWeek() + 6) % 7;	// convert to 0 as Monday
						$lno1wkst = (7-$lyearweekday+$rr->Wkst()) % 7;
						$lyearlen = 365 + DateAndTime::IsLeapYear($year-1);
						if ($lno1wkst >= 4)
						{
							$lno1wkst = 0;
							$lnumweeks = Utilities::Div(52+($lyearlen+($lyearweekday-$rr->Wkst())%7)%7,4);
						}
						else
						{
							$lnumweeks = Utilities::Div(52+($this->yearlen-$no1wkst)%7,4);
						}
					}
					else
					{
						$lnumweeks = -1;
					}
					if (Utilities::ArrayContains($rr->ByWeekNo(), $lnumweeks))
					{
						for ($i = 0; $i < $no1wkst; $i++)
						{
							$this->wnomask[$i] = 1;
						}
					}
				}
			}
		}
		
		if ($rr->ByNWeekday() && ($month != $this->lastmonth || $year != $this->lastyear))
		{
			$ranges = array();
			if ($rr->Frequency() == RecurrenceRuleConstants::$YEARLY)
			{
				if ($rr->ByMonth())
				{
					foreach ($rr->ByMonth() as $month)
					{
						array_push($ranges, array($this->mrange[$month-1], $this->mrange[$month]));
					}
				}
				else
				{
					$ranges = array(array(0, $this->yearlen));
				}
			}
			elseif ($rr->Frequency() == RecurrenceRuleConstants::$MONTHLY)
			{
				$ranges = array(array($this->mrange[$month-1], $this->mrange[$month]));
			}
			if (count($ranges))
			{
				// Weekly frequency won't get here, so we may not
				// care about cross-year weekly periods.
				$this->nwdaymask = array();
				for ($i = 0; $i < $this->yearlen; $i++)
				{
					array_push($this->nwdaymask, 0);
				}
				foreach ($ranges as $range)
				{
					list($first, $last) = $range;
					$last -= 1;
					foreach ($rr->ByNWeekday() as $wd)
					{
						list($wday, $n) = $wd;
						$i;
						if ($n < 0)
						{
							$i = $last+($n+1)*7;
							$i -= ($this->wdaymask[$i]-$wday)%7;
						}
						else
						{
							$i = $first+($n-1)*7;
							$i += (7-$this->wdaymask[$i]+$wday)%7;
						}
						if ($i >= $first && $i <= $last)
						{
							$this->nwdaymask[$i] = 1;
						}
					}
				}
			}
		}
		
		if ($rr->ByEaster())
		{
			$this->eastermask = array();
			for ($i = 0; $i < $this->yearlen+7; $i++)
			{
				array_push($this->eastermask, 0);
			}
			$eyday = DateAndTime::GetEaster($year).ToOrdinalDay() - $this->yearordinal;
			foreach ($rr->ByEaster() as $offset)
			{
				$this->eastermask[$eyday+$offset] = 1;
			}
		}
		
		$this->lastyear = $year;
		$this->lastmonth = $month;
	}
	
}

function _CreateNumericArray($start, $end)
{
   $result = array();
   for ($i = $start; $i <= $end; $i++)
      $result[] = $i;
   return $result;
}

function rrii_Formatampm_lower($rrii, $year, $month, $day)
{
	$set = _CreateNumericArray(0,$rrii->yearlen);
	
	return array(&$set, 0, $rrii->yearlen);
}

function rrii_mdayset($rrii, $year, $month, $day)
{
	$set = array();
	for ($i = 0; $i < $rrii->yearlen; $i++)
	{
		array_push($set, NULL);
	}
	$start = $rrii->mrange[$month-1];
	$end = $rrii->mrange[$month];
	for ($i = $start; $i < $end; $i++)
	{
		$set[$i] = $i;
	}
	
	return array(&$set, $start, $end);
}

function rrii_wdayset($rrii, $year, $month, $day)
{
	// We need to handle cross-year weeks here.
	$set = array();
	for ($i = 0; $i < $rrii->yearlen + 7; $i++)
	{
		array_push($set, NULL);
	}
	$i = new DateAndTime($year, $month, $day);
	$i = $i->ToOrdinalDay() - $rrii->yearordinal;
	$start = $i;
	for ($j = 0; $j < 7; $j++)
	{
		$set[$i] = $i;
		$i += 1;
		// This will cross the year boundary, if necessary.
		if ($rrii->wdaymask[$i] == $rrii->rrule->Wkst())
		{
			break;
		}
	}
	
	return array(&$set, $start, $i);
}

function rrii_ddayset($rrii, $year, $month, $day)
{
	$set = array();
	for ($i = 0; $i < $rrii->yearlen; $i++)
	{
		array_push($set, NULL);
	}
	$i = new DateAndTime($year, $month, $day);
	$i = $i->ToOrdinalDay() - $rrii->yearordinal;
	$set[$i] = $i;
	
	return array(&$set, $i, $i+1);
}

function rrii_htimeset($rrii, $hour, $minute, $second)
{
	$set = array();
	$rr = $rrii->rrule;
	if (defined($rr->ByMinute()))			// DRL ADDED
	{
		foreach ($rr->ByMinute() as $minute)
		{
			if (defined($rr->BySecond()))			// DRL ADDED
			{
				foreach ($rr->BySecond() as $second)
				{
					array_push($set, new DateAndTime(NULL, NULL, NULL, $hour, $minute, $second, 0, $rr->TzInfo()));
				}
			}
		}
	}
	usort($set, array('DateAndTime', 'Compare'));
	
	return $set;
}

function rrii_mtimeset($rrii, $hour, $minute, $second)
{
	$set = array();
	$rr = $rrii->rrule;
	if (defined($rr->BySecond()))			// DRL ADDED
	{
		foreach ($rr->BySecond() as $second)
		{
			array_push($set, new DateAndTime(NULL, NULL, NULL, $hour, $minute, $second, 0, $rr->TzInfo()));
		}
	}
	usort($set, array('DateAndTime', 'Compare'));
	
	return $set;
}

function rrii_stimeset($rrii, $hour, $minute, $second)
{
	$rr = $rrii->rrule;
	$set = array(new DateAndTime(NULL, NULL, NULL, $hour, $minute, $second, 0, $rr->TzInfo()));
	
	return $set;
}

?>

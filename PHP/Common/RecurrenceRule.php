<?php

// ========================================================================
//        Copyright (c) 2010 Dominique Lacerte, All Rights Reserved.
// 
// Redistribution and use in source and binary forms are prohibited without 
//   prior written consent from Dominique Lacerte (internet@lacerte.org).
// ========================================================================

require_once(dirname(__FILE__) . '/DateAndTime.php');
require_once(dirname(__FILE__) . '/Log.php');
require_once(dirname(__FILE__) . '/iCalEvent.php');
require_once(dirname(__FILE__) . '/Utilities.php');
require_once(dirname(__FILE__) . '/RecurrenceRuleConstants.php');
require_once(dirname(__FILE__) . '/RecurrenceRuleIterator.php');
require_once(dirname(__FILE__) . '/RecurrenceRuleIteratorInfo.php');
require_once(dirname(__FILE__) . '/RecurrenceRuleParser.php');
require_once(dirname(__FILE__) . '/RecurrenceRuleSet.php');

class RecurrenceRule
{
	private static $Debug = 1;			// set to 1 in order to perform some extra checking to help catch errors
	
	private static $FirstWeekDay = 6;	// 0 is Monday here, our DateAndTime module uses 0 is Sunday so we'll use that
   
   private $dtstart = NULL;
   private $tzinfo = NULL;
	private $cache = NULL;
	private $freq = NULL;
	private $interval = NULL;
	private $wkst = NULL;
	private $count = NULL;
	private $until = NULL;
//	private $weekday = NULL;
	private $bysetpos = NULL;
	private $bymonth = NULL;
	private $byyearday = NULL;
	private $byeaster = NULL;
	private $bymonthday = NULL;
	private $byweekno = NULL;
	private $byweekday = NULL;
	private $bynweekday = NULL;   // DRL Added
	private $byhour = NULL;
	private $byminute = NULL;
	private $bysecond = NULL;
//	private $len = NULL;

    /**
	 * SPL-compatible autoloader.
	 *
     * @param string $className Name of the class to load.
     *
     * @return boolean
    public static function autoload($className)
    {
        if ($className != 'RecurrenceRule')
		{
            return false;
		}
        return include str_replace('_', '/', $className) . '.php';
    }
     */

	function __construct($dtstart, $cache, $rrkwargs, $tzinfo=NULL)
	{
      if ($dtstart && !$dtstart->HasDate()) $dtstart = NULL;   // simplify some logic
      
		$this->dtstart = $dtstart;
         $this->tzinfo = $tzinfo;
		$this->cache = $cache;
		if (!empty($rrkwargs['freq'])) $this->freq = $rrkwargs['freq'];
		if (!empty($rrkwargs['interval'])) $this->interval = $rrkwargs['interval'];
		if (!empty($rrkwargs['count'])) $this->count = $rrkwargs['count'];
		if (!empty($rrkwargs['until'])) $this->until = $rrkwargs['until'];
//		if (!empty($rrkwargs['weekday'])) $this->weekday = $rrkwargs['weekday'];
		if (!empty($rrkwargs['bysetpos'])) $this->bysetpos = $rrkwargs['bysetpos'];
		if (!empty($rrkwargs['bymonth'])) $this->bymonth = $rrkwargs['bymonth'];
		if (!empty($rrkwargs['byyearday'])) $this->byyearday = $rrkwargs['byyearday'];
		if (!empty($rrkwargs['byeaster'])) $this->byeaster = $rrkwargs['byeaster'];
		if (!empty($rrkwargs['bymonthday'])) $this->bymonthday = $rrkwargs['bymonthday'];
		if (!empty($rrkwargs['byweekno'])) $this->byweekno = $rrkwargs['byweekno'];
		if (!empty($rrkwargs['byweekday'])) $this->byweekday = $rrkwargs['byweekday'];
		if (!empty($rrkwargs['byhour'])) $this->byhour = $rrkwargs['byhour'];
		if (!empty($rrkwargs['byminute'])) $this->byminute = $rrkwargs['byminute'];
		if (!empty($rrkwargs['bysecond'])) $this->bysecond = $rrkwargs['bysecond'];
	}
	
	function __destruct()
	{
	}

	function ToString()
	{
		if (empty($this->freq) || $this->freq == RecurrenceRuleConstants::$NO_RECURRENCE)
		{
			return NULL;
		}
		
		$result = "FREQ=" . RecurrenceRuleConstants::$Frequency[$this->freq];
		if (!empty($this->count))
		{
			$result .= ";COUNT=" . $this->count;
		}
		if (!empty($this->interval))
		{
			$result .= ";INTERVAL=" . $this->interval;
		}
		if (!empty($this->wkst))
		{
			$result .= ";WKST=" . RecurrenceRuleConstants::$Weekdays[$this->wkst];
		}
		if (!empty($this->until))
		{
			$result .= ";UNTIL=" . $this->until->ToFormat(DateAndTime::$ISO8601BasicFormat);
		}
		if (!empty($this->bymonth))
		{
			$result .= ";BYMONTH=" . join(',',$this->bymonth);
		}
		if (!empty($this->byweekno))
		{
			$result .= ";BYWEEKNO=" . join(',',$this->byweekno);
		}
		if (!empty($this->byyearday))
		{
			$result .= ";BYYEARDAY=" . join(',',$this->byyearday);
		}
		$weekday = array();
		if (!empty($this->byweekday))
		{
			foreach ($this->byweekday as $w)
			{
				$wkday = RecurrenceRuleConstants::$Weekdays[$w['weekday']];
				$n = $w['n'];
				if (empty($n))
				{
					array_push($weekday, $wkday);
				}
				else
				{
					array_push($weekday, $n . $wkday);
				}
			}
		}
		if (count($weekday))
		{
			$result .= ";BYDAY=" . join(',',$weekday);
		}
		if (!empty($this->byeaster))
		{
			$result .= ";BYEASTER=" . join(',',$this->byeaster);
		}
		if (!empty($this->bymonthday))
		{
			$result .= ";BYMONTHDAY=" . join(',',$this->bymonthday);
		}
		if (!empty($this->bysetpos))
		{
			$result .= ";BYSETPOS=" . join(',',$this->bysetpos);
		}
		if (!empty($this->byhour))
		{
			$result .= ";BYHOUR=" . join(',',$this->byhour);
		}
		if (!empty($this->byminute))
		{
			$result .= ";BYMINUTE=" . join(',',$this->byminute);
		}
		if (!empty($this->bysecond))
		{
			$result .= ";BYSECOND=" . join(',',$this->bysecond);
		}

      // DRL FIXIT! We are not returning the RDATE, EXRULE and EXDATE values!!
      		
		return "RRULE:" . $result;
	}
	
	function Start()
	{
		return $this->dtstart;
	}
	
	function SetStart($value)
	{
      if ($value && !$value->HasDate()) $value = NULL;   // simplify some logic
		$this->dtstart = $value;
	}
   
   function TzInfo()
   {
      if ($this->tzinfo !== NULL) return $this->tzinfo;
		return (!empty($this->dtstart) && $this->dtstart->HasZone()) ? $this->dtstart->Zone() : 0;
   }
	
	function Cache()
	{
		return $this->cache;
	}
	
	function SetCache($value)
	{
		$this->cache = $value;
	}
	
	function Frequency()
	{
		return $this->freq;
	}
	
	function SetFrequency($value)
	{
		$this->freq = $value;
	}
	
	function Interval()
	{
		return $this->interval;
	}
	
	function SetInterval($value)
	{
		if (!empty($value) && $value == 1) { $value = NULL; }
		$this->interval = $value;
	}
	
	function Count()
	{
		return $this->count;
	}
	
	function SetCount($value)
	{
		if (!empty($value) && $value == 0) { WriteDie("Invalid parameter"); }
		$this->count = $value;
	}
	
	function Until()
	{
		return $this->until;
	}
	
	function SetUntil($value)
	{
		$this->until = $value;
	}
	
	//function Weekday()
	//{
	//	return $this->weekday;
	//}
	//
	//function SetWeekday($value)
	//{
	//	$this->weekday = $value;
	//}
	
	function Wkst()
	{
		return $this->wkst;
	}
	
	function BySetPos()
	{
		return $this->bysetpos;
	}
	
	function SetBySetPos($value)
	{
		if (!empty($value) && count($$value) == 0) { $value = NULL; }
		$this->bysetpos = $value;
	}
	
	function ByMonth()
	{
		return $this->bymonth;
	}
	
	function SetByMonth($value)
	{
		if (!empty($value) && count($value) == 0) { $value = NULL; }
		$this->bymonth = $value;
	}
	
	function ByYearDay()
	{
		return $this->byyearday;
	}
	
	function SetByYearDay($value)
	{
		if (!empty($value) && count($value) == 0) { $value = NULL; }
		$this->byyearday = $value;
	}
	
	function ByEaster()
	{
		return $this->byeaster;
	}
	
	function SetByEaster($value)
	{
		if (!empty($value) && count($value) == 0) { $value = NULL; }
		$this->byeaster = $value;
	}
	
	function ByMonthDay()
	{
		return $this->bymonthday;
	}
	
	function SetByMonthDay($value)
	{
		if (!empty($value) && count($value) == 0) { $value = NULL; }
		$this->bymonthday = $value;
	}
	
	function ByWeekNo()
	{
		return $this->byweekno;
	}
	
	function SetByWeekNo($value)
	{
		if (!empty($value) && count($value) == 0) { $value = NULL; }
		$this->byweekno = $value;
	}
	
	function ByWeekday()
	{
		return $this->byweekday;
	}
	
	function SetByWeekday($value)
	{
		if (!empty($value) && count($value) == 0) { $value = NULL; }
		$this->byweekday = $value;
	}
	
	function ByNWeekday()
	{
		return $this->bynweekday;
	}
	
	function ByHour()
	{
		return $this->byhour;
	}
	
	function SetByHour($value)
	{
		if (!empty($value) && count($value) == 0) { $value = NULL; }
		$this->byhour = $value;
	}
	
	function ByMinute()
	{
		return $this->byminute;
	}
	
	function SetByMinute($value)
	{
		if (!empty($value) && count($value) == 0) { $value = NULL; }
		$this->byminute = $value;
	}
	
	function BySecond()
	{
		return $this->bysecond;
	}
	
	function SetBySecond($value)
	{
		if (!empty($value) && count($value) == 0) { $value = NULL; }
		$this->bysecond = $value;
	}
	
	static function _Log($msg)
	{
	//	print("$msg\r");
		return 1;
	}
	
	function GetIterator()
	{
		// Some local variables to speed things up a bit
		$dtstart = $this->dtstart;
      $tzinfo = $this->TzInfo();
		$freq = $this->freq;
		$interval = $this->interval;
		$wkst = $this->wkst;
		$until = $this->until;
		$bymonth = $this->bymonth;
		$byweekno = $this->byweekno;
		$byyearday = $this->byyearday;
		$byweekday = $this->byweekday;
		$bynweekday = $this->bynweekday;
		$byeaster = $this->byeaster;
		$bymonthday = $this->bymonthday;
		$bysetpos = $this->bysetpos;
		$byhour = $this->byhour;
		$byminute = $this->byminute;
		$bysecond = $this->bysecond;
		
		if (empty($dtstart))
		{
			$dtstart = DateAndTime::Now();
		}
		/*
		elseif (strcmp(get_class($dtstart), "DateAndTime") != 0)
		{
         // DRL FIXIT? Why is this here???
			$dtstart = DateAndTime::FromOrdinalDay($dtstart, $tzinfo);
         WriteCallStack('Passed ordinal start day?');
		}
		if (!empty($until) && strcmp(get_class($until), "DateAndTime") != 0)
		{
         // DRL FIXIT? Why is this here???
			$until = DateAndTime::FromOrdinalDay($until, $tzinfo);
         WriteCallStack('Passed ordinal until day?');
		}
		*/
      assert(strcmp(get_class($dtstart), "DateAndTime") == 0);
      assert($until == NULL || strcmp(get_class($until), "DateAndTime") == 0);
      
		// clear out milliseconds
		if ($dtstart->HasTime())
		{
			$dtstart->SetTime($dtstart->Hour(), $dtstart->Minute(), $dtstart->Second());
		}
		if (!empty($until) && $until->HasTime())
		{
			// clear out milliseconds
			$until->SetTime($until->Hour(), $until->Minute(), $until->Second());
		}
	
		// some defaults if not specified...
		if (empty($interval)) { $interval = 1; }
		if (empty($wkst)) { $wkst = RecurrenceRule::$FirstWeekDay; }
	
		// make sure we have a time zone
		if (!$dtstart->HasZone())
		{
			$dtstart->SetZone($tzinfo);
		}
		if (!empty($until) && !$until->HasZone())
		{
			$until->SetZone($tzinfo);
		}

		list($year, $month, $day, $hour, $minute, $second, $zone) = $dtstart->Extract();
		$weekday = ($dtstart->DayOfWeek() + 6) % 7;		// make Monday = 0
		$yearday = $dtstart->DayOfYear();					// Jan 1st is 1, etc.
		
		if (empty($year))
		{
			WriteDie("Invalid year!");
		}
	
		if (empty($weekday))
		{
			$weekday = RecurrenceRule::$FirstWeekDay;
		}
		elseif (is_array($weekday))
		{
			$weekday = $weekday['weekday'];
		}
		if (!empty($bysetpos) && !is_array($bysetpos))
		{
			$bysetpos = array($bysetpos);
		}
		if (!empty($bysetpos))
		{
			foreach ($bysetpos as $pos)
			{
				if ($pos == 0 || $pos < -366 || $pos > 366)
				{
					WriteError("bysetpos must be between 1 and 366, or between -366 and -1, but got: $pos");
				}
			}
		}
		if (empty($byweekno) && empty($byyearday) && empty($bymonthday) && empty($byweekday) && empty($byeaster))
		{
			if ($freq == RecurrenceRuleConstants::$YEARLY)
			{
				if (empty($bymonth))
				{
					$bymonth = $dtstart->Month();
				}
				$bymonthday = $dtstart->Day();
			}
			elseif ($freq == RecurrenceRuleConstants::$MONTHLY)
			{
				$bymonthday = $dtstart->Day();
			}
			elseif ($freq == RecurrenceRuleConstants::$WEEKLY)
			{
				$byweekday = ($dtstart->DayOfWeek() + 6) % 7;	// convert to 0 as Monday
			}
		}
		if (!empty($bymonth) && !is_array($bymonth))
		{
			$bymonth = array($bymonth);
		}
		if (!empty($byyearday) && !is_array($byyearday))
		{
			$byyearday = array($byyearday);
		}
		if (!empty($byeaster) && !is_array($byeaster))
		{
			$byeaster = array($byeaster);
		}
		$bynmonthday;
		if (empty($bymonthday))
		{
			// DRL I think it's strange that this value is always set to an array whereas for others (such as byeaster) it's NULL when not used
			$bymonthday = array();
			$bynmonthday = array();
		}
		elseif (is_array($bymonthday))
		{
			$p = array();
			$n = array();
			foreach ($bymonthday as $v)
			{
				if ($v < 0)
				{
					array_push($n, $v);
				}
				elseif ($v >= 0)
				{
					array_push($p, $v);
				}
			}
			$bymonthday =& $p;
			$bynmonthday =& $n;
		}
		else
		{
			if ($bymonthday >= 0)
			{
				$bymonthday = array($bymonthday);
				$bynmonthday = array();
			}
			else
			{
				$bymonthday = array();
				$bynmonthday = array($bymonthday);
			}
		}
		if (!empty($byweekno) && !is_array($byweekno))
		{
			$byweekno = array($byweekno);
		}
		if (!empty($byweekday))
		{
			if (!is_array($byweekday))
			{
				// ILA SA-134 Initialize $byweekday in expected array format
				$byweekday = [0 => ['weekday' => $byweekday]];
			}
			$temp = array();
			$bynweekday = array();
			foreach ($byweekday as $wday)
			{
				if (!isset($wday['n']) || $freq > RecurrenceRuleConstants::$MONTHLY)
				{
					array_push($temp, $wday['weekday']);
				}
				else
				{
					array_push($bynweekday, array($wday['weekday'], $wday['n']));
				}
			}
			$byweekday =& $temp;
			if (count($byweekday) == 0)
			{
				$byweekday = NULL;
			}
			if (count($bynweekday) == 0)
			{
				$bynweekday = NULL;
			}
		}
		if (!empty($byhour) && !is_array($byhour))
		{
			$byhour = array($byhour);
		}
		if (!empty($byminute) && !is_array($byminute))
		{
			$byminute = array($byminute);
		}
		if (!empty($bysecond) && !is_array($bysecond))
		{
			$bysecond = array($bysecond);
		}
	
		if (empty($byhour) && $freq < RecurrenceRuleConstants::$HOURLY)
		{
			$byhour = array($dtstart->Hour());
		}
		if (empty($byminute) && $freq < RecurrenceRuleConstants::$MINUTELY)
		{
			$byminute = array($dtstart->Minute());
		}
		if (empty($bysecond) && $freq < RecurrenceRuleConstants::$SECONDLY)
		{
			$bysecond = array($dtstart->Second());
		}
		
		// RecurrenceRuleIteratorInfo will be looking for these updated values...
		// DRL FIXIT! This will affect our ToString() method! Perhaps we should be saving these elsewhere?
		$this->wkst = $wkst;
		$this->bymonth = $bymonth;
		$this->byweekno = $byweekno;
		$this->byyearday = $byyearday;
		$this->byweekday = $byweekday;
		$this->bynweekday = $bynweekday;
		$this->byeaster = $byeaster;
		$this->bymonthday = $bymonthday;
		$this->bysetpos = $bysetpos;
		$this->byhour = $byhour;
		$this->byminute = $byminute;
		$this->bysecond = $bysecond;
	
		$ii = new RecurrenceRuleIteratorInfo($this);
		$ii->rebuild($year, $month);
	
		$iter = new RecurrenceRuleIterator();
		
		$getdayset;
		if ($freq == RecurrenceRuleConstants::$YEARLY)		{ $getdayset = 'rrii_Formatampm_lower'; }
		elseif ($freq == RecurrenceRuleConstants::$MONTHLY)	{ $getdayset = 'rrii_mdayset'; }
		elseif ($freq == RecurrenceRuleConstants::$WEEKLY)	{ $getdayset = 'rrii_wdayset'; }
		elseif ($freq == RecurrenceRuleConstants::$DAILY)	{ $getdayset = 'rrii_ddayset'; }
		elseif ($freq == RecurrenceRuleConstants::$HOURLY)	{ $getdayset = 'rrii_ddayset'; }
		elseif ($freq == RecurrenceRuleConstants::$MINUTELY){ $getdayset = 'rrii_ddayset'; }
		elseif ($freq == RecurrenceRuleConstants::$SECONDLY){ $getdayset = 'rrii_ddayset'; }
		else { WriteDie("Unknown frequency"); }
		
		$timeset;
		$gettimeset;
		
		if ($freq < RecurrenceRuleConstants::$HOURLY)
		{
			$timeset = array();
			foreach ($byhour as $hour)
			{
				foreach ($byminute as $minute)
				{
					foreach ($bysecond as $second)
					{
						array_push($timeset, new DateAndTime(NULL, NULL, NULL, $hour, $minute, $second, 0, $tzinfo));
					}
				}
			}
			usort($timeset, array('DateAndTime', 'Compare'));
		}
		else
		{
			if ($freq == RecurrenceRuleConstants::$HOURLY)		{ $gettimeset = RecurrenceRuleIteratorInfo::htimeset; }
			elseif ($freq == RecurrenceRuleConstants::$MINUTELY){ $gettimeset = RecurrenceRuleIteratorInfo::mtimeset; }
			elseif ($freq == RecurrenceRuleConstants::$SECONDLY){ $gettimeset = RecurrenceRuleIteratorInfo::stimeset; }
			else { WriteDie("Unknown frequency"); }
			
			if (($freq >= RecurrenceRuleConstants::$HOURLY && !empty($byhour) && !Utilities::ArrayContains($byhour, $hour)) ||
				($freq >= RecurrenceRuleConstants::$MINUTELY && !empty($byminute) && !Utilities::ArrayContains($byminute, $minute)) ||
				($freq >= RecurrenceRuleConstants::$SECONDLY && !empty($bysecond) && !Utilities::ArrayContains($bysecond, $second)))
			{
				$timeset = array();
			}
			else
			{
				$timeset = $gettimeset($ii, $hour, $minute, $second);
			}
		}
	
	//$Data::Dumper::Indent = 3;
	#print("ByMonth: " . Data::Dumper->Dump([$bymonth]) . "\r");
	#print("ByMonthDay: " . Data::Dumper->Dump([$bymonthday]) . "\r");
	#print("ByNMonthDay: " . Data::Dumper->Dump([$bynmonthday]) . "\r");
	#print("ByWeekDay: " . Data::Dumper->Dump([$byweekday]) . "\r");
	#print("ByNWeekDay: " . Data::Dumper->Dump([$bynweekday]) . "\r");
	#print("ByHour: " . Data::Dumper->Dump([$byhour]) . "\r");
	#print("ByMinute: " . Data::Dumper->Dump([$byminute]) . "\r");
	#print("BySecond: " . Data::Dumper->Dump([$bysecond]) . "\r");
	#print("MMask: " . Data::Dumper->Dump([$ii->mmask]) . "\r");
	#print("WDayMask: " . Data::Dumper->Dump([$ii->wdaymask]) . "\r");
		$total = 0;
		$count = $this->count;
	// DRL FIXIT! Our DateAndTime module has a limitation that it can't handle dates into 2038 and since the
	// code here goes beyond the end of the year by a few days we'll skip December 2037 too.
	//	while (1)
		while ($year < 2037 || ($year == 2037 && $month < 12))
		{
			// Get dayset with the right frequency
			list($_dayset, $start, $end) = $getdayset($ii, $year, $month, $day);
			$dayset = $_dayset;	// make a copy
			
			// Do the "hard" work ;-)
			$filtered = 0;
			for ($j = $start; $j < $end; $j++)
			{
				$i = $dayset[$j];
				if (($bymonth && !Utilities::ArrayContains($bymonth, $ii->mmask[$i]) && RecurrenceRule::_Log("bymonth")) ||
					($byweekno && !$ii->wnomask[$i] && RecurrenceRule::_Log("byweekno")) ||
					($byweekday && !Utilities::ArrayContains($byweekday, $ii->wdaymask[$i]) && RecurrenceRule::_Log("byweekday")) ||
					($ii->nwdaymask && !$ii->nwdaymask[$i] && RecurrenceRule::_Log("nwdaymask")) ||
					($byeaster && !$ii->eastermask[$i] && RecurrenceRule::_Log("eastermask")) ||
					((count($bymonthday) || count($bynmonthday)) && !Utilities::ArrayContains($bymonthday, $ii->mdaymask[$i]) && !Utilities::ArrayContains($bynmonthday, $ii->nmdaymask[$i]) && RecurrenceRule::_Log("bymonthday")) ||
					($byyearday && (($i < $ii->yearlen && !Utilities::ArrayContains($byyearday, $i+1) && !Utilities::ArrayContains($byyearday, -$ii->yearlen+$i) && RecurrenceRule::_Log("byyearday1")) ||
						($i >= $ii->yearlen && !Utilities::ArrayContains($byyearday, $i+1-$ii->yearlen) && !Utilities::ArrayContains($byyearday, -$ii->nextyearlen+$i-$ii->yearlen) && RecurrenceRule::_Log("byyearday2")))))
				{
					$dayset[$j] = NULL;
					$filtered = 1;
				}
				else
				{
					RecurrenceRule::_Log("Including day $i from entry $j");
				}
			}
			
			// Output results
			if (!empty($bysetpos) && count($bysetpos) && $timeset &&
				count($timeset))	// DRL ADDED
			{
				$poslist = array();
				foreach ($bysetpos as $pos)
				{
					$daypos;
					$timepos;
					if ($pos < 0)
					{
						$daypos = Utilities::Div($pos, count($timeset));
						$timepos = $pos % count($timeset);
					}
					else
					{
						$daypos = Utilities::Div($pos-1, count($timeset));
						$timepos = ($pos-1) % count($timeset);
					}
					$i;
					$time;
					try
					{
						$temp = array();
						for ($k = $start; $k < $end; $k++)
						{
							$x = $dayset[$k];
							if (!empty($x))
							{
								array_push($temp, $x);
							}
						}
                  $i = Utilities::ArrayItem($temp, $daypos);
                  $time = Utilities::ArrayItem($timeset, $timepos);
						
						
						$date = DateAndTime::FromOrdinalDay($ii->yearordinal + $i, $tzinfo);
						$date->SetTime($time->Time());
						if (!Utilities::ArrayContains($poslist, $date))
						{
							array_push($poslist, $date->Copy());	// have to copy as we're changing the same instance above
						}
					}
					catch (Exception $e)
					{
						WriteError("Exception!");	// DRL FIXIT???
					}
				}
				usort($poslist, array('DateAndTime', 'Compare'));
				foreach ($poslist as $res)
				{
					if ($until && DateAndTime::GreaterThan($res, $until, true))   // DRL FIXIT? Shouldn't have to ignore missing parts (time)?
					{
						$this->len = $total;
						return $iter;
					}
					elseif ($res >= $dtstart)
					{
						$total += 1;
						$iter->AddDate($res);
						if ($count)
						{
							$count -= 1;
							if (!$count)
							{
								$this->len = $total;
								return $iter;
							}
						}
					}
				}
			}
			else
			{
				for ($j = $start; $j < $end; $j++)
				{
					$i = $dayset[$j];
					if (!empty($i))
					{
						$date = DateAndTime::FromOrdinalDay($ii->yearordinal + $i, $tzinfo);
						foreach ($timeset as $time)
						{
							$date->SetTime($time->Time());
							if ($until && DateAndTime::GreaterThan($date, $until, true))    // DRL FIXIT? Shouldn't have to ignore missing parts (time)?
							{
								$this->len = $total;
								return $iter;
							}
							elseif (DateAndTime::GreaterThanOrEqual($date, $dtstart, true)) // DRL FIXIT? Shouldn't have to ignore missing parts (time)?
							{
								$total += 1;
								$iter->AddDate($date->Copy());	// have to copy as we're changing the same instance above
								if ($count)
								{
									$count -= 1;
									if (!$count)
									{
										$this->len = $total;
										return $iter;
									}
								}
							}
						}
					}
				}
			}
			
			// Handle frequency and interval
			$fixday = 0;
			if ($freq == RecurrenceRuleConstants::$YEARLY)
			{
				$year += $interval;
				if ($year > DateAndTime::$MAXYEAR)
				{
					$this->len = $total;
					return $iter;
				}
				$ii->rebuild($year, $month);
			}
			elseif ($freq == RecurrenceRuleConstants::$MONTHLY)
			{
				$month += $interval;
				if ($month > 12)
				{
					$div = Utilities::Div($month, 12);
					$mod = $month % 12;
					$month = $mod;
					$year += $div;
					if ($month == 0)
					{
						$month = 12;
						$year -= 1;
					}
					if ($year > DateAndTime::$MAXYEAR)
					{
						$this->len = $total;
						return $iter;
					}
				}
				$ii->rebuild($year, $month);
			}
			elseif ($freq == RecurrenceRuleConstants::$WEEKLY)
			{
				if ($wkst > $weekday)
				{
					$day += -($weekday+1+(6-$wkst))+$interval*7;
				}
				else
				{
					$day += -($weekday-$wkst)+$interval*7;
				}
				$weekday = $wkst;
				$fixday = 1;
			}
			elseif ($freq == RecurrenceRuleConstants::$DAILY)
			{
				$day += $interval;
				$fixday = 1;
			}
			elseif ($freq == RecurrenceRuleConstants::$HOURLY)
			{
				if ($filtered)
				{
					// Jump to one iteration before next day
					$hour += Utilities::Div(23-$hour,$interval)*$interval;
				}
				while (1)
				{
					$hour += $interval;
					$div = Utilities::Div($hour, 24);
					$mod = $hour % 24;
					if ($div)
					{
						$hour = $mod;
						$day += $div;
						$fixday = 1;
					}
					if (empty($byhour) || Utilities::ArrayContains($byhour, $hour))
					{
						break;
					}
				}
				$timeset = $gettimeset($ii, $hour, $minute, $second);
			}
			elseif ($freq == RecurrenceRuleConstants::$MINUTELY)
			{
				if ($filtered)
				{
					// Jump to one iteration before next day
					$minute += Utilities::Div(1439-($hour*60+$minute),$interval)*$interval;
				}
				while (1)
				{
					$minute += $interval;
					$div = Utilities::Div($minute, 60);
					$mod = $minute % 60;
					if ($div)
					{
						$minute = $mod;
						$hour += $div;
						$div = Utilities::Div($hour, 24);
						$mod = $hour % 24;
						if ($div)
						{
							$hour = $mod;
							$day += $div;
							$fixday = 1;
							$filtered = 0;
						}
					}
					if ((empty($byhour) || Utilities::ArrayContains($byhour, $hour)) &&
						(empty($byminute) || Utilities::ArrayContains($byminute, $minute)))
					{
						break;
					}
				}
				$timeset = $gettimeset($ii, $hour, $minute, $second);
			}
			elseif ($freq == RecurrenceRuleConstants::$SECONDLY)
			{
				if ($filtered)
				{
					// Jump to one iteration before next day
					$second += Utilities::Div(86399-($hour*3600+$minute*60+$second),$interval)*$interval;
				}
				while (1)
				{
					$second += $interval;
					$div = Utilities::Div($second, 60);
					$mod = $second % 60;
					if ($div)
					{
						$second = $mod;
						$minute += $div;
						$div = Utilities::Div($minute, 60);
						$mod = $minute % 60;
						if ($div)
						{
							$minute = $mod;
							$hour += $div;
							$div = Utilities::Div($hour, 24);
							$mod = $hour % 24;
							if ($div)
							{
								$hour = $mod;
								$day += $div;
								$fixday = 1;
							}
						}
					}
					if ((empty($byhour) || Utilities::ArrayContains($byhour, $hour)) &&
						(empty($byminute) || Utilities::ArrayContains($byminute, $minute)) &&
						(empty($bysecond) || Utilities::ArrayContains($bysecond, $second)))
					{
						break;
					}
				}
				$timeset = $gettimeset($ii, $hour, $minute, $second);
			}
				
			if ($fixday && $day > 28)
			{
				$daysinmonth = DateAndTime::DaysInMonth($year, $month);
				if ($day > $daysinmonth)
				{
					while ($day > $daysinmonth)
					{
						$day -= $daysinmonth;
						$month += 1;
						if ($month == 13)
						{
							$month = 1;
							$year += 1;
							if ($year > DateAndTime::$MAXYEAR)
							{
								$this->len = $total;
								return $iter;
							}
						}
						$daysinmonth = DateAndTime::DaysInMonth($year, $month);
					}
					$ii->rebuild($year, $month);
				}
			}
		}
		
		return $iter;
	}
	
	// only used for testing
	static function _CheckValues($testStartDate, $s, $values)
	{
//		$values = $values;	// make a copy
		$t = RecurrenceRule::FromString($s, $testStartDate);
		if (empty($t))
		{
			WriteDie("Can't parse $s!");
		}
		$s2 = $t->ToString();
		if (strcmp($s2, $s) != 0)
		{
			WriteDie("ToString() value $s2 doesn't match original $s");
		}
		$i = $t->GetIterator();
		if (empty($i))
		{
			WriteDie("Can't create iterator for $s!");
		}
		$length = count($values);
		$count = 0;
		while (count($values))
		{
			$x = array_shift($values);
			$d = $i->GetNext();
			if (!$d)
			{
				if (empty($x))
				{
					break;	// done
				}
				
				WriteDie("Not enough dates returned for $s. Got $count but wanted $length!");
			}
			if (empty($x))
			{
				WriteDie("Too many dates returned for $s!");
			}
			$count++;
			$y = DateAndTime::FromString($x, 0);
			if (empty($y))
			{
				WriteDie("Can't parse date $x for $s!");
			}
			if (DateAndTime::NotEqual($y, $d[0]))
			{
				WriteDie("Date '" . $d[0]->ToString() . "' doesn't match '" . $y->ToString() . "' for $s!");
			}
		}
		$i->Close();
	}
	
	static function FromString($s, $dtstart = NULL, $cache = NULL, $unfold = NULL, 
		$forceset = NULL, $compatible = NULL, $tzinfo = NULL)
	{
		if (empty($s))
		{
			return NULL;
		}
		$s = strtoupper($s);
		chop($s);
		if (strlen($s) == 0)
		{
			return NULL;
		}
      
      if (empty($cache)) { $cache = 0; }
      if (empty($unfold)) { $unfold = 0; }
      if (empty($forceset)) { $forceset = 0; }
      if (empty($compatible)) { $compatible = 0; }
      if ($compatible)
      {
         $forceset = 1;
         $unfold = 1;
      }
      if ($tzinfo === NULL && $dtstart)
         $tzinfo = $dtstart->Zone();
      
      $lines = explode("\n", $s);
		if ($unfold)
		{
			$i = 0;
			while ($i < count($lines))
			{
				$line = $lines[$i];
				chop($line);
				if (strlen($line) == 0)
				{
					array_splice($lines, $i, 1);
				}
				elseif ($i > 0 && strcmp(substr($line, 0, 1), " ") == 0)
				{
					$lines[$i-1] .= substr($line, 1);
					array_splice($lines, $i, 1);
				}
				else
				{
					$i++;
				}
			}
		}
	
		if (!$forceset && count($lines) == 1 && (strpos($s, ':') == FALSE || strcmp(substr($s, 0, 6), 'RRULE:') == 0))
		{
			return new RecurrenceRule($dtstart, $cache, RecurrenceRuleParser::ParseRecurrenceRule($lines[0], $tzinfo), $tzinfo);
		}
		
		$rrulevals = array();
		$rdatevals = array();
		$exrulevals = array();
		$exdatevals = array();
		
		foreach ($lines as $line)
		{
			$name;
			$value;
			
			if (strlen($line) > 0)
			{
				if (strpos($line, ':') == FALSE)
				{
					$name = "RRULE";
					$value = $line;
				}
				else
				{
					list($name, $value) = explode(':', $line, 2);
				}
				$parms = explode(';', $name);
				if (count($parms) == 0)
				{
					WriteError("empty property name");
				}
				$name = $parms[0];
				array_splice($parms, 0, 1);
				if (strcmp($name, "RRULE") == 0)
				{
					foreach ($parms as $parm)
					{
						WriteError("Recurrence rule unsupported RRULE param: $parm");
					}
					array_push($rrulevals, $value);
				}
				elseif (strcmp($name, "RDATE") == 0)
				{
					foreach ($parms as $parm)
					{
						if (strcmp($parm, "VALUE=DATE-TIME") != 0 && strcmp($parm, "VALUE=DATE") != 0)
						{
							WriteError("Recurrence rule unsupported RDATE param: $parm");
						}
					}
					array_push($rdatevals, $value);
				}
				elseif (strcmp($name, "EXRULE") == 0)
				{
					foreach ($parms as $parm)
					{
						WriteError("Recurrence rule unsupported EXRULE param: $parm");
					}
					array_push($exrulevals, $value);
				}
				elseif (strcmp($name, "EXDATE") == 0)
				{
					foreach ($parms as $parm)
					{
						if (strcmp($parm, "VALUE=DATE-TIME") != 0 && strcmp($parm, "VALUE=DATE") != 0)
						{
							WriteError("Recurrence rule unsupported EXDATE param: $parm");
						}
					}
					array_push($exdatevals, $value);
				}
				elseif (strcmp($name, "DTSTART") == 0)
				{
					foreach ($parms as $parm)
					{
						WriteError("Recurrence rule unsupported DTSTART param: $parm");
					}
					$dtstart = DateAndTime::FromString($value, $tzinfo);
				}
				else
				{
					WriteError("Recurrence rule unsupported property: $name");
				}
			}
		}
		
		if ($forceset || count($rrulevals) > 1 || count($rdatevals) > 0 || count($exrulevals) > 0 || count($exdatevals) > 0)
		{
			$set = new RecurrenceRuleSet($cache);
			foreach ($rrulevals as $value)
			{
				$set->AddRRule(new RecurrenceRule($dtstart, NULL, RecurrenceRuleParser::ParseRecurrenceRule($value, $tzinfo), $tzinfo));
			}
			foreach ($rdatevals as $value)
			{
				foreach (explode(',', $value) as $datestr)
				{
					$set->AddRDate(DateAndTime::FromString($datestr, $tzinfo));
				}
			}
			foreach ($exrulevals as $value)
			{
				$set->AddExRule(new RecurrenceRule($dtstart, NULL, RecurrenceRuleParser::ParseRecurrenceRule($value, $tzinfo), $tzinfo));
			}
			foreach ($exdatevals as $value)
			{
				foreach (explode(',', $value) as $datestr)
				{
					$set->AddExDate(DateAndTime::FromString($datestr, $tzinfo));
				}
			}
			if ($compatible && $dtstart)
			{
				$set->AddRDate($dtstart);
			}
			
			return $set;
		}
		
		return new RecurrenceRule($dtstart, $cache, RecurrenceRuleParser::ParseRecurrenceRule($rrulevals[0], $tzinfo));
	}

	static function FromRules($dtstart, $recurrenceRule, $recurrenceDates, $recurrenceExcludeRule, $recurrenceExcludeDates)
	{
      $s = 'RRULE:' . $recurrenceRule;
      if ($recurrenceDates && count($recurrenceDates) > 0)
      {
         foreach ($recurrenceDates as $date)
         {
            $i = strpos($date, ':');
            if ($i === false)
            {
               $s .= "\nRDATE;VALUE=DATE-TIME:$date";
            }
            else
            {
               $type = substr($date, 0, $i);
               $date = substr($date, $i+1);
               $s .= "\nRDATE;VALUE=$type:$date";
            }
         }
      }
      if ($recurrenceExcludeRule)
      {
         $s .= "\nEXRULE:" . $recurrenceExcludeRule;
      }
      if ($recurrenceExcludeDates && count($recurrenceExcludeDates) > 0)
      {
         foreach ($recurrenceExcludeDates as $date)
         {
            $i = strpos($date, ':');
            if ($i === false)
            {
               $s .= "\nEXDATE;VALUE=DATE-TIME:$date";
            }
            else
            {
               $type = substr($date, 0, $i);
               $date = substr($date, $i+1);
               $s .= "\nEXDATE;VALUE=$type:$date";
            }
         }
      }
      return RecurrenceRule::FromString($s, $dtstart);
   }

	static function FromEvent($iCal)
	{
      return RecurrenceRule::FromRules($iCal->Start(), $iCal->RecurrenceRule(), $iCal->RecurrenceDates(), 
         $iCal->RecurrenceExcludeRule(), $iCal->RecurrenceExcludeDates());
   }
}

// ===================================================================
//
//	This is code I use for testing this module. Use as an example.
//
// ===================================================================

$testStartDate = DateAndTime::FromString("2011/05/01 12:01:02 Z");

if (0)
{
	$iCal = iCalEvent::FromString(
"
BEGIN:VCALENDAR
VERSION:2.0
BEGIN:VEVENT
DTSTART:19980501T120000Z
RRULE:FREQ=YEARLY;UNTIL=20020101T000000Z
EXDATE:20000501T120000Z
EXDATE:19990501T120000Z
END:VEVENT
END:VCALENDAR
");

   $i = RecurrenceRule::FromEvent($iCal)->GetIterator();
	while ($d = $i->GetNext())
	{
		print("" . $d[0]->ToString() . "\r");
	}
	$i->Close();
}

if (0)
{
   $iCal = iCalEvent::FromString(
      "
BEGIN:VCALENDAR
VERSION:2.0
BEGIN:VEVENT
DTSTART:19980501T120000Z
RRULE:FREQ=WEEKLY;WKST=WE
EXDATE:20000501T120000Z
EXDATE:19990501T120000Z
END:VEVENT
END:VCALENDAR
");

   $i = RecurrenceRule::FromEvent($iCal)->GetIterator();
   $d = $i->GetNext();
   assert($d[0]->ToString() === '1998/05/01 12:00:00 +0:00');
   //echo ("" . $d[0]->ToString() . PHP_EOL);
   $d = $i->GetNext();
   assert($d[0]->ToString() === '1998/05/08 12:00:00 +0:00');
   //echo ("" . $d[0]->ToString() . PHP_EOL);
   $d = $i->GetNext();
   assert($d[0]->ToString() === '1998/05/15 12:00:00 +0:00');
   //echo ("" . $d[0]->ToString() . PHP_EOL);

   exit;
   while ($d = $i->GetNext())
   {
      echo ("" . $d[0]->ToString() . PHP_EOL);
   }
   $i->Close();
}

if (0)
{
	RecurrenceRule::_CheckValues($testStartDate, "RRULE:FREQ=YEARLY;UNTIL=20180101T000000Z\nEXDATE:20130501T120102Z",
		array(
		"2011/05/01 12:01:02",
		"2012/05/01 12:01:02",
		"2014/05/01 12:01:02",
		"2015/05/01 12:01:02",
		"2016/05/01 12:01:02",
		"2017/05/01 12:01:02",
		NULL
		));
   RecurrenceRule::_CheckValues($testStartDate, "RRULE:FREQ=YEARLY;UNTIL=20180101T000000Z",
		array(
		"2011/05/01 12:01:02",
		"2012/05/01 12:01:02",
		"2013/05/01 12:01:02",
		"2014/05/01 12:01:02",
		"2015/05/01 12:01:02",
		"2016/05/01 12:01:02",
		"2017/05/01 12:01:02",
		NULL
		));
   RecurrenceRule::_CheckValues($testStartDate, "RRULE:FREQ=MONTHLY;COUNT=8;BYDAY=MO,TU,WE,TH,FR;BYSETPOS=-1",
		array(
		"2011/05/31 12:01:02",
		"2011/06/30 12:01:02",
		"2011/07/29 12:01:02",
		"2011/08/31 12:01:02",
		"2011/09/30 12:01:02",
		"2011/10/31 12:01:02",
		"2011/11/30 12:01:02",
		"2011/12/30 12:01:02",
		NULL
		));
   RecurrenceRule::_CheckValues($testStartDate, "RRULE:FREQ=YEARLY;INTERVAL=2;BYMONTH=1;BYDAY=SU;BYHOUR=8,9;BYMINUTE=30",
		array(
		"2013/01/06 08:30:02",
		"2013/01/06 09:30:02",
		"2013/01/13 08:30:02",
		"2013/01/13 09:30:02",
		"2013/01/20 08:30:02",
		"2013/01/20 09:30:02",
		"2013/01/27 08:30:02",
		"2013/01/27 09:30:02",
		"2015/01/04 08:30:02",
		"2015/01/04 09:30:02",
		));
   RecurrenceRule::_CheckValues($testStartDate, "RRULE:FREQ=DAILY;COUNT=10;INTERVAL=2",
		array(
		"2011/05/01 12:01:02",
		"2011/05/03 12:01:02",
		"2011/05/05 12:01:02",
		"2011/05/07 12:01:02",
		"2011/05/09 12:01:02",
		"2011/05/11 12:01:02",
		"2011/05/13 12:01:02",
		"2011/05/15 12:01:02",
		"2011/05/17 12:01:02",
		"2011/05/19 12:01:02",
		NULL,
		));
   RecurrenceRule::_CheckValues($testStartDate, "RRULE:FREQ=MONTHLY;BYDAY=1SU",
		array(
		"2011/05/01 12:01:02",
		"2011/06/05 12:01:02",
		"2011/07/03 12:01:02",
		"2011/08/07 12:01:02",
		"2011/09/04 12:01:02",
		"2011/10/02 12:01:02",
		"2011/11/06 12:01:02",
		"2011/12/04 12:01:02",
		"2012/01/01 12:01:02",
		"2012/02/05 12:01:02",
		"2012/03/04 12:01:02",
		"2012/04/01 12:01:02",
		"2012/05/06 12:01:02",
		"2012/06/03 12:01:02",
		"2012/07/01 12:01:02",
		"2012/08/05 12:01:02",
		"2012/09/02 12:01:02",
		"2012/10/07 12:01:02",
		"2012/11/04 12:01:02",
		"2012/12/02 12:01:02",
		));
   RecurrenceRule::_CheckValues($testStartDate, "RRULE:FREQ=WEEKLY;BYDAY=TU,FR",
		array(
		"2011/05/03 12:01:02",
		"2011/05/06 12:01:02",
		"2011/05/10 12:01:02",
		"2011/05/13 12:01:02",
		"2011/05/17 12:01:02",
		"2011/05/20 12:01:02",
		"2011/05/24 12:01:02",
		"2011/05/27 12:01:02",
		"2011/05/31 12:01:02",
		"2011/06/03 12:01:02",
		"2011/06/07 12:01:02",
		"2011/06/10 12:01:02",
		"2011/06/14 12:01:02",
		"2011/06/17 12:01:02",
		"2011/06/21 12:01:02",
		"2011/06/24 12:01:02",
		"2011/06/28 12:01:02",
		"2011/07/01 12:01:02",
		"2011/07/05 12:01:02",
		"2011/07/08 12:01:02",
		"2011/07/12 12:01:02",
		"2011/07/15 12:01:02",
		"2011/07/19 12:01:02",
		"2011/07/22 12:01:02",
		"2011/07/26 12:01:02",
		"2011/07/29 12:01:02",
		"2011/08/02 12:01:02",
		"2011/08/05 12:01:02",
		"2011/08/09 12:01:02",
		"2011/08/12 12:01:02",
		"2011/08/16 12:01:02",
		"2011/08/19 12:01:02",
		"2011/08/23 12:01:02",
		"2011/08/26 12:01:02",
		"2011/08/30 12:01:02",
		"2011/09/02 12:01:02",
		"2011/09/06 12:01:02",
		"2011/09/09 12:01:02",
		"2011/09/13 12:01:02",
		"2011/09/16 12:01:02",
		"2011/09/20 12:01:02",
		"2011/09/23 12:01:02",
		"2011/09/27 12:01:02",
		"2011/09/30 12:01:02",
		"2011/10/04 12:01:02",
		"2011/10/07 12:01:02",
		"2011/10/11 12:01:02",
		"2011/10/14 12:01:02",
		"2011/10/18 12:01:02",
		"2011/10/21 12:01:02",
		"2011/10/25 12:01:02",
		"2011/10/28 12:01:02",
		"2011/11/01 12:01:02",
		"2011/11/04 12:01:02",
		"2011/11/08 12:01:02",
		"2011/11/11 12:01:02",
		"2011/11/15 12:01:02",
		"2011/11/18 12:01:02",
		"2011/11/22 12:01:02",
		"2011/11/25 12:01:02",
		"2011/11/29 12:01:02",
		"2011/12/02 12:01:02",
		"2011/12/06 12:01:02",
		"2011/12/09 12:01:02",
		"2011/12/13 12:01:02",
		"2011/12/16 12:01:02",
		"2011/12/20 12:01:02",
		"2011/12/23 12:01:02",
		"2011/12/27 12:01:02",
		"2011/12/30 12:01:02",
		"2012/01/03 12:01:02",
		"2012/01/06 12:01:02",
		"2012/01/10 12:01:02",
		"2012/01/13 12:01:02",
		"2012/01/17 12:01:02",
		"2012/01/20 12:01:02",
		"2012/01/24 12:01:02",
		"2012/01/27 12:01:02",
		"2012/01/31 12:01:02",
		"2012/02/03 12:01:02",
		"2012/02/07 12:01:02",
		"2012/02/10 12:01:02",
		"2012/02/14 12:01:02",
		"2012/02/17 12:01:02",
		"2012/02/21 12:01:02",
		"2012/02/24 12:01:02",
		"2012/02/28 12:01:02",
		"2012/03/02 12:01:02",
		"2012/03/06 12:01:02",
		"2012/03/09 12:01:02",
		"2012/03/13 12:01:02",
		"2012/03/16 12:01:02",
		"2012/03/20 12:01:02",
		"2012/03/23 12:01:02",
		"2012/03/27 12:01:02",
		"2012/03/30 12:01:02",
		"2012/04/03 12:01:02",
		"2012/04/06 12:01:02",
		"2012/04/10 12:01:02",
		"2012/04/13 12:01:02",
		"2012/04/17 12:01:02",
		"2012/04/20 12:01:02",
		"2012/04/24 12:01:02",
		"2012/04/27 12:01:02",
		"2012/05/01 12:01:02",
		"2012/05/04 12:01:02",
		"2012/05/08 12:01:02",
		"2012/05/11 12:01:02",
		"2012/05/15 12:01:02",
		"2012/05/18 12:01:02",
		"2012/05/22 12:01:02",
		"2012/05/25 12:01:02",
		"2012/05/29 12:01:02",
		"2012/06/01 12:01:02",
		"2012/06/05 12:01:02",
		"2012/06/08 12:01:02",
		"2012/06/12 12:01:02",
		"2012/06/15 12:01:02",
		"2012/06/19 12:01:02",
		"2012/06/22 12:01:02",
		"2012/06/26 12:01:02",
		"2012/06/29 12:01:02",
		"2012/07/03 12:01:02",
		"2012/07/06 12:01:02",
		"2012/07/10 12:01:02",
		"2012/07/13 12:01:02",
		"2012/07/17 12:01:02",
		"2012/07/20 12:01:02",
		"2012/07/24 12:01:02",
		"2012/07/27 12:01:02",
		"2012/07/31 12:01:02",
		"2012/08/03 12:01:02",
		"2012/08/07 12:01:02",
		"2012/08/10 12:01:02",
		"2012/08/14 12:01:02",
		"2012/08/17 12:01:02",
		"2012/08/21 12:01:02",
		"2012/08/24 12:01:02",
		"2012/08/28 12:01:02",
		"2012/08/31 12:01:02",
		"2012/09/04 12:01:02",
		"2012/09/07 12:01:02",
		"2012/09/11 12:01:02",
		"2012/09/14 12:01:02",
		"2012/09/18 12:01:02",
		"2012/09/21 12:01:02",
		"2012/09/25 12:01:02",
		"2012/09/28 12:01:02",
		"2012/10/02 12:01:02",
		"2012/10/05 12:01:02",
		"2012/10/09 12:01:02",
		"2012/10/12 12:01:02",
		"2012/10/16 12:01:02",
		"2012/10/19 12:01:02",
		"2012/10/23 12:01:02",
		"2012/10/26 12:01:02",
		"2012/10/30 12:01:02",
		"2012/11/02 12:01:02",
		"2012/11/06 12:01:02",
		"2012/11/09 12:01:02",
		"2012/11/13 12:01:02",
		"2012/11/16 12:01:02",
		"2012/11/20 12:01:02",
		"2012/11/23 12:01:02",
		"2012/11/27 12:01:02",
		"2012/11/30 12:01:02",
		"2012/12/04 12:01:02",
		"2012/12/07 12:01:02",
		"2012/12/11 12:01:02",
		"2012/12/14 12:01:02",
		"2012/12/18 12:01:02",
		"2012/12/21 12:01:02",
		"2012/12/25 12:01:02",
		"2012/12/28 12:01:02",
		"2013/01/01 12:01:02",
		"2013/01/04 12:01:02",
		"2013/01/08 12:01:02",
		"2013/01/11 12:01:02",
		"2013/01/15 12:01:02",
		"2013/01/18 12:01:02",
		"2013/01/22 12:01:02",
		"2013/01/25 12:01:02",
		"2013/01/29 12:01:02",
		"2013/02/01 12:01:02",
		"2013/02/05 12:01:02",
		"2013/02/08 12:01:02",
		"2013/02/12 12:01:02",
		"2013/02/15 12:01:02",
		"2013/02/19 12:01:02",
		"2013/02/22 12:01:02",
		"2013/02/26 12:01:02",
		"2013/03/01 12:01:02",
		"2013/03/05 12:01:02",
		"2013/03/08 12:01:02",
		"2013/03/12 12:01:02",
		"2013/03/15 12:01:02",
		"2013/03/19 12:01:02",
		"2013/03/22 12:01:02",
		"2013/03/26 12:01:02",
		"2013/03/29 12:01:02",
		"2013/04/02 12:01:02",
		"2013/04/05 12:01:02",
		"2013/04/09 12:01:02",
		"2013/04/12 12:01:02",
		"2013/04/16 12:01:02",
		"2013/04/19 12:01:02",
		"2013/04/23 12:01:02",
		"2013/04/26 12:01:02",
		"2013/04/30 12:01:02",
		"2013/05/03 12:01:02",
		"2013/05/07 12:01:02",
		"2013/05/10 12:01:02",
		"2013/05/14 12:01:02",
		"2013/05/17 12:01:02",
		"2013/05/21 12:01:02",
		"2013/05/24 12:01:02",
		"2013/05/28 12:01:02",
		"2013/05/31 12:01:02",
		"2013/06/04 12:01:02",
		"2013/06/07 12:01:02",
		"2013/06/11 12:01:02",
		"2013/06/14 12:01:02",
		"2013/06/18 12:01:02",
		"2013/06/21 12:01:02",
		"2013/06/25 12:01:02",
		"2013/06/28 12:01:02",
		"2013/07/02 12:01:02",
		"2013/07/05 12:01:02",
		"2013/07/09 12:01:02",
		"2013/07/12 12:01:02",
		"2013/07/16 12:01:02",
		"2013/07/19 12:01:02",
		"2013/07/23 12:01:02",
		"2013/07/26 12:01:02",
		"2013/07/30 12:01:02",
		"2013/08/02 12:01:02",
		"2013/08/06 12:01:02",
		"2013/08/09 12:01:02",
		"2013/08/13 12:01:02",
		"2013/08/16 12:01:02",
		"2013/08/20 12:01:02",
		"2013/08/23 12:01:02",
		"2013/08/27 12:01:02",
		"2013/08/30 12:01:02",
		"2013/09/03 12:01:02",
		"2013/09/06 12:01:02",
		"2013/09/10 12:01:02",
		"2013/09/13 12:01:02",
		"2013/09/17 12:01:02",
		"2013/09/20 12:01:02",
		"2013/09/24 12:01:02",
		"2013/09/27 12:01:02",
		"2013/10/01 12:01:02",
		"2013/10/04 12:01:02",
		"2013/10/08 12:01:02",
		"2013/10/11 12:01:02",
		"2013/10/15 12:01:02",
		"2013/10/18 12:01:02",
		"2013/10/22 12:01:02",
		"2013/10/25 12:01:02",
		"2013/10/29 12:01:02",
		"2013/11/01 12:01:02",
		"2013/11/05 12:01:02",
		"2013/11/08 12:01:02",
		"2013/11/12 12:01:02",
		"2013/11/15 12:01:02",
		"2013/11/19 12:01:02",
		"2013/11/22 12:01:02",
		"2013/11/26 12:01:02",
		"2013/11/29 12:01:02",
		"2013/12/03 12:01:02",
		"2013/12/06 12:01:02",
		"2013/12/10 12:01:02",
		"2013/12/13 12:01:02",
		"2013/12/17 12:01:02",
		"2013/12/20 12:01:02",
		"2013/12/24 12:01:02",
		"2013/12/27 12:01:02",
		"2013/12/31 12:01:02",
		"2014/01/03 12:01:02",
		"2014/01/07 12:01:02",
		"2014/01/10 12:01:02",
		"2014/01/14 12:01:02",
		"2014/01/17 12:01:02",
		"2014/01/21 12:01:02",
		"2014/01/24 12:01:02",
		"2014/01/28 12:01:02",
		"2014/01/31 12:01:02",
		"2014/02/04 12:01:02",
		"2014/02/07 12:01:02",
		"2014/02/11 12:01:02",
		"2014/02/14 12:01:02",
		"2014/02/18 12:01:02",
		"2014/02/21 12:01:02",
		"2014/02/25 12:01:02",
		"2014/02/28 12:01:02",
		"2014/03/04 12:01:02",
		"2014/03/07 12:01:02",
		"2014/03/11 12:01:02",
		"2014/03/14 12:01:02",
		"2014/03/18 12:01:02",
		"2014/03/21 12:01:02",
		"2014/03/25 12:01:02",
		"2014/03/28 12:01:02",
		"2014/04/01 12:01:02",
		"2014/04/04 12:01:02",
		"2014/04/08 12:01:02",
		"2014/04/11 12:01:02",
		"2014/04/15 12:01:02",
		"2014/04/18 12:01:02",
		"2014/04/22 12:01:02",
		"2014/04/25 12:01:02",
		"2014/04/29 12:01:02",
		"2014/05/02 12:01:02",
		"2014/05/06 12:01:02",
		"2014/05/09 12:01:02",
		"2014/05/13 12:01:02",
		"2014/05/16 12:01:02",
		"2014/05/20 12:01:02",
		"2014/05/23 12:01:02",
		"2014/05/27 12:01:02",
		"2014/05/30 12:01:02",
		"2014/06/03 12:01:02",
		"2014/06/06 12:01:02",
		"2014/06/10 12:01:02",
		"2014/06/13 12:01:02",
		"2014/06/17 12:01:02",
		"2014/06/20 12:01:02",
		"2014/06/24 12:01:02",
		"2014/06/27 12:01:02",
		"2014/07/01 12:01:02",
		"2014/07/04 12:01:02",
		"2014/07/08 12:01:02",
		"2014/07/11 12:01:02",
		"2014/07/15 12:01:02",
		"2014/07/18 12:01:02",
		"2014/07/22 12:01:02",
		"2014/07/25 12:01:02",
		"2014/07/29 12:01:02",
		"2014/08/01 12:01:02",
		"2014/08/05 12:01:02",
		"2014/08/08 12:01:02",
		"2014/08/12 12:01:02",
		"2014/08/15 12:01:02",
		"2014/08/19 12:01:02",
		"2014/08/22 12:01:02",
		"2014/08/26 12:01:02",
		"2014/08/29 12:01:02",
		"2014/09/02 12:01:02",
		"2014/09/05 12:01:02",
		"2014/09/09 12:01:02",
		"2014/09/12 12:01:02",
		"2014/09/16 12:01:02",
		"2014/09/19 12:01:02",
		"2014/09/23 12:01:02",
		"2014/09/26 12:01:02",
		"2014/09/30 12:01:02",
		"2014/10/03 12:01:02",
		"2014/10/07 12:01:02",
		"2014/10/10 12:01:02",
		"2014/10/14 12:01:02",
		"2014/10/17 12:01:02",
		"2014/10/21 12:01:02",
		"2014/10/24 12:01:02",
		"2014/10/28 12:01:02",
		"2014/10/31 12:01:02",
		"2014/11/04 12:01:02",
		"2014/11/07 12:01:02",
		"2014/11/11 12:01:02",
		"2014/11/14 12:01:02",
		"2014/11/18 12:01:02",
		"2014/11/21 12:01:02",
		"2014/11/25 12:01:02",
		"2014/11/28 12:01:02",
		"2014/12/02 12:01:02",
		"2014/12/05 12:01:02",
		"2014/12/09 12:01:02",
		"2014/12/12 12:01:02",
		"2014/12/16 12:01:02",
		"2014/12/19 12:01:02",
		"2014/12/23 12:01:02",
		"2014/12/26 12:01:02",
		"2014/12/30 12:01:02",
		"2015/01/02 12:01:02",
		"2015/01/06 12:01:02",
		"2015/01/09 12:01:02",
		"2015/01/13 12:01:02",
		"2015/01/16 12:01:02",
		"2015/01/20 12:01:02",
		"2015/01/23 12:01:02",
		"2015/01/27 12:01:02",
		"2015/01/30 12:01:02",
		"2015/02/03 12:01:02",
		"2015/02/06 12:01:02",
		"2015/02/10 12:01:02",
		"2015/02/13 12:01:02",
		"2015/02/17 12:01:02",
		"2015/02/20 12:01:02",
		"2015/02/24 12:01:02",
		"2015/02/27 12:01:02",
		"2015/03/03 12:01:02",
		"2015/03/06 12:01:02",
		"2015/03/10 12:01:02",
		"2015/03/13 12:01:02",
		"2015/03/17 12:01:02",
		"2015/03/20 12:01:02",
		"2015/03/24 12:01:02",
		"2015/03/27 12:01:02",
		"2015/03/31 12:01:02",
		"2015/04/03 12:01:02",
		"2015/04/07 12:01:02",
		"2015/04/10 12:01:02",
		"2015/04/14 12:01:02",
		"2015/04/17 12:01:02",
		"2015/04/21 12:01:02",
		"2015/04/24 12:01:02",
		"2015/04/28 12:01:02",
		"2015/05/01 12:01:02",
		"2015/05/05 12:01:02",
		"2015/05/08 12:01:02",
		"2015/05/12 12:01:02",
		"2015/05/15 12:01:02",
		"2015/05/19 12:01:02",
		"2015/05/22 12:01:02",
		"2015/05/26 12:01:02",
		"2015/05/29 12:01:02",
		"2015/06/02 12:01:02",
		"2015/06/05 12:01:02",
		"2015/06/09 12:01:02",
		"2015/06/12 12:01:02",
		"2015/06/16 12:01:02",
		"2015/06/19 12:01:02",
		"2015/06/23 12:01:02",
		"2015/06/26 12:01:02",
		"2015/06/30 12:01:02",
		"2015/07/03 12:01:02",
		"2015/07/07 12:01:02",
		"2015/07/10 12:01:02",
		"2015/07/14 12:01:02",
		"2015/07/17 12:01:02",
		"2015/07/21 12:01:02",
		"2015/07/24 12:01:02",
		"2015/07/28 12:01:02",
		"2015/07/31 12:01:02",
		"2015/08/04 12:01:02",
		"2015/08/07 12:01:02",
		"2015/08/11 12:01:02",
		"2015/08/14 12:01:02",
		"2015/08/18 12:01:02",
		"2015/08/21 12:01:02",
		"2015/08/25 12:01:02",
		"2015/08/28 12:01:02",
		"2015/09/01 12:01:02",
		"2015/09/04 12:01:02",
		"2015/09/08 12:01:02",
		"2015/09/11 12:01:02",
		"2015/09/15 12:01:02",
		"2015/09/18 12:01:02",
		"2015/09/22 12:01:02",
		"2015/09/25 12:01:02",
		"2015/09/29 12:01:02",
		"2015/10/02 12:01:02",
		"2015/10/06 12:01:02",
		"2015/10/09 12:01:02",
		"2015/10/13 12:01:02",
		"2015/10/16 12:01:02",
		"2015/10/20 12:01:02",
		"2015/10/23 12:01:02",
		"2015/10/27 12:01:02",
		"2015/10/30 12:01:02",
		"2015/11/03 12:01:02",
		"2015/11/06 12:01:02",
		"2015/11/10 12:01:02",
		"2015/11/13 12:01:02",
		"2015/11/17 12:01:02",
		"2015/11/20 12:01:02",
		"2015/11/24 12:01:02",
		"2015/11/27 12:01:02",
		"2015/12/01 12:01:02",
		"2015/12/04 12:01:02",
		"2015/12/08 12:01:02",
		"2015/12/11 12:01:02",
		"2015/12/15 12:01:02",
		"2015/12/18 12:01:02",
		"2015/12/22 12:01:02",
		"2015/12/25 12:01:02",
		"2015/12/29 12:01:02",
		"2016/01/01 12:01:02",
		));
   RecurrenceRule::_CheckValues($testStartDate, "RRULE:FREQ=WEEKLY;COUNT=5",
      array(
         "2011/05/02 12:01:02",
         "2011/05/09 12:01:02",
         "2011/05/16 12:01:02",
         "2011/05/23 12:01:02",
         "2011/05/30 12:01:02",
      ));
}
?>

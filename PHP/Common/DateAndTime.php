<?php

// ========================================================================
//        Copyright (c) 2010 Dominique Lacerte, All Rights Reserved.
// 
// Redistribution and use in source and binary forms are prohibited without 
//   prior written consent from Dominique Lacerte (internet@lacerte.org).
// ========================================================================

require_once(dirname(__FILE__) . '/Log.php');
require_once(dirname(__FILE__) . '/Utilities.php');

class DateAndTime
{
	static $JULIAN_CALENDAR = 1;
	static $ORTHODOX_CALENDAR = 2;
	static $WESTERN_CALENDAR  = 3;
	
	// DRL FIXIT? Don't know what these should be? They will really affect the RecurrenceRule* modules.
	static $MINYEAR = 1900;
	static $MAXYEAR = 2100; //2037;	// can't handle dates greater than this????
	
	static $DefaultFormat = "%/D %:T %Z";			// 2006/03/04 09:41:03 PST
	static $TimeFormat = "%H:%M:%S";				// 09:41:03
   static $LongDateFormat = "%A, %E %B %Y";		// Saturday, 4 March 2006
   static $LongDate2Format = "%a, %E %b %Y";		// Sat, 4 Mar 2006
	static $ShortDateFormat = "%Y/%m/%d";			// 2006/03/04
	static $LongFormat = "%A, %E %B %Y %:T %Z";		// Saturday, 4 March 2006 09:41:03 PST
	static $LongFormat2 = "%a, %E %b %Y %:T %Z";	// Sat, 4 Mar 2006 09:41:03 PST
	static $LongFormat3 = "%a, %E %b %Y %:T %z";	// Sat, 4 Mar 2006 09:41:03 -0800
	static $ShortFormat = "%/D %:T";				// 2006/03/04 09:41:03
	static $ISO8601ExtendedFormat = "%-DT%T%1Z";
	static $ISO8601DateFormat = "%D";
	static $ISO8601TimeFormat = "%1Z";
	static $ISO8601BasicFormat = "%DT%T%1Z";
	static $ISO8601BasicFormatWithMilliseconds = "%DT%.T%1Z";	// non-standard millisecond specification!

	static $LongFriendlyFormat = "%A, %E %B %Y %l:%M %P";		// Saturday, 4 March 2006 9:41 am
   // (no space below so the am/pm doesn't wrap)
   static $LongFriendlyFormat2 = "%a, %E %b %Y %l:%M %P";		// Sat, 4 Mar 2006 9:41am
	static $ShortFriendlyFormat = "%/D %l:%M%P";	// 2006/03/04 9:41am
	
	static $SecondsPerMinute = 60;
	static $SecondsPerHour = 3600;
	static $SecondsPerDay = 86400;
	static $SecondsPerWeek = 604800;
	
	static $SixtySevenYears = 67;
	static $DaysPerSixtySevenYears = 24405;
	static $DaysFromRataDieToEpoch = 719177;
	
	static $TimeZoneGMT = 0;

	static $Months = array(1=>"Jan", 2=>"Feb", 3=>"Mar", 4=>"Apr", 5=>"May", 6=>"Jun", 
      7=>"Jul", 8=>"Aug", 9=>"Sep", 10=>"Oct", 11=>"Nov", 12=>"Dec");
	static $LongMonths = array(1=>"January", 2=>"February", 3=>"March", 4=>"April", 5=>"May", 6=>"June", 
      7=>"July", 8=>"August", 9=>"September", 10=>"October", 11=>"November", 12=>"December");
	static $DaysOfWeek = array("Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat");
	static $LongDaysOfWeek = array("Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday");

	// ===================================================================
	//
	//	Constants.
	//
	// ===================================================================
	
	private static $Debug = 1;				// set to 1 in order to perform some extra checking to help catch errors
	
	private static $DefaultYear = 1970;		// year to use in storage if none specified, just so comparisons kinda work

	// from -12 to +12 hours from GMT...
	private static $TimeZones =    array(NULL, NULL, "HST", "YST", "PST", "MST", "CST", "EST", "AST", NULL, "FST", NULL, "GMT", "MET", "EET", "IST", NULL, NULL, NULL, NULL, NULL, "KST", NULL, NULL, NULL);
	private static $TimeZonesDST = array(NULL, NULL, NULL, NULL, NULL, "PDT", "MDT", "CDT", "EDT", NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);

	private static $NullDateTimeValues = array(
		'year' => 1970,
		'mon' => 1,
		'mday' => 1,
		'hours' => 0,
		'minutes' => 0,
		'seconds' => 0,
	);
	private static $NullDateTime = null;
	
	// DRL FIXIT! These are not yet supported!
	// %c	MM/DD/YY HH:MM:SS
	// %C	ctime format: Sat Nov 19 21:05:57 1994
	// %D	MM/DD/YY
	// %G	GPS week number (weeks since January 6, 1980)
	// %j	day of the year
	// %n	NEWLINE
	// %o	ornate day of month -- "1st", "2nd", "25th", etc.
	// %q	Quarter number, starting with 1
	// %r	time format: 09:05:57 PM
	// %s	seconds since the Epoch, UCT
	// %t	TAB
	// %U	week number, Sunday as first day of week
	// %w	day of the week, numerically, Sunday == 0
	// %W	week number, Monday as first day of week
	
	private static $SingleCodes =
	array(
		'a' => '_FormatShortDayOfWeek',
		'A' => '_FormatLongDayOfWeek',
		'b' => '_FormatShortMonth',
		'B' => '_FormatLongMonth',
		'd' => '_FormatLongDay',
		'D' => '_FormatDate',
		'e' => '_FormatSpaceDay',
		'E' => '_FormatShortDay',		// non-standard (day without any leading character)
		'h' => '_FormatShortMonth',
		'H' => '_FormatLong24Hour',
		'I' => '_FormatLong12Hour',
		'k' => '_FormatShort24Hour',
		'l' => '_FormatShort12Hour',
		'L' => '_FormatShortNumericMonth',
		'm' => '_FormatLongNumericMonth',
		'M' => '_FormatMinute',
		'p' => '_FormatAMPM',
		'P' => '_Formatampm_lower',
		'R' => '_FormatTimeNoSecColon',
		'S' => '_FormatSecond',
		'T' => '_FormatTime',
		'x' => '_FormatDate',
		'X' => '_FormatTimeColon',
		'y' => '_FormatShortYear',
		'Y' => '_FormatLongYear',
		'Z' => '_FormatZone',
		'z' => '_FormatNumericZone'
	);
	
	
	// DRL FIXIT? These codes are non-standard.
	private static $DoubleCodes =
	array(
		'/D' => '_FormatDateSlashes',
		'-D' => '_FormatDateHyphens',
		':T' => '_FormatTimeColon',
		'.T' => '_FormatTimeWithMillisecond',
		'6S' => '_FormatMillisecond',
		'1Z' => '_FormatCharZone',
		'/Z' => '_FormatIANAZone',
		':z' => '_FormatNumericColonZone'
	);

	private $Date;
	private $Time;
	private $Millisecond;
	private $Zone;
	private $CreatedBy;
	private $HasYear;
	
    /**
	 * SPL-compatible autoloader.
	 *
     * @param string $className Name of the class to load.
     *
     * @return boolean
    public static function autoload($className)
    {
        if ($className != 'DateAndTime')
		{
            return false;
		}
        return include str_replace('_', '/', $className) . '.php';
    }
     */

	function __construct($year = NULL, $month = NULL, $day = NULL, 
		$hour = NULL, $minute = NULL, $second = NULL, $millisecond = NULL, $zone = NULL)
	{
		$this->CreatedBy = "new(" . DateAndTime::_checkNull($year) . ", " . DateAndTime::_checkNull($month) . ", " . DateAndTime::_checkNull($day) . 		// used for debugging only
			", " . DateAndTime::_checkNull($hour) . ", " . DateAndTime::_checkNull($minute) . ", " . DateAndTime::_checkNull($second) . ", " . DateAndTime::_checkNull($millisecond) . ", " . DateAndTime::_checkNull($zone) . ")";
		$this->HasYear = 0;
	
		if (isset($month) && isset($day))
		{
			if (isset($year))
			{
				$this->HasYear = 1;
			}
			else
			{
				$year = DateAndTime::$DefaultYear;
			}
			if ($day < 1 || $day > 31 || $month < 1 || $month > 12 || $year < DateAndTime::$MINYEAR || $year > DateAndTime::$MAXYEAR)
			{
				WriteDie("Date out of range for $this->CreatedBy");
			}
			$this->Date = DateAndTime::_mydategm($day, $month-1, $year-1900);
		}
		else
		{
			$this->Date = NULL;
		}
		if (isset($hour) && isset($minute))
		{
			if (!isset($second))
			{
				$second = 0;
			}
			if ($hour < 0 || $hour > 23 || $minute < 0 || $minute > 59 || $second < 0 || $second > 59)
			{
				WriteDie("Time out of range for $this->CreatedBy");
			}
			$this->Time = DateAndTime::_mytimegm($second, $minute, $hour);
		}
		else
		{
			$this->Time = NULL;
		}
		if (isset($millisecond) && ($millisecond < 0 || $millisecond > 999))
		{
			WriteDie("Millisecond out of range for $this->CreatedBy");
		}
		$this->Millisecond = $millisecond;
		if (isset($zone) && ($zone < -Utilities::Div(DateAndTime::$SecondsPerDay, 2) || $zone > Utilities::Div(DateAndTime::$SecondsPerDay, 2)))
		{
			WriteDie("Zone out of range for $this->CreatedBy");
		}
		$this->Zone = $zone;
	}
	
	function __destruct()
	{
	}

	private static function _checkNull($val)
	{
		if (!isset($val))
			return 'NULL';
		return $val;
	}

	function Copy()
	{
		$result = new DateAndTime();
	
		$result->HasYear = $this->HasYear;
		$result->Date = $this->Date;
		$result->Time = $this->Time;
		$result->Millisecond = $this->Millisecond;
		$result->Zone = $this->Zone;
		$result->CreatedBy = "Copied from: " . $this->CreatedBy;		# used for debugging only
		
		return $result;
	}
	
	function Extract()
	{
		$second = NULL;
		$minute = NULL;
		$hour = NULL;
		$day = NULL;
		$month = NULL;
		$year = NULL;
		$wday = NULL;
		$yday = NULL;
		$isdst = NULL;
		
		if (isset($this->Date))
		{
			list($day, $month, $year, $wday, $yday, $isdst) = DateAndTime::_mygmdate($this->Date);
			$year += 1900;
			$month += 1;
		}
		if (isset($this->Time))
		{
			list($second, $minute, $hour) = DateAndTime::_mygmtime($this->Time);
		}
		if (!$this->HasYear)
		{
			$year = NULL;
		}
	
		return array($year, $month, $day, $hour, $minute, $second, $this->Millisecond, $this->Zone);
	}
	
	function GetAsMilliseconds()
	{
		return ($this->Date * DateAndTime::$SecondsPerDay * 1000) + ($this->Time * 1000) + $this->Millisecond;
	}
	
	private static function _Process($format, $hasValue, $key, $replacement)
	{
		$keyLen = strlen($key);
		$len = strlen($format);
		$i = strpos($format, $key);
		if ($i !== FALSE)
		{
			$c;
			$type = substr($key, -1);
			
			if ($hasValue)
			{
				$format = substr($format, 0, $i) . $replacement . substr($format, $i+$keyLen);
			}
			elseif ($i == 0)
			{
				// it's at the beginning, must be followed by space or T (for time)
				
				if ($i+$keyLen == $len || strcmp(substr($format, $i+$keyLen, 1), 'T') == 0)
				{
					// remove only this item
					$format = substr($format, $keyLen);
				}
				elseif ($i+$keyLen == $len || strcmp($c = substr($format, $i+$keyLen, 1), ' ') == 0 || strcmp($c, 'T') == 0 || strcmp($c, ',') == 0)
				{
					// remove this item plus the seperator
					$format = substr($format, $keyLen+1);
				}
			}
			else
			{
				$c = substr($format, $i-1, 1);
				if ((strcmp($type, 'T') == 0 && strcmp($c, 'T') == 0) ||    // time preceeded by 'T'
               // anything surrounded by space
				   (strcmp($c, ' ') == 0 && ($i+$keyLen == $len || strcmp(substr($format, $i+$keyLen, 1), ' ') == 0)) ||
               strcmp($c, ':') == 0)
				{
					// remove preceeding seperator as well
					$i--;
               $keyLen++;
            }
				$format = substr($format, 0, $i) . substr($format, $i+$keyLen);
			}
		}
		
		return $format;
	}
	
	function ToOrdinalDay()
	{
		$result = $this->ToOrdinal();
		$result = Utilities::Div($result, DateAndTime::$SecondsPerDay);
		return $result;
	}
	
	function ToOrdinal()
	{
		$result = $this->ToEpoch();
		$result += (DateAndTime::$DaysFromRataDieToEpoch * DateAndTime::$SecondsPerDay);
		return $result;
	}
	
	function ToEpoch()
	{
		if (!isset($this->Date))
		{
			return NULL;
		}
		$epoch = $this->Date * DateAndTime::$SecondsPerDay;
		if (isset($this->Time))
		{
			$epoch += $this->Time;
		}
		
		return $epoch;
	}
	
	function ToFormat($format)
	{
		if (!isset($format)) { $format = DateAndTime::$DefaultFormat; }
	
		// we want the special characteristic that when there is no value for an item
		// we assign it an empty string and also gobble one space seperator if found
	
		// zone is done first so it can be removed and the next item (time) can be properly removed too if necessary
		$format = DateAndTime::_Process($format, $this->HasZone(), '%Z', '%Z');
		$format = DateAndTime::_Process($format, $this->HasZone(), '%1Z', '%1Z');
	
		$format = DateAndTime::_Process($format, $this->HasTime(), '%T', '%H%M%S');
		$format = DateAndTime::_Process($format, $this->HasTime(), '%:T', '%H:%M:%S');
		$format = DateAndTime::_Process($format, $this->HasTime(), '%.T', '%H%M%6S');
	
		// this messes up some formats like YYYY-MM-DD so we only want to do it for formats like "2 October 1967"
		$format = DateAndTime::_Process($format, $this->HasYear(), '%Y', '%Y');
	
		$format = DateAndTime::_Process($format, $this->HasDate(), '%D', '%Y%m%d');
		$format = DateAndTime::_Process($format, $this->HasDate(), '%-D', '%Y-%m-%d');
		$format = DateAndTime::_Process($format, $this->HasDate(), '%/D', '%Y/%m/%d');
		
      // remove any of the replacements not covered above if their values are not available
      $remove = array();
      if (!$this->HasZone())
      {
         array_push($remove, 'z');
      }
      if (!$this->HasTime())
      {
         array_push($remove, 'H');
         array_push($remove, 'I');
         array_push($remove, 'k');
         array_push($remove, 'l');
         array_push($remove, 'M');
         array_push($remove, 'p');
         array_push($remove, 'P');
         array_push($remove, 'R');
         array_push($remove, 'S');
         array_push($remove, 'X');
      }
      if (!$this->HasYear())
      {
         array_push($remove, 'Y');
      }
      if (!$this->HasDate())
      {
         array_push($remove, 'a');
         array_push($remove, 'A');
         array_push($remove, 'b');
         array_push($remove, 'B');
         array_push($remove, 'd');
         array_push($remove, 'e');
         array_push($remove, 'E');
         array_push($remove, 'h');
         array_push($remove, 'L');
         array_push($remove, 'm');
         array_push($remove, 'x');
      }
      foreach ($remove as $item)
      {
      	$format = DateAndTime::_Process($format, false, '%' . $item, '');
      }
   	
		$len = strlen($format);
		$result = "";
		$last = 0;
		$i = strpos($format, '%');
		while ($i !== FALSE)
		{
			$result .= substr($format, $last, $i - $last);
			$last = $i + 1;		// skip '%'
			
			$remain = $len - $i;
			$method = null;
			if ($remain >= 2)
			{
				$temp = substr($format, $i+1, 2);
				if (isset(DateAndTime::$DoubleCodes[$temp]))
				{
					$method = DateAndTime::$DoubleCodes[$temp];
					$result .= $this->$method();
					$last += 2;		// skip double code
				}
			}
			if (!isset($method) && $remain >= 1)
			{
				$temp = substr($format, $i+1, 1);
				if (isset(DateAndTime::$SingleCodes[$temp]))
				{
					$method = DateAndTime::$SingleCodes[$temp];
					$result .= $this->$method();
					$last += 1;	// skip single code
				}
			}
			
			$i = strpos($format, '%', $last);
		}
		$result .= substr($format, $last);
		
		return $result;
	}
	
	function ToBinary()
	{
		$result = "$this->Date:$this->Time:$this->Millisecond:$this->Zone";
		if (!$this->HasYear)
		{
			$result .= ":$this->HasYear";
		}
		
		if (DateAndTime::$Debug)
		{
			if (DateAndTime::FromBinary($result) != $this)
			{
				WriteDie("DateAndTime::FromBinary() does not equal DateAndTime::ToBinary() for value: " . $result);
			}
		}
	
		return $result;
	}
	
	function ToString($utilitiesFormat = NULL, $options = NULL)
	{
		if (!isset($utilitiesFormat)) { $utilitiesFormat = Utilities::$ToStringFormatDefault; }
	
		$format;
		if (strcmp($utilitiesFormat, Utilities::$ToStringFormatSerialize) == 0)
		{
			$format = DateAndTime::$ISO8601BasicFormatWithMilliseconds;
		}
		else
		{
			$format = DateAndTime::$DefaultFormat;
		}
	
		$result = $this->ToFormat($format);
		
		if (DateAndTime::$Debug)
		{
			$temp = DateAndTime::FromString($result);
			if (DateAndTime::NotEqual($temp, $this))
			{
				WriteInfo("Original: $result");
				WriteInfo("Date: " . $this->Date);
				WriteInfo("Time: " . $this->Time);
				WriteInfo("Millisecond: " . $this->Millisecond);
				WriteInfo("Zone: " . $this->Zone);
				WriteInfo("HasYear: " . $this->HasYear);
				WriteInfo("Copy: ");
				WriteInfo("Date: " . $temp->Date);
				WriteInfo("Time: " . $temp->Time);
				WriteInfo("Millisecond: " . $temp->Millisecond);
				WriteInfo("Zone: " . $temp->Zone);
				WriteInfo("HasYear: " . $temp->HasYear);
				WriteDie("DateAndTime::FromString() does not equal DateAndTime::ToString() for value '" . $result .
					"' when using format '$format' for string created by " . $this->CreatedBy . ".");
			}
		}
	
		return $result;
	}

	static function FromOrdinalDay($ordinal, $zone = NULL)
	{
		if (is_null($ordinal)) return NULL;
		
		$epoch = $ordinal - DateAndTime::$DaysFromRataDieToEpoch;
		$epoch *= DateAndTime::$SecondsPerDay;
		$ret = DateAndTime::FromEpoch($epoch, $zone);
		$ret->CreatedBy = "FromOrdinalDay($ordinal, $zone)";		// used for debugging only
		return $ret;
	}
	
	static function FromOrdinal($ordinal, $zone = NULL)
	{
		if (is_null($ordinal)) return NULL;
		
		$epoch = $ordinal - (DateAndTime::$DaysFromRataDieToEpoch * DateAndTime::$SecondsPerDay);
		$ret = DateAndTime::FromEpoch($epoch, $zone);
		$ret->CreatedBy = "FromOrdinal($ordinal, $zone)";		// used for debugging only
		return $ret;
	}
	
	static function FromEpoch($epoch, $zone = NULL)
	{
		if (is_null($epoch)) return NULL;
		
		// I use gmdate() because getdate() sometimes returned a 2 hour error
		$str = gmdate('Y m d H i s', $epoch);
		$dt = explode(' ', $str);
		$ret = new DateAndTime($dt[0], $dt[1], $dt[2], $dt[3], $dt[4], $dt[5], 0, $zone);
		$ret->CreatedBy = "FromEpoch($epoch, $zone)";		// used for debugging only
      
		return $ret;
	}

	static function FromEpochMilli($epoch, $zone = NULL)
	{
		if (is_null($epoch)) return NULL;
		
      $epoch2 = Utilities::Div($epoch, 1000);
	   $ret = DateAndTime::FromEpoch($epoch2, $zone);
		$ret->Millisecond = Utilities::Mod($epoch, 1000);
		$ret->CreatedBy = "FromEpochMilli($epoch, $zone)";		// used for debugging only
      
		return $ret;
	}

	function FromBinary($binary)
	{
		list($date, $time, $millisecond, $zone, $hasYear) = split(':', $binary);
		
		$result = new DateAndTime();
	
		if (strlen($date) > 0)
		{
			$result->Date = $date;
			if (strlen($hasYear) > 0)
			{
				$result->HasYear = $hasYear;
			}
			else
			{
				$result->HasYear = 1;
			}
		}
		if (strlen($time) > 0)
		{
			$result->Time = $time;
		}
		if (strlen($millisecond) > 0)
		{
			$result->Millisecond = $millisecond;
		}
		if (strlen($zone) > 0)
		{
			$result->Zone = $zone;
		}
		
		$result->CreatedBy = "FromBinary()";		# used for debugging only
		return $result;
	}

	static function FromString($string, $defaultZone = NULL)
	{
		// DRL FIXIT! The ISO specification indicates that the minutes and seconds are optional,
		// and the last one provided may also contain a fractional portion and that the zone may
		// be specified as an offset instead of a character as well with optional bits. We
		// currently don't support any of this!
		
		// DRL FIXIT! For serialization/deserialization correctness we should be able to
		// deserialize a date and time zone (i.e. only time was null) but we don't!
	
		if (!isset($string)) { return NULL; }
		if (Utilities::IsEmpty($string)) { return new DateAndTime(); }
	
		$originalString = $string;
		
	//	WriteError("Converting date from: $string");
	
		// strip the day of week
		$comma = strpos($string, ", ");
		if ($comma !== FALSE)
		{
			//  could be "Saturday, 4 March 2006 09:41:03 PST" or "October 6, 1990"
			$temp = substr($string, $comma+2);
			if (strlen($temp) == 4)												// check for 4 digit year
			{
				$string = substr($string, 0, $comma) . " " . $temp;	// just remove comma
			}
			else
			{
				$string = $temp;													// remove everything before and including the comma
			}
		}
		
		$string = trim($string);   // the day sometimes has a space before it if it is one digit
	
      // DRL I sometimes got strings with two time zones at the end such as "-0700 (PDT)" so I check 
      // for (PDT) first and also check for the other format below and remove them both, prefering the first one.
		if (substr($string, -5, 1) == '(' && substr($string, -1, 1) == ')')
		{
			// we found the time zone
			$zone = DateAndTime::TimeZoneOffset(substr($string, -4, 3));
         if ($zone !== NULL)
            $defaultZone = $zone;
			
         // strip it
         $lastSpace = strlen($string) - 6;
         while ($lastSpace > 0 && $string[$lastSpace] == ' ')
            $lastSpace--;
			$string = substr($string, 0, $lastSpace+1);
		}

		// look for a time zone and split it out
		$lastSpace = strrpos($string, " ");
		if ($lastSpace === FALSE)
      {
         $len = strlen($string);
         
         // look for "2013-04-18T12:00:00-07:00" format (but avoid catching "2013-04-18")
         if ($len >= 6 && $string[$len-3] != '-' && ($string[$len-6] == '+' || $string[$len-6] == '-'))
            $lastSpace = $len-7;
         // look for "2013-04-18T12:00:00-0700" format
         else if ($len >= 5 && ($string[$len-5] == '+' || $string[$len-5] == '-'))
            $lastSpace = $len-6;
         // look for "2013-04-18T12:00:00G" format
         else if ($len >= 3 && Utilities::IsInteger(substr($string, $len-3, 2)) && Utilities::IsAlphabetic($string[$len-1]))
            $lastSpace = $len-2;
         else
            $lastSpace = -1;
      }
		$zone = substr($string, $lastSpace + 1);
	
		// remove the time zone from the string (check for the formats: Z, GMT, +000, +0:00, +0000, +00:00)
		$sign = substr($zone, 0, 1);
		if ((Utilities::IsAlphabetic($zone) && (strlen($zone) == 1 || strlen($zone) == 3)) ||
			((strcmp($sign, "+") == 0 || strcmp($sign, "-") == 0) && strlen($zone) >= 4 && strlen($zone) <= 6))
		{
			// we found the time zone
			$zone = DateAndTime::TimeZoneOffset($zone);
			
         // strip it
         while ($lastSpace > 0 && $string[$lastSpace] == ' ')
            $lastSpace--;
			$string = substr($string, 0, $lastSpace+1);
		}
		// remove the time zone from the string (check for the single character form: 000Z)
		elseif (Utilities::IsInteger(substr($string, -2, 1)) && Utilities::IsAlphabetic(substr($string, -1, 1)))
		{
			// we found the time zone
			$zone = DateAndTime::TimeZoneOffset(substr($string, -1));
         
         // strip it
			$string = substr($string, 0, -1);
		}
		else
		{
			// no time zone, use default if provided
			
			$zone = $defaultZone;
		}
	
		// look for a am/pm and split it out
      $isPM = false;
      if (strlen($string) >= 2)
      {
         $temp = strtolower(substr($string, -2));
   		if (strcmp($temp, "am") == 0 || strcmp($temp, "pm") == 0)
   		{
            $isPM = strcmp($temp, "pm") == 0;
            
            $len = 2;
            if (strlen($string) >= 3 && substr($string, -3, 1) == " ")
               $len++;
   			$string = substr($string, 0, -$len);
   		}
      }
	
		// split time and date portions
		$lastSpace = strrpos($string, " ");
		if ($lastSpace === FALSE)
		{
			// is this a full time and date with no spaces (19980101T050000)?
			$lastSpace = strrpos($string, "T");
			if ($lastSpace !== FALSE)
			{
				$date = substr($string, 0, $lastSpace);
				$time = substr($string, $lastSpace+1);
	
				if (!Utilities::StringContains($time, ":"))
				{
					// add the colons to the time
					for ($i = 2; $i < 8 && $i < strlen($time); $i += 3)
					{
						$time = substr($time, 0, $i) . ":" . substr($time, $i);
					}
				}
	
				if (!Utilities::StringContains($date, "/") && !Utilities::StringContains($date, "-"))
				{
					// add the separators to the date
					$i = strlen($date) >= 8 ? 4 : 2;
					for (; $i < strlen($date); $i += 3)
					{
						$date = substr($date, 0, $i) . "-" . substr($date, $i);
					}
				}
				
				$string = $date . " " . $time;
		
				$lastSpace = strlen($date);
			}
			elseif (!isset($zone) && !Utilities::StringContains($string, ":") && !Utilities::StringContains($string, "-") && !Utilities::StringContains($string, "/"))
			{
				// if we have something with no separators lets assume it is a date and add separators as necessary
				$i = strlen($string) >= 8 ? 4 : 2;
				for (; $i < strlen($string); $i += 3)
				{
					$string = substr($string, 0, $i) . "-" . substr($string, $i);
				}
			}
		}
	
		// is the last chunk a time or a date?
		if ($lastSpace == FALSE)
		{
			if (Utilities::StringContains($string, ":"))
			{
				$lastSpace = 0;
			}
			else
			{
				$lastSpace = strlen($string);
			}
		}
		else
		{
			// the thing after the last space could be the AM/PM indicator OR a day or year if there is no time portion
			if (Utilities::StringContains(substr($string, $lastSpace+1), ":"))
			{
				$lastSpace++;
			}
			else
			{
				$lastSpace = strlen($string);
			}
		}
		
		$year = NULL;
		$month = NULL;
		$day = NULL;
		$hour = NULL;
		$minute = NULL;
		$second = NULL;
		$millisecond = NULL;
	
		// process a time if specified
		if ($lastSpace < strlen($string))
		{
			$time = substr($string, $lastSpace);
			
   		$temp = explode(":", $time);
         $hour = $temp[0]; 
         $minute = $temp[1]; 
         if (isset($temp[2])) $second = $temp[2];
            
			// apply the result of finding PM above
			if ($isPM && $hour < 12)
			{
				$hour = $hour + 12;
			}
			// seconds may not have been provided
			if (!isset($second))
			{
				$second = 0;
			}
	
			if (strpos($second, '.') !== FALSE)
			{
				list($second, $millisecond) = explode('.', $second);
				
				// DRL Added this for WindowsLive date/time of "2011-06-09T01:18:16.9570000Z"
				if (strlen($millisecond) > 3)
				{
					$millisecond = substr($millisecond, 0, 3);
				}
			}
		}
		else
		{
			$lastSpace = strlen($string);
		}
		
		// process a date if specified
		if ($lastSpace > 1)
		{
			// remove any spaces that were between the date and time
			while ($lastSpace > 0 && strcmp(substr($string, $lastSpace-1, 1), " ") == 0)
			{
				$lastSpace--;
			}
			
			// could be "1984" or "1984/10/02" or "1984-10-02" or "841002" or "19841002" followed by something like "6:00"
			$date = substr($string, 0, $lastSpace);
		
			//parse the date
			$seperator = NULL;
         $t = array(NULL, NULL);
			if (strpos($date, '-') !== FALSE)
			{
				$seperator = '-';
			}
			elseif (strpos($date, '/') !== FALSE)
			{
				$seperator = '/';
			}
			elseif (strpos($date, ' ') !== FALSE)
			{
				$seperator = ' ';
			}
         if ($seperator != NULL)
         {
   			$t = explode($seperator, $date);
         }
         else if (strlen($string) == 4)
         {
            $t[0] = $string;
         }
         else if (strlen($string) == 6)
         {
            $t[0] = substr($string, 0, 2);
            $t[1] = substr($string, 2, 2);
            $t[2] = substr($string, 4, 2);
         }
         else if (strlen($string) == 8)
         {
            $t[0] = substr($string, 0, 4);
            $t[1] = substr($string, 4, 2);
            $t[2] = substr($string, 6, 2);
         }
			$year = $t[0];
			$month = $t[1];
			$day = isset($t[2]) ? $t[2] : NULL;
			$temp = DateAndTime::ParseMonth($year);
			if (!isset($day) && isset($month))
			{
				// if there was no day then we probably have a date with no year as 
            // in "October 2" or "2 October"
				$m = DateAndTime::ParseMonth($month);
				if (isset($m))
				{
					$day = $year;
					$year = NULL;
				}
				else
				{
					$day = $month;
					$month = $year;
					$year = NULL;
				}
			}
			elseif (isset($temp))
			{
				// we could have a date order as in "October 2 1967"
				$temp = $month;
				$month = $year;
				$year = $day;
				$day = $temp;
			}
			else
			{
				// we could have a date order as in "25 Jan 2010" or "dd/mm/yyyy" (or perhaps "mm/dd/yyyy")
				if (strlen($month) == 2 && $month > 12)
				{
					$temp = $month;
					$month = $year;
					$year = $day;
					$day = $temp;
				}
				elseif ($day > 31)
				{
					$temp = $day;
					$day = $year;
					$year = $temp;
				}
			}
			$temp = DateAndTime::ParseMonth($month);
			if (strlen($month) >= 3 && isset($temp))
			{
				$month = $temp;
			}
			if (isset($year))
			{
				if (strlen($year) == 2)
				{
					// for a two digit year we have to guess at what century is meant
					if ($year >= 50)
					{
						$year += 1900;
					}
					else
					{
						$year += 2000;
					}
				}
				elseif (strcmp($year, "0000") == 0)	// special value for year not specified
				{
					$year = NULL;
				}
			}
			if (isset($month) && strcmp($month, "00") == 0)	// special handling for date not specified
			{
				$month = NULL;
			}
		}
		
	//	WriteError("Converted date and time: $year, $month, $day, $hour, $minute, $second, $millisecond");
		$ret = new DateAndTime($year, $month, $day, $hour, $minute, $second, $millisecond, $zone);
		$ret->CreatedBy = "FromString(" . DateAndTime::_checkNull($originalString) . ", " . DateAndTime::_checkNull($defaultZone) . ")";		// used for debugging only
		return $ret;
	}
   
   static function StripDate($dateTime)
   {
      $date = $dateTime->Copy();
      $date->SetDate(NULL);
      return $date;
   }
   
   static function StripTime($dateTime)
   {
      $date = $dateTime->Copy();
      $date->SetTime(NULL);
      return $date;
   }
   
   function Date()
	{
		$day = NULL;
		$month = NULL;
		$year = NULL;
		$wday = NULL;
		$yday = NULL;
		$isdst = NULL;
		
		if (isset($this->Date))
		{
			list($day, $month, $year, $wday, $yday, $isdst) = DateAndTime::_mygmdate($this->Date);
			$year += 1900;
			$month += 1;
			if (!$this->HasYear)
			{
				$year = NULL;
			}
		}
		
		return array($year, $month, $day);
	}
	
	function SetDate($year = NULL, $month = NULL, $day = NULL)
	{
      if (is_array($year))
      {
         list($year, $month, $day) = array_merge($year, array(NULL, NULL, NULL));
      }
      
		$this->HasYear = 0;
		
		if (isset($month) && isset($day))
		{
			if (isset($year))
			{
				$this->HasYear = 1;
			}
			else
			{
				$year = DateAndTime::$DefaultYear;
			}
			$this->Date = DateAndTime::_mydategm($day, $month-1, $year-1900);
		}
		else
		{
			$this->Date = NULL;
		}
	}
	
	function Year()
	{
		$day = NULL;
		$month = NULL;
		$year = NULL;
		$wday = NULL;
		$yday = NULL;
		$isdst = NULL;
		
		if ($this->HasYear)
		{
			list($day, $month, $year, $wday, $yday, $isdst) = DateAndTime::_mygmdate($this->Date);
			$year += 1900;
		}
      
		return $year;
	}
	
	function Month()
	{
		$day = NULL;
		$month = NULL;
		$year = NULL;
		$wday = NULL;
		$yday = NULL;
		$isdst = NULL;
		
		if (isset($this->Date))
		{
			list($day, $month, $year, $wday, $yday, $isdst) = DateAndTime::_mygmdate($this->Date);
			$month += 1;
		}
		
		return $month;
	}
	
	function Day()
	{
		$day = NULL;
		$month = NULL;
		$year = NULL;
		$wday = NULL;
		$yday = NULL;
		$isdst = NULL;
		
		if (isset($this->Date))
		{
			list($day, $month, $year, $wday, $yday, $isdst) = DateAndTime::_mygmdate($this->Date);
		}
		
		return $day;
	}
	
	function Time()
	{
		$millisecond = NULL;
		$second = NULL;
		$minute = NULL;
		$hour = NULL;
		
		if (isset($this->Time))
		{
			list($second, $minute, $hour) = DateAndTime::_mygmtime($this->Time);
	
			if (isset($this->Millisecond))
			{
				$millisecond = $this->Millisecond;
			}
			else
			{
				$millisecond = 0;
			}
		}
		
		return array($hour, $minute, $second, $millisecond);
	}
	
	function SetTime($hour = NULL, $minute = NULL, $second = NULL, $millisecond = NULL)
	{
      if (is_array($hour))
      {
         list($hour, $minute, $second, $millisecond) = array_merge($hour, array(NULL, NULL, NULL, NULL));
      }
		if (isset($hour))
		{
			$this->Time = DateAndTime::_mytimegm($second, $minute, $hour);
		}
		else
		{
			$this->Time = NULL;
		}
		$this->Millisecond = $millisecond;
	}
	
	function Hour()
	{
		$second = NULL;
		$minute = NULL;
		$hour = NULL;
		
		if (isset($this->Time))
		{
			list($second, $minute, $hour) = DateAndTime::_mygmtime($this->Time);
		}
		
		return $hour;
	}
	
	function Minute()
	{
		$second = NULL;
		$minute = NULL;
		$hour = NULL;
		
		if (isset($this->Time))
		{
			list($second, $minute, $hour) = DateAndTime::_mygmtime($this->Time);
		}
		
		return $minute;
	}
	
	function Second()
	{
		$second = NULL;
		$minute = NULL;
		$hour = NULL;
		
		if (isset($this->Time))
		{
			list($second, $minute, $hour) = DateAndTime::_mygmtime($this->Time);
		}
		
		return $second;
	}
	
	function Millisecond()
	{
		if (!isset($this->Time))
		{
			return NULL;
		}
		elseif (isset($this->Millisecond))
		{
			return $this->Millisecond;
		}
		else
		{
			return 0;
		}
	}
	
	function SetMillisecond($millisecond)
	{
		return $this->Millisecond = intVal($millisecond);
	}
	
	function Zone()
	{
		return $this->Zone;
	}
	
	function SetZone($zone = NULL)
	{
		if (isset($zone) && !Utilities::IsInteger($zone))
		{
			WriteDie("Attempting to set time zone of '$zone' when numeric is expected!");
		}
		
		$this->Zone = isset($zone) ? intVal($zone) : NULL;
	}
	
	function HasYear()
	{
		return $this->HasYear;
	}
	
	function HasDate()
	{
		return isset($this->Date) ? 1 : 0;
	}
	
	function HasTime()
	{
		return isset($this->Time) ? 1 : 0;
	}
	
	function HasZone()
	{
		return isset($this->Zone) ? 1 : 0;
	}
	
	function IsDaylightSavings()
	{
		$res = localtime($this->ToEpoch(), true);
		return $res['tm_isdst'] ? 1 : 0;
	}
	
	function DayOfWeek()
	{
		$day = NULL;
		$month = NULL;
		$year = NULL;
		$wday = NULL;
		$yday = NULL;
		$isdst = NULL;
		
		if ($this->HasYear)
		{
			list($day, $month, $year, $wday, $yday, $isdst) = DateAndTime::_mygmdate($this->Date);
		}
	
		return $wday;
	}
	
	function DayOfYear()
	{
		if (!isset($this->Date) || !$this->HasYear)
		{
			return NULL;
		}
		
		$date = $this->Copy();
		$date->SetDate($this->Year(), 1, 1);
		
		return Utilities::Div(DateAndTime::Subtract($this, $date), DateAndTime::$SecondsPerDay) + 1;
	}
	
	function DaysThisMonth()
	{
		if (!isset($this->Date) || !$this->HasYear)
		{
			return NULL;
		}
	
		$year = $this->Year();
		$month = $this->Month()+1;
		if ($month == 13)
		{
			$year++;
			$month = 1;
		}
	
		// use a fixed day (5) in case time zone calculations cause it to wrap a month
		$date1 = DateAndTime::_mydategm(5, $month-1, $year-1900);
		$date2 = $this->Date - $this->Day() + 5;
		return $date1 - $date2;
	}
	
	static function Equal($obj1, $obj2)
	{
      if (is_null($obj1) || is_null($obj2))
      {
         return is_null($obj1) && is_null($obj2);
      }
      if (!is_object($obj1) || !is_object($obj2))
      {
         $obj1 = $obj1;
      }
		if ($obj1->HasYear != $obj2->HasYear ||
			$obj1->HasDate() != $obj2->HasDate() ||
			$obj1->HasTime() != $obj2->HasTime() ||
			$obj1->HasZone() != $obj2->HasZone())
		{
			return 0;
		}
		return DateAndTime::Subtract($obj1, $obj2) == 0;
	}
	
	static function NotEqual($obj1, $obj2)
	{
      if (is_null($obj1) || is_null($obj2))
      {
         return !(is_null($obj1) && is_null($obj2));
      }
      if (!is_object($obj1) || !is_object($obj2))
      {
         WriteCallStack('Non-object passed instead of DateAndTime!');
         assert(0);
         return true;
      }
		if ($obj1->HasYear != $obj2->HasYear ||
			$obj1->HasDate() != $obj2->HasDate() ||
			$obj1->HasTime() != $obj2->HasTime() ||
			$obj1->HasZone() != $obj2->HasZone())
		{
			return 1;
		}
		return DateAndTime::Subtract($obj1, $obj2) != 0;
	}
	
	static function GreaterThan($obj1, $obj2, $ignoreMissingParts=false)
	{
      if (!is_object($obj1) || !is_object($obj2))
      {
         WriteCallStack('Non-object passed instead of DateAndTime!');
         assert(0);
         return false;
      }
		return DateAndTime::Subtract($obj1, $obj2, $ignoreMissingParts) > 0;
	}
	
	static function GreaterThanOrEqual($obj1, $obj2, $ignoreMissingParts=false)
	{
      if (!is_object($obj1) || !is_object($obj2))
      {
         WriteCallStack('Non-object passed instead of DateAndTime!');
         assert(0);
         return false;
      }
		return DateAndTime::Subtract($obj1, $obj2, $ignoreMissingParts) >= 0;
	}
	
	static function LessThan($obj1, $obj2, $ignoreMissingParts=false)
	{
      if (!is_object($obj1) || !is_object($obj2))
      {
         WriteCallStack('Non-object passed instead of DateAndTime!');
         assert(0);
         return false;
      }
		return DateAndTime::Subtract($obj1, $obj2, $ignoreMissingParts) < 0;
	}
	
	static function LessThanOrEqual($obj1, $obj2, $ignoreMissingParts=false)
	{
      if (!is_object($obj1) || !is_object($obj2))
      {
         WriteCallStack('Non-object passed instead of DateAndTime!');
         assert(0);
         return false;
      }
		return DateAndTime::Subtract($obj1, $obj2, $ignoreMissingParts) <= 0;
	}
	
	static function Compare($obj1, $obj2)
	{
      if (!is_object($obj1) || !is_object($obj2))
      {
         WriteCallStack('Non-object passed instead of DateAndTime!');
         assert(0);
         return 0;
      }
      
		$result = 0;
      $time1 = $obj1->ToEpoch();
      $time2 = $obj2->ToEpoch();
		
		// DRL FIXIT! What about HasYear?

      // if either does not have a time then we don't apply the time zone since one
      // of them is the actual day, regardless of the time zone or time
      if (isset($obj1->Time) && isset($obj2->Time))
      {
   		// convert to a common time zone (GMT)
   		$time1 -=  $obj1->HasZone() ? $obj1->Zone() : 0;
   		$time2 -=  $obj2->HasZone() ? $obj2->Zone() : 0;
      }
		
		if (isset($obj1->Date) && isset($obj2->Date))
		{
			$date1 = Utilities::Div($time1, DateAndTime::$SecondsPerDay);
			$date2 = Utilities::Div($time2, DateAndTime::$SecondsPerDay);
			
			if ($date1 < $date2)
				$result = -1;
			elseif ($date1 > $date2)
				$result = 1;
			else
				$result = 0;
		}
		elseif (isset($obj1->Date))
		{
			$result = -1;
		}
		elseif (isset($obj2->Date))
		{
			$result = 1;
		}
		if ($result == 0)
		{
			if (isset($obj1->Time) && isset($obj2->Time))
			{
				if ($time1 < $time2)
					$result = -1;
				elseif ($time1 > $time2)
					$result = 1;
				elseif ($result == 0)
				{
					if ($obj1->Millisecond() < $obj2->Millisecond())
						$result = -1;
					elseif ($obj1->Millisecond() > $obj2->Millisecond())
						$result = 1;
					else
						$result = 0;
				}
			}
			elseif (isset($obj1->Time))
			{
				$result = -1;
			}
			elseif (isset($obj2->Time))
			{
				$result = 1;
			}
		}
		
		return $result;
	}
	
	static function SubtractMilli($obj1, $milliOrDate, $ignoreMissingParts=false)
	{
		// DRL FIXIT! What about HasYear?
		
		if (is_object($milliOrDate))
		{
			$result = 0;
			if (isset($obj1->Date))
			{
				if (!isset($milliOrDate->Date))
				{
               if (!$ignoreMissingParts)
   					WriteDie("Attempting to subtract DateAndTime having date with one not having date!");
				}
            else
   				$result += ($obj1->Date - $milliOrDate->Date) * DateAndTime::$SecondsPerDay * 1000;
			}
			elseif (isset($milliOrDate->Date))
			{
            if (!$ignoreMissingParts)
   				WriteDie("Attempting to subtract DateAndTime having no date with one having a date!");
			}
			if (isset($obj1->Time))
			{
				if (!isset($milliOrDate->Time))
				{
               if (!$ignoreMissingParts)
   					WriteDie("Attempting to subtract DateAndTime having time with one not having time!");
				}
            else
   				$result += ($obj1->Time - $milliOrDate->Time) * 1000;
			}
			elseif (isset($milliOrDate->Time))
			{
            if (!$ignoreMissingParts)
   				WriteDie("Attempting to subtract DateAndTime having no time with one having a time!");
			}
			if (isset($obj1->Millisecond))
			{
				if (!isset($milliOrDate->Millisecond))
				{
               if (!$ignoreMissingParts)
   					WriteDie("Attempting to subtract DateAndTime having millisecond with one not having millisecond!");
				}
            else
   				$result += $obj1->Millisecond - $milliOrDate->Millisecond;
			}
			elseif (isset($milliOrDate->Millisecond))
			{
            if (!$ignoreMissingParts)
   				WriteDie("Attempting to subtract DateAndTime having no millisecond with one having a millisecond!");
			}
			if (isset($obj1->Zone))
			{
				if (!isset($milliOrDate->Zone))
				{
               if (!$ignoreMissingParts)
   					WriteDie("Attempting to subtract DateAndTime having time zone with one not having a time zone!");
				}
            else
   				$result -= ($obj1->Zone - $milliOrDate->Zone) * 1000;
			}
			elseif (isset($milliOrDate->Zone))
			{
            if (!$ignoreMissingParts)
   				WriteDie("Attempting to subtract DateAndTime having no time zone with one having a time zone!");
			}
			
			return $result;
		}
		else
		{
			$result = $obj1->Copy();
	
			if (!isset($obj1->Millisecond))
			{
            if (!$ignoreMissingParts)
   				WriteDie("Attempting to subtract milliseconds from a DateAndTime not having millisecond!");
			}
			else if (!isset($obj1->Time))
			{
            if (!$ignoreMissingParts)
   				WriteDie("Attempting to subtract milliseconds from a DateAndTime not having time!");
			}
			else if (!isset($obj1->Date))
			{
            if (!$ignoreMissingParts)
   				WriteDie("Attempting to subtract milliseconds from a DateAndTime not having date!");
			}
         else
         {
   			// DRL FIXIT! Looping like this is not efficient!
   			$result->Millisecond -= $milliOrDate;
   			while ($result->Millisecond < 0)
   			{
   				$result->Millisecond += 1000;
   				$result->Time -= 1;
   			}
   			while ($result->Millisecond >= 1000)
   			{
   				$result->Millisecond -= 1000;
   				$result->Time += 1;
   			}
   			while ($result->Time >= DateAndTime::$SecondsPerDay)
   			{
   				$result->Time -= DateAndTime::$SecondsPerDay;
   				$result->Date += 1;
   			}
         }
			
			return $result;
		}
	}
	
	static function AddMilli($obj1, $milli)
	{
		// DRL FIXIT! What about HasYear?
		
		if (!isset($obj1->Millisecond))
		{
			WriteDie("Attempting to add milliseconds to a DateAndTime not having millisecond!");
		}
		if (!isset($obj1->Time))
		{
			WriteDie("Attempting to add milliseconds to a DateAndTime not having time!");
		}
		if (!isset($obj1->Date))
		{
			WriteDie("Attempting to add milliseconds to a DateAndTime not having date!");
		}
		
		$result = $obj1->Copy();

		// DRL FIXIT! Looping like this is not efficient!
		$result->Millisecond += $milli;
		while ($result->Millisecond < 0)
		{
			$result->Millisecond += 1000;
			$result->Time -= 1;
		}
		while ($result->Millisecond >= 1000)
		{
			$result->Millisecond -= 1000;
			$result->Time += 1;
		}
		while ($result->Time >= DateAndTime::$SecondsPerDay)
		{
			$result->Time -= DateAndTime::$SecondsPerDay;
			$result->Date += 1;
		}
		
		return $result;
	}
	
	// ignores milliseconds when subtracting two dates because it returns seconds!
	static function Subtract($obj1, $secondsOrDate, $ignoreMissingParts=false)
	{
		// DRL FIXIT! What about HasYear?
		
		if (is_object($secondsOrDate))
		{
			$result = 0;
         
			if (isset($obj1->Date))
			{
				if (!isset($secondsOrDate->Date))
				{
               if (!$ignoreMissingParts)
   					WriteDie("Attempting to subtract DateAndTime having date with one not having date!");
				}
            else
   				$result += ($obj1->Date - $secondsOrDate->Date) * DateAndTime::$SecondsPerDay;
			}
			elseif (isset($secondsOrDate->Date))
			{
            if (!$ignoreMissingParts)
   				WriteDie("Attempting to subtract DateAndTime having no date with one having a date!");
			}
			if (isset($obj1->Time))
			{
				if (!isset($secondsOrDate->Time))
				{
               if (!$ignoreMissingParts)
   					WriteDie("Attempting to subtract DateAndTime having time with one not having time!");
				}
            else
            {
   				$result += $obj1->Time - $secondsOrDate->Time;
   
   				// we only take the zone into consideration if there is a time component!
               
               if (isset($obj1->Zone))
               {
                  if (!isset($secondsOrDate->Zone))
                  {
                     if (!$ignoreMissingParts)
                        WriteDie("Attempting to subtract DateAndTime having time zone with one not having a time zone!");
                  }
                  else
                     $result -= $obj1->Zone - $secondsOrDate->Zone;
               }
               elseif (isset($secondsOrDate->Zone))
               {
                  if (!$ignoreMissingParts)
                  {
                     WriteDie("Attempting to subtract DateAndTime having no time zone with one having a time zone!");
                  }
               }
            }
			}
			elseif (isset($secondsOrDate->Time))
			{
            if (!$ignoreMissingParts)
   				WriteDie("Attempting to subtract DateAndTime having no time with one having a time!");
			}
			
			return $result;
		}
		else
		{
			$result = new DateAndTime();
	
			$temp = - $secondsOrDate;
			if (isset($obj1->Date))
			{
				$temp += $obj1->Date * DateAndTime::$SecondsPerDay;
			}
			if (isset($obj1->Time))
			{
				$temp += $obj1->Time;
			}
			if (isset($obj1->Date))
			{
				$result->Date = Utilities::Div($temp, DateAndTime::$SecondsPerDay);
				$result->HasYear = $obj1->HasYear;
			}
			if (isset($obj1->Time))
			{
				$result->Time = $temp % DateAndTime::$SecondsPerDay;
	
				while ($result->Time < 0)
				{
					if (isset($obj1->Date))
					{
						$result->Date--;
					}
					$result->Time += DateAndTime::$SecondsPerDay;
				}
			}
			$result->Zone = $obj1->Zone;
			
			return $result;
		}
	}
	
	static function Add($obj1, $seconds)
	{
		$result = new DateAndTime();
	
		// DRL FIXIT! What about HasYear?
		
		$temp = $seconds;
		if (isset($obj1->Date))
		{
			$temp += $obj1->Date * DateAndTime::$SecondsPerDay;
		}
		if (isset($obj1->Time))
		{
			$temp += $obj1->Time;
		}
		if (isset($obj1->Date))
		{
			$result->Date = Utilities::Div($temp, DateAndTime::$SecondsPerDay);
			$result->HasYear = $obj1->HasYear;
		}
		if (isset($obj1->Time))
		{
			$result->Time = $temp % DateAndTime::$SecondsPerDay;
	
			while ($result->Time < 0)
			{
				if (isset($obj1->Date))
				{
					$result->Date--;
				}
				$result->Time += DateAndTime::$SecondsPerDay;
			}
		}
		$result->Zone = $obj1->Zone;
		
		return $result;
	}
	
	static function Now($zone = NULL)
	{
		$t = new DateAndTime();
	
		$t->Zone = is_null($zone) ? DateAndTime::LocalTimeZoneOffset() : $zone;
		
		// time() returns GMT, convert to desired time zone
		$temp = time() + $t->Zone;
		$t->Date = Utilities::Div($temp, DateAndTime::$SecondsPerDay);
		$t->HasYear = 1;
		$t->Time = $temp % DateAndTime::$SecondsPerDay;
		while ($t->Time < 0)
		{
			$t->Date--;
			$t->Time += DateAndTime::$SecondsPerDay;
		}
		
		$temp = explode(' ', microtime());
		$t->Millisecond = intVal($temp[0] * 1000);
		
		return $t;
	}
	
	static function LocalTimeZoneOffset()
	{
		return DateAndTime::TimeZoneOffset(strftime('%z', time()));
	}
	
	static function TimeZoneOffset($zone)
	{
		if (!isset($zone) || strlen($zone) == 0)
		{
			return NULL;
		}
		
		$sign = substr($zone, 0, 1);
	
		$zone = Utilities::ReplaceInString($zone, ":", "");
		
		if (strcmp($sign, "-") == 0 || strcmp($sign, "+") == 0 || strcmp($sign, "0") == 0)
		{
			// format is "000", "-800", "-1000" or "+800"
			
			return (substr($zone, 0, strlen($zone) - 2) * 3600) +
				(substr($zone, strlen($zone) - 2, 2) * 60);
		}

		// format is probably a string

		if (strlen($zone) == 1 && strcmp($zone, "A") >= 0 && strcmp($zone, "Z") <= 0)
		{
			// this is likely a time zone character as in 'Z' for UTC or GMT
			// NOTE: "J" isn't used!

			if (strcmp($zone, "I") <= 0)
			{
				return - (ord($zone) - ord("A") + 1) * DateAndTime::$SecondsPerHour;
			}
			elseif (strcmp($zone, "M") <= 0)
			{
				return - (ord($zone) - ord("A")) * DateAndTime::$SecondsPerHour;
			}
			elseif (strcmp($zone, "Y") <= 0)
			{
				return (ord($zone) - ord("N") + 1) * DateAndTime::$SecondsPerHour;
			}
			else
			{
				return 0;
			}
		}
		
		$i;
		for ($i = 0; $i < 24; $i++)
		{
			if (isset(DateAndTime::$TimeZones[$i]) && strcmp($zone, DateAndTime::$TimeZones[$i]) == 0)
			{
				return ($i - 12) * 3600;
			}
			if (isset(DateAndTime::$TimeZonesDST[$i]) && strcmp($zone, DateAndTime::$TimeZonesDST[$i]) == 0)
			{
				return ($i - 12) * 3600;
			}
		}
		
		// DRL FIXIT! This is a bad way of figuring out time zone!
		if (strcmp($zone, "Pacific Standard Time") == 0)
		{
			return -8 * 3600;
		}
		elseif (strcmp($zone, "Pacific Daylight Time") == 0)
		{
			return -7 * 3600;
		}
		elseif (strcmp($zone, "India Standard Time") == 0)
		{
			return 5.5 * 3600;
		}
      
      // A time zone of the format "America/Los_Angeles"
      $dateTimeZone = new DateTimeZone($zone);
      $date = new DateTime(null, $dateTimeZone);   // DRL FIXIT! Need to pass a date that isn't in DST?
      return $dateTimeZone->getOffset($date);
	}
	
	// this is used for ISO 8601 basic and extended formats
	static function TimeZoneToChar($zone)
	{
		if (!isset($zone))
		{
			return "";
		}
	
		$hours = Utilities::Div($zone, 3600);
		$minutes = Utilities::Div($zone - ($hours * 3600), 60);
	
		if ($minutes == 0)
		{
			// NOTE: "J" is not used
			if ($hours <= -10)
			{
				return chr(ord("A") - $hours);
			}
			elseif ($hours < 0)
			{
				return chr(ord("A") - $hours - 1);
			}
			elseif ($hours > 0)
			{
				return chr(ord("N") + $hours - 1);
			}
			else
			{
				return "Z";
			}
		}
		else
		{
			if ($minutes < 0)
			{
				$minutes = -$minutes;
			}
	
			// DRL FIXIT? Is it OK to use the extended format even for basic?
			return $hours . ":" . sprintf("%02d", $minutes);
		}
	}
	
	static function TimeZoneToNumeric($zone, $separator = '')
	{
		if (!isset($zone))
		{
			return "";
		}
		
		$hours = Utilities::Div($zone, 3600);
		$minutes = Utilities::Div($zone - ($hours * 3600), 60);
	
		if ($minutes < 0)
		{
			$minutes = -$minutes;
		}
		
		if ($hours < 0)
		{
			return sprintf("%03d", $hours) . $separator . sprintf("%02d", $minutes);
		}
		else
		{
			return sprintf("+%02d", $hours) . $separator . sprintf("%02d", $minutes);
		}
	}
	
	static function TimeZoneToString($zone, $daylightSavings = NULL)
	{
		if (!isset($zone))
		{
			return "";
		}
		
		$hours = Utilities::Div($zone, 3600);
		$minutes = Utilities::Div($zone - ($hours * 3600), 60);
	
		if (!isset($daylightSavings))
		{
			$daylightSavings = DateAndTime::Now()->IsDaylightSavings();
		}
		
		$result;
		if ($daylightSavings)
		{
			$result = DateAndTime::$TimeZonesDST[$hours + 12];
		}
		else
		{
			$result = DateAndTime::$TimeZones[$hours + 12];
		}
		if ($minutes != 0 || !isset($result))
		{
			if ($minutes < 0)
			{
				$minutes = -$minutes;
			}
			if ($hours >= 0)
			{
				$hours = '+' . $hours;
			}
			
			$result = $hours . ":" . sprintf("%02d", $minutes);
		}
		
		return $result;
	}

   // note that there are multiple IANA entries for a zone so you may not get back 
   // the specific one for your area (ie. America/Los_Angeles and America/Yellowknife 
   // are the same time zone)
	static function TimeZoneToIANA($offset)
	{
		if ($offset == NULL)
		{
			return "";
		}

// DRL This doesn't work, simply returns what was passed into constructor:		
//      $dateTimeZone = new DateTimeZone($zone);
//      return $dateTimeZone->getName();

      static $offsets = array();
      if (count($offsets) == 0)
      {
         $now = new DateTime("@946684800");   // GMT
         $timezone_identifiers = DateTimeZone::listIdentifiers();
         foreach ($timezone_identifiers as $zone)
         {
            $dateTimeZone = new DateTimeZone($zone);
            $offsets[$dateTimeZone->getOffset($now)] = $zone;
         }
      }
      
      if (array_key_exists($offset, $offsets))
         return $offsets[$offset];
         
      return "";
	}

	static function ParseMonth($month)
	{
		$month = substr($month, 0, 3);
		$i;
		
		for ($i = 1; $i <= 12; $i++)
		{
			if (strcmp(DateAndTime::$Months[$i], $month) == 0)
			{
				return $i;
			}
		}
		
		return NULL;
	}

	static function GetMonthString($month, $long = NULL)
	{
		if (!isset($long)) { $long = 0; }
		
		if ($long)
		{
			return DateAndTime::$LongMonths[$month];
		}
		else
		{
			return DateAndTime::$Months[$month];
		}
	}
	
	static function ParseDayOfWeek($weekday)
	{
		$weekday = substr($weekday, 0, 3);
		$i;
		
		for ($i = 0; $i < 7; $i++)
		{
			if (strcmp(DateAndTime::$DaysOfWeek[$i], $weekday) == 0)
			{
				return $i;
			}
		}
		
		WriteDie("Unrecognized day of week: " . $weekday);
	}
	
	static function GetDayOfWeekString($day, $long = NULL)
	{
		if (!isset($long)) { $long = 0; }
		
		if ($long)
		{
			return DateAndTime::$LongDaysOfWeek[$day];
		}
		else
		{
			return DateAndTime::$DaysOfWeek[$day];
		}
	}
	
	static function &GetTimeZoneArray()
	{
		$values = array();
		for ($i = -12; $i <= 12; $i++)
		{
			$value;
	// DRL FIXIT? For the vCard time zone we only want to show the hours (see ContactAddEdit.cgi).
	//		if (isset(DateAndTime::$TimeZones[$i]))
	//		{
	//			$value = DateAndTime::$TimeZones[$i];
	//		}
	//		if (isset(DateAndTime::$TimeZonesDST[$i]))
	//		{
	//			if (isset($value))
	//			{
	//				$value .= "/" . DateAndTime::$TimeZonesDST[$i];
	//			}
	//			else
	//			{
	//				$value = DateAndTime::$TimeZonesDST[$i];
	//			}
	//		}
			$zone = $i . ":00";
			if ($i > 0) { $zone = "+" . $zone; }
			if (isset($value))
			{
				$value .= " ($zone)";
			}
			else
			{
				$value = $zone;
			}
			array_push($values, $value);
		}
		return $values;
	}
	
	static function &GetFormatsAsValueDisplayArray()
	{
		$values = array();
	
		$now = Now();
		
		array_push($values, NULL);
		array_push($values, "Default");
	
		array_push($values, DateAndTime::$TimeFormat);
		array_push($values, $now->ToFormat(DateAndTime::$TimeFormat));
	
		array_push($values, DateAndTime::$LongDateFormat);
		array_push($values, $now->ToFormat(DateAndTime::$LongDateFormat));
	
		array_push($values, DateAndTime::$ShortDateFormat);
		array_push($values, $now->ToFormat(DateAndTime::$ShortDateFormat));
	
		array_push($values, DateAndTime::$LongFormat);
		array_push($values, $now->ToFormat(DateAndTime::$LongFormat));
	
		array_push($values, DateAndTime::$LongFormat2);
		array_push($values, $now->ToFormat(DateAndTime::$LongFormat2));
	
		array_push($values, DateAndTime::$LongFormat3);
		array_push($values, $now->ToFormat(DateAndTime::$LongFormat3));
	
		array_push($values, DateAndTime::$ShortFormat);
		array_push($values, $now->ToFormat(DateAndTime::$ShortFormat));
	
		array_push($values, DateAndTime::$ISO8601ExtendedFormat);
		array_push($values, $now->ToFormat(DateAndTime::$ISO8601ExtendedFormat));
	
		array_push($values, DateAndTime::$ISO8601BasicFormat);
		array_push($values, $now->ToFormat(DateAndTime::$ISO8601BasicFormat));
	
		array_push($values, DateAndTime::$ISO8601BasicFormatWithMilliseconds);
		array_push($values, $now->ToFormat(DateAndTime::$ISO8601BasicFormatWithMilliseconds));
		
		return $values;
	}
	
	static function IsLeapYear($year)
	{
		if ($year % 4) { return 0; }
		if ($year % 100) { return 1; }
		if ($year % 400) { return 0; }
		return 1;
	}
	
	static function DaysInMonth($year, $month)
	{
		$date = new DateAndTime($year, $month, 1);
		
		return $date->DaysThisMonth();
	}
	
	static function GetEaster($year, $calendar = NULL)
	{
	//	This method was ported from the work done by GM Arts,
	//	on top of the algorithm by Claus Tondering, which was
	//	based in part on the algorithm of Ouding (1940), as
	//	quoted in "Explanatory Supplement to the Astronomical
	//	Almanac", P.  Kenneth Seidelmann, editor.
	//	
	//	This algorithm implements three different easter
	//	calculation methods:
	//	
	//	1 - Original calculation in Julian calendar, valid in
	//		dates after 326 AD
	//	2 - Original method, with date converted to Gregorian
	//		calendar, valid in years 1583 to 4099
	//	3 - Revised method, in Gregorian calendar, valid in
	//		years 1583 to 4099 as well
	//
	//	More about the algorithm may be found at:
	//	
	//	http://users.chariot.net.au/~gmarts/eastalg.htm
	//	
	//	and
	//	
	//	http://www.tondering.dk/claus/calendar.html
	
		if (!isset($calendar))
		{
			$calendar = $WESTERN_CALENDAR;
		}
		
		if ($calendar != $JULIAN_CALENDAR && $calendar != $ORTHODOX_CALENDAR && $calendar != $WESTERN_CALENDAR)
		{
			WriteDie("invalid calendar");
		}
		
		// g - Golden year - 1
		// c - Century
		// h - (23 - Epact) mod 30
		// i - Number of days from March 21 to Paschal Full Moon
		// j - Weekday for PFM (0=Sunday, etc)
		// p - Number of days from March 21 to Sunday on or before PFM
		//     (-6 to 28 methods 1 & 3, to 56 for method 2)
		// e - Extra days to add for method 2 (converting Julian
		//     date to Gregorian date)
		
		$y = $year;
		$g = $y % 19;
		$e = 0;
		$i;
		$j;
		if ($calendar == $JULIAN_CALENDAR || $calendar == $ORTHODOX_CALENDAR)
		{
			// Old method
			$i = (19*$g+15)%30;
			$j = ($y+$y/4+$i)%7;
			if ($calendar == $ORTHODOX_CALENDAR)
			{
				// Extra dates to convert Julian to Gregorian date
				$e = 10;
				if ($y > 1600)
				{
					$e = $e+$y/100-16-($y/100-16)/4;
				}
			}
		}
		else
		{
			// New method
			$c = $y/100;
			$h = ($c-$c/4-(8*$c+13)/25+19*$g+15)%30;
			$i = $h-($h/28)*(1-($h/28)*(29/($h+1))*((21-$g)/11));
			$j = ($y+$y/4+$i+2-$c+$c/4)%7;
		}
		
		// p can be from -6 to 56 corresponding to dates 22 March to 23 May
		// (later dates apply to method 2, although 23 May never actually occurs)
		$p = $i-$j+$e;
		$d = 1+($p+27+($p+6)/40)%31;
		$m = 3+($p+26)/30;
		
		return new DateAndTime($y, $m, $d);
	}
	
	private static function _mydategm($day, $month, $year)
	{
		$adjust = 0;
		if ($year < 70)
		{
			$year += DateAndTIme::$SixtySevenYears;
			$adjust = 1;
		}
	
		$t = new DateTime(NULL, new DateTimeZone('GMT'));
		$t->setDate($year+1900, $month+1, $day);
//		$tmp = $t->getTimestamp(); not available in PHP 5.2!
		$tmp = $t->format('U');
		$result = Utilities::Div($tmp, DateAndTime::$SecondsPerDay);
		
		if ($adjust)
		{
			$result -= DateAndTIme::$DaysPerSixtySevenYears;
		}
		
		return $result;
	}
	
	
	private static function _mytimegm($second, $minute, $hour)
	{
      if (!is_numeric($second) || !is_numeric($minute) || !is_numeric($hour))
      {
         $hour = $hour;
      }
		return ($hour * DateAndTime::$SecondsPerHour) + ($minute * DateAndTime::$SecondsPerMinute) + $second;
	}
	
	
	private static function _mygmdate($date)
	{
		$second;
		$minute;
		$hour;
		$day;
		$month;
		$year;
		$wday;			// 0 is Sunday
		$yday;			// 0 is first day
		$isdst;
		
		if ($date >= 0)
		{
			// I use gmdate() because getdate() sometimes returned a 2 hour error
			$values = gmdate('s i H d m Y w z I', $date * DateAndTime::$SecondsPerDay);
			$values = explode(' ', $values);
			list($second, $minute, $hour, $day, $month, $year, $wday, $yday, $isdst) = 
				array($values[0], $values[1], $values[2], $values[3], $values[4], $values[5], $values[6], $values[7], $values[8]);
		}
		else
		{
			// I use gmdate() because getdate() sometimes returned a 2 hour error
			$values = gmdate('s i H d m Y w z I', ($date + DateAndTime::$DaysPerSixtySevenYears) * DateAndTime::$SecondsPerDay);
			$values = explode(' ', $values);
			list($second, $minute, $hour, $day, $month, $year, $wday, $yday, $isdst) = 
				array($values[0], $values[1], $values[2], $values[3], $values[4], $values[5], $values[6], $values[7], $values[8]);
			$year -= DateAndTIme::$SixtySevenYears;
		}
		
		$month--;
		$year -= 1900;
	
		return array($day, $month, $year, $wday, $yday, $isdst);
	}
	
	private static function _mygmtime($time)
	{
		$hour = Utilities::Div($time, DateAndTime::$SecondsPerHour);
		$time -= $hour * DateAndTime::$SecondsPerHour;
		$minute = Utilities::Div($time, DateAndTime::$SecondsPerMinute);
		$time -= $minute * DateAndTime::$SecondsPerMinute;
		
		return array($time, $minute, $hour);
	}
	
/*	DRL FIXIT?
	static function NullDateTime()
	{
		if (DateAndTime::$NullDateTime == null)
		{
			DateAndTime::$NullDateTime = DateTime::createFromFormat("Y-m-d H:i:s T", 
				DateAndTime::$NullDateTimeValues['year'] . '-' . 
				DateAndTime::$NullDateTimeValues['mon'] . '-' . 
				DateAndTime::$NullDateTimeValues['mday'] . ' ' . 
				DateAndTime::$NullDateTimeValues['hours'] . ':' . 
				DateAndTime::$NullDateTimeValues['minutes'] . ':' . 
				DateAndTime::$NullDateTimeValues['seconds'] . ' Z');
		}
		return clone DateAndTime::$NullDateTime;
	}
	
	static function HasDate($datetime)
	{
		if ($datetime == null)
			return false;
			
		DRL FIXIT! Use gmdate() instead!
		$temp = getdate($datetime->getTimestamp());
		
		return $temp['year'] != DateAndTime::$NullDateTimeValues['year'] &&
			 $temp['mon'] != DateAndTime::$NullDateTimeValues['mon'] &&
			$temp['mday'] != DateAndTime::$NullDateTimeValues['mday'];
	}

	static function HasTime($datetime)
	{
		if ($datetime == null)
			return false;
			
		// DRL FIXIT! Is there no way we can distinguish a null time?
		return true;
	}

	static function CopyDate($dest, $source)
	{
		if ($source != null)
		{
			if ($dest == null)
				$dest = DateAndTime::NullDateTime();
			DRL FIXIT! Use gmdate() instead!
			$temp = getdate($source->getTimestamp());
			$dest->setDate($temp['year'], $temp['mon'], $temp['mday']);
		}
		else
		{
			if ($dest != null)
			{
				if (DateAndTime::HasTime($dest))
					$dest->setDate(DateAndTime::$NullDateTimeValues['year'], DateAndTime::$NullDateTimeValues['mon'], DateAndTime::$NullDateTimeValues['mday']);
				else
					$dest = null;
			}
		}
	}

	static function CopyTime($dest, $source)
	{
		if ($source != null)
		{
			if ($dest == null)
				$dest = DateAndTime::NullDateTime();
			DRL FIXIT! Use gmdate() instead!
			$temp = getdate($source->getTimestamp());
			$dest->setTime($temp['hours'], $temp['minutes'], $temp['seconds']);
		}
		else
		{
			if ($dest != null)
			{
				if (DateAndTime::HasDate($dest))
					$dest->setTime(DateAndTime::$NullDateTimeValues['hours'], DateAndTime::$NullDateTimeValues['minutes'], DateAndTime::$NullDateTimeValues['seconds']);
				else
					$dest = null;
			}
		}
	}

	static function GetDateOnly($datetime)
	{
		if ($datetime == null)
			$datetime = DateAndTime::NullDateTime();
		$datetime = clone $datetime;
		$datetime->setTime(DateAndTime::$NullDateTimeValues['hours'], 
			DateAndTime::$NullDateTimeValues['minutes'], 
			DateAndTime::$NullDateTimeValues['seconds']);
		return $datetime;
	}

	static function GetTimeOnly($datetime)
	{
		if ($datetime == null)
			$datetime = DateAndTime::NullDateTime();
		$datetime = clone $datetime;
		$datetime->setDate(DateAndTime::$NullDateTimeValues['year'], 
			DateAndTime::$NullDateTimeValues['mon'], 
			DateAndTime::$NullDateTimeValues['mday']);
		return $datetime;
	}
*/
	
	private function _FormatLongYear()
	{
		return sprintf("%04d", $this->Year());
	}
	
	private function _FormatShortYear()
	{
		return sprintf("%02d", ($this->Year() % 100));
	}
	
	private function _FormatLongNumericMonth()
	{
		return sprintf("%02d", $this->Month());
	}
	
	private function _FormatShortNumericMonth()
	{
		return sprintf("%d", $this->Month());
	}
	
	private function _FormatLongDay()
	{
		return sprintf("%02d", $this->Day());
	}
	
	private function _FormatLong24Hour()
	{
		return sprintf("%02d", $this->Hour());
	}
	
	private function _FormatLong12Hour()
	{
		if ($this->Hour() > 12)
		{
			return sprintf("%02d", ($this->Hour() - 12));
		}
		return sprintf("%02d", $this->Hour());
	}
	
	private function _FormatShort24Hour()
	{
		return sprintf("%d", $this->Hour());
	}
	
	private function _FormatShort12Hour()
	{
		if ($this->Hour() > 12)
		{
			return sprintf("%d", ($this->Hour() - 12));
		}
		return sprintf("%d", $this->Hour());
	}
	
	private function _FormatMinute()
	{
		return sprintf("%02d", $this->Minute());
	}
	
	private function _FormatAMPM()
	{
		if ($this->Hour() < 12)
		{
			return 'AM';
		}
		else
		{
			return 'PM';
		}
	}
	
	private function _Formatampm_lower()
	{
		if ($this->Hour() < 12)
		{
			return 'am';
		}
		else
		{
			return 'pm';
		}
	}
	
	private function _FormatSecond()
	{
		return sprintf("%02d", $this->Second());
	}
	
	private function _FormatZone()
	{
		return DateAndTime::TimeZoneToString($this->Zone());
	}
	
	private function _FormatShortDayOfWeek()
	{
		return DateAndTime::GetDayOfWeekString($this->DayOfWeek(), 0);
	}
	
	private function _FormatLongDayOfWeek()
	{
		return DateAndTime::GetDayOfWeekString($this->DayOfWeek(), 1);
	}
	
	private function _FormatSpaceDay()
	{
		return sprintf("% 2d", $this->Day());
	}
	
	private function _FormatShortDay()
	{
		return sprintf("%d", $this->Day());
	}
	
	private function _FormatShortMonth()
	{
		return DateAndTime::GetMonthString($this->Month(), 0);
	}
	
	private function _FormatLongMonth()
	{
		return DateAndTime::GetMonthString($this->Month(), 1);
	}
	
	private function _FormatMillisecond()
	{
		return sprintf("%02d.%03d", $this->Second(), $this->Millisecond());
	}
	
	private function _FormatCharZone()
	{
		return DateAndTime::TimeZoneToChar($this->Zone());
	}
	
	private function _FormatIANAZone()
	{
		return DateAndTime::TimeZoneToIANA($this->Zone());
	}
	
	private function _FormatNumericZone()
	{
		return DateAndTime::TimeZoneToNumeric($this->Zone());
	}
	
	private function _FormatNumericColonZone()
	{
		return DateAndTime::TimeZoneToNumeric($this->Zone(), ':');
	}
	
	private function _FormatTime()
	{
		return sprintf("%02d%02d%02d", $this->Hour(), $this->Minute(), $this->Second());
	}
	
	private function _FormatTimeColon()
	{
		return sprintf("%02d:%02d:%02d", $this->Hour(), $this->Minute(), $this->Second());
	}
	
	private function _FormatTimeNoSecColon()
	{
		return sprintf("%02d:%02d", $this->Hour(), $this->Minute());
	}
	
	private function _FormatTimeWithMillisecond()
	{
		return sprintf("%02d%02d%02d.%03d", $this->Hour(), $this->Minute(), $this->Second(), $this->Millisecond());
	}
	
	private function _FormatDate()
	{
		return sprintf("%02d%02d%02d", $this->Month(), $this->Day(), ($this->Year() % 100));
	}
	
	private function _FormatDateHyphens()
	{
		return sprintf("%04d-%02d-%02d", $this->Year(), $this->Month(), $this->Day());
	}
	
	private function _FormatDateSlashes()
	{
		return sprintf("%04d/%02d/%02d", $this->Year(), $this->Month(), $this->Day());
	}
}

if (0)
{
	$dt = DateAndTime::Now();
	print('test: ' . $dt->ToFormat('%E %B %Y'));
	$d = new DateTime(NULL, new DateTimeZone('GMT'));
	print('Original: ' . $d->format('Y-m-d H:i:s') . ' ' . $d->format('U') . '<br>');
//	print('GetDateOnly: ' . DateAndTime::GetDateOnly($d)->format('Y-m-d H:i:s') . ' ' . DateAndTime::GetDateOnly($d)->format('U') . '<br>');
//	print('GetTimeOnly: ' . DateAndTime::GetTimeOnly($d)->format('Y-m-d H:i:s') . ' ' . DateAndTime::GetTimeOnly($d)->format('U') . '<br>');

	$dt1 = DateAndTime::Now(DateAndTime::$SecondsPerHour * 5);
	$dt2 = DateAndTime::Now(DateAndTime::$SecondsPerHour * 8);
	$ep1 = $dt1->ToEpoch();
	$ep2 = $dt2->ToEpoch();
	if ($ep1 == $ep2)
		WriteDie("Error with Now()");
		
	$dt1 = DateAndTime::Now();
	sleep(5);
	$dt2 = DateAndTime::Now();
	
	$d1 = DateAndTime::SubtractMilli($dt2, $dt1);
	$d2 = DateAndTime::SubtractMilli($dt1, $dt2);
	$dt3 = DateAndTime::SubtractMilli($dt1, 5000);
	
	print("dt1: " . $dt1->ToFormat(DateAndTime::$ISO8601BasicFormatWithMilliseconds) . "<BR>\r\n");
	print("dt2: " . $dt2->ToFormat(DateAndTime::$ISO8601BasicFormatWithMilliseconds) . "<BR>\r\n");
	print("dt2-dt1: " . $d1 . "m<BR>\r\n");
	print("dt1-dt2: " . $d2 . "m<BR>\r\n");
	print("dt1-5000m: " . $dt3->ToFormat(DateAndTime::$ISO8601BasicFormatWithMilliseconds) . "<BR>\r\n");

	$d1 = DateAndTime::AddMilli($dt1, 5000);
	$d2 = DateAndTime::AddMilli($dt1, -5000);
	
	print("dt1+5000m: " . $d1->ToFormat(DateAndTime::$ISO8601BasicFormatWithMilliseconds) . "<BR>\r\n");
	print("dt2-5000m: " . $d2->ToFormat(DateAndTime::$ISO8601BasicFormatWithMilliseconds) . "<BR>\r\n");
   
   $d1->SetDate(NULL, 10, 02);
   $d2 = DateAndTime::FromString($d1->ToString(Utilities::$ToStringFormatSerialize));
	if (DateAndTime::Compare($d1, $d2) != 0)
   {
		WriteDie("Error NULL year handling");
   }
   
   $d1 = new DateAndTime(NULL, NULL, NULL, 0, 0, NULL);
   $d2 = DateAndTime::FromString("0:00 am");
	if (DateAndTime::Compare($d1, $d2) != 0)
   {
		WriteDie("Error in FromString time handling");
   }
   
   $d1 = new DateAndTime(NULL, NULL, NULL, 12, 0, NULL);
   $d2 = DateAndTime::FromString("0:00pm");
	if (DateAndTime::Compare($d1, $d2) != 0)
   {
		WriteDie("Error in FromString time handling");
   }
   
   $d1 = new DateAndTime(1991, 06, 15, NULL, NULL, NULL);
   $d2 = DateAndTime::FromString("1991/06/15");
	if (DateAndTime::Compare($d1, $d2) != 0)
   {
		WriteDie("Error in FromString date handling");
   }
   
   $d1 = new DateAndTime(1991, 06, 15, 12, 10, 05);
   $d2 = DateAndTime::FromString("1991/06/15 12:10:05 pm");
	if (DateAndTime::Compare($d1, $d2) != 0)
   {
		WriteDie("Error in FromString date handling");
   }
   
   $d1 = DateAndTime::FromString("Sat,  5 Aug 2017 01:29:45 -0400 (EDT)");
   $d2 = new DateAndTime(2017, 8, 5, 01, 29, 45, 0, -4 * DateAndTime::$SecondsPerHour);
   if (DateAndTime::Compare($d1, $d2) != 0)
   {
      WriteDie("Error in FromString date handling");
   }

   $d1->SetDate(NULL);
   $d1->SetTime(NULL);
   $s = $d1->ToFormat("%-D %:T");
	if (!empty($s))
   {
		WriteDie("Error in ToFormat() no date/time handling");
   }

   $d1 = DateAndTime::FromString("Sat, 10 Jun 2017 08:06:35 -0700 (PDT)");
	$s = $d1->ToString();
	if ($s != '2017/06/10 08:06:35 PDT' && $tmp != '2017/06/10 08:06:35 MST')
   {
		WriteDie("Error with Now()");
   }
}

?>

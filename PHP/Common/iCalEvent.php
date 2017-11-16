<?php

require_once(dirname(__FILE__) . '/DateAndTime.php');
require_once(dirname(__FILE__) . '/File.php');
require_once(dirname(__FILE__) . '/iCalAttendee.php');
require_once(dirname(__FILE__) . '/Log.php');
require_once(dirname(__FILE__) . '/MimeTypes.php');
require_once(dirname(__FILE__) . '/Url.php');
require_once(dirname(__FILE__) . '/Utilities.php');
require_once(dirname(__FILE__) . '/vCardCalBase.php');

/*
===================================================================

Implementation.

===================================================================

These are the iCalendar (RFC 2445) fields we support:

DRL FIXIT? ...

These I added are not standard:

X-CALENDAR   points to calendar(s) containing this event

*/


class iCalEvent extends vCardCalBase
{
	// the first two are the main ones, the others aren't as common
	// Note: a VALARM may only appear within one of the first two so it doesn't have its own type.
	static $EventType = 1;		// VEVENT
	static $TaskType = 2;		// VTODO
	static $JournalType = 3;	// VJOURNAL
	static $FreeBusyType = 4;	// VFREEBUSY
	static $TypeNames = array("Unknown", "Event", "Task", "Journal", "FreeBusy");
	
	private $typeOfEvent = NULL;

   /**
	 * SPL-compatible autoloader.
	 *
     * @param string $className Name of the class to load.
     *
     * @return boolean
    public static function autoload($className)
    {
        if ($className != 'iCalEvent') {
            return false;
		}
        return include str_replace('_', '/', $className) . '.php';
    }
     */

     /**
    * Builder factory
    *
    * Creates an instance of the correct parser class, based on the
    * parameter passed. For example, File_IMC::parse('vCalendar') creates
    * a new object to parse an iCalendar file.
    *
    * @param string $format  Type of file to parse, iCalendar or vCalendar
    * @param mixed  $version Optionally, the version.
    *
    * @return mixed
    * @throws File_IMC_Exception In case the driver is not found/available.
    */
	function __construct()
	{
		$this->object = File_IMC::build('vCalendar');
		
		// set some default/required fields...
		$this->object->setVersion();
		
		// we need to create an event section
		$this->typeOfEvent = 'VEVENT';
		$this->object->value[$this->typeOfEvent] = array();
	}
	
	function __destruct()
	{
		$this->object = NULL;
	}

	function ToBinary()
	{
		return $this->ToString();
	}
	
	function ToString()
	{
		$result = "";
		if ($this->object != NULL)
			$result = $this->Fetch('VCALENDAR');
			
		return $result;
	}
	
	static function FromBinary($binary)
	{
		return iCalEvent::FromString($binary);
	}
	
	static function FromString($string)
	{
	   $string = Utilities::RemoveSurroundingSpaces($string);
		if ($string == NULL || strlen($string) == 0)
		{
			return NULL;
		}
		
		$result = new iCalEvent();
		
		try
		{
			// create iCalendar parser
			$parse = File_IMC::parse('vCalendar');
			
			// parse an iCalendar file and store the data in $cardinfo
			$info = $parse->fromText($string);
			
			$cal_key = array_keys($info);	// could be vCalendar or VCALENDAR
			$cal_key = $cal_key[0];
			$evt_key = NULL;				// could be VEVENT, VTODO, etc.
			foreach (array_keys($info[$cal_key][0]) as $i)
			{
				if (in_array(strtoupper($i), array("VTODO", "VJOURNAL", "VFREEBUSY", "VEVENT")))
					$evt_key = $i;
			}
			$result->object->setFromArray($info[$cal_key]);
			
			$result->typeOfEvent = $evt_key;
		}
		catch (Exception $e)
		{
			WriteError("Error evaluating iCalendar object: " . $e . "/nSTART...\n$string\nEND");
		}
		
		return $result;
	}

	function Type()
	{
		$value = $this->typeOfEvent;
		if ($value != NULL)
		{
			if ($value == "VTODO")
			{
				$value = iCalEvent::$TaskType;
			}
			else if ($value == "VJOURNAL")
			{
				$value = iCalEvent::$JournalType;
			}
			else if ($value == "VFREEBUSY")
			{
				$value = iCalEvent::$FreeBusyType;
			}
			else
			{
				if ($value != "VEVENT")
				{
					WriteError("Unsupported calendar item type of '$value'!");
				}
				
				$value = iCalEvent::$EventType;	// the default
			}
		}
		
		return $value;
	}
	
	function SetType($value)
	{
		$oldType = $this->Type();
		$oldKey = $this->typeOfEvent;
		
		if ($value != NULL)
		{
			if ($value == iCalEvent::$TaskType)
			{
				$this->typeOfEvent = "VTODO";
			}
			else if ($value == iCalEvent::$JournalType)
			{
				$this->typeOfEvent = "VJOURNAL";
			}
			else if ($value == iCalEvent::$FreeBusyType)
			{
				$this->typeOfEvent = "VFREEBUSY";
			}
			else
			{
				if ($value != iCalEvent::$EventType)
				{
					WriteError("Unrecognized calendar item type constant of '$value'!");
				}
				
				$this->typeOfEvent = "VEVENT";	// the default
			}
		}
		else
			$this->typeOfEvent = "VEVENT";		// the default
			
		if ($value != $oldType)
		{
			$this->object->value[$this->typeOfEvent] = $this->object->value[$oldKey];
			unset($this->object->value[$oldKey]);
			if (isset($this->object->param[$oldKey])) $this->object->param[$this->typeOfEvent] = $this->object->param[$oldKey];
			unset($this->object->param[$oldKey]);
		}
	}
	
	function Method()
	{
		$value = NULL;
		if ($this->object != NULL)
		{
			$id = array(array('METHOD'));
			$value = $this->_GetProperty($id);
		}
		
		return $value;
	}
	
	function SetMethod($value)
	{
		$id = array(array('METHOD'));
		$this->_SetProperty($id, $value);
	}
	
	function Subject()
	{
		$value = NULL;
		if ($this->object != NULL)
		{
			$id = array(array($this->typeOfEvent), array('SUMMARY'));
			$value = $this->_GetProperty($id);
		}
		
		return $value;
	}
	
	function SetSubject($value)
	{
		$id = array(array($this->typeOfEvent), array('SUMMARY'));
		$this->_SetProperty($id, $value);
	}
	
	function Body()
	{
		$value = NULL;
		if ($this->object != NULL)
		{
			$id = array(array($this->typeOfEvent), array('DESCRIPTION'));
			$value = $this->_GetProperty($id);
		}
		
		return $value;
	}
	
	function SetBody($value)
	{
		$id = array(array($this->typeOfEvent), array('DESCRIPTION'));
		$this->_SetProperty($id, $value);
	}
	
	function Organizer()
	{
		$attendee = NULL;
		if ($this->object != NULL)
		{
			$id = array(array($this->typeOfEvent), array('ORGANIZER'));
			$nodes = $this->_GetPropertyValues($id);
         if ($nodes != NULL)
         {
            $node = $nodes[0];
            
            $attendee = new iCalAttendee($node['ID']);
            if (isset($node['CN'])) $attendee->SetDisplayName($node['CN'][0]);
         }
		}
		return $attendee;
	}
	
	// can take a string or a hash containing an ID element (for the identifier) and any of the other
	// acceptable organizer parameters such as CN
	function SetOrganizer($organizer)
	{
      $values = array();
      if ($organizer != NULL)
      {
         $temp = array();
         $temp['ID'] = $organizer->Id();
         if ($organizer->DisplayName()) $temp['CN'] = $organizer->DisplayName();
         $values[] = $temp;
      }
		$id = array(array($this->typeOfEvent), array('ORGANIZER'));
		$this->_SetPropertyValues($id, $values);
	}
	
	function Categories()
	{
		$values = array();
		$temp;
		$id = array(array($this->typeOfEvent), array('CATEGORIES'));
		if ($this->object != NULL && ($temp = $this->_GetProperty($id)) != NULL)
		{
			$values = preg_split("/,\s*/", $temp);
		}
		
		return $values;
	}
	
	function SetCategories($ref_values)
	{
		$id = array(array($this->typeOfEvent), array('CATEGORIES'));
		$this->_SetProperty($id, join(",", $ref_values));
	}
	
	function StartDate()
	{
		return $this->_GetDate('DTSTART');
	}
	
	function SetStartDate($value)
	{
		$this->_SetDate('DTSTART', $value);
	}
	
	function EndDate()
	{
		return $this->_GetDate('DTEND');
	}
	
	function SetEndDate($value)
	{
		$this->_SetDate('DTEND', $value);
	}
	
	function StartTime()
	{
		return $this->_GetTime('DTSTART');
	}
	
	function SetStartTime($value)
	{
		$this->_SetTime('DTSTART', $value);
	}
	
	function EndTime()
	{
		return $this->_GetTime('DTEND');
	}
	
	function SetEndTime($value)
	{
		$this->_SetTime('DTEND', $value);
	}
	
	function Start()
	{
		$value = new DateAndTime();

      $date = $this->StartDate();
      $d = $date ? $date->Date() : array(NULL, NULL, NULL);
		$value->SetDate($d[0], $d[1], $d[2]);

      $time = $this->StartTime();
      $t = $time ? $time->Time() : array(NULL, NULL, NULL, NULL);
		$value->SetTime($t[0], $t[1], $t[2], $t[3]);
      if ($time) $value->SetZone($time->Zone());
		
		return $value;
	}
	
	function SetStart($value)
	{
		$this->SetStartDate($value);
		$this->SetStartTime($value);
	}
	
	function End()
	{
		$value = new DateAndTime();

      $date = $this->EndDate();
      $d = $date ? $date->Date() : array(NULL, NULL, NULL);
		$value->SetDate($d[0], $d[1], $d[2]);

      $time = $this->EndTime();
      $t = $time ? $time->Time() : array(NULL, NULL, NULL, NULL);
		$value->SetTime($t[0], $t[1], $t[2], $t[3]);
		if ($time) $value->SetZone($time->Zone());
      
      if (!$date && !$time)
         return NULL;
		
		return $value;
	}
	
	function SetEnd($value)
	{
		$this->SetEndDate($value);
		$this->SetEndTime($value);
	}
	
	function Duration()
	{
		$start = $this->Start();
		$end = $this->End();
      
      if ($start == NULL || $end == NULL)
         return NULL;
         
      if ($start->HasDate() != $end->HasDate() ||
         $start->HasTime() != $end->HasTime())
      {
         WriteCallStack('Can\'t do math on dates missing parts: ' . $start->ToString() . ' v.s. ' . $end->ToString());
         return NULL;
      }

      return DateAndTime::Subtract($end, $start);
	}
	
	function SetDuration($seconds)
	{
      if ($seconds === NULL)
      {
         $this->SetEnd(NULL);
         return;
      }
      
		$start = $this->Start();
      
      if ($start == NULL)
      {
         WriteDie('Can\'t set duration without a start date/time!');
      }
         
      // an all day event won't have the time specified, but will have a duration of 24 hours
      if (!$start->HasDate() || ($seconds != DateAndTime::$SecondsPerDay && !$start->HasTime()))
      {
         WriteDie('Can\'t set duration without start date/time: ' . $start->ToString());
      }
      
      $end = DateAndTime::Add($start, $seconds);
      $this->SetEnd($end);
	}
	
   // format: FREQ=DAILY;COUNT=10
	function RecurrenceRule()
	{
		$value = NULL;
		if ($this->object != NULL)
		{
			$id = array(array($this->typeOfEvent), array('RRULE'));
			$value = $this->_GetProperty($id);
		}
		
		return $value;
	}
	
	function SetRecurrenceRule($value)
	{
		$id = array(array($this->typeOfEvent), array('RRULE'));
		$this->_SetProperty($id, $value);
	}
	
   // formats:
   // 20111230T120000Z
   // PERIOD:20111230T120000Z/20151230T120000Z
   // DATE:20111230T120000Z,20151230T120000Z
	function RecurrenceDates()
	{
		$result = array();
		if ($this->object != NULL)
		{
			$id = array(array($this->typeOfEvent), array('RDATE'));
			$nodes = $this->_GetPropertyValues($id);
         if ($nodes)
         {
            foreach ($nodes as $values)
            {
               $temp = (isset($values['VALUE']) ? $values['VALUE'][0] . ':' : '') . $values['ID'];
               $result[] = $temp;
            }
         }
		}
		return $result;
	}
	
	function SetRecurrenceDates($dates)
	{
		$id = array(array($this->typeOfEvent), array('RDATE'));
      $nodes = array();
      foreach ($dates as $date)
      {
         $i = strpos($date, ':');
         if ($i === false)
         {
            $nodes[] = $date;
         }
         else
         {
            $nodes[] = array('ID' => substr($date, $i+1), 'VALUE' => substr($date, 0, $i));
         }
      }
		$this->_SetPropertyValues($id, $nodes);
	}
	
	function RecurrenceExcludeRule()
	{
		$value = NULL;
		if ($this->object != NULL)
		{
			$id = array(array($this->typeOfEvent), array('EXRULE'));
			$value = $this->_GetProperty($id);
		}
		
		return $value;
	}
	
	function SetRecurrenceExcludeRule($value)
	{
		$id = array(array($this->typeOfEvent), array('EXRULE'));
		$this->_SetProperty($id, $value);
	}
	
   // formats:
   // 20111230T120000Z
   // PERIOD:20111230T120000Z/20151230T120000Z
   // DATE:20111230T120000Z,20151230T120000Z
	function RecurrenceExcludeDates()
	{
		$result = array();
		if ($this->object != NULL)
		{
			$id = array(array($this->typeOfEvent), array('EXDATE'));
			$nodes = $this->_GetPropertyValues($id);
         if ($nodes)
         {
            foreach ($nodes as $values)
            {
               $temp = (isset($values['VALUE']) ? $values['VALUE'][0] . ':' : '') . $values['ID'];
               $result[] = $temp;
            }
         }
		}
		return $result;
	}
	
	function SetRecurrenceExcludeDates($dates)
	{
		$id = array(array($this->typeOfEvent), array('EXDATE'));
      $nodes = array();
      foreach ($dates as $date)
      {
         $i = strpos($date, ':');
         if ($i === false)
         {
            $nodes[] = $date;
         }
         else
         {
            $nodes[] = array('ID' => substr($date, $i+1), 'VALUE' => substr($date, 0, $i));
         }
      }
		$this->_SetPropertyValues($id, $nodes);
	}
	
	function Due()
	{
		$value = NULL;
		$id = array(array($this->typeOfEvent), array('DUE'));
		if ($this->object != NULL && $this->_GetProperty($id) != NULL)
		{
			$value = DateAndTime::FromString($this->_GetProperty($id));
		}
		
		return $value;
	}
	
	function SetDue($value)
	{
		$id = array(array($this->typeOfEvent), array('DUE'));
		$this->_SetProperty($id, $value != NULL ? $value->ToFormat(DateAndTime::$ISO8601BasicFormat) : NULL);
	}
	
   // leaves time unchanged, unless setting NULL
	function SetDueDate($value)
	{
      $due = $this->Due();
      if ($due && $value)
      {
         $due->SetDate($value->Date());
         $value = $due;
      }
      else if ($value && $value->HasTime())
      {
         $value = $value->Copy();
         $value->SetTime(NULL);
      }
      $this->SetDue($value);
	}
	
	function Venue()
	{
		$value = NULL;
		if ($this->object != NULL)
		{
			$id = array(array($this->typeOfEvent), array('LOCATION'));
			$value = $this->_GetProperty($id);
		}
		
		return $value;
	}
	
	function SetVenue($value)
	{
		$id = array(array($this->typeOfEvent), array('LOCATION'));
		$this->_SetProperty($id, $value);
	}
	
	function ConferenceURL()
	{
		$value = NULL;
		if ($this->object != NULL)
		{
			$id = array(array($this->typeOfEvent), array('X-CONFERENCE-URL'));
			$value = $this->_GetProperty($id);
		}
		
		return $value;
	}
	
	function SetConferenceURL($value)
	{
		$id = array(array($this->typeOfEvent), array('X-CONFERENCE-URL'));
		$this->_SetProperty($id, $value);
	}
	
	function Location()
	{
		$value = NULL;
		if ($this->object != NULL)
		{
			$id = array(array($this->typeOfEvent), array('GEO'));
			$value = preg_split("/;\s*/", $this->_GetProperty($id));
   		if (count($value) < 2)
   		{
   			$value = NULL;
   		}
		}
		
		return $value;
	}
	
   // can pass both params or a two dimension array as the first param
	function SetLocation($latitude, $longitude = NULL)
	{
      if (is_array($latitude))
      {
         $longitude = $latitude[1];
         $latitude = $latitude[0];
      }
      
		$id = array(array($this->typeOfEvent), array('GEO'));
		if ($latitude != NULL && $longitude != NULL)
		{
			$this->_SetProperty($id, "$latitude;$longitude");
		}
		else
		{
			$this->_SetProperty($id, NULL);
		}
	}
	
	function Priority()
	{
		$value = NULL;
		if ($this->object != NULL)
		{
			$id = array(array($this->typeOfEvent), array('PRIORITY'));
			$value = $this->_GetProperty($id);
		}
		
		return $value;
	}
	
	function SetPriority($value)
	{
		$id = array(array($this->typeOfEvent), array('PRIORITY'));
		$this->_SetProperty($id, $value);
	}
	
	function Status()
	{
	// Status values for a "VEVENT"
	//	"TENTATIVE"		Indicates event is tentative
	//	"CONFIRMED"		Indicates event is definite
	//	"CANCELLED"		Indicates event was cancelled
	// Status values for "VTODO"
	//	"NEEDS-ACTION"	Indicates to-do needs action
	//	"COMPLETED"		Indicates to-do completed
	//	"IN-PROCESS"	Indicates to-do is actively being worked on
	//	"CANCELLED"		Indicates to-do was cancelled
	// Status values for "VJOURNAL"
	//	"DRAFT"			Indicates journal is draft
	//	"FINAL"			Indicates journal is final
	//	"CANCELLED"		Indicates journal is removed
	
		$value = NULL;
		if ($this->object != NULL)
		{
			$id = array(array($this->typeOfEvent), array('STATUS'));
			$value = $this->_GetProperty($id);
		}
		
		return $value;
	}
	
	function SetStatus($value)
	{
		$id = array(array($this->typeOfEvent), array('STATUS'));
		$this->_SetProperty($id, $value);
	}
	
	function PercentComplete()
	{
		$value = NULL;
		if ($this->object != NULL)
		{
			$id = array(array($this->typeOfEvent), array('PERCENT-COMPLETE'));
			$value = $this->_GetProperty($id);
		}
		
		return $value;
	}
	
	function SetPercentComplete($value)
	{
		$id = array(array($this->typeOfEvent), array('PERCENT-COMPLETE'));
		$this->_SetProperty($id, $value);
	}
	
	function Completed()
	{
		$value = NULL;
		$id = array(array($this->typeOfEvent), array('COMPLETED'));
		if ($this->object != NULL && $this->_GetProperty($id) != NULL)
		{
			$value = DateAndTime::FromString($this->_GetProperty($id));
		}
		
		return $value;
	}
	
	function SetCompleted($value)
	{
		$id = array(array($this->typeOfEvent), array('COMPLETED'));
		$this->_SetProperty($id, $value != NULL ? $value->ToFormat(DateAndTime::$ISO8601BasicFormat) : NULL);
	}
	
	function PictureURL()
	{
		$value = NULL;
		if ($this->object != NULL)
		{
			$id = array(array($this->typeOfEvent), array('X-PICTURE-URL'));
			$value = $this->_GetProperty($id);
		}
		
		return $value;
	}
	
	function SetPictureURL($value)
	{
		$id = array(array($this->typeOfEvent), array('X-PICTURE-URL'));
		$this->_SetProperty($id, $value);
	}
	
	function Picture()
	{
		$value = NULL;
		if ($this->object != NULL)
		{
			foreach (MimeTypes::GetImageExtensions() as $type)
			{
				$id = array(array($this->typeOfEvent), array('X-PICTURE-' . strtoupper($type)));
				$value = $this->_GetProperty($id);
				if ($value != NULL)
				{
					// we always assume the value is base64 encoded
					return array(MimeTypes::GetMimeTypeFromExtension($type), Utilities::DecodeBase64($value));
				}
			}
		}
		
		return NULL;
	}
	
   // can pass both params or a two dimension array as the first param
	function SetPicture($mimeType, $value = NULL)
	{
      if (is_array($mimeType))
      {
         $value = $mimeType[1];
         $mimeType = $mimeType[0];
      }
      
		// remove existing pictures so we don't end up with duplicate entries
		foreach (MimeTypes::GetImageExtensions() as $type)
		{
			$id = array(array($this->typeOfEvent), array('X-PICTURE-' . strtoupper($type)));
			$this->_SetProperty($id, NULL);	// remove the node
		}
      
      if ($mimeType != NULL && $value != NULL)
      {
   		// we always base64 encode the value
   		$id = array(array($this->typeOfEvent), array('X-PICTURE-' . strtoupper(MimeTypes::GetExtensionForMimeType($mimeType))));
   		$this->_SetProperty($id, Utilities::EncodeBase64($value));
      }
	}
	
	function Created()
	{
		$value = NULL;
		$id = array(array($this->typeOfEvent), array('DTSTAMP'));
		if ($this->object != NULL && $this->_GetProperty($id) != NULL)
		{
			$value = DateAndTime::FromString($this->_GetProperty($id));
		}
		
		return $value;
	}
	
	function SetCreated($value)
	{
		$id = array(array($this->typeOfEvent), array('DTSTAMP'));
		$this->_SetProperty($id, $value != NULL ? $value->ToFormat(DateAndTime::$ISO8601BasicFormat) : NULL);
	}
	
	function LastUpdated()
	{
		$value = NULL;
		$id = array(array($this->typeOfEvent), array('LAST-MODIFIED'));
		if ($this->object != NULL && $this->_GetProperty($id) != NULL)
		{
			$value = DateAndTime::FromString($this->_GetProperty($id));
		}
		
		return $value;
	}
	
	function SetLastUpdated($value)
	{
		$id = array(array($this->typeOfEvent), array('LAST-MODIFIED'));
		$this->_SetProperty($id, $value != NULL ? $value->ToFormat(DateAndTime::$ISO8601BasicFormat) : NULL);
	}
   
	function Sequence()
	{
		$value = NULL;
		if ($this->object != NULL)
		{
			$id = array(array($this->typeOfEvent), array('SEQUENCE'));
			$value = $this->_GetProperty($id);
		}
		
		return $value;
	}
	
	function SetSequence($value)
	{
		$id = array(array($this->typeOfEvent), array('SEQUENCE'));
		$this->_SetProperty($id, $value);
	}
	
/*
   // DRL NOTE! The ETAG is not standard, I added it for Google.
	function ETag()
	{
		$value = NULL;
		if ($this->object != NULL)
		{
			$id = array(array($this->typeOfEvent), array('ETAG'));
			$value = $this->_GetProperty($id);
		}
		
		return $value;
	}
	
	function SetETag($value)
	{
		$id = array(array($this->typeOfEvent), array('ETAG'));
		$this->_SetProperty($id, $value);
	}
*/   
	function Uid()
	{
		$value = NULL;
		if ($this->object != NULL)
		{
			$id = array(array($this->typeOfEvent), array('UID'));
			$value = $this->_GetProperty($id);
		}
		
		return $value;
	}
	
	function SetUid($value)
	{
		$id = array(array($this->typeOfEvent), array('UID'));
		$this->_SetProperty($id, $value);
	}

	// Some calendar system (Google) create new events to hold differences for instances
   // of recurring events. This field matches up all events sourced from the same recurrance.
	function RecurringUid()
	{
		$value = NULL;
		if ($this->object != NULL)
		{
			$id = array(array($this->typeOfEvent), array('RECURRENCE-ID'));
			$value = $this->_GetProperty($id);
		}
		
		return $value;
	}
	
	function SetRecurringUid($value)
	{
		$id = array(array($this->typeOfEvent), array('RECURRENCE-ID'));
		$this->_SetProperty($id, $value);
	}
	
	// returns an array where the indices are the emails
	function Attendees()
	{
		$result = array();
		if ($this->object != NULL)
		{
   		$id = array(array($this->typeOfEvent), array('ATTENDEE'));
   		$nodes = $this->_GetPropertyValues($id);
         if ($nodes)
         {
            foreach ($nodes as $node)
            {
               $attendee = new iCalAttendee($node['ID']);
               if (isset($node['CN'])) $attendee->SetDisplayName($node['CN'][0]);
               if (isset($node['ROLE'])) $attendee->SetRole($node['ROLE'][0]);
               if (isset($node['PARTSTAT'])) $attendee->SetStatus($node['PARTSTAT'][0]);
               if (isset($node['RSVP'])) $attendee->SetRsvp(strtoupper($node['RSVP'][0]) == 'TRUE');
               // this is non-standard...
               if (isset($node['ATTENDED'])) $attendee->SetAttended(strtoupper($node['ATTENDED'][0]) == 'TRUE');
               $result[$node['ID']] = $attendee;
            }
         }
		}
		return $result;
	}
	
	// the array indices are ignored
	function SetAttendees($attendees)
	{
      $values = array();
      foreach ($attendees as $attendee)
      {
         $temp = array();
         $temp['ID'] = $attendee->Id();
         if ($attendee->DisplayName()) $temp['CN'] = $attendee->DisplayName();
         if ($attendee->Role()) $temp['ROLE'] = $attendee->Role();
         if ($attendee->Status()) $temp['PARTSTAT'] = $attendee->Status();
         if ($attendee->Rsvp() !== NULL) $temp['RSVP'] = $attendee->Rsvp() ? 'TRUE' : 'FALSE';
         if ($attendee->Attended() !== NULL) $temp['ATTENDED'] = $attendee->Attended() ? 'TRUE' : 'FALSE';
         $values[] = $temp;
      }
		$id = array(array($this->typeOfEvent), array('ATTENDEE'));
		$this->_SetPropertyValues($id, $values);
	}
	
	// DRL FIXIT! The storage of reminders is non-standard - should be using VALARM elements!
	// DRL FIXIT! currently returns a simple array... enhance later
	// The format of the data is either a date/time (19980403T120000Z) or an offset from the start (-PT30M, -P4DT1H30M0S, P1D, -P2W)
	function Reminders()
	{
		$result = array();
		if ($this->object != NULL)
		{
			$id = array(array($this->typeOfEvent), array('X-REMINDER'));
			$nodes = $this->_GetPropertyValues($id);
         if ($nodes)
         {
            foreach ($nodes as $values)
            {
               $result[] = $values['ID'];
            }
         }
		}
		return $result;
	}
	
	// DRL FIXIT! currently takes a simple array... enhance later
	function SetReminders($ref_reminders)
	{
		$id = array(array($this->typeOfEvent), array('X-REMINDER'));
		$this->_SetPropertyValues($id, $ref_reminders);
	}
	
	function Calendars()
	{
		$values = array();
		if ($this->object != NULL)
		{
			$id = array(array($this->typeOfEvent), array('X-CALENDAR'));
			$nodes = $this->_GetPropertyValues($id);
         if ($nodes)
         {
            foreach ($nodes as $node)
            {
               $values[] = $node['ID'];
            }
         }
		}
		return $values;
	}
	
	function AddCalendar($uid)
	{
		if ($uid == null) { WriteError("Undefined calendar!"); return; }
	
		$ref_calendars = $this->Calendars();
		$calendars = $ref_calendars;
		
		if (!in_array($uid, $calendars))
		{
			$calendars[] = $uid;
   		$this->SetCalendars($calendars);
		}
	}
	
	function RemoveCalendar($uid)
	{
		$ref_calendars = $this->Calendars();
		$calendars = $ref_calendars;
		
		Utilities::RemoveFromArray($calendars, $uid);
	
		$this->SetCalendars($calendars);
	}
	
	function SetCalendars($ref_values)
	{
		$id = array(array($this->typeOfEvent), array('X-CALENDAR'));
		$this->_SetPropertyValues($id, $ref_values);
	}
   
	function TaskList()
	{
		$value = NULL;
		if ($this->object != NULL)
		{
			$id = array(array($this->typeOfEvent), array('X-TASKLIST'));
			$value = $this->_GetProperty($id);
		}
		
		return $value;
	}
	
	function SetTaskList($value)
	{
		$id = array(array($this->typeOfEvent), array('X-TASKLIST'));
		$this->_SetProperty($id, $value);
	}
 
	// most syncs only support a single calendar so all we can do is merge it over,
   // this is a useful callback for them to pass for the "Calendar" CopyFrom()
   public static function SupportsSingleCalendar($dest, $source, $replace)
   {
      $dest->SetCalendars(Utilities::MergeArrays($dest->Calendars(), $source->Calendars()));
   }
   
   // copy from another object, optionally only certain fields, and
   // optionally merge (combine collections such as attendees from both, 
   // don't remove a value if it is not set in the source, etc.)
   // These are never copied:
   //   IsDeleted
	//   Created
	//   LastUpdated
   //   Sequence
	//   Uid
   function CopyFrom($source, $fields = NULL, $replace = true)
   {
       $fields = Utilities::ExecuteCallbacksAndGetRemainingFields($this, $source, $fields, $replace);

	   if ((!$fields || Utilities::ArrayContains($fields, 'Type')) && ($replace || $source->Type())) $this->SetType($source->Type());
	   if ((!$fields || Utilities::ArrayContains($fields, 'Subject')) && ($replace || $source->Subject())) $this->SetSubject($source->Subject());
  	   if ((!$fields || Utilities::ArrayContains($fields, 'Body')) && ($replace || $source->Body())) $this->SetBody($source->Body());
	   if ((!$fields || Utilities::ArrayContains($fields, 'Organizer')) && ($replace || $source->Organizer())) $this->SetOrganizer($source->Organizer());
	   if ((!$fields || Utilities::ArrayContains($fields, 'StartDate')) && ($replace || $source->StartDate())) $this->SetStartDate($source->StartDate());
	   if ((!$fields || Utilities::ArrayContains($fields, 'EndDate')) && ($replace || $source->EndDate())) $this->SetEndDate($source->EndDate());
	   if ((!$fields || Utilities::ArrayContains($fields, 'StartTime')) && ($replace || $source->StartTime())) $this->SetStartTime($source->StartTime());
	   if ((!$fields || Utilities::ArrayContains($fields, 'EndTime')) && ($replace || $source->EndTime())) $this->SetEndTime($source->EndTime());
	   if ((!$fields || Utilities::ArrayContains($fields, 'RecurrenceRule')) && ($replace || $source->RecurrenceRule())) $this->SetRecurrenceRule($source->RecurrenceRule());
	   if ((!$fields || Utilities::ArrayContains($fields, 'RecurrenceDates')) && ($replace || $source->RecurrenceDates())) $this->SetRecurrenceDates($source->RecurrenceDates());
	   if ((!$fields || Utilities::ArrayContains($fields, 'RecurrenceExcludeRule')) && ($replace || $source->RecurrenceExcludeRule())) $this->SetRecurrenceExcludeRule($source->RecurrenceExcludeRule());
	   if ((!$fields || Utilities::ArrayContains($fields, 'RecurrenceExcludeDates')) && ($replace || $source->RecurrenceExcludeDates())) $this->SetRecurrenceExcludeDates($source->RecurrenceExcludeDates());
	   if ((!$fields || Utilities::ArrayContains($fields, 'Due')) && ($replace || $source->Due())) $this->SetDue($source->Due());
	   if ((!$fields || Utilities::ArrayContains($fields, 'DueDate')) && ($replace || $source->Due())) $this->SetDueDate($source->Due());
	   if ((!$fields || Utilities::ArrayContains($fields, 'Venue')) && ($replace || $source->Venue())) $this->SetVenue($source->Venue());
	   if ((!$fields || Utilities::ArrayContains($fields, 'Location')) && ($replace || $source->Location())) $this->SetLocation($source->Location());
	   if ((!$fields || Utilities::ArrayContains($fields, 'Priority')) && ($replace || $source->Priority())) $this->SetPriority($source->Priority());
	   if ((!$fields || Utilities::ArrayContains($fields, 'Status')) && ($replace || $source->Status())) $this->SetStatus($source->Status());
	   if ((!$fields || Utilities::ArrayContains($fields, 'PercentComplete')) && ($replace || $source->PercentComplete())) $this->SetPercentComplete($source->PercentComplete());
	   if ((!$fields || Utilities::ArrayContains($fields, 'Completed')) && ($replace || $source->Completed())) $this->SetCompleted($source->Completed());
	   if ((!$fields || Utilities::ArrayContains($fields, 'PictureURL')) && ($replace || $source->PictureURL())) $this->SetPictureURL($source->PictureURL());
	   if ((!$fields || Utilities::ArrayContains($fields, 'Picture')) && ($replace || $source->Picture())) $this->SetPicture($source->Picture());
	   if ((!$fields || Utilities::ArrayContains($fields, 'RecurringUid')) && ($replace || $source->RecurringUid())) $this->SetRecurringUid($source->RecurringUid());
	   if ((!$fields || Utilities::ArrayContains($fields, 'TaskList')) && ($replace || $source->TaskList())) $this->SetTaskList($source->TaskList());

      // the conference URL is tricky because different sources support different URLs and if you 
      // try to send one URL to another source it'll just get wiped out so what we do is we never 
      // clear out the URL because it likely just isn't supported in the source, and if both are set
      // we never copy it over if they are from different domains
	   if ((!$fields || Utilities::ArrayContains($fields, 'ConferenceURL')) && $source->ConferenceURL() && (!$this->ConferenceURL() || Url::GetDomain($this->ConferenceURL()) == Url::GetDomain($source->ConferenceURL()))) $this->SetConferenceURL($source->ConferenceURL());
            
      // these are a regular array
	   if (!$fields || Utilities::ArrayContains($fields, 'Categories')) { if ($replace) { $this->SetCategories($source->Categories()); } else { $this->SetCategories(Utilities::MergeArrays($this->Categories(), $source->Categories())); } }
	   if (!$fields || Utilities::ArrayContains($fields, 'Attendees')) { if ($replace) { $this->SetAttendees($source->Attendees()); } else { $this->SetAttendees(Utilities::MergeArrays($this->Attendees(), $source->Attendees())); } }
	   if (!$fields || Utilities::ArrayContains($fields, 'Reminders')) { if ($replace) { $this->SetReminders($source->Reminders()); } else { $this->SetReminders(Utilities::MergeArrays($this->Reminders(), $source->Reminders())); } }
	   if (!$fields || Utilities::ArrayContains($fields, 'Calendars')) { if ($replace) { $this->SetCalendars($source->Calendars()); } else { $this->SetCalendars(Utilities::MergeArrays($this->Calendars(), $source->Calendars())); } }
      
      // lets always take the most restrictive read-only state
      if ($source->IsReadOnly())
         $this->SetIsReadOnly(true);
   }
   
   // returns whether the passed item is fairly certain to be the 
   // same as this one and therefore they should be combined
   function IsSimilarTo($item)
   {
      $same = 0;
      $diff = 0;
      if (!empty($this->Type())) { if ($this->Type() == $item->Type()) $same++; else if (!empty($item->Type())) $diff++; }
      if (!empty($this->Subject())) { if ($this->Subject() == $item->Subject()) $same++; else if (!empty($item->Subject())) $diff++; }
      if (!empty($this->Body())) { if ($this->Body() == $item->Body()) $same++; else if (!empty($item->Body())) $diff++; }

      // ILA SA-161 we remove the check for organizers
      //if (!empty($this->Organizer())) { if ($this->Organizer() == $item->Organizer()) $same++; else if (!empty($item->Organizer())) $diff++; }

      // ILA SA-161 We replace the check for StartDate and StartTime with a check for Start so that we can compare absolute dates
      // if (!empty($this->StartDate())) { if (DateAndTime::Equal($this->StartDate(), $item->StartDate())) $same++; else if (!empty($item->StartDate())) $diff++; }
      // if (!empty($this->StartTime())) { if (DateAndTime::Equal($this->StartTime(), $item->StartTime())) $same++; else if (!empty($item->StartTime())) $diff++; }
		if (!empty($this->Start()))
		{
         $start1 = strtotime($this->Start()->ToString());
         $start2 = strtotime($item->Start()->ToString());
         if ( $start1 == $start2 ) $same++;
         else if (!empty($item->Start())) $diff++;
		}

		// @todo ILA SA-161 The following should also get replaced with a check for End() as above
      if (!empty($this->EndDate())) { if (DateAndTime::Equal($this->EndDate(), $item->EndDate())) $same++; else if (!empty($item->EndDate())) $diff++; }
      if (!empty($this->EndTime())) { if (DateAndTime::Equal($this->EndTime(), $item->EndTime())) $same++; else if (!empty($item->EndTime())) $diff++; }

      if (!empty($this->RecurrenceRule())) { if ($this->RecurrenceRule() == $item->RecurrenceRule()) $same++; else if (!empty($item->RecurrenceRule())) $diff++; }
      if (!empty($this->RecurrenceDates())) { if ($this->RecurrenceDates() == $item->RecurrenceDates()) $same++; else if (!empty($item->RecurrenceDates())) $diff++; }
      if (!empty($this->RecurrenceExcludeRule())) { if ($this->RecurrenceExcludeRule() == $item->RecurrenceExcludeRule()) $same++; else if (!empty($item->RecurrenceExcludeRule())) $diff++; }
      if (!empty($this->RecurrenceExcludeDates())) { if ($this->RecurrenceExcludeDates() == $item->RecurrenceExcludeDates()) $same++; else if (!empty($item->RecurrenceExcludeDates())) $diff++; }
      if (!empty($this->Due())) { if (DateAndTime::Equal($this->Due(), $item->Due())) $same++; else if (!empty($item->Due())) $diff++; }
      if (!empty($this->Venue())) { if ($this->Venue() == $item->Venue()) $same++; else if (!empty($item->Venue())) $diff++; }
      if (!empty($this->Location())) { if ($this->Location() == $item->Location()) $same++; else if (!empty($item->Location())) $diff++; }
      if (!empty($this->Priority())) { if ($this->Priority() == $item->Priority()) $same++; else if (!empty($item->Priority())) $diff++; }

      // ILA SA-161 Although we fixed the case saving of status in Microsoft ApiBase we need to check also for the already stored one with the different case
		// @todo ILA SA-161 make a note to DLR to change the getter of iCalEvent->Status()
      if (!empty($this->Status())) { if (strtoupper( $this->Status()) == strtoupper($item->Status())) $same++; else if (!empty($item->Status())) $diff++; }

      if (!empty($this->PercentComplete())) { if ($this->PercentComplete() == $item->PercentComplete()) $same++; else if (!empty($item->PercentComplete())) $diff++; }
      if (!empty($this->PictureURL())) { if ($this->PictureURL() == $item->PictureURL()) $same++; else if (!empty($item->PictureURL())) $diff++; }
      if (!empty($this->Picture())) { if ($this->Picture() == $item->Picture()) $same++; else if (!empty($item->Picture())) $diff++; }
// not sure which UID this would be (local, remote, etc.)
//      if (!empty($this->RecurringUid())) { if ($this->RecurringUid() == $item->RecurringUid()) $same++; else if (!empty($item->RecurringUid())) $diff++; }
      if (!empty($this->ConferenceURL())) { if ($this->ConferenceURL() == $item->ConferenceURL()) $same++; else if (!empty($item->ConferenceURL())) $diff++; }
      
      // the arrays don't have to match as they'll be merged
      
      return $diff == 0 && $same > 0;
   }
   
	function _SetDate($name, $value)
	{
		$temp = new DateAndTime();
		$id = array(array($this->typeOfEvent), array($name));
		if ($this->_GetProperty($id) != NULL)
			$temp = DateAndTime::FromString($this->_GetProperty($id));
      if ($value && $value->HasDate())
      {
         $date = $value->Date();
         $temp->SetDate($date[0], $date[1], $date[2]);
      }
      else
         $temp->SetDate(NULL, NULL, NULL);
      if ($temp->HasDate() || $temp->HasTime())
      {
      	$this->_SetProperty($id, $temp->ToFormat(DateAndTime::$ISO8601BasicFormat));
      }
      else
      {
      	$this->_SetProperty($id, NULL);
      }
	}
	
	function _SetTime($name, $value)
	{
		$temp = new DateAndTime();
		$id = array(array($this->typeOfEvent), array($name));
		if ($this->_GetProperty($id) != NULL)
      {
			$temp = DateAndTime::FromString($this->_GetProperty($id));
      }
      if ($value && $value->HasTime())
      {
         $time = $value->Time();
         $temp->SetTime($time[0], $time[1], $time[2], $time[3]);
         $temp->SetZone($value->Zone());
      }
      else
      {
         $temp->SetTime(NULL, NULL, NULL, NULL);
         $temp->SetZone(NULL);
      }
      if ($temp->HasDate() || $temp->HasTime())
      {
      	$this->_SetProperty($id, $temp->ToFormat(DateAndTime::$ISO8601BasicFormat));
      }
      else
      {
      	$this->_SetProperty($id, NULL);
      }
	}
	
	function _GetDate($name)
	{
		$value = NULL;
		$id = array(array($this->typeOfEvent), array($name));
		if ($this->object != NULL && $this->_GetProperty($id) != NULL)
		{
			$value = DateAndTime::FromString($this->_GetProperty($id));
			// clear the time and zone
			$value->SetTime(NULL, NULL, NULL, NULL);
			$value->setZone(NULL);
		}
		
		return $value;
	}
	
	function _GetTime($name)
	{
		$value = NULL;
		$id = array(array($this->typeOfEvent), array($name));
		if ($this->object != NULL && $this->_GetProperty($id) != NULL)
		{
			$value = DateAndTime::FromString($this->_GetProperty($id));
			// clear the date
         $value->SetDate(NULL, NULL, NULL);
		}
		
		return $value;
	}
	
	function _GetProperty($id)
	{
		$properties = $this->_GetPropertyValues($id);
		if ($properties != NULL && count($properties) > 0)
		{
			return $properties[0]['ID'];
		}
		
		return NULL;
	}
	
	function _SetProperty($id, $value = NULL)
	{
		if ($value != NULL && $value != "")
		{
			$this->_SetPropertyValues($id, array($value));
		}
		else
		{
			$this->_SetPropertyValues($id, NULL);
		}
	}

	// returns an array, each item of which is a reference to a
	// hash containing an "ID" key and any number of other keys for the params
	function _GetPropertyValues($id)
	{
		$nodes = $this->_FindNodes($id);
		if (count($nodes) == 0)
			return NULL;

		settype($id, 'array');			// for $id of name only
		if (!is_array($id[0]))			// for $id of array(name, node, ...)
			$id = array($id);			// convert to array(array(name, node), ...)
			
		$result = array();
		foreach ($nodes as $node)
		{
			$id[count($id)-1][1] = $node;	// set the node item of the last entry
			$params = $this->_GetParams($id);
			$params['ID'] = $this->_GetValue($id);
			$result[] = $params;
		}
		return $result;
	}
	
	// ref_values is an array, each item of which can be a regular item or it can be a reference to a
	// hash containing an "ID" key and any number of other keys for the params
	function _SetPropertyValues($id, $ref_values = NULL)
	{
		$nodes = $this->_FindNodes($id);
		
		settype($id, 'array');			// for $id of name only
		if (!is_array($id[0]))			// for $id of array(name, node, ...)
			$id = array($id);			// convert to array(array(name, node), ...)
			
		// remove existing values before adding new ones below
		foreach (array_reverse($nodes) as $node)
		{
			$id[count($id)-1][1] = $node;	// set the node item of the last entry
			$this->_RemoveNode($id);
		}
		$node = 0;
		$id[count($id)-1][1] = $node;	// set the node item of the last entry
		
		if ($ref_values != NULL)
		{
			$data = array();
			foreach ($ref_values as $temp)
			{
				if (is_array($temp))
				{
					$params = array();
					foreach (array_keys($temp) as $key)
					{
						if ($key != "ID")
						{
							$params[$key] = $temp[$key];
						}
					}
					$this->_SetValue($id, $temp['ID']);
					$this->_SetParams($id, $params);
				}
				else
				{
					$this->_SetValue($id, $temp);
				}
				$node++;
				$id[count($id)-1][1] = $node;	// set the node item of the last entry
			}
		}
		else
		{
			$this->_SetValue($id, NULL);
		}
	}
};

//spl_autoload_register(array('iCalEvent', 'autoload'));

/*
===================================================================

	This is code I use for testing this module. Use as an example.

===================================================================
*/


if (0)
{
	$iCal = iCalEvent::FromString(
"
BEGIN:VCALENDAR
VERSION:2.0
BEGIN:VEVENT
DTSTART:19980501T120000Z
RRULE:FREQ=YEARLY;UNTIL=20120101T000000Z
EXDATE;VALUE=PERIOD:20020501T120000Z/20030501T120000Z
EXDATE;VALUE=DATE:19990501T120000Z,20000501T120000Z
END:VEVENT
END:VCALENDAR
");

   $temp = $iCal->RecurrenceExcludeDates();
   $temp = array('20010501T120000Z', 'PERIOD:20120501T120000Z/20130501T120000Z');
   $iCal->SetRecurrenceExcludeDates($temp);
   $temp = $iCal->RecurrenceExcludeDates();
   $temp = $temp;

	$iCal = new iCalEvent();
	$iCal->SetSubject("This is a test!");
	$subject = $iCal->Subject();
	if ($subject != "This is a test!")
	{
		WriteDie("Error in SetSubject()");
	}
   $iCal->AddCalendar('Cal1');
   $iCal->AddCalendar('Cal2');
	$cals = $iCal->Calendars();
	if (!Utilities::ArrayEquals($cals, array('Cal1', 'Cal2')))
	{
		WriteDie("Error in AddCalendar()");
	}
   $iCal->SetMethod('TestMethod');
	$attendees = array(
      new iCalAttendee("mailto:dom\@uberfine.com"), 
      new iCalAttendee("FaceBook:1234567890", "Dom Lacerte", NULL, "TENTATIVE")
   );
	$iCal->SetAttendees($attendees);
	$text = $iCal->ToString();
	
	$data = File::ReadTextFile("iCalendar1.ics");
	$iCal = iCalEvent::FromString($data);
	$subject = $iCal->Subject();
	if ($subject != "Bastille Day Party")
	{
		WriteDie("Error in Subject()");
	}
	$text = $iCal->ToString();


	$iCal = new iCalEvent();
	$subject = $iCal->Subject();
	if ($subject != "")
	{
		WriteDie("Error in Subject()");
	}
	$date = $iCal->StartDate();
	if ($date != NULL)
	{
		WriteDie("Error in StartDate()");
	}
	
	$data = File::ReadTextFile("iCalendar1.ics");
	$iCal = iCalEvent::FromString($data);
	$type = $iCal->Type();
	if ($type != iCalEvent::$EventType)
	{
		WriteDie("Error in Type()");
	}
	$iCal->SetType(iCalEvent::$TaskType);
	$type = $iCal->Type();
	if ($type != iCalEvent::$TaskType)
	{
		WriteDie("Error in SetType()");
	}
	$subject = $iCal->Subject();
	if ($subject != "Bastille Day Party")
	{
		WriteDie("Error in Subject()");
	}
	$date = $iCal->StartDate();
	if (DateAndTime::NotEqual($date, DateAndTime::FromString('1997-07-14')))
	{
		WriteDie("Error in StartDate()");
	}
	$date = $iCal->EndDate();
	if (DateAndTime::NotEqual($date, DateAndTime::FromString('1997-07-15')))
	{
		WriteDie("Error in EndDate()");
	}
	$date = $iCal->StartTime();
	$d = DateAndTime::FromString('17:00:00 Z');
	if (DateAndTime::NotEqual($date, $d))
	{
		WriteDie("Error in StartTime()");
	}
	$date = $iCal->EndTime();
	if (DateAndTime::NotEqual($date, DateAndTime::FromString('03:59:59 Z')))
	{
		WriteDie("Error in EndTime()");
	}
	$date = $iCal->Due();
	if ($date != NULL)
	{
		WriteDie("Error in Due()");
	}
	
	$iCal->SetSubject("This is a test!");
	$subject = $iCal->Subject();
	if ($subject != "This is a test!")
	{
		WriteDie("Error in SetSubject()");
	}
	$categories = $iCal->Categories();
	if (count($categories) != 0)
	{
		WriteDie("Error in Categories()");
	}
	$categories = array("Cat1", "Cat2");
	$iCal->SetCategories($categories);
	$categories = $iCal->Categories();
	if (count($categories) != 2 || $categories[0] != "Cat1" || $categories[1] != "Cat2")
	{
		WriteDie("Error in SetCategories() or Categories()");
	}
	$attendees = $iCal->Attendees();
	if (count($attendees) != 3)
	{
		WriteDie("Error in Attendees()");
	}
	$attendees = array(
      new iCalAttendee("mailto:dom\@uberfine.com"), 
      new iCalAttendee("FaceBook:1234567890", "Dom Lacerte", NULL, "TENTATIVE")
   );
	$iCal->SetAttendees($attendees);
	$attendees = $iCal->Attendees();
	if (count($attendees) != 2 || 
      $attendees["mailto:dom\@uberfine.com"]->DisplayName() != NULL || 
      $attendees["mailto:dom\@uberfine.com"]->Email() != "dom\@uberfine.com" || 
      $attendees["FaceBook:1234567890"]->DisplayName() != "Dom Lacerte" || 
      $attendees["FaceBook:1234567890"]->Email() != NULL || 
      $attendees["FaceBook:1234567890"]->Status() != "TENTATIVE")
	{
		WriteDie("Error in SetAttendees() or Attendees()");
	}
	$reminders = $iCal->Reminders();
	if (count($reminders) != 0)
	{
		WriteDie("Error in Reminders()");
	}
	$reminders = array("-P15DT5H0M20S", "-P1W");
	$iCal->SetReminders($reminders);
	$reminders = $iCal->Reminders();
	if (count($reminders) != 2 || $reminders[0] != "-P15DT5H0M20S" || $reminders[1] != "-P1W")
	{
		WriteDie("Error in SetReminders() or Reminders()");
	}


	$temp = $iCal->Location();
	$latitude = $temp[0]; $longitude = $temp[1];
	if ($latitude != NULL || $longitude != NULL)
	{
		WriteDie("Error getting location 1");
	}
	$iCal->SetLocation("28.893", "-101.23");
	$temp = $iCal->Location();
	$latitude = $temp[0]; $longitude = $temp[1];
	if ($latitude != "28.893" || $longitude != "-101.23")
	{
		WriteDie("Error setting location 1");
	}
	
	$data2 = $iCal->ToString();

	$iCal2 = iCalEvent::FromString($data2);
	$type = $iCal2->Type();
	if ($type != iCalEvent::$TaskType)
	{
		WriteDie("Error in Type()");
	}
	$subject = $iCal2->Subject();
	if ($subject != "This is a test!")
	{
		WriteDie("Error in SetSubject()");
	}
	$date = $iCal2->StartDate();
	if (DateAndTime::NotEqual($date, DateAndTime::FromString('1997-07-14')))
	{
		WriteDie("Error in StartDate()");
	}
	$date = $iCal2->EndDate();
	if (DateAndTime::NotEqual($date, DateAndTime::FromString('1997-07-15')))
	{
		WriteDie("Error in EndDate()");
	}
	$date = $iCal2->StartTime();
	if (DateAndTime::NotEqual($date, DateAndTime::FromString('17:00:00 Z')))
	{
		WriteDie("Error in StartTime()");
	}
	$date = $iCal2->EndTime();
	if (DateAndTime::NotEqual($date, DateAndTime::FromString('03:59:59 Z')))
	{
		WriteDie("Error in EndTime()");
	}
	$date = $iCal2->Due();
	if ($date != NULL)
	{
		WriteDie("Error in Due()");
	}
	$categories = $iCal2->Categories();
	if (count($categories) != 2 || $categories[0] != "Cat1" || $categories[1] != "Cat2")
	{
		WriteDie("Error in SetCategories() or Categories()");
	}
	$temp = $iCal2->Location();
	$latitude = $temp[0]; $longitude = $temp[1];
	if ($latitude != "28.893" || $longitude != "-101.23")
	{
		WriteDie("Error setting location 1");
	}

	$data = File::ReadTextFile("iCalendar2.ics");
	$iCal = iCalEvent::FromString($data);
	$type = $iCal->Type();
	if ($type != iCalEvent::$EventType)
	{
		WriteDie("Error in Type()");
	}
	$categories = $iCal->Categories();
	if (count($categories) != 3 || $categories[0] != "ANNIVERSARY" || $categories[1] != "PERSONAL" || $categories[2] != "SPECIAL OCCASION")
	{
		WriteDie("Error in Categories()");
	}
	$attendees = $iCal->Attendees();
	if (count($attendees) != 2 ||
		$attendees['MAILTO:hcabot@host2.com']->DisplayName() != 'Henry Cabot' ||
		$attendees['MAILTO:jdoe@host1.com']->Role() != 'REQ-PARTICIPANT' || 
      $attendees['MAILTO:jdoe@host1.com']->Status() != 'ACCEPTED')
	{
		WriteDie("Error in Attendees()");
	}

	$iCal->SetPicture("image/jpeg", "SOMEMOREDATA");
	$iCal->SetPictureURL("http://URL.jpeg");
	$pic = $iCal->Picture();
	if ($iCal->PictureURL() != "http://URL.jpeg" ||
		$pic[0] != "image/jpeg" || $pic[1] != "SOMEMOREDATA")
	{
		WriteDie("Error with setting/getting pictures");
	}

	$iCal = iCalEvent::FromString(
"
BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//ABC Corporation//NONSGML My Product//EN
BEGIN:VTODO
DTSTAMP:19980130T134500Z
SEQUENCE:2
UID:uid4@host1.com
ORGANIZER;CN=John Doe:MAILTO:unclesam@us.gov
ATTENDEE;PARTSTAT=ACCEPTED:MAILTO:jqpublic@example.com
DUE:19980415T235959
STATUS:NEEDS-ACTION
SUMMARY:Submit Income Taxes
BEGIN:VALARM
ACTION:AUDIO
TRIGGER:19980403T120000
ATTACH;FMTTYPE=audio/basic:http://example.com/pub/audio-
 files/ssbanner.aud
REPEAT:4
DURATION:PT1H
END:VALARM
END:VTODO
END:VCALENDAR
");

	$organizer = $iCal->Organizer();
	if (!$organizer ||
		$organizer->Email() != 'unclesam@us.gov' ||
		$organizer->DisplayName() != 'John Doe')
	{
		WriteDie("Error in Organizer()");
	}
	$organizer = new iCalAttendee("FaceBook:1234567890","Dom Lacerte");
	$iCal->SetOrganizer($organizer);
	$organizer = $iCal->Organizer();
	if (!$organizer ||
		$organizer->Id() != 'FaceBook:1234567890' ||
		$organizer->DisplayName() != 'Dom Lacerte')
	{
		WriteDie("Error in SetOrganizer()");
	}

   $iCal->SetSequence($iCal->Sequence()+1);
   $temp = $iCal->ToString();
   $iCal = iCalEvent::FromString($temp);
	if ($iCal->Sequence() != 3)
	{
		WriteDie("Error in Sequence()");
	}

// DRL FIXIT! Add support for and test VALARM!
}


?>

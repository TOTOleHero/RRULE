<?php

require_once(dirname(__FILE__) . '/DateAndTime.php');
require_once(dirname(__FILE__) . '/Log.php');
//require_once(dirname(__FILE__) . '/MimeTypes.php');
require_once(dirname(__FILE__) . '/Utilities.php');
require_once(dirname(__FILE__) . '/vCardCalBase.php');

/*
===================================================================

Implementation.

===================================================================

This module implements a NON-STANDARD wrapper for RFC 2426 with 
respect to identifying calendars.

Note that the methods of this object which return a simple string
never return an null value since the storage of a vCard can't
distinguish between a null and an empty string.

===================================================================
These are the fields we support:

	FN - calendar name
   X-CALENDAR-KIND - always "calendar"
	NOTE - free form text
	REV - instance revision (as in "1995-10-31T22:27:10Z" or "1997-11-15")
	UID - unique identifier
	VERSION - must be "3.0"
*/
	

class iCalCalendar extends vCardCalBase
{
    /**
	 * SPL-compatible autoloader.
	 *
     * @param string $className Name of the class to load.
     *
     * @return boolean
    public static function autoload($className)
    {
        if ($className != 'iCalCalendar') {
            return false;
		}
        return include str_replace('_', '/', $className) . '.php';
    }
     */

    /**
    * Builder factory
    *
    * Creates an instance of the correct parser class, based on the
    * parameter passed. For example, File_IMC::parse('vCard') creates
    * a new object to parse a vCard file.
    *
    * @return mixed
    * @throws File_IMC_Exception In case the driver is not found/available.
    */
	function __construct()
	{
		$this->object = File_IMC::build('vCard');   // DRL FIXIT? vCard is weird to use, but we just need a place to hold a name, etc.

		// set some default/required fields...
		$this->object->setVersion();
		$this->_SetValue(array('X-CALENDAR-KIND', $this->_GetNode('X-CALENDAR-KIND')), 'calendar');
	}
	
	function __destruct()
	{
		$this->object = null;
	}

	function ToBinary()
	{
		return $this->ToString();
	}
	
	function ToString()
	{
		$result = "";
		if ($this->object != null)
			$result = $this->Fetch('VCARD');
			
		return $result;
	}
	
	static function FromBinary($binary)
	{
		return iCalCalendar::FromString($binary);
	}
	
	static function FromString($string)
	{
		if ($string == null || strlen($string) == 0)
		{
			return null;
		}
		
		$result = new iCalCalendar();
		
		try
		{
			// create vCard parser
			$parse = File_IMC::parse('vCard');
			
			// parse a vCard file and store the data in $cardinfo
			$info = $parse->fromText($string);
			
			if ($result->object == null)
				$result->object = File_IMC::build('vCard');
			
			$key = array_keys($info);	// could be vCard or VCARD
			$result->object->setFromArray($info[$key[0]]);
		}
		catch (Exception $e)
		{
			WriteError("Error evaluating vCard object: " . $e . "/nSTART...\n$string\nEND");
		}
		
		return $result;
	}

	function Name()
	{
		$node = $this->_FindNode('FN');
		if ($node == -1) { return ""; }
		
		return $this->_GetValue(array('FN', $node));
	}
	
	function SetName($value)
	{
		$this->_SetValue(array('FN', $this->_GetNode('FN')), $value);
	}
	
	function Note()
	{
		$node = $this->_FindNode('NOTE');
		if ($node == -1) { return ""; }
		
		return $this->_GetValue(array('NOTE', $node));
	}
	
	function SetNote($value)
	{
		$this->_SetValue(array('NOTE', $this->_GetNode('NOTE')), $value);
	}
	
	function LastUpdated()
	{
		$node = $this->_FindNode('REV');
		if ($node == -1) { return null; }
		
		$date;
		$str = $this->_GetValue(array('REV', $node));
		if ($str != null && strcmp($str, "") != 0)
		{
			// format can be 1995-10-31T22:27:10Z or 1997-11-15
			$date = DateAndTime::FromString($str);
		}
		return $date;
	}
	
	function SetLastUpdated($value)
	{
		$str = NULL;
		if ($value != null && $value->HasDate())
		{
         $str = $value->ToFormat(DateAndTime::$ISO8601BasicFormat);
		}
		$this->_SetValue(array('REV', $this->_GetNode('REV')), $str);
	}
/*	
   // DRL NOTE! The ETAG is not standard, I added it for Google.
	function ETag()
	{
		$node = $this->_FindNode('ETAG');
		if ($node == -1) { return ""; }
		
		return $this->_GetValue(array('ETAG', $node));
	}

	function SetETag($value)
	{
		$this->_SetValue(array('ETAG', $this->_GetNode('ETAG')), $value);
	}
*/	
	function Uid()
	{
		$node = $this->_FindNode('UID');
		if ($node == -1) { return ""; }
		
		return $this->_GetValue(array('UID', $node));
	}
	
	function SetUid($value)
	{
		$this->_SetValue(array('UID', $this->_GetNode('UID')), $value);
	}
	
   // copy from another object, optionally only certain fields, and 
   // optionally merge (combine collections such as attendees from both, 
   // don't remove a value if it is not set in the source, etc.)
   // These are never copied:
   //   IsDeleted
	//   LastUpdated
	//   Uid
   function CopyFrom($source, $fields = NULL, $replace = true)
   {
       $fields = Utilities::ExecuteCallbacksAndGetRemainingFields($this, $source, $fields, $replace);

	   if ((!$fields || Utilities::ArrayContains($fields, 'Name')) && ($replace || $source->Name())) $this->SetName($source->Name());
	   if ((!$fields || Utilities::ArrayContains($fields, 'Note')) && ($replace || $source->Note())) $this->SetNote($source->Note());
      
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
      if (!empty($this->Name())) { if ($this->Name() == $item->Name()) $same++; else if (!empty($item->Name())) $diff++; }
      
      return $diff == 0 && $same > 0;
   }
   
	// gets a node for setting, creates it if not found
	private function _GetNode($name)
	{
		$node = $this->_FindNode($name);
	
		// if no match found add a new node
		if ($node == -1)
			$node = $this->_CreateNode($name, array());
	
		return $node;
	}
	
	// looks for a matching node, returns index or -1 if not found
	private function _FindNode($name)
	{
		$nodes = $this->_FindNodes($name);
	
		if (count($nodes) == 0)
			return -1;
			
		return $nodes[0];
	}
}

//spl_autoload_register(array('iCalCalendar', 'autoload'));

/*
===================================================================

	This is code I use for testing this module. Use as an example.

===================================================================
*/

if (0)
{
}


?>

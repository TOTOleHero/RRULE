<?php

require_once(dirname(__FILE__) . '/DateAndTime.php');
require_once(dirname(__FILE__) . '/Log.php');
require_once(dirname(__FILE__) . '/Utilities.php');
require_once(dirname(__FILE__) . '/vCardCalBase.php');

/*
===================================================================

Implementation.

===================================================================

This module implements a wrapper for RFC 2426 with respect to GROUPs.

Note that the methods of this object which return a simple string
never return a null value since the storage of a vCard can't
distinguish between a null and an empty string.

===================================================================
These are the vCard (RFC 2426) fields we support:

	FN - group name
   X-ADDRESSBOOKSERVER-KIND - always "group"
   X-ADDRESSBOOKSERVER-MEMBER - UIDs of members
	NOTE - free form text
	REV - vCard instance revision (as in "1995-10-31T22:27:10Z" or "1997-11-15")
	UID - unique identifier
	VERSION - must be "3.0"
*/
	

class vCardGroup extends vCardCalBase
{
   protected $isArchive = false;// this is not persisted!

    /**
	 * SPL-compatible autoloader.
	 *
     * @param string $className Name of the class to load.
     *
     * @return boolean
    public static function autoload($className)
    {
        if ($className != 'vCardGroup') {
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
		$this->object = File_IMC::build('vCard');  // DRL FIXIT? vCard is weird to use, but we just need a place to hold a name, etc.

		// set some default/required fields...
		$this->object->setVersion();
		$this->_SetValue(array('X-ADDRESSBOOKSERVER-KIND', $this->_GetNode('X-ADDRESSBOOKSERVER-KIND')), 'group');
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
		return vCardGroup::FromString($binary);
	}
	
	static function FromString($string)
	{
		if ($string == null || strlen($string) == 0)
		{
			return null;
		}
		
		$result = new vCardGroup();
		
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

   public function IsArchive()
   {
      return $this->isArchive;
   }
   
   public function SetIsArchive($val)
   {
      $this->isArchive = $val;
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
	
   // this returns the UID of the parent group or NULL
	function Parent()
	{
		$node = $this->_FindNode('X-GROUP-PARENT');
		if ($node == -1) { return ""; }
		
		return $this->_GetValue(array('X-GROUP-PARENT', $node));
	}
	
	function SetParent($value)
	{
		$this->_SetValue(array('X-GROUP-PARENT', $this->_GetNode('X-GROUP-PARENT')), $value);
	}
	
   // this returns UIDs of contacts
	function Members()
	{
		$nodes = $this->_FindNodes('X-ADDRESSBOOKSERVER-MEMBER');
		
		$values = array();
		foreach ($nodes as $node)
		{
			$values[] = $this->_GetValue(array('X-ADDRESSBOOKSERVER-MEMBER', $node));
		}
		
		return $values;
	}
	
	function AddMember($uid)
	{
		if ($uid == null) { WriteError("Undefined member!"); return; }
	
		$ref_members = $this->Members();
		$members = $ref_members;
		
		if (!in_array($uid, $members))
		{
			$members[] = $uid;
   		$this->SetMembers($members);
		}
	}
	
	function RemoveMember($uid)
	{
		$ref_members = $this->Members();
		$members = $ref_members;
		
		Utilities::RemoveFromArray($members, $uid);
	
		$this->SetMembers($members);
	}
	
	function SetMembers($ref_values)
	{
		$newMembers = $ref_values;
		
		$nodes = $this->_FindNodes('X-ADDRESSBOOKSERVER-MEMBER');
		foreach (array_reverse($nodes) as $node)	// remove nodes in reverse order to preserve indices
		{
			$this->_RemoveNode(array('X-ADDRESSBOOKSERVER-MEMBER', $node));
		}
	
		sort($newMembers);	   // Store nodes in order - EL
		foreach ($newMembers as $uid)
		{
			$this->_CreateNode('X-ADDRESSBOOKSERVER-MEMBER', array(), $uid);
		}
	}
	
   // copy from another vCard, optionally only certain fields, and 
   // optionally merge (combine collections such as phones from both, 
   // don't remove a value if it is not set in the source, etc.)
   // These are never copied:
   //   IsDeleted
	//   LastUpdated
	//   Uid
   function CopyFrom($source, $fields = NULL, $replace = true)
   {
       $fields = Utilities::ExecuteCallbacksAndGetRemainingFields($this, $source, $fields, $replace);

	   if ((!$fields || Utilities::ArrayContains($fields, 'Parent')) && ($replace || $source->Parent())) $this->SetParent($source->Parent());
	   if ((!$fields || Utilities::ArrayContains($fields, 'Name')) && ($replace || $source->Name())) $this->SetName($source->Name());
	   if ((!$fields || Utilities::ArrayContains($fields, 'Note')) && ($replace || $source->Note())) $this->SetNote($source->Note());
     
      // this is a regular array
	   if (!$fields || Utilities::ArrayContains($fields, 'Members')) { if ($replace) { $this->SetMembers($source->Members()); } else { $this->SetMembers(Utilities::MergeArrays($this->Members(), $source->Members())); } }
      
      // lets always take the most restrictive read-only and archive flags
      if ($source->IsReadOnly())
         $this->SetIsReadOnly(true);
      if ($source->IsArchive())
         $this->SetIsArchive(true);
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

//spl_autoload_register(array('vCardGroup', 'autoload'));

/*
===================================================================

	This is code I use for testing this module. Use as an example.

===================================================================
*/

if (0)
{
	// added this test because it was previously failing on an empty vCard
	$card = new vCardGroup();
	$members =
	array(
		"jqpublic\@xyz.dom1.com"
	);
	$card->SetMembers($members);
	if (count($card->Members()) != 1)
	{
		WriteDie("Error with SetMembers()");
	}
	$card->AddMember("Frank_Dawson\@Lotus.com");
	if (count($card->Members()) != 2)
	{
		WriteDie("Error with AddMember()");
	}
	$card->RemoveMember("jqpublic\@xyz.dom1.com");
	if (count($card->Members()) != 1)
	{
		WriteDie("Error with RemoveMember()");
	}
	
	$str = "
BEGIN:VCARD
N:Friends
FN:Friends
X-ADDRESSBOOKSERVER-KIND:group
X-ADDRESSBOOKSERVER-MEMBER:urn:uuid:AwesomeO
X-ADDRESSBOOKSERVER-MEMBER:urn:uuid:Xavier
X-ADDRESSBOOKSERVER-MEMBER:urn:uuid:joe
UID:group-uid
END:VCARD
	";
	
	$card = vCardGroup::FromString($str);
	if (count($card->Members()) != 3)
	{
		WriteDie("Error with Members()");
	}
	$name = $card->Name();
	if ($name != "Friends")
	{
		WriteDie("Error getting name");
	}
	$card->SetName("Buddies");
	$name = $card->Name();
	if ($name != "Buddies")
	{
		WriteDie("Error setting name");
	}
}


?>

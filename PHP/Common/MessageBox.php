<?php

require_once(dirname(__FILE__) . '/DateAndTime.php');
require_once(dirname(__FILE__) . '/Utilities.php');

/*
===================================================================

Implementation.

===================================================================

This module implements a wrapper for a MIME email.

===================================================================
*/
	

class MessageBox
{
   // these are not persisted!
   protected $uid = NULL;
   protected $isDeleted = false;
   protected $name = NULL;        // Examples: Email, SIP IM, XMPP, Skype, SMS
   protected $protocol = NULL;    // examples: mailto, sip, xmpp, skype, sms
   protected $lastUpdated = NULL;
   
	function __construct()
	{

	}
	
	function __destruct()
	{

	}

//	function ToBinary()
//	{
//	}
	
//	function ToString()
//	{
//	}
	
//	static function FromBinary($binary)
//	{
//	}
	
//	static function FromString($string)
//	{
//	}

   public function Uid()
   {
      return $this->uid;
   }
   
   public function SetUid($val)
   {
      $this->uid = $val;
   }
   
   public function IsDeleted()
   {
      return $this->isDeleted;
   }
   
   public function SetIsDeleted($val)
   {
      $this->isDeleted = $val;
   }
   
   public function Name()
   {
      return $this->name;
   }
   
   public function SetName($val)
   {
      $this->name = $val;
   }
   
   public function Protocol()
   {
      return $this->protocol;
   }
   
   public function SetProtocol($val)
   {
      $this->protocol = $val;
   }
   
   public function LastUpdated()
   {
      return $this->lastUpdated ? $this->lastUpdated : $this->Date();
   }
   
   public function SetLastUpdated($val)
   {
      $this->lastUpdated = $val;
   }

   // copy from another MessageBox, optionally only certain fields, and 
   // optionally merge (combine collections from both, 
   // don't remove a value if it is not set in the source, etc.)
   // These are never copied:
   //   IsDeleted
	//   LastUpdated
	//   Uid
   function CopyFrom($source, $fields = NULL, $replace = true)
   {
       $fields = Utilities::ExecuteCallbacksAndGetRemainingFields($this, $source, $fields, $replace);

	   if ((!$fields || Utilities::ArrayContains($fields, 'Name')) && ($replace || $source->Name())) $this->SetName($source->Name());
	   if ((!$fields || Utilities::ArrayContains($fields, 'Protocol')) && ($replace || $source->Protocol())) $this->SetProtocol($source->Protocol());
   }

   // returns whether the passed item is fairly certain to be the 
   // same as this one and therefore they should be combined
   function IsSimilarTo($item)
   {
      $same = 0;
      $diff = 0;
      if (!empty($this->Name())) { if ($this->Name() == $item->Name()) $same++; else if (!empty($item->Name())) $diff++; }
      if (!empty($this->Protocol())) { if ($this->Protocol() == $item->Protocol()) $same++; else if (!empty($item->Protocol())) $diff++; }
      
      return $diff == 0 && $same > 0;
   }
}

/*
===================================================================

	This is code I use for testing this module. Use as an example.

===================================================================
*/

if (0)
{
}


?>

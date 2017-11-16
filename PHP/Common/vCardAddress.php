<?php
require_once(dirname(__FILE__) . '/Utilities.php');

class vCardAddress
{
	private $postOfficeBox = null;
	private $extendedAddress = null;
	private $streetAddress = null;
	private $city = null;
	private $region = null;
	private $country = null;
	private $postalCode = null;
	private $tags = null;

    /**
	 * SPL-compatible autoloader.
	 *
     * @param string $className Name of the class to load.
     *
     * @return boolean
    public static function autoload($className)
    {
        if ($className != 'vCardAddress') {
            return false;
		}
        return include str_replace('_', '/', $className) . '.php';
    }
     */

	function __construct($vCard = NULL, $node = -1)
	{
		if ($vCard && $node != -1)
		{
			$this->postOfficeBox = $vCard->_GetValue(array('ADR', $node, 'po_box'));
			$this->extendedAddress = $vCard->_GetValue(array('ADR', $node, 'extended'));
			$this->streetAddress = $vCard->_GetValue(array('ADR', $node, 'street'));
			$this->city = $vCard->_GetValue(array('ADR', $node, 'city'));
			$this->region = $vCard->_GetValue(array('ADR', $node, 'region'));
			$this->country = $vCard->_GetValue(array('ADR', $node, 'country'));
			$this->postalCode = $vCard->_GetValue(array('ADR', $node, 'post_code'));
			$this->tags = $vCard->_GetTypes(array('ADR', $node));
		}
	}
	
	function __destruct()
	{
	}

	// this assumes we're setting the values in an empty node (i.e. not updating an existing node with old data)
	function ToNode($vCard, $node)
	{
		// we can't set an undef value as it would remove the node so we check before setting - this
		// will not work if we're updating an existing node!
		if ($this->postOfficeBox) { $vCard->_SetValue(array('ADR', $node, 'po_box'), $this->postOfficeBox); }
		if ($this->extendedAddress) { $vCard->_SetValue(array('ADR', $node, 'extended'), $this->extendedAddress); }
		if ($this->streetAddress) { $vCard->_SetValue(array('ADR', $node, 'street'), $this->streetAddress); }
		if ($this->city) { $vCard->_SetValue(array('ADR', $node, 'city'), $this->city); }
		if ($this->region) { $vCard->_SetValue(array('ADR', $node, 'region'), $this->region); }
		if ($this->country) { $vCard->_SetValue(array('ADR', $node, 'country'), $this->country); }
		if ($this->postalCode) { $vCard->_SetValue(array('ADR', $node, 'post_code'), $this->postalCode); }
	
	// The tags should already have been set when the node was created!
	//	$this->tags
	}
	
	function IsEmpty()
	{
		return $this->postOfficeBox == null && 
			$this->extendedAddress == null && 
			$this->streetAddress = null && 
			$this->city = null && 
			$this->region = null && 
			$this->country = null && 
			$this->postalCode = null && 
			$this->tags = null;
	}
	
	function PostOfficeBox()
	{
		return $this->postOfficeBox;
	}
	
	function SetPostOfficeBox($value)
	{
		$this->postOfficeBox = $value;
	}
	
	function ExtendedAddress()
	{
		return $this->extendedAddress;
	}
	
	function SetExtendedAddress($value)
	{
		$this->extendedAddress = $value;
	}
	
	function StreetAddress()
	{
		return $this->streetAddress;
	}
	
	function SetStreetAddress($value)
	{
		$this->streetAddress = $value;
	}
	
	function City()
	{
		return $this->city;
	}
	
	function SetCity($value)
	{
		$this->city = $value;
	}
	
	function Region()
	{
		return $this->region;
	}
	
	function SetRegion($value)
	{
		$this->region = $value;
	}
	
	function Country()
	{
		return $this->country;
	}
	
	function SetCountry($value)
	{
		$this->country = $value;
	}
	
	function PostalCode()
	{
		return $this->postalCode;
	}
	
	function SetPostalCode($value)
	{
		$this->postalCode = $value;
	}
	
	function Tags()
	{
		if ($this->tags != null)
		{
			return $this->tags;
		}
		
		return array();
	}
	
	// takes a reference to an array of strings as in ["HOME", "WORK", "OTHER"]
	function SetTags($value)
	{
		if ($value != null && count($value) == 0) { $value = null; }
		
		$this->tags = $value;
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

	   if ((!$fields || Utilities::ArrayContains($fields, 'PostOfficeBox')) && ($replace || $source->PostOfficeBox())) $this->SetPostOfficeBox($source->PostOfficeBox());
	   if ((!$fields || Utilities::ArrayContains($fields, 'ExtendedAddress')) && ($replace || $source->ExtendedAddress())) $this->SetExtendedAddress($source->ExtendedAddress());
  	   if ((!$fields || Utilities::ArrayContains($fields, 'StreetAddress')) && ($replace || $source->StreetAddress())) $this->SetStreetAddress($source->StreetAddress());
	   if ((!$fields || Utilities::ArrayContains($fields, 'City')) && ($replace || $source->City())) $this->SetCity($source->City());
	   if ((!$fields || Utilities::ArrayContains($fields, 'Region')) && ($replace || $source->Region())) $this->SetRegion($source->Region());
	   if ((!$fields || Utilities::ArrayContains($fields, 'Country')) && ($replace || $source->Country())) $this->SetCountry($source->Country());
	   if ((!$fields || Utilities::ArrayContains($fields, 'PostalCode')) && ($replace || $source->PostalCode())) $this->SetPostalCode($source->PostalCode());
   }

   // returns whether the passed item is fairly certain to be the 
   // same as this one and therefore they should be combined
   function IsSimilarTo($item)
   {
      $same = 0;
      $diff = 0;
      if (!empty($this->PostOfficeBox())) { if ($this->PostOfficeBox() == $item->PostOfficeBox()) $same++; else if (!empty($item->PostOfficeBox())) $diff++; }
      if (!empty($this->ExtendedAddress())) { if ($this->ExtendedAddress() == $item->ExtendedAddress()) $same++; else if (!empty($item->ExtendedAddress())) $diff++; }
      if (!empty($this->StreetAddress())) { if ($this->StreetAddress() == $item->StreetAddress()) $same++; else if (!empty($item->StreetAddress())) $diff++; }
      if (!empty($this->City())) { if ($this->City() == $item->City()) $same++; else if (!empty($item->City())) $diff++; }
      if (!empty($this->Region())) { if ($this->Region() == $item->Region()) $same++; else if (!empty($item->Region())) $diff++; }
      if (!empty($this->Country())) { if ($this->Country() == $item->Country()) $same++; else if (!empty($item->Country())) $diff++; }
      if (!empty($this->PostalCode())) { if ($this->PostalCode() == $item->PostalCode()) $same++; else if (!empty($item->PostalCode())) $diff++; }
      
      return $diff == 0 && $same > 0;
   }
   
	function ToString()
	{
		$tags = $this->Tags();
		
		$result = "";
		
		if ($this->postOfficeBox != null) { $result = Utilities::CombineStrings(', ', $result, $this->postOfficeBox); }
		if ($this->extendedAddress != null) { $result = Utilities::CombineStrings(', ', $result, $this->extendedAddress); }
		if ($this->streetAddress != null) { $result = Utilities::CombineStrings(', ', $result, $this->streetAddress); }
		if (strlen($result)) { $result .= "\r\n"; }
		if ($this->city != null || $this->region != null || $this->country != null)
		{
			$result .= $this->city . ", " . $this->region . ", " . $this->country . "\r\n";
		}
		if ($this->postalCode != null) { $result .= $this->postalCode . "\r\n"; }
		if (count($tags) > 0) { $result .= "Tags: " . join(', ', $tags); }
		
		return $result;
	}
};

//spl_autoload_register(array('vCardAddress', 'autoload'));

?>

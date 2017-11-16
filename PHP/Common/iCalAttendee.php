<?php
require_once(dirname(__FILE__) . '/Utilities.php');

class iCalAttendee
{
   private $id = null;            // as in 'MAILTO:me@test.com'
	private $email = null;         // as in 'me@test.com'
	private $displayName = null;
	private $role = null;          // CHAIR, REQ-PARTICIPANT, OPT-PARTICIPANT, NON-PARTICIPANT
	private $status = null;        // NEEDS-ACTION, ACCEPTED, DECLINED, TENTATIVE, DELEGATED and for VTODO: COMPLETED, IN-PROCESS
	private $rsvp = null;          // TRUE, FALSE   NOTE: this indicates whether an RSVP should be requested, see status for actual RSVP!
   // this is non-standard...
   private $attended = null;      // TRUE, FALSE

    /**
	 * SPL-compatible autoloader.
	 *
     * @param string $className Name of the class to load.
     *
     * @return boolean
    public static function autoload($className)
    {
        if ($className != 'iCalAttendee') {
            return false;
		}
        return include str_replace('_', '/', $className) . '.php';
    }
     */

	function __construct($id = NULL, $displayName = NULL, $role = NULL, $status = NULL, $rsvp = NULL, $attended = NULL)
	{
		$this->id = $id; 
      assert(!empty(Url::GetProtocol($id)));
      assert(strtoupper(substr($id, 0, 7)) == "MAILTO:" || strtoupper(substr($id, 0, 7)) == "TEL:");
		if (strtoupper(Url::GetProtocol($id)) == 'MAILTO') $this->email = Url::StripProtocol($id); 
		$this->displayName = $displayName; 
		$this->role = $role;
		$this->status = $status; 
		$this->rsvp = $rsvp;
		$this->attended = $attended;
	}
	
	function __destruct()
	{
	}

	function IsEmpty()
	{
		return $this->id == null && 
			$this->email == null && 
			$this->displayName == null && 
			$this->role == null && 
			$this->status == null && 
			$this->rsvp == null;
	}
	
	function Id()
	{
		return $this->id;
	}
	
	function SetId($value)
	{
      assert(!empty($value));
      assert(!empty(Url::GetProtocol($value)));
      assert(strtoupper(substr($value, 0, 7)) == "MAILTO:" || strtoupper(substr($value, 0, 7)) == "TEL:");
		$this->id = $value;
	}
	
	function Email()
	{
		return $this->email;
	}
	
	function SetEmail($value)
	{
		$this->email = $value;
	}
	
	function DisplayName()
	{
		return $this->displayName;
	}
	
	function SetDisplayName($value)
	{
		$this->displayName = $value;
	}
	
	function Role()
	{
		return $this->role;
	}
	
	function SetRole($value)
	{
		$this->role = $value;
	}
	
	function Status()
	{
		return $this->status;
	}
	
	function SetStatus($value)
	{
		$this->status = $value;
	}
	
	function Rsvp()
	{
		return $this->rsvp;
	}
	
	function SetRsvp($value)
	{
		$this->rsvp = $value;
	}
	
   // this is non-standard...
	function Attended()
	{
		return $this->attended;
	}
	
   // this is non-standard...
	function SetAttended($value)
	{
		$this->attended = $value;
	}
	
   // copy from another iCalAttendee, optionally only certain fields, and 
   // optionally merge (combine collections such as phones from both, 
   // don't remove a value if it is not set in the source, etc.)
   // These are never copied:
   //   IsDeleted
	//   LastUpdated
	//   Uid
   function CopyFrom($source, $fields = NULL, $replace = true)
   {
       $fields = Utilities::ExecuteCallbacksAndGetRemainingFields($this, $source, $fields, $replace);

	   if ((!$fields || Utilities::ArrayContains($fields, 'Email')) && ($replace || $source->Email())) $this->SetEmail($source->Email());
	   if ((!$fields || Utilities::ArrayContains($fields, 'DisplayName')) && ($replace || $source->DisplayName())) $this->SetDisplayName($source->DisplayName());
  	   if ((!$fields || Utilities::ArrayContains($fields, 'Role')) && ($replace || $source->Role())) $this->SetRole($source->Role());
	   if ((!$fields || Utilities::ArrayContains($fields, 'Status')) && ($replace || $source->Status())) $this->SetStatus($source->Status());
	   if ((!$fields || Utilities::ArrayContains($fields, 'Rsvp')) && ($replace || $source->Rsvp())) $this->SetRsvp($source->Rsvp());
	   if ((!$fields || Utilities::ArrayContains($fields, 'Attended')) && ($replace || $source->Attended())) $this->SetAttended($source->Attended());
   }

   // returns whether the passed item is fairly certain to be the 
   // same as this one and therefore they should be combined
   function IsSimilarTo($item)
   {
      $same = 0;
      $diff = 0;
      if (!empty($this->Email())) { if ($this->Email() == $item->Email()) $same++; else if (!empty($item->Email())) $diff++; }
      if (!empty($this->DisplayName())) { if ($this->DisplayName() == $item->DisplayName()) $same++; else if (!empty($item->DisplayName())) $diff++; }
      if (!empty($this->Role())) { if ($this->Role() == $item->Role()) $same++; else if (!empty($item->Role())) $diff++; }
      if (!empty($this->Status())) { if ($this->Status() == $item->Status()) $same++; else if (!empty($item->Status())) $diff++; }
      if (!empty($this->Rsvp())) { if ($this->Rsvp() == $item->Rsvp()) $same++; else if (!empty($item->Rsvp())) $diff++; }
      if (!empty($this->Attended())) { if ($this->Attended() == $item->Attended()) $same++; else if (!empty($item->Attended())) $diff++; }
      
      return $diff == 0 && $same > 0;
   }
   
	function ToString()
	{
		$result = "";
		
		if ($this->email != null) { $result = Utilities::CombineStrings(', ', $result, $this->email); }
		if ($this->displayName != null) { $result = Utilities::CombineStrings(', ', $result, $this->displayName); }
		if ($this->role != null) { $result = Utilities::CombineStrings(', ', $result, $this->role); }
		if ($this->status != null) { $result = Utilities::CombineStrings(', ', $result, $this->status); }
		if ($this->rsvp != null) { $result = Utilities::CombineStrings(', RSVP=', $result, $this->rsvp); }
		if ($this->attended != null) { $result = Utilities::CombineStrings(', attended=', $result, $this->attended); }
		
		return $result;
	}
};

//spl_autoload_register(array('iCalAttendee', 'autoload'));

?>

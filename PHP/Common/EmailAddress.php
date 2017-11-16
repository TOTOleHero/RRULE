<?php

class EmailAddress
{
   private $email;
   private $name;
   
   // if $name is null then $email will be parsed for a name
   // NOTE: We also use this method for phone numbers in the Message object for SMS.
	function __construct($email = NULL, $name = NULL)
	{
      if ($email != NULL && $name == NULL)
      {
         $i = strpos($email, '<');      // check for <me@test.com> format address
         if ($i !== false)
         {
            $j = strpos($email, '"');   // check for name enclosed in quotes
            if ($j !== false)
            {
               $name = substr($email, $j+1);
               $j = strpos($name, '"');
               if ($j !== false)
               {
                  $name = trim(substr($name, 0, $j));
               }
               else
               {
                  $name = '';
               }
            }
            else
            {
               // name is anything before the address
               $name = trim(substr($email, 0, $i));
            }
            
            // extract parts between the two parentheses
            $temp = substr($email, $i+1);
            $i = strpos($temp, '>');
            if ($i !== false)
            {
               $email = trim(substr($temp, 0, $i));
            }
         }
      }
      
      $this->email = $email;
      $this->name = $name;
	}
	
	function __destruct()
	{
	}
   
   function Email()
   {
      return $this->email;
   }
   
   function SetEmail($email)
   {
      $this->email = $email;
   }
   
   function Name()
   {
      return $this->name;
   }
   
   function SetName($name)
   {
      $this->name = $name;
   }
   
   static function FromString($email)
   {
      return new EmailAddress($email);
   }
   
   function ToString($alwaysIncludeBrackets = false)
   {
      if (!empty($this->name))
         return "\"$this->name\" <$this->email>";
         
      if (empty($this->email))
         return NULL;
         
      if ($alwaysIncludeBrackets)
         return "<$this->email>";
         
      return $this->email;
   }
}

?>
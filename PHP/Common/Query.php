<?php

// ========================================================================
//        Copyright (c) 2012 Dominique Lacerte, All Rights Reserved.
// 
// Redistribution and use in source and binary forms are prohibited without 
//   prior written consent from Dominique Lacerte (internet@lacerte.org).
// ========================================================================

class Query
{
	private $db;
	private $qr;
	private $sql;
	private $readOnly;
   private $types;
   private $lastErrno;
/*
    public static function autoload($className)
    {
        if ($className != 'Query')
		{
            return false;
		}
        return include str_replace('_', '/', $className) . '.php';
    }
*/
	function __construct($db, $qr, $sql, $readOnly)
	{
	   $this->db = $db;
	   $this->qr = $qr;
      $this->sql = $sql;
      $this->readOnly = $readOnly;
      $this->types = array();
      $this->lastErrno = $db->errno;

		if (is_object($this->qr))  // skip for non-SELECT queries
      {
         // MySQL returns all values as strings, so find out which fields should be converted
         // to something other than string and we'll do that conversion in Fetch()
         $fields = $this->qr->fetch_fields();
         foreach($fields as $field)
         {
            switch ($field->type)
            {
               case 1:  // tinyint(1)
                  $this->types[$field->name] = 'bool';
                  break;
               case 3:  // int(11)
               case 8:  // BIGINT
                  $this->types[$field->name] = 'int';
                  break;
               case 4:
                  $this->types[$field->name] = 'float';
                  break;
               case 12: // datetime
               default:
                  break;
            }
         }
      }
	}
	
	function __destruct()
	{
      if (!is_bool($this->qr))
         $this->qr->close();
	}

	function Fetch()
	{
	   assert($this->readOnly);
	   
		$result = $this->qr->fetch_array(MYSQLI_ASSOC);
		if (!$result) return NULL;

		// convert non-string types as needed
		foreach ($this->types as $name => $type)
      {
         if ($result[$name] !== NULL)
            settype($result[$name], $type);
      }

		return $result;
	}
 
	// use for INSERT/UPDATE/DELETE queries
   function AffectedRowCount()
   {
      assert(!$this->readOnly);
      
      $temp = $this->db->affected_rows;
      
      
      // the above value is supposed to be -1 on error but I have seen it be -1 on success too!
      
      /*
       * ILA SA-221 There is a bug in the mysqli extension which manifests when using Xdebug.
       * See https://bugs.php.net/bug.php?id=67348
       * In short:
       * When a script reads $dbc->stat, subsequent reads of $dbc->affected_rows return a different result.
       * The debug handler loops over all properties, thus when Xdebug is running ->stat is read also.
       */
      
      if ($temp == -1 && $this->lastErrno == 0)
      {
         // let's see if this happens in the live environment (hopefully not)
         WriteError("Got a -1 for affected_rows but no error for: " . $this->sql);
         $temp = 1;
      }
      return $temp > 0 ? $temp : 0;
   }
   
   // use for SELECT/INFO queries
   function MatchingRowCount()
   {
      assert($this->readOnly);
      
      return $this->qr->num_rows;
   }
   
   function LastInsertID()
	{
      assert(!$this->readOnly);
      
      return $this->db->insert_id;
	}
	
	function ErrorCode()
	{
		return $this->db->errno;
	}

	function ErrorMessage()
	{
		return $this->db->error;
	}
   
   // throws an exception if there is an error code
   function ThrowException()
   {
      Database::_ThrowException($this->db->errno, $this->db->error);
   }
};

?>
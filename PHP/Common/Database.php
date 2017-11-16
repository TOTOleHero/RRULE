<?php

// ========================================================================
//        Copyright (c) 2012 Dominique Lacerte, All Rights Reserved.
// 
// Redistribution and use in source and binary forms are prohibited without 
//   prior written consent from Dominique Lacerte (internet@lacerte.org).
// ========================================================================

require_once(dirname(__FILE__) . '/DateAndTime.php');
require_once(dirname(__FILE__) . '/Log.php');
require_once(dirname(__FILE__) . '/Query.php');

class DatabaseException extends Exception
{
   public $ErrorCode;
   
   public function __construct($errorCode, $message)
   {
      parent::__construct($message, E_USER_ERROR);
      
      $this->ErrorCode = $errorCode;
   }

   // custom string representation of object
   public function __toString()
   {
      return __CLASS__ . ': ' . $this->message . '(' . $this->ErrorCode . ')';
   }
}

class DuplicateKeyDatabaseException extends DatabaseException
{
   public function __construct($errorCode, $message)
   {
      parent::__construct($errorCode, $message);
   }
}

class ForeignKeyConstraintDatabaseException extends DatabaseException
{
   public function __construct($errorCode, $message)
   {
      parent::__construct($errorCode, $message);
   }
}

class Database
{
	private $db;
	private $lastSql, $lastErrno, $lastError;
	private $autocommit;
	private $inTransaction;
	private $dbHost, $dbUser, $dbPassword, $dbName;
/*	
   public static function autoload($className)
   {
      if ($className != 'Database')
		{
         return false;
		}
      return include str_replace('_', '/', $className) . '.php';
   }
*/
	function __construct($dbHost, $dbUser, $dbPassword, $dbName)
	{
		$this->autocommit = true;		// I believe this is the default
		$this->inTransaction = false;
      $this->dbHost = $dbHost;
      $this->dbUser = $dbUser;
      $this->dbPassword = $dbPassword;
      $this->dbName = $dbName;
		
		$this->_init();
	}
	
	function _init()
	{
		$this->lastSql = 'NONE';
      $this->db = new mysqli($this->dbHost, $this->dbUser, $this->dbPassword, $this->dbName);
      if ($this->db->connect_error)
      {
         WriteError('Could not connect to database: ' . $this->db->connect_error);
         $this->db = NULL;
      }
      elseif (!$this->db->query("SET NAMES 'utf8mb4'"))
      {
         WriteError("Could not set UTF8MB4: " . $this->db->error);
         mysqli_close($this->db);
         $this->db = NULL;
      }
      elseif (!$this->db->set_charset('utf8mb4'))
      {
         WriteError("Could not set charset to UTF8MB4: " . $this->db->error);
         mysqli_close($this->db);
         $this->db = NULL;
      }
		
		$this->SetAutoCommit($this->autocommit);
	}
	
	function __destruct()
	{
		if ($this->db)
			mysqli_close($this->db);
	}

	function IsConnected()
	{
      return $this->db != NULL;
   }
   
   // use this for read-only queries such as SELECT
   function Query($sql)
   {
      assert(strpos($sql,'SELECT') !== false || strpos ($sql, 'SHOW') !== false);
      return $this->_execute($sql, true);
   }
   
   // use this for write queries such as INSERT, UPDATE and DELETE
   function Update($sql)
   {
      assert(strpos($sql,'INSERT') !== false || strpos($sql,'UPDATE') !== false || strpos($sql,'DELETE') !== false);
      return $this->_execute($sql, false);
   }
   
   function _execute($sql, $readOnly)
	{
		$qr = NULL;
		
		if (!$this->db && !$this->inTransaction)
		{
			WriteError("Database not initialized, attempting to do so.");
			$this->_init();
		}
		
		if ($this->db)
		{
			if (!$this->autocommit && !$this->inTransaction)
			{
            $this->ThrowException("Database query being attempted with autocommit OFF and no transaction started!");
			}
         
			$qr = $this->_query($sql, $readOnly);
			
			$errno = $this->lastErrno;
			$error = $this->lastError;
			if (!$qr && ($errno == 2006 || $errno == 2013))
			{
				mysqli_close($this->db);
				$this->db = NULL;
				
				if (!$this->inTransaction)
				{
					WriteError("Database has gone away ($errno), attempting to reconnect.");
					$this->_init();
				}
				else
				{
               $this->ThrowException("Database has gone away during a transaction, executing: $sql");
				}
				
				if ($this->db)
				{
					$qr = $this->_query($sql, $readOnly);
					if ($qr)
					{
						WriteError("Retry successful.");
					}
					else
					{
         			$errno = $this->lastErrno;
         			$error = $this->lastError;
						WriteError("Retry failed ($errno): $error");
					}
				}
			}
         else if (!$qr)
         {
// This may be expected, in any case the caller is responsible for handling it.
//				WriteError("Query failed ($errno): $error");
         }
		}
		else
		{
			WriteError("Database has gone away, ignoring: $sql");
		}
		
		return $qr;
	}
	
	function _query($sql, $readOnly)
	{
		$start = DateAndTime::Now();
		$qr = $this->db->query($sql);
		$end = DateAndTime::Now();

		$this->lastSql = $sql;
      // save the errors from the last actual query so we can throw an exception after restoring the autocommit
		$this->lastErrno = $this->db->errno;
		$this->lastError = $this->db->error;
      
		if ($qr === FALSE)
		{
			$error = $this->db->error;
			if ($error == 'MySQL server has gone away' ||
            $error == 'Lost connection to MySQL server during query' ||
            strpos($error, 'You have an error in your SQL syntax') === 0)
			{
				$code = $this->db->errno;
				$duration = DateAndTime::SubtractMilli($end, $start);
				WriteError("SQL command failed with $error ($code) taking $duration milliseconds:");
				WriteError($sql);
				WriteError("Last SQL command:");
				WriteError($this->lastSql);
			}
			
			return NULL;
		}
				
		return new Query($this->db, $qr, $sql, $readOnly);
	}

	function StartTransaction()
	{
		if ($this->autocommit)
		{
			$this->ThrowException("SQL transaction started while autocommit is on!");
		}
		if ($this->inTransaction)
		{
			$this->ThrowException("SQL transaction started while previous transaction not commit!");
		}
		
		$qr = $this->db->query('START TRANSACTION');
		if (!$qr)
		{
			return false;
		}
		$this->inTransaction = true;
		return true;
	}
	
	function Commit()
	{
		if ($this->autocommit)
		{
			$this->ThrowException("SQL transaction committed while autocommit is on!");
		}
		if (!$this->inTransaction)
		{
			$this->ThrowException("SQL transaction commit while no transaction is active!");
		}
		
		$qr = $this->db->query('COMMIT');
		if (!$qr)
		{
			return false;
		}
		$this->inTransaction = false;
		return true;
	}
	
	function RollBack()
	{
		if ($this->autocommit)
		{
			$this->ThrowException("SQL transaction rolled back while autocommit is on!");
		}
		if (!$this->inTransaction)
		{
			$this->ThrowException("SQL transaction rolled back while no transaction is active!");
		}
		
		$qr = $this->db->query('ROLLBACK');
		if (!$qr)
		{
			return false;
		}
		$this->inTransaction = false;
		return true;
	}
	
	function SetAutoCommit($auto)
	{
		if ($this->inTransaction && $auto)
		{
			$this->ThrowException("SQL autocommit being enabled while a transaction is active!");
		}
		
		$qr = $this->db->query($auto ? 'SET AUTOCOMMIT=1' : 'SET AUTOCOMMIT=0');
		if (!$qr)
		{
			return false;
		}
		$this->autocommit = $auto;
		return true;
	}
	
	function ErrorCode()
	{
		return $this->lastErrno;
	}

	function ErrorMessage()
	{
		return $this->lastError;
	}
   
	function IsDuplicateKeyError()
	{
		return $this->lastErrno == 1062 || $this->lastErrno == 1586;
	}

   // throws an exception if there is an error code, or a message is provided
   function ThrowException($message = NULL)
   {
      if (empty($message))
      {
         if ($this->lastErrno)
         {
            Database::_ThrowException($this->lastErrno, $this->lastError);
         }
      }
      else
      {
         Database::_ThrowException($this->lastErrno, $message);
      }
   }
   
   static function _ThrowException($errorCode, $message)
   {
      WriteCallStack("Database Exception: $message ($errorCode)");
      
      if ($errorCode)
      {
         switch ($errorCode)
         {
            case 1062:
            case 1586:
               throw new DuplicateKeyDatabaseException($errorCode, $message);
               break;
            case 1451:   // on delete
               throw new ForeignKeyConstraintDatabaseException($errorCode, $message);
               break;
            default:
               throw new DatabaseException($errorCode, $message);
               break;
         }
      }
   }
};

?>
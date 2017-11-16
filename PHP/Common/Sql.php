<?php

// ========================================================================
//        Copyright (c) 2012 Dominique Lacerte, All Rights Reserved.
// 
// Redistribution and use in source and binary forms are prohibited without 
//   prior written consent from Dominique Lacerte (internet@lacerte.org).
// ========================================================================

require_once(dirname(__FILE__) . '/DateAndTime.php');
require_once(dirname(__FILE__) . '/Log.php');
require_once(dirname(__FILE__) . '/Utilities.php');

function ValidateUtf8($str)
{
   $res = preg_replace('/[\x00-\x08\x10\x0B\x0C\x0E-\x19\x7F]'.
      '|(?<=^|[\x00-\x7F])[\x80-\xBF]+'.
      '|([\xC0\xC1]|[\xF0-\xFF])[\x80-\xBF]*'.
      '|[\xC2-\xDF]((?![\x80-\xBF])|[\x80-\xBF]{2,})'.
      '|[\xE0-\xEF](([\x80-\xBF](?![\x80-\xBF]))|(?![\x80-\xBF]{2})|[\x80-\xBF]{3,})/',
      '?', $str);
   $res = preg_replace('/\xE0[\x80-\x9F][\x80-\xBF]'.
      '|\xED[\xA0-\xBF][\x80-\xBF]/S','?', $res);
   if ($res != $str)
   {
      WriteError('Invalid UTF-8 found - replaced by question mark!');
//      WriteHexString($str);
   }
   
   return $res;
}

function SqlPrepInt($value)
{
	if (!isset($value)) return 'NULL';
	return $value;
}

function SqlPrepIntArray($values)
{
   $result = $values;
   foreach ($result as &$value)
   {
      $value = SqlPrepInt($value);
   }
   return $result;
}

function SqlPrepStrArray($values)
{
   $result = $values;
   foreach ($result as &$value)
   {
      $value = SqlPrepStr($value);
   }
   return $result;
}

// SQL type is DECIMAL
function SqlPrepDec($value)
{
	if (!isset($value)) return 'NULL';
	return $value;
}

// SQL type is DECIMAL(8,2)
function SqlPrepCurrency($value)
{
	if (!isset($value)) return 'NULL';
	return $value;
}

function SqlPrepStr($value)
{
	if (!isset($value)) return 'NULL';
   $value = ValidateUtf8($value);
	$value = Utilities::ReplaceInString($value, "\\", "\\\\");
	$value = Utilities::ReplaceInString($value, "'", "''");
	return "'" . $value . "'";
}

function SqlPrepStrs($values)
{
   $result = '';
   foreach ($values as $value)
   {
   	if (isset($value))
      {
      	$value = Utilities::ReplaceInString($value, "\\", "\\\\");
      	$value = Utilities::ReplaceInString($value, "'", "''");
      	$value = "'" . $value . "'";
      }
      else
         $value = 'NULL';
         
      if (!empty($result))
         $result .= ',';
      $result .= $value;
   }
   return $result;
}

function SqlPrepBin($value)
{
	if (!isset($value)) return 'NULL';
   $value = bin2hex($value);
	return "X'" . $value . "'";
}

function SqlPrepJson($value)
{
	if (!isset($value)) return 'NULL';
   $value = json_encode($value);
	return SqlPrepStr($value);
}

function SqlPrepBool($value)
{
	if (!isset($value)) return 'NULL';
	return $value ? 'TRUE' : 'FALSE';
}

// NOTE: This method does not preserve time zone unless both a date and time are provided.
function SqlPrepDate($value)
{
	if (!isset($value) || (!$value->HasDate() && !$value->HasTime()))
      return 'NULL';

	// we save our date/time as GMT
   if ($value->HasDate() && $value->HasTime() && $value->HasZone())
   	$temp = DateAndTime::FromEpoch($value->ToEpoch() - $value->Zone());
   else
      $temp = $value;

   // We use MySql and our own hacks to allow saving NULL date or time.
   $format = $value->HasDate() ? '%-D' : '0000-00-00';
   $format .= $value->HasTime() ? ' %:T' : ' 23:59:58';
   
   return '"' . $temp->ToFormat($format) . '"';
}

// For SQL type DECIMAL(13,3) used by MeetupBoost
function SqlPrepDateOld($value)
{
	if (!isset($value) || (!$value->HasDate() && !$value->HasTime()))
      return 'NULL';

	$type = get_class($value);
	if (strcmp($type, 'DateAndTime') == 0)
	{
		$milli = $value->Millisecond();
		while (strlen($milli) < 3)
			$milli = '0' . $milli;
		// we save our date as GMT
		return ($value->ToEpoch() - $value->Zone()) . '.' . $milli;
	}
	if (strcmp($type, 'DateTime') == 0)
		return $value->getTimestamp() . '.000';	// DRL FIXIT? Is this GMT??
	WriteDie("SqlPrepDate passed unhandled type '$type'");
}

// For SQL type DECIMAL(13,3) used by MeetupBoost
function SqlPrepEpochMilliOld($value)
{
	if (!isset($value)) return 'NULL';

	// DRL I don't use math here because I had problems getting correct 
	// results (maybe due to the size of the values?)
	return substr($value, 0, strlen($value) - 3) . '.' . substr($value, strlen($value) - 3);
}

// For SQL type DECIMAL(13,3) used by MeetupBoost
function SqlPrepEpochOld($value)
{
	if (!isset($value)) return 'NULL';

	return $value . '.000';
}

function SqlParseDate($value)
{
	if (empty($value)) return NULL;
	
   $temp = DateAndTime::FromString($value, 0);
   
   // We use MySql hacks to allow saving NULL date or time.
   // the hack for the date is already handled by DatendTime so 
   // we just need to worry about the time hack...
   if (strpos($value, "23:59:58") !== FALSE)
      $temp->SetTime(NULL);
      
   return $temp;
}

// For SQL type DECIMAL(13,3) used by MeetupBoost
function SqlParseDateOld($value)
{
	if (empty($value)) return NULL;
	
	$i = strpos($value, '.');
	if ($i != strlen($value) - 4) WriteDie("Bad date DECIMAL '$value'!");
	$date = DateAndTime::FromEpoch(substr($value, 0, $i), DateAndTime::$TimeZoneGMT);
	$date->SetMillisecond(substr($value, $i+1));
	return $date;
}

// SQL type is DECIMAL(13,3), Meetup is milliseconds since epoch
function SqlParseDateToMeetup($value)
{
	if (empty($value)) return NULL;
	
	$i = strpos($value, '.');
	if ($i != strlen($value) - 4) WriteDie("Bad date DECIMAL '$value'!");
	$date = substr($value, 0, $i) . substr($value, $i+1);
	return $date;
}

// SQL type is DECIMAL(13,3), Meetup is milliseconds since epoch
function SqlParseDateToNoMilliOld($value)
{
	if (empty($value)) return NULL;
	
	$i = strpos($value, '.');
	if ($i != strlen($value) - 4) WriteDie("Bad date DECIMAL '$value'!");
	$date = substr($value, 0, $i);
	return $date;
}

function SqlParseJson($value)
{
	if (empty($value)) return NULL;
	
   return json_decode($value, true);
}

?>
<?php

// ========================================================================
//        Copyright (c) 2012 Dominique Lacerte, All Rights Reserved.
// 
// Redistribution and use in source and binary forms are prohibited without 
//   prior written consent from Dominique Lacerte (internet@lacerte.org).
// ========================================================================


// DRL FIXIT! This still does not catch errors like calling a method on a null 
// variable such as:
//
//	$obj->Test();

error_reporting(E_ALL);

$paused_errors = array();

function ErrorPause($errno)
{
	global $paused_errors;
	
	if (!isset($paused_errors[$errno]))
		$paused_errors[$errno] = 0;
	$paused_errors[$errno]++;
}

function ErrorResume($errno)
{
	global $paused_errors;
	
	if (!isset($paused_errors[$errno]))
		WriteDie("Unpaused error $errno!");
	$paused_errors[$errno]--;
	if ($paused_errors[$errno] == 0)
		unset($paused_errors[$errno]);
}

function MyErrorHandler($errno, $errstr, $errfile, $errline)
{
	global $paused_errors;
	
	if (array_key_exists($errno, $paused_errors))
		return true;	// don't execute PHP internal error handler
	
	switch ($errno)
	{
		case E_STRICT:
			WriteCallStack("STRICT: $errstr, on line $errline in file $errfile");
			break;
		
		case E_USER_NOTICE:
		case E_NOTICE:
			WriteCallStack("NOTICE: $errstr, on line $errline in file $errfile");
			break;
		
		case E_USER_WARNING:
		case E_WARNING:
			WriteCallStack("WARNING: $errstr, on line $errline in file $errfile");
			if (error_reporting() !== 0)	// skip if the '@' operator was used
				throw new Exception($errstr);
			break;
		
		case E_USER_ERROR:
		case E_ERROR:
			WriteCallStack("ERROR: $errstr, on line $errline in file $errfile");
			if (error_reporting() !== 0)	// skip if the '@' operator was used
				throw new Exception($errstr);
			break;
		
		default:
			WriteCallStack("Unknown error number $errno in file $errfile: $errstr");
			break;
	}
	
	return true;	// don't execute PHP internal error handler
}

// set to the user defined error handler
$old_error_handler = set_error_handler('MyErrorHandler');

function MyExceptionHandler($exception)
{
   WriteCallStack("Uncaught exception: " . $exception->getMessage());
}

$old_exception_handler = set_exception_handler('MyExceptionHandler');


?>
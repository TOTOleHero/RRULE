<?php

# ========================================================================
#        Copyright (c) 2010 Dominique Lacerte, All Rights Reserved.
# 
# Redistribution and use in source and binary forms are prohibited without 
#   prior written consent from Dominique Lacerte (internet@lacerte.org).
# ========================================================================

// DRL FIXIT! This implementation is incomplete!

class DbSchema
{
	/**
	 * SPL-compatible autoloader.
	 *
	 * @param string $className Name of the class to load.
	 *
	 * @return boolean
	public static function autoload($className)
	{
	    if ($className != 'DbSchema')
		{
	        return false;
		}
	    return include str_replace('_', '/', $className) . '.php';
	}
	 */
	
	function __construct($schema)
	{
	}
	
	function __destruct()
	{
	}

	static function FromString($schema)
	{
		return new DbSchema($schema);
	}
   
	function PopulateForEvaluation($expressionEnvironment)
   {
   }
   
   function GetColumnNames()
   {
      return array();
   }
}
<?php

# ========================================================================
#        Copyright (c) 2010 Dominique Lacerte, All Rights Reserved.
# 
# Redistribution and use in source and binary forms are prohibited without 
#   prior written consent from Dominique Lacerte (internet@lacerte.org).
# ========================================================================

require_once(dirname(__FILE__) . '/File.php');
require_once(dirname(__FILE__) . '/IteratorBase.php');
require_once(dirname(__FILE__) . '/Utilities.php');

class MyArrayIterator extends IteratorBase
{
	private $Array;
	private $Index;

	/**
	 * SPL-compatible autoloader.
	 *
	 * @param string $className Name of the class to load.
	 *
	 * @return boolean
	public static function autoload($className)
	{
	    if ($className != 'MyArrayIterator')
		{
	        return false;
		}
	    return include str_replace('_', '/', $className) . '.php';
	}
	 */
	
	function __construct(&$ref_array, $dbSchema, $dbSelect = NULL, $dbWhere = NULL, $expressionEnvironment = NULL)
	{
		parent::__construct($dbSchema, $dbSelect, $dbWhere, $expressionEnvironment);

		# make a copy of the array with only the rows matching the WHERE clause
		$array = array();
		foreach ($ref_array as $ref_line)
		{
			# if what we are iterating is not a reference to an array we wrap it in a
			# reference to an array - this allows us to iterate anything
			if (strcmp(get_class($ref_line), "ARRAY") != 0)
			{
				$ref_line = array($ref_line);
			}
			
			if ($this->MatchesWhere($ref_line))
			{
				array_push($array, $ref_line);
			}
			$err = $this->LastError();
			if (!empty($err))
			{
				# stop on error with no results
				$array = array();
				break;
			}
		}

/* DRL FIXIT!		
		# now sort the resulting rows and limit if required
		if (count($array) > 0 && !empty($dbWhere))
		{
			var $orderBy = $dbWhere->GetOrderBy();
			if (count($orderBy) > 0)
			{
				var $ascending = $dbWhere->GetAscending();
				
				foreach ($orderBy as $column)
				{
					# convert to index
					var $name = $column;
					$column = $dbSchema->IndexOf($name);
					if ($column < 0)
					{
						WriteError("Column '$name' not found as specified in ORDER BY '" . join(',', $orderBy) . "' for WHERE clause '$dbWhere'");
					}
				}
				
				var $sortFunc = function
				{
					var $result = 0;
					var $i = 0;
					do
					{
						var $colNum = $orderBy[$i];
						var $c = $a->[$colNum];
						var $d = $b->[$colNum];
						if (empty($c))
						{
							$result = defined $d ? -1 : 0;
						}
						elseif (empty($d))
						{
							$result = 1;
						}
						else
						{
							var $type = $dbSchema->GetInfoByIndex($colNum)->[DbSchema::$IndexExprType];
							if ($type == ExpressionTypes::$DataTypeString)
							{
								$result = $c cmp $d;
							}
							else
							{
								$result = $c <=> $d;
							}
						}
						if (!$ascending[$i])
						{
							$result = -$result;
						}
						$i++;
					} while (!$result && $i < count($orderBy));
					
					return $result;
				};
				
				@array = sort $sortFunc @array;
			}
			var $offset = 0;
			if (!empty($dbWhere->GetOffset()))
			{
				$offset = $dbWhere->GetOffset();
			}
			var $limit = count($array);
			if (!empty($dbWhere->GetLimit()) && $dbWhere->GetLimit() < $limit)
			{
				$limit = $dbWhere->GetLimit();
			}
			if ($offset != 0 || $limit != count($array))
			{
				var $end = $offset+$limit-1;
				if ($end > count($array)) { $end = count($array)-1; }
				@array = @array[$offset..$end];
			}
		}
*/
		
		$this->Array =& $array;
	
		$this->Index = 0;
	}
	
	function __destruct()
	{
	}
	
	function Close()
	{
		$this->Array = NULL;
	}
	
	function GetNext()
	{
		$ref_array =& $this->Array;
	
		if ($this->Index >= count($ref_array))
		{
			return array();
		}
		
		$ref_line =& $ref_array[$this->Index++];
		
		$result = $this->PerformSelect($ref_line);

/* DRL FIXIT!		
		if (wantarray())
		{
			return @result;
		}
		else
		{
			return $result[0];	# supports the case where individual items are being iterated
		}
*/
		return $result;
	}
}
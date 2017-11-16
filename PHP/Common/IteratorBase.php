<?php

# ========================================================================
#        Copyright (c) 2010 Dominique Lacerte, All Rights Reserved.
# 
# Redistribution and use in source and binary forms are prohibited without 
#   prior written consent from Dominique Lacerte (internet@lacerte.org).
# ========================================================================

class ExpressionEnvironment
{
}

class IteratorBase
{
	private $DbSchema;
	private $ColumnNames;
	private $ExpressionEnvironment;
	private $DbSelect;
	private $DbWhere;

	/**
	 * SPL-compatible autoloader.
	 *
	 * @param string $className Name of the class to load.
	 *
	 * @return boolean
	public static function autoload($className)
	{
	    if ($className != 'ArrayIteratorBase')
		{
	        return false;
		}
	    return include str_replace('_', '/', $className) . '.php';
	}
	 */
	
	function __construct($dbSchema, $dbSelect = NULL, $dbWhere = NULL, $expressionEnvironment = NULL)
	{
		if (empty($dbSchema))
		{
			WriteDie("Required schema not available!");
		}
	
		if (empty($expressionEnvironment))
		{
			$expressionEnvironment = new ExpressionEnvironment();
		}
		
		$this->DbSchema = $dbSchema;
		$this->ColumnNames = $dbSchema->GetColumnNames();
		$dbSchema->PopulateForEvaluation($expressionEnvironment);
		$this->ExpressionEnvironment = $expressionEnvironment;
	
		$this->DbSelect = $dbSelect;
		$this->DbWhere = $dbWhere;
	
// The limitting has to be done after sorting so we can't do it here. See ArrayIterator.
//		my $limit;
//		my $offset;
//		if (defined($dbWhere))
//		{
//			$limit = $dbWhere->GetLimit();
//			$offset = $dbWhere->GetOffset();
//		}
//		
//		$this->{Limit} = $limit;
//		$this->{Offset} = $offset;
//		$this->{Index} = -1;			# zero based index of last row returned by GetNext()
//		$this->{Count} = 0;
	}
	
	function __destruct()
	{
	}
	

	function LastError()
	{
		$dbSelect =& $this->DbSelect;
		if (!empty($dbSelect))
		{
			$err = $dbSelect->LastError();
			if (!empty($err))
			{
				return $dbSelect->LastError();
			}
		}
	
		$dbWhere =& $this->DbWhere;
		if (!empty($dbWhere))
		{
			$err = $dbWhere->LastError();
			if (!empty($err))
			{
				return $dbWhere->LastError();
			}
		}
	
		return NULL;
	}
	
	# must be called exactly once per row, in order
	function MatchesWhere(&$ref_line)
	{
		$dbWhere =& $this->DbWhere;
		$result = 1;
	
		if (!empty($dbWhere))
		{
			$this->_PopulateExpressionEnvironment($ref_line);
			
			$result = $dbWhere->Evaluate($this->ExpressionEnvironment);
		}
		
//		if ($result)
//		{
//			$this->{Index}++;
//			if (defined($this->{Offset}) && $this->{Offset} >= $this->{Index})
//			{
//				return 0;
//			}
//			$this->{Count}++;
//			if (defined($this->{Limit}) && $this->{Limit} < $this->{Count})
//			{
//				return 0;
//			}
//		}
		
		return $result;
	}
	
	function &PerformSelect(&$ref_line)
	{
		$dbSelect =& $this->DbSelect;
		if (!empty($dbSelect))
		{
			$this->_PopulateExpressionEnvironment($ref_line);
	
			return $dbSelect->Evaluate($this->ExpressionEnvironment);
		}
	
		return $ref_line;
	}
	
	function _PopulateExpressionEnvironment(&$ref_line)
	{
		$dbSchema =& $this->DbSchema;
		$ref_columnNames =& $this->ColumnNames;
		$evaluationFields =& $this->ExpressionEnvironment;
		
		if (count($ref_line) != count($ref_columnNames))
		{
			WriteDie("Number of items in database row with values (" . join(",", $ref_line) .
				") doesn't match number of items in schema: " . $dbSchema->ToString());
		}
		
		for ($i = 0; $i < count($ref_line); $i++)
		{
			// the evaluation fields is a hash by field name containing arrays of (data type, value)
			$evaluationFields->SetVariable($ref_columnNames[$i], $ref_line[$i]);
		}
	}
}

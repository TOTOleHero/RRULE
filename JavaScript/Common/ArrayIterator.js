// ========================================================================
//        Copyright (c) 2010 Dominique Lacerte, All Rights Reserved.
// 
// Redistribution and use in source and binary forms are prohibited without 
//   prior written consent from Dominique Lacerte (internet@lacerte.org).
// ========================================================================

// ===================================================================
//
//	This is code I use for testing this module. Use as an example.
//
// ===================================================================

if (0)
{
	var data =
	[
		["Dominique", 41],
		["Rene", 14],
		["Jennifer", 39],
	];
	var iter = new ArrayIterator(data);
	if (iter == null)
	{
		Log_Die("Can't create ArrayIterator!");
	}
	
	var result = iter.GetNext();
	if (result == null)
	{
		Log_Die("Error calling GetNext()!");
	}
	if (result[0] != "Dominique" || result[1] != 41)
	{
		Log_Die("Error with GetNext() data!");
	}
	
	result = iter.GetNext();
	if (result == null)
	{
		Log_Die("Error calling GetNext()!");
	}
	if (result[0] != "Rene" || result[1] != 14)
	{
		Log_Die("Error with GetNext() data!");
	}
	
	result = iter.GetNext();
	if (result == null)
	{
		Log_Die("Error calling GetNext()!");
	}
	if (result[0] != "Jennifer" || result[1] != 39)
	{
		Log_Die("Error with GetNext() data!");
	}
	
	result = iter.GetNext();
	if (result != null)
	{
		Log_Die("Error calling last GetNext()!");
	}
	
	iter.Close();
}

// ===================================================================
//
//	Implementation.
//
// ===================================================================

function ArrayIterator(array)	// the Perl version has more options
{
	// make a copy of the array
	var copy = [];
	for (var i = 0; i < array.length; i++)
	{
		var line = array[i];

		// if what we are iterating is not a reference to an array we wrap it in a
		// reference to an array - this allows us to iterate anything
		if (!Utilities_IsArray(line))
		{
			line = [line];
		}
		
//		if (this.MatchesWhere(line))
		{
			copy.push(line);
		}
//		if (defined(this.LastError()))
//		{
//			// stop on error with no results
//			@array = ();
//			break;
//		}
	}
/*
	// now sort the resulting rows and limit if required
	if (copy.length > 0 && dbWhere != null)
	{
		var orderBy = dbWhere.GetOrderBy();
		if (orderBy.length > 0)
		{
			var ascending = dbWhere.GetAscending();
			
			for (var i = 0; i < orderBy.length; i++)
			{
				var column = orderBy[i];

				// convert to index
				var name = column;
				column = dbSchema.IndexOf(name);
				if (column < 0)
				{
					Log_WriteError("Column '"+ name + "' not found as specified in ORDER BY '" . orderBy.join(',') . "' for WHERE clause '" + dbWhere + "'");
				}
			}
			
			var sortFunc = function
			{
				var result = 0;
				var i = 0;
				do
				{
					var colNum = orderBy[i];
					var c = a[colNum];
					var d = b[colNum];
					if (c == null)
					{
						result = d!=null ? -1 : 0;
					}
					else if (d == null)
					{
						result = 1;
					}
					else
					{
						result = c <=> d;
					}
					if (!ascending[i])
					{
						result = -result;
					}
					i++;
				} while (!result && i < orderBy.length);
				
				return result;
			};
			
			array.sort(sortFunc);
		}
		var offset = 0;
		if (dbWhere.GetOffset() != null)
		{
			offset = dbWhere.GetOffset();
		}
		var limit = copy.length;
		if (dbWhere.GetLimit() != null && dbWhere.GetLimit() < limit)
		{
			limit = dbWhere.GetLimit();
		}
		if (offset != 0 || limit != copy.length)
		{
			var end = offset+limit-1;
			if (end >= copy.length) { end = copy.length-1; }
			copy = copy.slice(offset,end);
		}
	}
*/
	this.Array = copy;

	this.Index = 0;
}

ArrayIterator.prototype.Close = function()
{
	this.Array = null;
}

ArrayIterator.prototype.GetNext = function()
{
	var array = this.Array;

	if (this.Index >= array.length)
	{
		return null;
	}
	
	var line = array[this.Index++];
	
//	line = this.PerformSelect(line);
	
	return line;
}

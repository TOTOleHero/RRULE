// ========================================================================
//        Copyright (c) 2010 Dominique Lacerte, All Rights Reserved.
// 
// Redistribution and use in source and binary forms are prohibited without 
//   prior written consent from Dominique Lacerte (internet@lacerte.org).
// ========================================================================

//use overload
//	"==" => \&Equal,
//	"!=" => \&NotEqual,
//	">" => \&GreaterThan,
//	">=" => \&GreaterThanOrEqual,
//	"<" => \&LessThan,
//	"<=" => \&LessThanOrEqual,
//	"<=>" => \&Compare;

// ===================================================================
//
//	Implementation.
//
// ===================================================================

function RecurrenceRuleSetIterator(genlist, gen)
{
	this.gen = gen;

	// the caller expects us to have retrieved the initial value
	// and only add ourselves to the list if we have a value
	var data = this.GetNext();
	if (data != null)
	{
		genlist.push(this);
		this.genlist = genlist;
	}
}

RecurrenceRuleSetIterator.prototype.GetCurrent = function()
{
	return this.dt;
}

RecurrenceRuleSetIterator.prototype.GetNext = function()
{
	var data = this.gen.GetNext();
	if (data == null)
	{
		if (this.genlist != null)
		{
			Utilities_RemoveFromArray(this.genlist, this);

			this.Close();
		}
		
		return null;
	}
	
	this.dt = data[0];
	
	return data[0];
}

RecurrenceRuleSetIterator.prototype.Close = function()
{
	this.genlist = null;
	this.gen = null;
}

RecurrenceRuleSetIterator.prototype.Equal = function(obj2)
{
	// turn them both into dates
	var obj1 = this;
	if (obj1.dt == null) { Log_Die("NULL date value"); }
	obj1 = obj1.dt;
	if (obj2 instanceof RecurrenceRuleSetIterator)
	{
		if (obj2.dt == null) { Log_Die("NULL date value"); }
		obj2 = obj2.dt;
	}
	
	return obj1.Equal(obj2);
}

RecurrenceRuleSetIterator.prototype.NotEqual = function(obj2)
{
	// turn them both into dates
	var obj1 = this;
	if (obj1.dt == null) { Log_Die("NULL date value"); }
	obj1 = obj1.dt;
	if (obj2 instanceof RecurrenceRuleSetIterator)
	{
		if (obj2.dt == null) { Log_Die("NULL date value"); }
		obj2 = obj2.dt;
	}
	
	return obj1.NotEqual(obj2);
}

RecurrenceRuleSetIterator.prototype.GreaterThan = function(obj2)
{
	// turn them both into dates
	var obj1 = this;
	if (obj1.dt == null) { Log_Die("NULL date value"); }
	obj1 = obj1.dt;
	if (obj2 instanceof RecurrenceRuleSetIterator)
	{
		if (obj2.dt == null) { Log_Die("NULL date value"); }
		obj2 = obj2.dt;
	}
	
	return obj1.GreaterThan(obj2);
}

RecurrenceRuleSetIterator.prototype.GreaterThanOrEqual = function(obj2)
{
	// turn them both into dates
	var obj1 = this;
	if (obj1.dt == null) { Log_Die("NULL date value"); }
	obj1 = obj1.dt;
	if (obj2 instanceof RecurrenceRuleSetIterator)
	{
		if (obj2.dt == null) { Log_Die("NULL date value"); }
		obj2 = obj2.dt;
	}
	
	return obj1.GreaterThanOrEqual(obj2);
}

RecurrenceRuleSetIterator.prototype.LessThan = function(obj2)
{
	// turn them both into dates
	var obj1 = this;
	if (obj1.dt == null) { Log_Die("NULL date value"); }
	obj1 = obj1.dt;
	if (obj2 instanceof RecurrenceRuleSetIterator)
	{
		if (obj2.dt == null) { Log_Die("NULL date value"); }
		obj2 = obj2.dt;
	}

	return obj1.LessThan(obj2);
}

RecurrenceRuleSetIterator.prototype.LessThanOrEqual = function(obj2)
{
	// turn them both into dates
	var obj1 = this;
	if (obj1.dt == null) { Log_Die("NULL date value"); }
	obj1 = obj1.dt;
	if (obj2 instanceof RecurrenceRuleSetIterator)
	{
		if (obj2.dt == null) { Log_Die("NULL date value"); }
		obj2 = obj2.dt;
	}
	
	return obj1.LessThanOrEqual(obj2);
}

RecurrenceRuleSetIterator.prototype.Compare = function(obj2)
{
	// turn them both into dates
	var obj1 = this;
	if (obj1.dt == null) { Log_Die("NULL date value"); }
	obj1 = obj1.dt;
	if (obj2 instanceof RecurrenceRuleSetIterator)
	{
		if (obj2.dt == null) { Log_Die("NULL date value"); }
		obj2 = obj2.dt;
	}
	
	return obj1.Compare(obj2);
}

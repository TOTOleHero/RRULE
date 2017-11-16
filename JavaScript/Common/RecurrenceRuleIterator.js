// ========================================================================
//        Copyright (c) 2010 Dominique Lacerte, All Rights Reserved.
// 
// Redistribution and use in source and binary forms are prohibited without 
//   prior written consent from Dominique Lacerte (internet@lacerte.org).
// ========================================================================

// ===================================================================
//
//	Implementation.
//
// ===================================================================

function RecurrenceRuleIterator()
{
	this.Values = [];
}

RecurrenceRuleIterator.prototype.AddDate = function(date)
{
	this.Values.push(date);
}

RecurrenceRuleIterator.prototype.GetNext = function()
{
	if (this.Values.length == 0)
	{
		return null;
	}

	return [this.Values.shift()];
}

RecurrenceRuleIterator.prototype.GetCount = function()
{
	return this.Values.length;
}

RecurrenceRuleIterator.prototype.Close = function()
{
	this.Values = null;
}

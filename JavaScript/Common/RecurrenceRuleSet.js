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

function RecurrenceRuleSet(cache)
{
	this.cache = cache;
	this.rrule = [];
	this.rdate = [];
	this.exrule = [];
	this.exdate = [];
}

RecurrenceRuleSet.prototype.toString = function()
{
	var result = "";
	
	if (this.rdate.length)
	{
		if (result.length) { result += "\n"; }
		result += "RDATE:";
		var first = 1;
		for (var i = 0; i < this.rdate.length; i++)
		{
			var date = this.rdate[i];
			if (!first) { result += ","; } else { first = 0; }
			result += date.ToFormat(DateAndTime_ISO8601BasicFormat);
		}
	}
	if (this.rrule.length)
	{
		if (result.length) { result += "\n"; }
		result += "RRULE:";
		var first = 1;
		for (var i = 0; i < this.rrule.length; i++)
		{
			var rule = this.rrule[i];
			if (!first) { result += ","; } else { first = 0; }
			result += substr(rule.toString(), 6);
		}
	}
	if (this.exdate.length)
	{
		if (result.length) { result += "\n"; }
		result += "EXDATE:";
		var first = 1;
		for (var i = 0; i < this.exdate.length; i++)
		{
			var date = this.exdate[i];
			if (!first) { result += ","; } else { first = 0; }
			result += date.ToFormat(DateAndTime_ISO8601BasicFormat);
		}
	}
	if (this.exrule.length)
	{
		if (result.length) { result += "\n"; }
		result += "EXRULE:";
		var first = 1;
		for (var i = 0; i < this.exrule.length; i++)
		{
			var rule = this.exrule[i];
			if (!first) { result += ","; } else { first = 0; }
			result += substr(rule.toString(), 6);
		}
	}
	
	return result;
}

RecurrenceRuleSet.prototype.GetIterator = function()
{
	var iter = new RecurrenceRuleIterator();

	var rlist = [];
	if (this.rdate.length)
	{
		var temp = this.rdate.slice();	// make a copy, is this required?
		temp.sort(function(a, b) { return a.Compare(b); });
		new RecurrenceRuleSetIterator(rlist, new ArrayIterator(temp/*, DbSchema_FromString("Col1 datetime NOT NULL PRIMARY KEY (Col1)")*/));
	}
	for (var i = 0; i < this.rrule.length; i++)
	{
		var rrule = this.rrule[i];
		new RecurrenceRuleSetIterator(rlist, rrule.GetIterator());
	}
	rlist.sort(function(a, b) { return a.Compare(b); });

	var exlist = [];
	if (this.exdate.length)
	{
		var temp = this.exdate.slice();	// make a copy, is this required?
		temp.sort(function(a, b) { return a.Compare(b); });
		new RecurrenceRuleSetIterator(exlist, new ArrayIterator(temp/*, DbSchema_FromString("Col1 datetime NOT NULL PRIMARY KEY (Col1)")*/));
	}
	for (var i = 0; i < this.exrule.length; i++)
	{
		var rrule = this.exrule[i];
		new RecurrenceRuleSetIterator(exlist, rrule.GetIterator());
	}
	exlist.sort(function(a, b) { return a.Compare(b); });

	var lastdt = null;
	var total = 0;
	while (rlist.length)
	{
		var ritem = rlist[0];
		if (!lastdt || lastdt.Compare(ritem.GetCurrent()) != 0)
		{
			while (exlist.length && exlist[0].GetCurrent().Compare(ritem.GetCurrent()) < 0)
			{
				exlist[0].GetNext();
				exlist.sort(function(a, b) { return a.Compare(b); });
			}
			if (exlist.length == 0 || exlist[0].GetCurrent().Compare(ritem.GetCurrent()) != 0)
			{
				total += 1;
				iter.AddDate(ritem.GetCurrent());
			}
			lastdt = ritem.GetCurrent();
		}
		ritem.GetNext();
		rlist.sort(function(a, b) { return a.Compare(b); });
	}
	this.len = total;

	return iter;
}

RecurrenceRuleSet.prototype.RRule = function()
{
	return this.rrule;
}

RecurrenceRuleSet.prototype.AddRRule = function(rrule)
{
	this.rrule.push(rrule);
}

RecurrenceRuleSet.prototype.ExRule = function()
{
	return this.exrule;
}

RecurrenceRuleSet.prototype.AddExRule = function(exrule)
{
	this.exrule.push(exrule);
}

RecurrenceRuleSet.prototype.RDate = function()
{
	return this.rdate;
}

RecurrenceRuleSet.prototype.AddRDate = function(rdate)
{
	this.rdate.push(rdate);
}

RecurrenceRuleSet.prototype.ExDate = function()
{
	return this.exdate;
}

RecurrenceRuleSet.prototype.AddExDate = function(exdate)
{
	this.exdate.push(exdate);
}

// ========================================================================
//        Copyright (c) 2010 Dominique Lacerte, All Rights Reserved.
// 
// Redistribution and use in source and binary forms are prohibited without 
//   prior written consent from Dominique Lacerte (internet@lacerte.org).
// ========================================================================

// ===================================================================
//
//	Constants.
//
// ===================================================================

// Every mask is 7 days longer to handle cross-year weekly periods.
var M366MASK =
[
	1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,
	2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,
	3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,3,
	4,4,4,4,4,4,4,4,4,4,4,4,4,4,4,4,4,4,4,4,4,4,4,4,4,4,4,4,4,4,
	5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,5,
	6,6,6,6,6,6,6,6,6,6,6,6,6,6,6,6,6,6,6,6,6,6,6,6,6,6,6,6,6,6,
	7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,7,
	8,8,8,8,8,8,8,8,8,8,8,8,8,8,8,8,8,8,8,8,8,8,8,8,8,8,8,8,8,8,8,
	9,9,9,9,9,9,9,9,9,9,9,9,9,9,9,9,9,9,9,9,9,9,9,9,9,9,9,9,9,9,
	10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,
	11,11,11,11,11,11,11,11,11,11,11,11,11,11,11,11,11,11,11,11,11,11,11,11,11,11,11,11,11,11,
	12,12,12,12,12,12,12,12,12,12,12,12,12,12,12,12,12,12,12,12,12,12,12,12,12,12,12,12,12,12,12,
	1,1,1,1,1,1,1,
];
var M365MASK = M366MASK.slice(); M365MASK.splice(59, 1);
var M29 = [1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29];
var M30 = [1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30];
var M31 = [1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31];
var MDAY366MASK = Utilities_ConcatArrays(M31,M29,M31,M30,M31,M30,M31,M31,M30,M31,M30,M31,[1,2,3,4,5,6,7]);
var MDAY365MASK = MDAY366MASK.slice(); MDAY365MASK.splice(59, 1);
M29 = [-29,-28,-27,-26,-25,-24,-23,-22,-21,-20,-19,-18,-17,-16,-15,-14,-13,-12,-11,-10,-9,-8,-7,-6,-5,-4,-3,-2,-1];
M30 = [-30,-29,-28,-27,-26,-25,-24,-23,-22,-21,-20,-19,-18,-17,-16,-15,-14,-13,-12,-11,-10,-9,-8,-7,-6,-5,-4,-3,-2,-1];
M31 = [-31,-29,-28,-27,-26,-25,-24,-23,-22,-21,-20,-19,-18,-17,-16,-15,-14,-13,-12,-11,-10,-9,-8,-7,-6,-5,-4,-3,-2,-1];
var NMDAY366MASK = Utilities_ConcatArrays(M31,M29,M31,M30,M31,M30,M31,M31,M30,M31,M30,M31,[-31,-29,-28,-27,-26,-25,-24]);
var NMDAY365MASK = NMDAY366MASK.slice(); NMDAY366MASK.splice(31, 1);
var M366RANGE = [0, 31, 60, 91, 121, 152, 182, 213, 244, 274, 305, 335, 366];
var M365RANGE = [0, 31, 59, 90, 120, 151, 181, 212, 243, 273, 304, 334, 365];
var WDAYMASK =
[
	0,1,2,3,4,5,6,0,1,2,3,4,5,6,0,1,2,3,4,5,6,0,1,2,3,4,5,6,0,1,2,3,4,5,6,0,1,2,3,4,5,6,0,1,2,3,4,5,6,0,1,2,3,4,5,6,0,1,2,3,4,5,6,0,1,2,3,4,5,6,
	0,1,2,3,4,5,6,0,1,2,3,4,5,6,0,1,2,3,4,5,6,0,1,2,3,4,5,6,0,1,2,3,4,5,6,0,1,2,3,4,5,6,0,1,2,3,4,5,6,0,1,2,3,4,5,6,0,1,2,3,4,5,6,0,1,2,3,4,5,6,
	0,1,2,3,4,5,6,0,1,2,3,4,5,6,0,1,2,3,4,5,6,0,1,2,3,4,5,6,0,1,2,3,4,5,6,0,1,2,3,4,5,6,0,1,2,3,4,5,6,0,1,2,3,4,5,6,0,1,2,3,4,5,6,0,1,2,3,4,5,6,
	0,1,2,3,4,5,6,0,1,2,3,4,5,6,0,1,2,3,4,5,6,0,1,2,3,4,5,6,0,1,2,3,4,5,6,0,1,2,3,4,5,6,0,1,2,3,4,5,6,0,1,2,3,4,5,6,0,1,2,3,4,5,6,0,1,2,3,4,5,6,
	0,1,2,3,4,5,6,0,1,2,3,4,5,6,0,1,2,3,4,5,6,0,1,2,3,4,5,6,0,1,2,3,4,5,6,0,1,2,3,4,5,6,0,1,2,3,4,5,6,0,1,2,3,4,5,6,0,1,2,3,4,5,6,0,1,2,3,4,5,6,
	0,1,2,3,4,5,6,0,1,2,3,4,5,6,0,1,2,3,4,5,6,0,1,2,3,4,5,6,0,1,2,3,4,5,6
];
M29 = [];
M30 = [];
M31 = [];

// ===================================================================
//
//	Implementation.
//
// ===================================================================

function RecurrenceRuleIteratorInfo(rrule)
{
	this.rrule = rrule;
	this.lastyear = null;
	this.lastmonth = null;
	this.yearlen = null;
	this.nextyearlen = null;
	this.yearordinal = null;
	this.yearweekday = null;
	this.mmask = null;
	this.mrange = null;
	this.mdaymask = null;
	this.nmdaymask = null;
	this.wdaymask = null;
	this.wnomask = null;
	this.nwdaymask = null;
	this.eastermask = null;
}

RecurrenceRuleIteratorInfo.prototype.rebuild = function(year, month)
{
	// Every mask is 7 days longer to handle cross-year weekly periods.
	var rr = this.rrule;
	if (year != this.lastyear)
	{
		this.yearlen = 365+DateAndTime_IsLeapYear(year);
		this.nextyearlen = 365+DateAndTime_IsLeapYear(year+1);
		var firstyday = new DateAndTime(year, 1, 1);
		this.yearordinal = firstyday.ToOrdinalDay();
		this.yearweekday = (firstyday.DayOfWeek() + 6) % 7;	// convert to 0 as Monday
		
		var wday = new DateAndTime(year, 1, 1);
		wday = (wday.DayOfWeek() + 6) % 7;	// convert to 0 as Monday
		
		if (this.yearlen == 365)
		{
			this.mmask = M365MASK;
			this.mdaymask = MDAY365MASK;
			this.nmdaymask = NMDAY365MASK;
			this.mrange = M365RANGE;
		}
		else
		{
			this.mmask = M366MASK;
			this.mdaymask = MDAY366MASK;
			this.nmdaymask = NMDAY366MASK;
			this.mrange = M366RANGE;
		}
		this.wdaymask = WDAYMASK.slice();	// make a copy
		this.wdaymask.splice(0, wday);
		
		if (!rr.byweekno)
		{
			this.wnomask = null;
		}
		else
		{
			this.wnomask = [];
			for (var i = 0; i < this.yearlen+7; i++)
			{
				this.wnomask.push(0);
			}
			// no1wkst = firstwkst = this.wdaymask.index(rr.wkst)
			var no1wkst = (7 - this.yearweekday + rr.wkst) % 7;
			var firstwkst = no1wkst;
			var wyearlen;
			if (no1wkst >= 4)
			{
				no1wkst = 0;
				// Number of days in the year, plus the days we got
				// from last year.
				wyearlen = this.yearlen + (this.yearweekday - rr.wkst) % 7;
			}
			else
			{
				// Number of days in the year, minus the days we
				// left in last year.
				wyearlen = this.yearlen - no1wkst;
			}
			var div = Utilities_Div(wyearlen, 7);
			var mod = wyearlen % 7;
			var numweeks = Utilities_Div(div+mod,4);
			for (var i = 0; i < rr.byweekno.length; i++)
			{
				var n = rr.byweekno[i];
				if (n < 0)
				{
					n += numweeks+1;
				}
				if (n > 0 && n <= numweeks)
				{
					var i;
					if (n > 1)
					{
						i = no1wkst+(n-1)*7;
						if (no1wkst != firstwkst)
						{
							i -= 7-firstwkst;
						}
					}
					else
					{
						i = no1wkst;
					}
					for (var j = 0; j < 7; j++)
					{
						this.wnomask[i] = 1;
						i += 1;
						if (this.wdaymask[i] == rr.wkst)
						{
							break;
						}
					}
				}
			}
			if (Utilities_ArrayContains(rr.byweekno, 1))
			{
				// Check week number 1 of next year as well
				// TODO: Check -numweeks for next year.
				var i = no1wkst+numweeks*7;
				if (no1wkst != firstwkst)
				{
					i -= 7-firstwkst;
				}
				if (i < this.yearlen)
				{
					// If week starts in next year, we
					// don't care about it.
					for (var j = 0; j < 7; j++)
					{
						this.wnomask[i] = 1;
						i += 1;
						if (this.wdaymask[i] == rr.wkst)
						{
							break;
						}
					}
				}
			}
			if (no1wkst)
			{
				// Check last week number of last year as
				// well. If no1wkst is 0, either the year
				// started on week start, or week number 1
				// got days from last year, so there are no
				// days from last year's last week number in
				// this year.
				var lnumweeks;
				if (!Utilities_ArrayContains(rr.byweekno, -1))
				{
					var lyearweekday = new DateAndTime(year-1, 1, 1);
					lyearweekday = (lyearweekday.DayOfWeek() + 6) % 7;	// convert to 0 as Monday
					var lno1wkst = (7-lyearweekday+rr.wkst) % 7;
					var lyearlen = 365 + DateAndTime_IsLeapYear(year-1);
					if (lno1wkst >= 4)
					{
						lno1wkst = 0;
						lnumweeks = Utilities_Div(52+(lyearlen+(lyearweekday-rr.wkst)%7)%7,4);
					}
					else
					{
						lnumweeks = Utilities_Div(52+(this.yearlen-no1wkst)%7,4);
					}
				}
				else
				{
					lnumweeks = -1;
				}
				if (Utilities_ArrayContains(rr.byweekno, lnumweeks))
				{
					for (var i = 0; i < no1wkst; i++)
					{
						this.wnomask[i] = 1;
					}
				}
			}
		}
	}
	
	if (rr.bynweekday && (month != this.lastmonth || year != this.lastyear))
	{
		var ranges = [];
		if (rr.freq == RecurrenceRuleConstants_YEARLY)
		{
			if (rr.bymonth)
			{
				for (var i = 0; i < rr.bymonth.length; i++)
				{
					var month = rr.bymonth[i];
					ranges.push([this.mrange[month-1], this.mrange[month]]);
				}
			}
			else
			{
				ranges = [[0, this.yearlen]];
			}
		}
		else if (rr.freq == RecurrenceRuleConstants_MONTHLY)
		{
			ranges = [[this.mrange[month-1], this.mrange[month]]];
		}
		if (ranges.length > 0)
		{
			// Weekly frequency won't get here, so we may not
			// care about cross-year weekly periods.
			this.nwdaymask = [];
			for (var i = 0; i < this.yearlen; i++)
			{
				this.nwdaymask.push(0);
			}
			for (var i = 0; i < ranges.length; i++)
			{
				var range = ranges[i];
				var first = range[0];
				var last = range[1];
				last -= 1;
				for (var i = 0; i < rr.bynweekday.length; i++)
				{
					var wd = rr.bynweekday[i];
					var wday = wd[0];
					var n = wd[1];
					var i;
					if (n < 0)
					{
						i = last+(n+1)*7;
						i -= (this.wdaymask[i]-wday)%7;
					}
					else
					{
						i = first+(n-1)*7;
						i += (7-this.wdaymask[i]+wday)%7;
					}
					if (i >= first && i <= last)
					{
						this.nwdaymask[i] = 1;
					}
				}
			}
		}
	}
	
	if (rr.byeaster)
	{
		this.eastermask = [];
		for (var i = 0; i < this.yearlen+7; i++)
		{
			this.eastermask.push(0);
		}
		var eyday = DateAndTime_GetEaster(year).ToOrdinalDay() - this.yearordinal;
		for (var i = 0; i < rr.byeaster.length; i++)
		{
			var offset = rr.byeaster[i];
			this.eastermask[eyday+offset] = 1;
		}
	}
	
	this.lastyear = year;
	this.lastmonth = month;
}

RecurrenceRuleIteratorInfo.prototype.ydayset = function(year, month, day)
{
	var set = [];
	for (var i = 0; i <= this.yearlen; i++)	// DRL FIXIT? < or <=?
	{
		set.push(i);
	}
	
	return [set, 0, this.yearlen];
}

RecurrenceRuleIteratorInfo.prototype.mdayset = function(year, month, day)
{
	var set = [];
	for (var i = 0; i < this.yearlen; i++)
	{
		set.push(null);
	}
	var start = this.mrange[month-1];
	var end = this.mrange[month];
	for (var i = start; i < end; i++)
	{
		set[i] = i;
	}
	
	return [set, start, end];
}

RecurrenceRuleIteratorInfo.prototype.wdayset = function(year, month, day)
{
	// We need to handle cross-year weeks here.
	var set = [];
	for (var i = 0; i < this.yearlen + 7; i++)
	{
		set.push(null);
	}
	var i = new DateAndTime(year, month, day);
	i = i.ToOrdinalDay() - this.yearordinal;
	var start = i;
	for (var j = 0; j < 7; j++)
	{
		set[i] = i;
		i += 1;
		// This will cross the year boundary, if necessary.
		if (this.wdaymask[i] == this.rrule.wkst)
		{
			break;
		}
	}
	
	return [set, start, i];
}

RecurrenceRuleIteratorInfo.prototype.ddayset = function(year, month, day)
{
	var set = [];
	for (var i = 0; i < this.yearlen; i++)
	{
		set.push(null);
	}
	var i = new DateAndTime(year, month, day);
	i = i.ToOrdinalDay() - this.yearordinal;
	set[i] = i;
	
	return [set, i, i+1];
}

RecurrenceRuleIteratorInfo.prototype.htimeset = function(hour, minute, second)
{
	var set = [];
	var rr = this.rrule;
	if (rr.byminute != null)			// DRL ADDED
	{
		for (var i = 0; i < rr.byminute.length; i++)
		{
			var minute = rr.byminute[i];
			if (rr.bysecond != null)			// DRL ADDED
			{
				for (var j = 0; j < rr.bysecond.length; j++)
				{
					var second = rr.bysecond[j];
					set.push(new DateAndTime(null, null, null, hour, minute, second, 0, rr.tzinfo));
				}
			}
		}
	}
	set.sort(function(a, b) { return a <= b; });
	
	return set;
}

RecurrenceRuleIteratorInfo.prototype.mtimeset = function(hour, minute, second)
{
	var set = [];
	var rr = this.rrule;
	if (rr.bysecond != null)			// DRL ADDED
	{
		for (var i = 0; i < rr.bysecond.length; i++)
		{
			var second = rr.bysecond[i];
			set.push(new DateAndTime(null, null, null, hour, minute, second, 0, rr.tzinfo));
		}
	}
	set.sort(function(a, b) { return a <= b; });
	
	return set;
}

RecurrenceRuleIteratorInfo.prototype.stimeset = function(hour, minute, second)
{
	var rr = this.rrule;
	var set = [new DateAndTime(null, null, null, hour, minute, second, 0, rr.tzinfo)];
	
	return set;
}

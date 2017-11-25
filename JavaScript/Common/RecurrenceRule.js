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

var Debug = 1;					// set to 1 in order to perform some extra checking to help catch errors

var FirstWeekDay = 6;		// 0 is Monday here, our DateAndTime module uses 0 is Sunday so we'll use that

// ===================================================================
//
//	This is code I use for testing this module. Use as an example.
//
// ===================================================================

// ===================================================================
//
//	Implementation.
//
// ===================================================================

function RecurrenceRule(dtstart, cache, rrkwargs)
{
	this.dtstart = dtstart;
	this.cache = cache;
	this.freq = rrkwargs.freq;
	this.interval = rrkwargs.interval;
	this.count = rrkwargs.count;
	this.until = rrkwargs.until;
//	this.weekday = rrkwargs.weekday;
	this.bysetpos = rrkwargs.bysetpos;
	this.bymonth = rrkwargs.bymonth;
	this.byyearday = rrkwargs.byyearday;
	this.byeaster = rrkwargs.byeaster;
	this.bymonthday = rrkwargs.bymonthday;
	this.byweekno = rrkwargs.byweekno;
	this.byweekday = rrkwargs.byweekday;
//Log_WriteInfo("Init byweekday: " + Dumper this.byweekday);
	this.byhour = rrkwargs.byhour;
	this.byminute = rrkwargs.byminute;
	this.bysecond = rrkwargs.bysecond;
}

RecurrenceRule.prototype.toString = function()
{
	if (this.freq == null || this.freq == RecurrenceRuleConstants_NO_RECURRENCE)
	{
		return null;
	}
	
	var result = "FREQ=" + RecurrenceRuleConstants_Frequency[this.freq];
	if (this.count != null)
	{
		result += ";COUNT=" + this.count;
	}
	if (this.interval != null)
	{
		result += ";INTERVAL=" + this.interval;
	}
	if (this.wkst != null)
	{
		result += ";WKST=" + RecurrenceRuleConstants_Weekdays[this.wkst];
	}
	if (this.until != null)
	{
		result += ";UNTIL=" + this.until.ToFormat(DateAndTime_ISO8601BasicFormat);
	}
	if (this.bymonth != null)
	{
		result += ";BYMONTH=" + this.bymonth.join(',');
	}
	if (this.byweekno != null)
	{
		result += ";BYWEEKNO=" + this.byweekno.join(',');
	}
	if (this.byyearday != null)
	{
		result += ";BYYEARDAY=" + this.byyearday.join(',');
	}
	var weekday = [];
	if (this.byweekday != null)
	{
		for (var i = 0; i < this.byweekday.length; i++)
		{
			var w = this.byweekday[i];
			var wkday = RecurrenceRuleConstants_Weekdays[w.weekday];
			var n = w.n;
			if (n == null)
			{
				weekday.push(wkday);
			}
			else
			{
				weekday.push(n + wkday);
			}
		}
	}
	if (weekday.length)
	{
		result += ";BYDAY=" + weekday.join(',');
	}
	if (this.byeaster != null)
	{
		result += ";BYEASTER=" + this.byeaster.join(',');
	}
	if (this.bymonthday != null)
	{
		result += ";BYMONTHDAY=" + this.bymonthday.join(',');
	}
	if (this.bysetpos != null)
	{
		result += ";BYSETPOS=" + this.bysetpos.join(',');
	}
	if (this.byhour != null)
	{
		result += ";BYHOUR=" + this.byhour.join(',');
	}
	if (this.byminute != null)
	{
		result += ";BYMINUTE=" + this.byminute.join(',');
	}
	if (this.bysecond != null)
	{
		result += ";BYSECOND=" + this.bysecond.join(',');
	}
	
	return "RRULE:" + result;
};

RecurrenceRule.prototype.DTStart = function()
{
	return this.dtstart;
};

RecurrenceRule.prototype.SetDTStart = function(value)
{
	this.dtstart = value;
};

RecurrenceRule.prototype.Cache = function()
{
	return this.cache;
};

RecurrenceRule.prototype.SetCache = function(value)
{
	this.cache = value;
};

RecurrenceRule.prototype.Frequency = function()
{
	return this.freq;
};

RecurrenceRule.prototype.SetFrequency = function(value)
{
	this.freq = value;
};

RecurrenceRule.prototype.Interval = function()
{
	return this.interval;
};

RecurrenceRule.prototype.SetInterval = function(value)
{
	if (value != null && value == 1) { value = null; }
	this.interval = value;
};

RecurrenceRule.prototype.Count = function()
{
	return this.count;
};

RecurrenceRule.prototype.SetCount = function(value)
{
	if (value != null && value == 0) { Log_Die("Invalid parameter"); }
	this.count = value;
};

RecurrenceRule.prototype.Until = function()
{
	return this.until;
};

RecurrenceRule.prototype.SetUntil = function(value)
{
	this.until = value;
};

//RecurrenceRule.prototype.Weekday = function()
//{
//	return this.weekday;
//};
//
//RecurrenceRule.prototype.SetWeekday = function(value)
//{
//	this.weekday = value;
//};

RecurrenceRule.prototype.BySetPos = function()
{
	return this.bysetpos;
};

RecurrenceRule.prototype.SetBySetPos = function(value)
{
	if (value != null && value.length == 0) { value = null; }
	this.bysetpos = value;
};

RecurrenceRule.prototype.ByMonth = function()
{
	return this.bymonth;
};

RecurrenceRule.prototype.SetByMonth = function(value)
{
	if (value != null && value.length == 0) { value = null; }
	this.bymonth = value;
};

RecurrenceRule.prototype.ByYearDay = function()
{
	return this.byyearday;
};

RecurrenceRule.prototype.SetByYearDay = function(value)
{
	if (value != null && value.length == 0) { value = null; }
	this.byyearday = value;
};

RecurrenceRule.prototype.ByEaster = function()
{
	return this.byeaster;
};

RecurrenceRule.prototype.SetByEaster = function(value)
{
	if (value != null && value.length == 0) { value = null; }
	this.byeaster = value;
};

RecurrenceRule.prototype.ByMonthDay = function()
{
	return this.bymonthday;
};

RecurrenceRule.prototype.SetByMonthDay = function(value)
{
	if (value != null && value.length == 0) { value = null; }
	this.bymonthday = value;
};

RecurrenceRule.prototype.ByWeekNo = function()
{
	return this.byweekno;
};

RecurrenceRule.prototype.SetByWeekNo = function(value)
{
	if (value != null && value.length == 0) { value = null; }
	return this.byweekno = value;
};

RecurrenceRule.prototype.ByWeekday = function()
{
	return this.byweekday;
};

RecurrenceRule.prototype.SetByWeekday = function(value)
{
	if (value != null && value.length == 0) { value = null; }
	this.byweekday = value;
};

RecurrenceRule.prototype.ByHour = function()
{
	return this.byhour;
};

RecurrenceRule.prototype.SetByHour = function(value)
{
	if (value != null && value.length == 0) { value = null; }
	this.byhour = value;
};

RecurrenceRule.prototype.ByMinute = function()
{
	return this.byminute;
};

RecurrenceRule.prototype.SetByMinute = function(value)
{
	if (value != null && value.length == 0) { value = null; }
	this.byminute = value;
};

RecurrenceRule.prototype.BySecond = function()
{
	return this.bysecond;
};

RecurrenceRule.prototype.SetBySecond = function(value)
{
	if (value != null && value.length == 0) { value = null; }
	this.bysecond = value;
};

function _Log(msg)
{
//	Log_WriteInfo(msg);
	return 1;
}

RecurrenceRule.prototype.GetIterator = function()
{
	// Some local variables to speed things up a bit
	var dtstart = this.dtstart;
	var freq = this.freq;
	var interval = this.interval;
	var wkst = this.wkst;
	var until = this.until;
	var bymonth = this.bymonth;
	var byweekno = this.byweekno;
	var byyearday = this.byyearday;
	var byweekday = this.byweekday;
	var byeaster = this.byeaster;
	var bymonthday = this.bymonthday;
	var bysetpos = this.bysetpos;
	var byhour = this.byhour;
	var byminute = this.byminute;
	var bysecond = this.bysecond;

	var tzinfo = 0;
	if (dtstart == null)
	{
		dtstart = DateAndTime_Now();
	}
	else if (!(dtstart instanceof DateAndTime))
	{
		dtstart = DateAndTime_FromOrdinalDay(dtstart, tzinfo);
	}
	// clear out milliseconds
	if (dtstart.HasTime())
	{
		dtstart.SetTime(dtstart.Hour(), dtstart.Minute(), dtstart.Second());
	}
	// make sure we have a time zone
	if (!dtstart.HasZone())
	{
		dtstart.SetZone(DateAndTime_LocalTimeZoneOffset());
	}

	// some defaults if not specified...
	if (interval == null) { interval = 1; }
	if (wkst == null) { wkst = FirstWeekDay; }
	tzinfo = (dtstart != null && dtstart.HasZone()) ? dtstart.Zone() : 0;

	var temp = dtstart.Extract();
	var year = temp[0], month = temp[1], day = temp[2], hour = temp[3], minute = temp[4], second = temp[5], zone = temp[6];
	var weekday = (dtstart.DayOfWeek() + 6) % 7;		// make Monday = 0
	var yearday = dtstart.DayOfYear();					// Jan 1st is 1, etc.

	if (until != null && !(until instanceof DateAndTime))
	{
		until = DateAndTime_FromOrdinalDay(until, tzinfo);
	}
	if (until != null && until.HasTime())
	{
		// clear out milliseconds
		until.SetTime(until.Hour(), until.Minute(), until.Second());
	}
	if (weekday == null)
	{
		weekday = FirstWeekDay;
	}
	else if (typeof weekday == "object")
	{
		weekday = weekday.weekday;
	}
	if (bysetpos == null)
	{
	}
	else if (!(bysetpos instanceof Array))
	{
		bysetpos = [bysetpos];
	}
	if (bysetpos != null)
	{
		for (var i = 0; i < bysetpos.length; i++)
		{
			var pos = bysetpos[i];
			if (pos == 0 || pos < -366 || pos > 366)
			{
				Log_WriteError("bysetpos must be between 1 and 366, or between -366 and -1, but got: " + pos);
			}
		}
	}
	if (byweekno == null && byyearday == null && bymonthday == null && byweekday == null && byeaster == null)
	{
		if (freq == RecurrenceRuleConstants_YEARLY)
		{
			if (bymonth == null)
			{
				bymonth = dtstart.Month();
			}
			bymonthday = dtstart.Day();
		}
		else if (freq == RecurrenceRuleConstants_MONTHLY)
		{
			bymonthday = dtstart.Day();
		}
		else if (freq == RecurrenceRuleConstants_WEEKLY)
		{
			byweekday = (dtstart.DayOfWeek() + 6) % 7;	// convert to 0 as Monday
		}
	}
//Log_WriteInfo("1 byweekday: " + Dumper byweekday);
	if (bymonth != null && !(bymonth instanceof Array))
	{
		bymonth = [bymonth];
	}
	if (byyearday != null && !(byyearday instanceof Array))
	{
		byyearday = [byyearday];
	}
	if (byeaster != null && !(byeaster instanceof Array))
	{
		byeaster = [byeaster];
	}
	var bynmonthday;
	if (bymonthday == null)
	{
		// DRL I think it's strange that this value is always set to an array whereas for others (such as byeaster) it's null when not used
		bymonthday = [];
		bynmonthday = [];
	}
	else if (bymonthday instanceof Array)
	{
		var p = [];
		var n = [];
		for (var i = 0; i < bymonthday.length; i++)
		{
			var v = bymonthday[i];
			if (v < 0)
			{
				n.push(v);
			}
			else if (v >= 0)
			{
				p.push(v);
			}
		}
		bymonthday = p;
		bynmonthday = n;
	}
	else
	{
		var temp = bymonthday;
		if (bymonthday >= 0)
		{
			bymonthday = [temp];
			bynmonthday = [];
		}
		else
		{
			bymonthday = [];
			bynmonthday = [temp];
		}
	}
	if (byweekno != null && !(byweekno instanceof Array))
	{
		byweekno = [byweekno];
	}
	var bynweekday;
	if (byweekday != null)
	{
		if (!(byweekday instanceof Array))
		{
			byweekday = [byweekday];
		}
		var temp = [];
		bynweekday = [];
		for (var i = 0; i < byweekday.length; i++)
		{
			var wday = byweekday[i];
			if (! wday.n || freq > RecurrenceRuleConstants_MONTHLY)
			{
				temp.push(wday.weekday);
			}
			else
			{
				bynweekday.push([wday.weekday,wday.n]);
			}
		}
		byweekday = temp;
		if (byweekday.length == 0)
		{
			byweekday = null;
		}
		if (bynweekday.length == 0)
		{
			bynweekday = null;
		}
	}
//Log_WriteInfo("2 byweekday: " + Dumper byweekday);
	if (byhour != null  && !(byhour instanceof Array))
	{
		byhour = [byhour];
	}
	if (byminute != null && !(byminute instanceof Array))
	{
		byminute = [byminute];
	}
	if (bysecond != null && !(bysecond instanceof Array))
	{
		bysecond = [bysecond];
	}

	if (byhour == null && freq < RecurrenceRuleConstants_HOURLY)
	{
		byhour = [dtstart.Hour()];
	}
	if (byminute == null && freq < RecurrenceRuleConstants_MINUTELY)
	{
		byminute = [dtstart.Minute()];
	}
	if (bysecond == null && freq < RecurrenceRuleConstants_SECONDLY)
	{
		bysecond = [dtstart.Second()];
	}
	
	// RecurrenceRuleIteratorInfo will be looking for these updated values...
	// DRL FIXIT! This will affect our ToString() method! Perhaps we should be saving these elsewhere?
	this.wkst = wkst;
	this.bymonth = bymonth;
	this.byweekno = byweekno;
	this.byyearday = byyearday;
	this.byweekday = byweekday;
	this.bynweekday = bynweekday;
	this.byeaster = byeaster;
	this.bymonthday = bymonthday;
	this.bysetpos = bysetpos;
	this.byhour = byhour;
	this.byminute = byminute;
	this.bysecond = bysecond;

	var ii = new RecurrenceRuleIteratorInfo(this);
	ii.rebuild(year, month);

	var iter = new RecurrenceRuleIterator();
	
	var getdayset;
	if (freq == RecurrenceRuleConstants_YEARLY)			{ getdayset = RecurrenceRuleIteratorInfo.prototype.ydayset; }
	else if (freq == RecurrenceRuleConstants_MONTHLY)	{ getdayset = RecurrenceRuleIteratorInfo.prototype.mdayset; }
	else if (freq == RecurrenceRuleConstants_WEEKLY)	{ getdayset = RecurrenceRuleIteratorInfo.prototype.wdayset; }
	else if (freq == RecurrenceRuleConstants_DAILY)		{ getdayset = RecurrenceRuleIteratorInfo.prototype.ddayset; }
	else if (freq == RecurrenceRuleConstants_HOURLY)	{ getdayset = RecurrenceRuleIteratorInfo.prototype.ddayset; }
	else if (freq == RecurrenceRuleConstants_MINUTELY)	{ getdayset = RecurrenceRuleIteratorInfo.prototype.ddayset; }
	else if (freq == RecurrenceRuleConstants_SECONDLY)	{ getdayset = RecurrenceRuleIteratorInfo.prototype.ddayset; }
	else { Log_Die("Unknown frequency"); }
	
	var timeset;
	var gettimeset;
	
	if (freq < RecurrenceRuleConstants_HOURLY)
	{
		timeset = [];
		for (var i = 0; i < byhour.length; i++)
		{
			var hour = byhour[i];
			for (var j = 0; j < byminute.length; j++)
			{
				var minute = byminute[j];
				for (var k = 0; k < bysecond.length; k++)
				{
					var second = bysecond[k];
					timeset.push(new DateAndTime(null, null, null, hour, minute, second, 0, tzinfo));
				}
			}
		}
		timeset.sort(function(a, b) { return a.Compare(b); });
	}
	else
	{
		if (freq == RecurrenceRuleConstants_HOURLY)			{ gettimeset = RecurrenceRuleIteratorInfo.prototype.htimeset; }
		else if (freq == RecurrenceRuleConstants_MINUTELY)	{ gettimeset = RecurrenceRuleIteratorInfo.prototype.mtimeset; }
		else if (freq == RecurrenceRuleConstants_SECONDLY)	{ gettimeset = RecurrenceRuleIteratorInfo.prototype.stimeset; }
		else { Log_Die("Unknown frequency"); }
		
		if ((freq >= RecurrenceRuleConstants_HOURLY && byhour != null && !Utilities_ArrayContains(byhour, hour)) ||
			(freq >= RecurrenceRuleConstants_MINUTELY && byminute != null && !Utilities_ArrayContains(byminute, minute)) ||
			(freq >= RecurrenceRuleConstants_SECONDLY && bysecond != null && !Utilities_ArrayContains(bysecond, second)))
		{
			timeset = [];
		}
		else
		{
			timeset = gettimeset.call(ii, hour, minute, second);
		}
	}
	
	var total = 0;
	var count = this.count;
// DRL FIXIT! Some recurrences returned a lot of values and took a long time!
//	while (1)
	while (year < 2038)
	{
		// Get dayset with the right frequency
		var temp = getdayset.call(ii, year, month, day);
		var _dayset = temp[0], start = temp[1], end = temp[2];
		var dayset = _dayset.slice();	// make a copy
		
		// Do the "hard" work ;-)
		var filtered = 0;
		for (var j = start; j < end; j++)
		{
			var i = dayset[j];
			
			if ((bymonth != null && !Utilities_ArrayContains(bymonth, ii.mmask[i]) && _Log("bymonth")) ||
				(byweekno != null && !ii.wnomask[i] && _Log("byweekno")) ||
				(byweekday != null && !Utilities_ArrayContains(byweekday, ii.wdaymask[i]) && _Log("byweekday")) ||
				(ii.nwdaymask != null && !ii.nwdaymask[i] && _Log("nwdaymask")) ||
				(byeaster != null && !ii.eastermask[i] && _Log("eastermask")) ||
				((bymonthday.length || bynmonthday.length) && !Utilities_ArrayContains(bymonthday, ii.mdaymask[i]) && !Utilities_ArrayContains(bynmonthday, ii.nmdaymask[i]) && _Log("bymonthday")) ||
				(byyearday != null && ((i < ii.yearlen && !Utilities_ArrayContains(byyearday, i+1) && !Utilities_ArrayContains(byyearday, -ii.yearlen+i) && _Log("byyearday1")) ||
					(i >= ii.yearlen && !Utilities_ArrayContains(byyearday, i+1-ii.yearlen) && !Utilities_ArrayContains(byyearday, -ii.nextyearlen+i-ii.yearlen) && _Log("byyearday2")))))
			{
				dayset[j] = null;
				filtered = 1;
			}
			else
			{
				_Log("Including day " + i + " from entry " + j);
			}
		}
		
		// Output results
		if (bysetpos != null && bysetpos.length && timeset &&
			timeset.length)	// DRL ADDED
		{
			var poslist = [];
			for (var j = 0; j < bysetpos.length; j++)
			{
				var pos = bysetpos[j];
				var daypos;
				var timepos;
				if (pos < 0)
				{
					daypos = Utilities_Div(pos, timeset.length);
					timepos = pos % timeset.length;
				}
				else
				{
					daypos = Utilities_Div(pos-1, timeset.length);
					timepos = (pos-1) % timeset.length;
				}
				var i;
				var time;
				try
				{
					var temp = [];
					for (var k = start; k < end; k++)
					{
						var x = dayset[k];
						if (x != null)
						{
							temp.push(x);
						}
					}
					if (daypos < 0) { daypos = temp.length + daypos; }	// Javascript doesn't support negative indexing
					i = temp[daypos];
					time = timeset[timepos];
				}
				catch (err)
				{
				}
				finally
				{
					var date = DateAndTime_FromOrdinalDay(ii.yearordinal + i, tzinfo);
					date.SetTime(time.Hour(), time.Minute(), time.Second());
					if (!Utilities_ArrayContains(poslist, date))
					{
						poslist.push(date.Copy());	// have to copy as we're changing the same instance above
					}
				}
			}
			poslist.sort(function(a, b) { return a.Compare(b); });
			for (var i = 0; i < poslist.length; i++)
			{
				var res = poslist[i];
				if (until && res.Compare(until) > 0)
				{
					this.len = total;
					return iter;
				}
				else if (res.Compare(dtstart) >= 0)
				{
					total += 1;
					iter.AddDate(res);
					if (count)
					{
						count -= 1;
						if (!count)
						{
							this.len = total;
							return iter;
						}
					}
				}
			}
		}
		else
		{
			for (var j = start; j < end; j++)
			{
				var i = dayset[j];
				if (i != null)
				{
					var date = DateAndTime_FromOrdinalDay(ii.yearordinal + i, tzinfo);
					for (var i = 0; i < timeset.length; i++)
					{
						var time = timeset[i];
						date.SetTime(time.Hour(), time.Minute(), time.Second());
						if (until && date.Compare(until) > 0)
						{
							this.len = total;
							return iter;
						}
						else if (date.Compare(dtstart) >= 0)
						{
							total += 1;
							iter.AddDate(date.Copy());	// have to copy as we're changing the same instance above
							if (count)
							{
								count -= 1;
								if (!count)
								{
									this.len = total;
									return iter;
								}
							}
						}
					}
				}
			}
		}
		
		// Handle frequency and interval
		var fixday = 0;
		if (freq == RecurrenceRuleConstants_YEARLY)
		{
			year += interval;
			if (year > DateAndTime_MAXYEAR)
			{
				this.len = total;
				return iter;
			}
			ii.rebuild(year, month);
		}
		else if (freq == RecurrenceRuleConstants_MONTHLY)
		{
			month += interval;
			if (month > 12)
			{
				var div = Utilities_Div(month, 12);
				var mod = month % 12;
				month = mod;
				year += div;
				if (month == 0)
				{
					month = 12;
					year -= 1;
				}
				if (year > DateAndTime_MAXYEAR)
				{
					this.len = total;
					return iter;
				}
			}
			ii.rebuild(year, month);
		}
		else if (freq == RecurrenceRuleConstants_WEEKLY)
		{
			if (wkst > weekday)
			{
				day += -(weekday+1+(6-wkst))+interval*7;
			}
			else
			{
				day += -(weekday-wkst)+interval*7;
			}
			weekday = wkst;
			fixday = 1;
		}
		else if (freq == RecurrenceRuleConstants_DAILY)
		{
			day += interval;
			fixday = 1;
		}
		else if (freq == RecurrenceRuleConstants_HOURLY)
		{
			if (filtered)
			{
				// Jump to one iteration before next day
				hour += Utilities_Div(23-hour,interval)*interval;
			}
			while (1)
			{
				hour += interval;
				var div = Utilities_Div(hour, 24);
				var mod = hour % 24;
				if (div)
				{
					hour = mod;
					day += div;
					fixday = 1;
				}
				if (byhour == null || Utilities_ArrayContains(byhour, hour))
				{
					break;
				}
			}
			timeset = gettimeset.call(ii, hour, minute, second);
		}
		else if (freq == RecurrenceRuleConstants_MINUTELY)
		{
			if (filtered)
			{
				// Jump to one iteration before next day
				minute += Utilities_Div(1439-(hour*60+minute),interval)*interval;
			}
			while (1)
			{
				minute += interval;
				var div = Utilities_Div(minute, 60);
				var mod = minute % 60;
				if (div)
				{
					minute = mod;
					hour += div;
					div = Utilities_Div(hour, 24);
					mod = hour % 24;
					if (div)
					{
						hour = mod;
						day += div;
						fixday = 1;
						filtered = 0;
					}
				}
				if ((byhour == null || Utilities_ArrayContains(byhour, hour)) &&
					(byminute == null || Utilities_ArrayContains(byminute, minute)))
				{
					break;
				}
			}
			timeset = gettimeset.call(ii, hour, minute, second);
		}
		else if (freq == RecurrenceRuleConstants_SECONDLY)
		{
			if (filtered)
			{
				// Jump to one iteration before next day
				second += Utilities_Div(86399-(hour*3600+minute*60+second),interval)*interval;
			}
			while (1)
			{
				second += interval;
				var div = Utilities_Div(second, 60);
				var mod = second % 60;
				if (div)
				{
					second = mod;
					minute += div;
					div = Utilities_Div(minute, 60);
					mod = minute % 60;
					if (div)
					{
						minute = mod;
						hour += div;
						div = Utilities_Div(hour, 24);
						mod = hour % 24;
						if (div)
						{
							hour = mod;
							day += div;
							fixday = 1;
						}
					}
				}
				if ((byhour == null || Utilities_ArrayContains(byhour, hour)) &&
					(byminute == null || Utilities_ArrayContains(byminute, minute)) &&
					(bysecond == null || Utilities_ArrayContains(bysecond, second)))
				{
					break;
				}
			}
			timeset = gettimeset.call(ii, hour, minute, second);
		}
			
		if (fixday && day > 28)
		{
			var daysinmonth = DateAndTime_DaysInMonth(year, month);
			if (day > daysinmonth)
			{
				while (day > daysinmonth)
				{
					day -= daysinmonth;
					month += 1;
					if (month == 13)
					{
						month = 1;
						year += 1;
						if (year > DateAndTime_MAXYEAR)
						{
							this.len = total;
							return iter;
						}
					}
					daysinmonth = DateAndTime_DaysInMonth(year, month);
				}
				ii.rebuild(year, month);
			}
		}
	}
	
	return iter;
};

// only used for testing
function _CheckValues(testStartDate, s, _values)
{
	var values = _values.slice();	// make a copy
	var t = RecurrenceRule_FromString(s, testStartDate);
	if (t == null)
	{
		Log_Die("Can't parse " + s + "!");
	}
	var s2 = t.toString();
	if (s2 != s)
	{
		Log_Die("toString() value " + s2 + " doesn't match original " + s);
	}
	var i = t.GetIterator();
	if (i == null)
	{
		Log_Die("Can't create iterator for " + s + "!");
	}
	var length = values.length;
	var count = 0;
	while (values.length)
	{
		var x = values.shift();
		var d = i.GetNext();
		if (d == null)
		{
			if (x == null)
			{
				break;	// done
			}
			
			Log_Die("Not enough dates returned for " + s + ". Got " + count + " but wanted " + length + "!");
		}
		if (x == null)
		{
			Log_Die("Too many dates returned for " + s + "!");
		}
		count++;
		var y = DateAndTime_FromString(x, 0);
		if (y == null)
		{
			Log_Die("Can't parse date " + x + " for " + s + "!");
		}
		if (y.Compare(d[0]) != 0)
		{
			Log_Die("Date '" + d[0] + "' doesn't match '" + y + "' for " + s + "!");
		}
	}
	i.Close();
}

function RecurrenceRule_FromString(s, dtstart, cache, unfold, forceset, compatible, ignoretz, tzinfo)
{
	if (cache == null) { cache = 0; }
	if (unfold == null) { unfold = 0; }
	if (forceset == null) { forceset = 0; }
	if (compatible == null) { compatible = 0; }
	if (ignoretz == null) { ignoretz = 0; }
	if (compatible)
	{
		forceset = 1;
		unfold = 1;
	}

	if (s == null)
	{
		return null;
	}
	s = strtoupper(s);
	Utilities_Chomp(s);
	if (s.length == 0)
	{
		return null;
	}
	
	var lines = s.split(/\n/);
   if (unfold)
	{
		var i = 0;
		while (i < lines.length)
		{
			var line = lines[i];
			Utilities__Chomp(line);
			if (line.length == 0)
			{
				lines.splice(i, 1);
			}
			else if (i > 0 && substr(line, 0, 1) == " ")
			{
				lines[i-1] += substr(line, 1);
				lines.splice(i, 1);
			}
			else
			{
				i++;
			}
		}
	}

	if (!forceset && lines.length == 1 && (strpos(s, ':') === FALSE || substr(s, 0, 6) == 'RRULE:'))
	{
		return new RecurrenceRule(dtstart, cache, RecurrenceRuleParser_ParseRecurrenceRule(lines[0], ignoretz, tzinfo));
	}
	
	var rrulevals = [];
	var rdatevals = [];
	var exrulevals = [];
	var exdatevals = [];
	
	for (var i = 0; i < lines.length; i++)
	{
		var line = lines[i];
		var name;
		var value;
		
		if (line.length > 0)
		{
			if (strpos(line, ':') === FALSE)
			{
				name = "RRULE";
				value = line;
			}
			else
			{
				var temp = line.split(':', 2);
				name = temp[0];
				value = temp[1];
			}
			var parms = name.split(';');
			if (parms.length == 0)
			{
				Log_WriteError("empty property name");
			}
			name = parms[0];
			parms.splice(0, 1);
			if (name == "RRULE")
			{
				for (var j = 0; j < parms.length; j++)
				{
					var parm = parms[j];
					Log_WriteError("unsupported RRULE parameter: " + parm);
				}
				rrulevals.push(value);
			}
			else if (name == "RDATE")
			{
				for (var j = 0; j < parms.length; j++)
				{
					var parm = parms[j];
					if (parm != "VALUE=DATE-TIME")
					{
						Log_WriteError("unsupported RDATE parameter: " + parm);
					}
				}
				rdatevals.push(value);
			}
			else if (name == "EXRULE")
			{
				for (var j = 0; j < parms.length; j++)
				{
					var parm = parms[j];
					Log_WriteError("unsupported EXRULE parameter: " + parm);
				}
				exrulevals.push(value);
			}
			else if (name == "EXDATE")
			{
				for (var j = 0; j < parms.length; j++)
				{
					var parm = parms[j];
					if (parm != "VALUE=DATE-TIME")
					{
						Log_WriteError("unsupported RDATE parameter: " + parm);
					}
				}
				exdatevals.push(value);
			}
			else if (name == "DTSTART")
			{
				for (var j = 0; j < parms.length; j++)
				{
					var parm = parms[j];
					Log_WriteError("unsupported DTSTART parameter: " + parm);
				}
				dtstart = DateAndTime_FromString(value, tzinfo); //, ignoretz=ignoretz, tzinfo=tzinfo);
			}
			else
			{
				Log_WriteError("unsupported property: " + name);
			}
		}
	}
	
	if (forceset || rrulevals.length > 1 || rdatevals.length > 0 || exrulevals.length > 0 || exdatevals.length > 0)
	{
		var set = new RecurrenceRuleSet(cache);
		for (var i = 0; i < rrulevals.length; i++)
		{
			var value = rrulevals[i];
			set.AddRRule(new RecurrenceRule(dtstart, null, RecurrenceRuleParser_ParseRecurrenceRule(value, ignoretz, tzinfo)));
		}
		for (var i = 0; i < rdatevals.length; i++)
		{
			var value = rdatevals[i];
			var array = value.split(',');
			for (var j = 0; j < array.length; j++)
			{
				var datestr = array[j];
				set.AddRDate(DateAndTime_FromString(datestr, tzinfo));	//, ignoretz=ignoretz, tzinfo=tzinfo))
			}
		}
		for (var i = 0; i < exrulevals.length; i++)
		{
			var value = exrulevals[i];
			set.AddExRule(new RecurrenceRule(dtstart, null, RecurrenceRuleParser_ParseRecurrenceRule(value, ignoretz, tzinfo)));
		}
		for (var i = 0; i < exdatevals.length; i++)
		{
			var value = exdatevals[i];
			var array = value.split(',');
			for (var j = 0; j < array.length; j++)
			{
				var datestr = array[j];
				set.AddExDate(DateAndTime_FromString(datestr, tzinfo));	//, ignoretz=ignoretz, tzinfo=tzinfo))
			}
		}
		if (compatible && dtstart)
		{
			set.AddRDate(dtstart);
		}
		
		return set;
	}
	
	return new RecurrenceRule(dtstart, cache, RecurrenceRuleParser_ParseRecurrenceRule(rrulevals[0], ignoretz, tzinfo));
}

var testStartDate = DateAndTime_FromString("20110501T120102Z");

if (0)
{
// use this for testing...
	var t = RecurrenceRule_FromString("RRULE:FREQ=WEEKLY;INTERVAL=;BYDAY=TU,FR", testStartDate);
	var s = t.toString();
	Log_WriteInfo(s);
	var i = t.GetIterator();
	alert(s);
	if (i)
	{
		var d = i.GetNext();
		while (d != null)
		{
			Log_WriteInfo("" + d[0].toString() + "\r");
			d = i.GetNext();
		}
		i.Close();
	}
}
if (1)
{
	_CheckValues(testStartDate, "RRULE:FREQ=YEARLY;UNTIL=20180101T000000Z\nEXDATE:20130501T120102Z",
		[
		"2011/05/01 12:01:02",
		"2012/05/01 12:01:02",
		"2014/05/01 12:01:02",
		"2015/05/01 12:01:02",
		"2016/05/01 12:01:02",
		"2017/05/01 12:01:02",
		null
		]);
	_CheckValues(testStartDate, "RRULE:FREQ=YEARLY;UNTIL=20180101T000000Z",
		[
		"2011/05/01 12:01:02",
		"2012/05/01 12:01:02",
		"2013/05/01 12:01:02",
		"2014/05/01 12:01:02",
		"2015/05/01 12:01:02",
		"2016/05/01 12:01:02",
		"2017/05/01 12:01:02",
		null
		]);
	_CheckValues(testStartDate, "RRULE:FREQ=MONTHLY;COUNT=8;BYDAY=MO,TU,WE,TH,FR;BYSETPOS=-1",
		[
		"2011/05/31 12:01:02",
		"2011/06/30 12:01:02",
		"2011/07/29 12:01:02",
		"2011/08/31 12:01:02",
		"2011/09/30 12:01:02",
		"2011/10/31 12:01:02",
		"2011/11/30 12:01:02",
		"2011/12/30 12:01:02",
		null
		]);
	_CheckValues(testStartDate, "RRULE:FREQ=YEARLY;INTERVAL=2;BYMONTH=1;BYDAY=SU;BYHOUR=8,9;BYMINUTE=30",
		[
		"2013/01/06 08:30:02",
		"2013/01/06 09:30:02",
		"2013/01/13 08:30:02",
		"2013/01/13 09:30:02",
		"2013/01/20 08:30:02",
		"2013/01/20 09:30:02",
		"2013/01/27 08:30:02",
		"2013/01/27 09:30:02",
		"2015/01/04 08:30:02",
		"2015/01/04 09:30:02",
		]);
	_CheckValues(testStartDate, "RRULE:FREQ=DAILY;COUNT=10;INTERVAL=2",
		[
		"2011/05/01 12:01:02",
		"2011/05/03 12:01:02",
		"2011/05/05 12:01:02",
		"2011/05/07 12:01:02",
		"2011/05/09 12:01:02",
		"2011/05/11 12:01:02",
		"2011/05/13 12:01:02",
		"2011/05/15 12:01:02",
		"2011/05/17 12:01:02",
		"2011/05/19 12:01:02",
		null,
		]);
	_CheckValues(testStartDate, "RRULE:FREQ=MONTHLY;BYDAY=1SU",
		[
		"2011/05/01 12:01:02",
		"2011/06/05 12:01:02",
		"2011/07/03 12:01:02",
		"2011/08/07 12:01:02",
		"2011/09/04 12:01:02",
		"2011/10/02 12:01:02",
		"2011/11/06 12:01:02",
		"2011/12/04 12:01:02",
		"2012/01/01 12:01:02",
		"2012/02/05 12:01:02",
		"2012/03/04 12:01:02",
		"2012/04/01 12:01:02",
		"2012/05/06 12:01:02",
		"2012/06/03 12:01:02",
		"2012/07/01 12:01:02",
		"2012/08/05 12:01:02",
		"2012/09/02 12:01:02",
		"2012/10/07 12:01:02",
		"2012/11/04 12:01:02",
		"2012/12/02 12:01:02",
		]);
	_CheckValues(testStartDate, "RRULE:FREQ=WEEKLY;BYDAY=TU,FR",
		[
		"2011/05/03 12:01:02",
		"2011/05/06 12:01:02",
		"2011/05/10 12:01:02",
		"2011/05/13 12:01:02",
		"2011/05/17 12:01:02",
		"2011/05/20 12:01:02",
		"2011/05/24 12:01:02",
		"2011/05/27 12:01:02",
		"2011/05/31 12:01:02",
		"2011/06/03 12:01:02",
		"2011/06/07 12:01:02",
		"2011/06/10 12:01:02",
		"2011/06/14 12:01:02",
		"2011/06/17 12:01:02",
		"2011/06/21 12:01:02",
		"2011/06/24 12:01:02",
		"2011/06/28 12:01:02",
		"2011/07/01 12:01:02",
		"2011/07/05 12:01:02",
		"2011/07/08 12:01:02",
		"2011/07/12 12:01:02",
		"2011/07/15 12:01:02",
		"2011/07/19 12:01:02",
		"2011/07/22 12:01:02",
		"2011/07/26 12:01:02",
		"2011/07/29 12:01:02",
		"2011/08/02 12:01:02",
		"2011/08/05 12:01:02",
		"2011/08/09 12:01:02",
		"2011/08/12 12:01:02",
		"2011/08/16 12:01:02",
		"2011/08/19 12:01:02",
		"2011/08/23 12:01:02",
		"2011/08/26 12:01:02",
		"2011/08/30 12:01:02",
		"2011/09/02 12:01:02",
		"2011/09/06 12:01:02",
		"2011/09/09 12:01:02",
		"2011/09/13 12:01:02",
		"2011/09/16 12:01:02",
		"2011/09/20 12:01:02",
		"2011/09/23 12:01:02",
		"2011/09/27 12:01:02",
		"2011/09/30 12:01:02",
		"2011/10/04 12:01:02",
		"2011/10/07 12:01:02",
		"2011/10/11 12:01:02",
		"2011/10/14 12:01:02",
		"2011/10/18 12:01:02",
		"2011/10/21 12:01:02",
		"2011/10/25 12:01:02",
		"2011/10/28 12:01:02",
		"2011/11/01 12:01:02",
		"2011/11/04 12:01:02",
		"2011/11/08 12:01:02",
		"2011/11/11 12:01:02",
		"2011/11/15 12:01:02",
		"2011/11/18 12:01:02",
		"2011/11/22 12:01:02",
		"2011/11/25 12:01:02",
		"2011/11/29 12:01:02",
		"2011/12/02 12:01:02",
		"2011/12/06 12:01:02",
		"2011/12/09 12:01:02",
		"2011/12/13 12:01:02",
		"2011/12/16 12:01:02",
		"2011/12/20 12:01:02",
		"2011/12/23 12:01:02",
		"2011/12/27 12:01:02",
		"2011/12/30 12:01:02",
		"2012/01/03 12:01:02",
		"2012/01/06 12:01:02",
		"2012/01/10 12:01:02",
		"2012/01/13 12:01:02",
		"2012/01/17 12:01:02",
		"2012/01/20 12:01:02",
		"2012/01/24 12:01:02",
		"2012/01/27 12:01:02",
		"2012/01/31 12:01:02",
		"2012/02/03 12:01:02",
		"2012/02/07 12:01:02",
		"2012/02/10 12:01:02",
		"2012/02/14 12:01:02",
		"2012/02/17 12:01:02",
		"2012/02/21 12:01:02",
		"2012/02/24 12:01:02",
		"2012/02/28 12:01:02",
		"2012/03/02 12:01:02",
		"2012/03/06 12:01:02",
		"2012/03/09 12:01:02",
		"2012/03/13 12:01:02",
		"2012/03/16 12:01:02",
		"2012/03/20 12:01:02",
		"2012/03/23 12:01:02",
		"2012/03/27 12:01:02",
		"2012/03/30 12:01:02",
		"2012/04/03 12:01:02",
		"2012/04/06 12:01:02",
		"2012/04/10 12:01:02",
		"2012/04/13 12:01:02",
		"2012/04/17 12:01:02",
		"2012/04/20 12:01:02",
		"2012/04/24 12:01:02",
		"2012/04/27 12:01:02",
		"2012/05/01 12:01:02",
		"2012/05/04 12:01:02",
		"2012/05/08 12:01:02",
		"2012/05/11 12:01:02",
		"2012/05/15 12:01:02",
		"2012/05/18 12:01:02",
		"2012/05/22 12:01:02",
		"2012/05/25 12:01:02",
		"2012/05/29 12:01:02",
		"2012/06/01 12:01:02",
		"2012/06/05 12:01:02",
		"2012/06/08 12:01:02",
		"2012/06/12 12:01:02",
		"2012/06/15 12:01:02",
		"2012/06/19 12:01:02",
		"2012/06/22 12:01:02",
		"2012/06/26 12:01:02",
		"2012/06/29 12:01:02",
		"2012/07/03 12:01:02",
		"2012/07/06 12:01:02",
		"2012/07/10 12:01:02",
		"2012/07/13 12:01:02",
		"2012/07/17 12:01:02",
		"2012/07/20 12:01:02",
		"2012/07/24 12:01:02",
		"2012/07/27 12:01:02",
		"2012/07/31 12:01:02",
		"2012/08/03 12:01:02",
		"2012/08/07 12:01:02",
		"2012/08/10 12:01:02",
		"2012/08/14 12:01:02",
		"2012/08/17 12:01:02",
		"2012/08/21 12:01:02",
		"2012/08/24 12:01:02",
		"2012/08/28 12:01:02",
		"2012/08/31 12:01:02",
		"2012/09/04 12:01:02",
		"2012/09/07 12:01:02",
		"2012/09/11 12:01:02",
		"2012/09/14 12:01:02",
		"2012/09/18 12:01:02",
		"2012/09/21 12:01:02",
		"2012/09/25 12:01:02",
		"2012/09/28 12:01:02",
		"2012/10/02 12:01:02",
		"2012/10/05 12:01:02",
		"2012/10/09 12:01:02",
		"2012/10/12 12:01:02",
		"2012/10/16 12:01:02",
		"2012/10/19 12:01:02",
		"2012/10/23 12:01:02",
		"2012/10/26 12:01:02",
		"2012/10/30 12:01:02",
		"2012/11/02 12:01:02",
		"2012/11/06 12:01:02",
		"2012/11/09 12:01:02",
		"2012/11/13 12:01:02",
		"2012/11/16 12:01:02",
		"2012/11/20 12:01:02",
		"2012/11/23 12:01:02",
		"2012/11/27 12:01:02",
		"2012/11/30 12:01:02",
		"2012/12/04 12:01:02",
		"2012/12/07 12:01:02",
		"2012/12/11 12:01:02",
		"2012/12/14 12:01:02",
		"2012/12/18 12:01:02",
		"2012/12/21 12:01:02",
		"2012/12/25 12:01:02",
		"2012/12/28 12:01:02",
		"2013/01/01 12:01:02",
		"2013/01/04 12:01:02",
		"2013/01/08 12:01:02",
		"2013/01/11 12:01:02",
		"2013/01/15 12:01:02",
		"2013/01/18 12:01:02",
		"2013/01/22 12:01:02",
		"2013/01/25 12:01:02",
		"2013/01/29 12:01:02",
		"2013/02/01 12:01:02",
		"2013/02/05 12:01:02",
		"2013/02/08 12:01:02",
		"2013/02/12 12:01:02",
		"2013/02/15 12:01:02",
		"2013/02/19 12:01:02",
		"2013/02/22 12:01:02",
		"2013/02/26 12:01:02",
		"2013/03/01 12:01:02",
		"2013/03/05 12:01:02",
		"2013/03/08 12:01:02",
		"2013/03/12 12:01:02",
		"2013/03/15 12:01:02",
		"2013/03/19 12:01:02",
		"2013/03/22 12:01:02",
		"2013/03/26 12:01:02",
		"2013/03/29 12:01:02",
		"2013/04/02 12:01:02",
		"2013/04/05 12:01:02",
		"2013/04/09 12:01:02",
		"2013/04/12 12:01:02",
		"2013/04/16 12:01:02",
		"2013/04/19 12:01:02",
		"2013/04/23 12:01:02",
		"2013/04/26 12:01:02",
		"2013/04/30 12:01:02",
		"2013/05/03 12:01:02",
		"2013/05/07 12:01:02",
		"2013/05/10 12:01:02",
		"2013/05/14 12:01:02",
		"2013/05/17 12:01:02",
		"2013/05/21 12:01:02",
		"2013/05/24 12:01:02",
		"2013/05/28 12:01:02",
		"2013/05/31 12:01:02",
		"2013/06/04 12:01:02",
		"2013/06/07 12:01:02",
		"2013/06/11 12:01:02",
		"2013/06/14 12:01:02",
		"2013/06/18 12:01:02",
		"2013/06/21 12:01:02",
		"2013/06/25 12:01:02",
		"2013/06/28 12:01:02",
		"2013/07/02 12:01:02",
		"2013/07/05 12:01:02",
		"2013/07/09 12:01:02",
		"2013/07/12 12:01:02",
		"2013/07/16 12:01:02",
		"2013/07/19 12:01:02",
		"2013/07/23 12:01:02",
		"2013/07/26 12:01:02",
		"2013/07/30 12:01:02",
		"2013/08/02 12:01:02",
		"2013/08/06 12:01:02",
		"2013/08/09 12:01:02",
		"2013/08/13 12:01:02",
		"2013/08/16 12:01:02",
		"2013/08/20 12:01:02",
		"2013/08/23 12:01:02",
		"2013/08/27 12:01:02",
		"2013/08/30 12:01:02",
		"2013/09/03 12:01:02",
		"2013/09/06 12:01:02",
		"2013/09/10 12:01:02",
		"2013/09/13 12:01:02",
		"2013/09/17 12:01:02",
		"2013/09/20 12:01:02",
		"2013/09/24 12:01:02",
		"2013/09/27 12:01:02",
		"2013/10/01 12:01:02",
		"2013/10/04 12:01:02",
		"2013/10/08 12:01:02",
		"2013/10/11 12:01:02",
		"2013/10/15 12:01:02",
		"2013/10/18 12:01:02",
		"2013/10/22 12:01:02",
		"2013/10/25 12:01:02",
		"2013/10/29 12:01:02",
		"2013/11/01 12:01:02",
		"2013/11/05 12:01:02",
		"2013/11/08 12:01:02",
		"2013/11/12 12:01:02",
		"2013/11/15 12:01:02",
		"2013/11/19 12:01:02",
		"2013/11/22 12:01:02",
		"2013/11/26 12:01:02",
		"2013/11/29 12:01:02",
		"2013/12/03 12:01:02",
		"2013/12/06 12:01:02",
		"2013/12/10 12:01:02",
		"2013/12/13 12:01:02",
		"2013/12/17 12:01:02",
		"2013/12/20 12:01:02",
		"2013/12/24 12:01:02",
		"2013/12/27 12:01:02",
		"2013/12/31 12:01:02",
		"2014/01/03 12:01:02",
		"2014/01/07 12:01:02",
		"2014/01/10 12:01:02",
		"2014/01/14 12:01:02",
		"2014/01/17 12:01:02",
		"2014/01/21 12:01:02",
		"2014/01/24 12:01:02",
		"2014/01/28 12:01:02",
		"2014/01/31 12:01:02",
		"2014/02/04 12:01:02",
		"2014/02/07 12:01:02",
		"2014/02/11 12:01:02",
		"2014/02/14 12:01:02",
		"2014/02/18 12:01:02",
		"2014/02/21 12:01:02",
		"2014/02/25 12:01:02",
		"2014/02/28 12:01:02",
		"2014/03/04 12:01:02",
		"2014/03/07 12:01:02",
		"2014/03/11 12:01:02",
		"2014/03/14 12:01:02",
		"2014/03/18 12:01:02",
		"2014/03/21 12:01:02",
		"2014/03/25 12:01:02",
		"2014/03/28 12:01:02",
		"2014/04/01 12:01:02",
		"2014/04/04 12:01:02",
		"2014/04/08 12:01:02",
		"2014/04/11 12:01:02",
		"2014/04/15 12:01:02",
		"2014/04/18 12:01:02",
		"2014/04/22 12:01:02",
		"2014/04/25 12:01:02",
		"2014/04/29 12:01:02",
		"2014/05/02 12:01:02",
		"2014/05/06 12:01:02",
		"2014/05/09 12:01:02",
		"2014/05/13 12:01:02",
		"2014/05/16 12:01:02",
		"2014/05/20 12:01:02",
		"2014/05/23 12:01:02",
		"2014/05/27 12:01:02",
		"2014/05/30 12:01:02",
		"2014/06/03 12:01:02",
		"2014/06/06 12:01:02",
		"2014/06/10 12:01:02",
		"2014/06/13 12:01:02",
		"2014/06/17 12:01:02",
		"2014/06/20 12:01:02",
		"2014/06/24 12:01:02",
		"2014/06/27 12:01:02",
		"2014/07/01 12:01:02",
		"2014/07/04 12:01:02",
		"2014/07/08 12:01:02",
		"2014/07/11 12:01:02",
		"2014/07/15 12:01:02",
		"2014/07/18 12:01:02",
		"2014/07/22 12:01:02",
		"2014/07/25 12:01:02",
		"2014/07/29 12:01:02",
		"2014/08/01 12:01:02",
		"2014/08/05 12:01:02",
		"2014/08/08 12:01:02",
		"2014/08/12 12:01:02",
		"2014/08/15 12:01:02",
		"2014/08/19 12:01:02",
		"2014/08/22 12:01:02",
		"2014/08/26 12:01:02",
		"2014/08/29 12:01:02",
		"2014/09/02 12:01:02",
		"2014/09/05 12:01:02",
		"2014/09/09 12:01:02",
		"2014/09/12 12:01:02",
		"2014/09/16 12:01:02",
		"2014/09/19 12:01:02",
		"2014/09/23 12:01:02",
		"2014/09/26 12:01:02",
		"2014/09/30 12:01:02",
		"2014/10/03 12:01:02",
		"2014/10/07 12:01:02",
		"2014/10/10 12:01:02",
		"2014/10/14 12:01:02",
		"2014/10/17 12:01:02",
		"2014/10/21 12:01:02",
		"2014/10/24 12:01:02",
		"2014/10/28 12:01:02",
		"2014/10/31 12:01:02",
		"2014/11/04 12:01:02",
		"2014/11/07 12:01:02",
		"2014/11/11 12:01:02",
		"2014/11/14 12:01:02",
		"2014/11/18 12:01:02",
		"2014/11/21 12:01:02",
		"2014/11/25 12:01:02",
		"2014/11/28 12:01:02",
		"2014/12/02 12:01:02",
		"2014/12/05 12:01:02",
		"2014/12/09 12:01:02",
		"2014/12/12 12:01:02",
		"2014/12/16 12:01:02",
		"2014/12/19 12:01:02",
		"2014/12/23 12:01:02",
		"2014/12/26 12:01:02",
		"2014/12/30 12:01:02",
		"2015/01/02 12:01:02",
		"2015/01/06 12:01:02",
		"2015/01/09 12:01:02",
		"2015/01/13 12:01:02",
		"2015/01/16 12:01:02",
		"2015/01/20 12:01:02",
		"2015/01/23 12:01:02",
		"2015/01/27 12:01:02",
		"2015/01/30 12:01:02",
		"2015/02/03 12:01:02",
		"2015/02/06 12:01:02",
		"2015/02/10 12:01:02",
		"2015/02/13 12:01:02",
		"2015/02/17 12:01:02",
		"2015/02/20 12:01:02",
		"2015/02/24 12:01:02",
		"2015/02/27 12:01:02",
		"2015/03/03 12:01:02",
		"2015/03/06 12:01:02",
		"2015/03/10 12:01:02",
		"2015/03/13 12:01:02",
		"2015/03/17 12:01:02",
		"2015/03/20 12:01:02",
		"2015/03/24 12:01:02",
		"2015/03/27 12:01:02",
		"2015/03/31 12:01:02",
		"2015/04/03 12:01:02",
		"2015/04/07 12:01:02",
		"2015/04/10 12:01:02",
		"2015/04/14 12:01:02",
		"2015/04/17 12:01:02",
		"2015/04/21 12:01:02",
		"2015/04/24 12:01:02",
		"2015/04/28 12:01:02",
		"2015/05/01 12:01:02",
		"2015/05/05 12:01:02",
		"2015/05/08 12:01:02",
		"2015/05/12 12:01:02",
		"2015/05/15 12:01:02",
		"2015/05/19 12:01:02",
		"2015/05/22 12:01:02",
		"2015/05/26 12:01:02",
		"2015/05/29 12:01:02",
		"2015/06/02 12:01:02",
		"2015/06/05 12:01:02",
		"2015/06/09 12:01:02",
		"2015/06/12 12:01:02",
		"2015/06/16 12:01:02",
		"2015/06/19 12:01:02",
		"2015/06/23 12:01:02",
		"2015/06/26 12:01:02",
		"2015/06/30 12:01:02",
		"2015/07/03 12:01:02",
		"2015/07/07 12:01:02",
		"2015/07/10 12:01:02",
		"2015/07/14 12:01:02",
		"2015/07/17 12:01:02",
		"2015/07/21 12:01:02",
		"2015/07/24 12:01:02",
		"2015/07/28 12:01:02",
		"2015/07/31 12:01:02",
		"2015/08/04 12:01:02",
		"2015/08/07 12:01:02",
		"2015/08/11 12:01:02",
		"2015/08/14 12:01:02",
		"2015/08/18 12:01:02",
		"2015/08/21 12:01:02",
		"2015/08/25 12:01:02",
		"2015/08/28 12:01:02",
		"2015/09/01 12:01:02",
		"2015/09/04 12:01:02",
		"2015/09/08 12:01:02",
		"2015/09/11 12:01:02",
		"2015/09/15 12:01:02",
		"2015/09/18 12:01:02",
		"2015/09/22 12:01:02",
		"2015/09/25 12:01:02",
		"2015/09/29 12:01:02",
		"2015/10/02 12:01:02",
		"2015/10/06 12:01:02",
		"2015/10/09 12:01:02",
		"2015/10/13 12:01:02",
		"2015/10/16 12:01:02",
		"2015/10/20 12:01:02",
		"2015/10/23 12:01:02",
		"2015/10/27 12:01:02",
		"2015/10/30 12:01:02",
		"2015/11/03 12:01:02",
		"2015/11/06 12:01:02",
		"2015/11/10 12:01:02",
		"2015/11/13 12:01:02",
		"2015/11/17 12:01:02",
		"2015/11/20 12:01:02",
		"2015/11/24 12:01:02",
		"2015/11/27 12:01:02",
		"2015/12/01 12:01:02",
		"2015/12/04 12:01:02",
		"2015/12/08 12:01:02",
		"2015/12/11 12:01:02",
		"2015/12/15 12:01:02",
		"2015/12/18 12:01:02",
		"2015/12/22 12:01:02",
		"2015/12/25 12:01:02",
		"2015/12/29 12:01:02",
		"2016/01/01 12:01:02",
		]);
}


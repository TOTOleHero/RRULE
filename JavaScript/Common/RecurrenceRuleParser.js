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

var handlers =
{
	"INTERVAL"		: _handle_int,
	"COUNT"			: _handle_int,
	"BYSETPOS"		: _handle_int_list,
	"BYMONTH"		: _handle_int_list,
	"BYMONTHDAY"	: _handle_int_list,
	"BYYEARDAY"		: _handle_int_list,
	"BYEASTER"		: _handle_int_list,
	"BYWEEKNO"		: _handle_int_list,
	"BYHOUR"			: _handle_int_list,
	"BYMINUTE"		: _handle_int_list,
	"BYSECOND"		: _handle_int_list,
	"FREQ"			: _handle_FREQ,
	"UNTIL"			: _handle_UNTIL,
	"WKST"			: _handle_WKST,
	"BYDAY"			: _handle_BYWEEKDAY,
	"BYWEEKDAY"		: _handle_BYWEEKDAY
};


// ===================================================================
//
//	Implementation.
//
// ===================================================================

function RecurrenceRuleParser_ParseRecurrenceRule(line, ignoretz, tzinfo)
{
	var name;
	var value;
	if (strpos(line, ':') !== FALSE)
	{
		var temp = line.split(':', 2);
		name = temp[0];
		value = temp[1];
		if (name != "RRULE")
		{
			Log_WriteError("unknown parameter (expected RRULE): " + name);
			return null;
		}
	}
	else
	{
		value = line;
	}
	
	var rrkwargs = {};
	var array = value.split(';');
	for (var i = 0; i < array.length; i++)
	{
		var pair = array[i];
		var temp = pair.split('=', 2);
		name = strtoupper(temp[0]);
		value = strtoupper(temp[1]);
		var handler = handlers[name];
		if (handler == null)
		{
			Log_WriteError("unknown parameter for name/value pair: " + pair);
		}
		else
		{
			handler(rrkwargs, name, value, ignoretz, tzinfo);
		}
	}
	
	return rrkwargs;
}

function _handle_int(rrkwargs, name, value, ignoretz, tzinfo)
{
	var temp = parseInt(value);
	if (isNaN(temp))
	{
		temp = null;
		Log_WriteError("not a legal integer for " + name + ": " + value);
	}
	rrkwargs[strtolower(name)] = temp;
}

function _handle_int_list(rrkwargs, name, value, ignoretz, tzinfo)
{
	var values = value.split(',');
	var result = [];
	for (var i in values)
	{
		var value = parseInt(values[i]);
		if (isNaN(value))
			Log_WriteError("not a legal integer for " + name + ": " + values[i]);
		else
			result.push(value);
	}
	rrkwargs[strtolower(name)] = result;
}

function _handle_FREQ(rrkwargs, name, value, ignoretz, tzinfo)
{
	rrkwargs["freq"] = RecurrenceRuleConstants_FrequencyMap[value];
}

function _handle_UNTIL(rrkwargs, name, value, ignoretz, tzinfo)
{
	var date = DateAndTime_FromString(value, tzinfo);	//, ignoretz, tzinfo)
	if (date == null)
	{
		Log_writeError("invalid until date: " + value);
	}
	else
	{
		rrkwargs["until"] = date;
	}
}

function _handle_WKST(rrkwargs, name, value, ignoretz, tzinfo)
{
	rrkwargs["wkst"] = RecurrenceRuleConstants_WeekdayMap[value];
}

function _handle_BYWEEKDAY(rrkwargs, name, value, ignoretz, tzinfo)
{
	var values = [];
	var array = value.split(',');
	for (var j = 0; j < array.length; j++)
	{
		var wday = array[j];

		// each day value (MO, TU, etc.) may be preceded by a positive or negative integer
		var i;
		for (i = 0; i < wday.length && Utilities_StringContains("+-0123456789", substr(wday, i, 1)); i++)
		{
		}
		var n;
		var w;
		if (i > 0)
		{
			n = parseInt(substr(wday, 0, i));
			w = substr(wday, i);
		}
		else
		{
			n = null;
			w = wday;
		}
		var temp =
		{
			"weekday"	: RecurrenceRuleConstants_WeekdayMap[w],
			"n"			: n
		};
		values.push(temp);
	}
	rrkwargs["byweekday"] = values;
}

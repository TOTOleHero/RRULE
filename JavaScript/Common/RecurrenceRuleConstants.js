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

var RecurrenceRuleConstants_NO_RECURRENCE = 0;
var RecurrenceRuleConstants_YEARLY = 1;
var RecurrenceRuleConstants_MONTHLY = 2;
var RecurrenceRuleConstants_WEEKLY = 3;
var RecurrenceRuleConstants_DAILY = 4;
var RecurrenceRuleConstants_HOURLY = 5;
var RecurrenceRuleConstants_MINUTELY = 6;
var RecurrenceRuleConstants_SECONDLY = 7;
var RecurrenceRuleConstants_Frequency = ["NONE","YEARLY","MONTHLY","WEEKLY","DAILY","HOURLY","MINUTELY","SECONDLY"];
//var RecurrenceRuleConstants_FrequencyValueDisplay = [NO_RECURRENCE,"No Recurrence",YEARLY,"Yearly",MONTHLY,"Monthly",WEEKLY,"Weekly",DAILY,"Daily",HOURLY,"Hourly",MINUTELY,"Minutely",SECONDLY,"Secondly"];
var RecurrenceRuleConstants_FrequencyMap =
{
	"NONE"		: RecurrenceRuleConstants_NO_RECURRENCE,
	"YEARLY"		: RecurrenceRuleConstants_YEARLY,
	"MONTHLY"	: RecurrenceRuleConstants_MONTHLY,
	"WEEKLY"		: RecurrenceRuleConstants_WEEKLY,
	"DAILY"		: RecurrenceRuleConstants_DAILY,
	"HOURLY"		: RecurrenceRuleConstants_HOURLY,
	"MINUTELY"	: RecurrenceRuleConstants_MINUTELY,
	"SECONDLY"	: RecurrenceRuleConstants_SECONDLY
};

var RecurrenceRuleConstants_Weekdays = ["MO", "TU", "WE", "TH", "FR", "SA", "SU"];
var RecurrenceRuleConstants_WeekdayValueDisplay = ["MO","Monday","TU","Tuesday","WE","Wednesday","TH","Thursday","FR","Friday","SA","Saturday","SU","Sunday"];
var RecurrenceRuleConstants_WeekdayMap =
{
	"MO" : 0,
	"TU" : 1,
	"WE" : 2,
	"TH" : 3,
	"FR" : 4,
	"SA" : 5,
	"SU" : 6
};

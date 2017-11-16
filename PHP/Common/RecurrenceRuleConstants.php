<?php

// ========================================================================
//        Copyright (c) 2010 Dominique Lacerte, All Rights Reserved.
// 
// Redistribution and use in source and binary forms are prohibited without 
//   prior written consent from Dominique Lacerte (internet@lacerte.org).
// ========================================================================

class RecurrenceRuleConstants
{	
	// ===================================================================
	//
	//	Constants.
	//
	// ===================================================================
	
	public static $NO_RECURRENCE = 0;
	public static $YEARLY = 1;
	public static $MONTHLY = 2;
	public static $WEEKLY = 3;
	public static $DAILY = 4;
	public static $HOURLY = 5;
	public static $MINUTELY = 6;
	public static $SECONDLY = 7;
	public static $Frequency = array("NONE","YEARLY","MONTHLY","WEEKLY","DAILY","HOURLY","MINUTELY","SECONDLY");
	public static $FrequencyValueDisplay = array(0,"No Recurrence",1,"Yearly",2,"Monthly",3,"Weekly",4,"Daily",5,"Hourly",6,"Minutely",7,"Secondly");
	public static $FrequencyMap =
	array(
		"NONE"		=> 0,
		"YEARLY"	=> 1,
		"MONTHLY"	=> 2,
		"WEEKLY"	=> 3,
		"DAILY"		=> 4,
		"HOURLY"	=> 5,
		"MINUTELY"	=> 6,
		"SECONDLY"	=> 7
	);
	
	public static $Weekdays = array( "MO", "TU", "WE", "TH", "FR", "SA", "SU" );
	public static $WeekdayValueDisplay = array("MO","Monday","TU","Tuesday","WE","Wednesday","TH","Thursday","FR","Friday","SA","Saturday","SU","Sunday");
	public static $WeekdayMap =
	array(
		"MO" => 0,
		"TU" => 1,
		"WE" => 2,
		"TH" => 3,
		"FR" => 4,
		"SA" => 5,
		"SU" => 6
	);
}

?>

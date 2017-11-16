// ========================================================================
//        Copyright � 2017 Dominique Lacerte, All Rights Reserved.
//
// Redistribution and use in source and binary forms are prohibited without
//   prior written consent from Dominique Lacerte (internet@lacerte.org).
// ========================================================================

// Convert a single INPUT element into the necessary HTML elements allowing
// the user to edit an RRULE contained in the INPUT element.
//
// Usage:
//
//	<INPUT class='RecurrenceEditor' type='text' StartDate='Start' name='Repeats' value='FREQ=WEEKLY;INTERVAL=2;COUNT=8;WKST=SU;BYDAY=TU,TH'>
//  StartDate is the name of a form field that contains te start date for the event.
//
// This module depends on: Utilities.js, Visibility.js, DateAndTime.js, DateAndTimeChooser.js



RecurrenceEditor =
{
	Init: function()
	{
      var elems = Utilities_GetElementsByTag('input');
      // using forEach below didn't work on Firefox because we're changing the select elements I believe?
		for (var i = 0; i < elems.length; i++)
		{
			if (Class_HasByElement(elems[i], 'RecurrenceEditor'))
			{
				RecurrenceEditor.MakeRecurrenceEditor(elems[i]);
			}
		}
	},

	Editors: {},

	UntilDateFormat: DateAndTime_LongDateFormat,

	FreqOptions: {
		"NONE" : {
			"label": "Does not repeat",
			"hide" : ["_INTERVAL_C", "_BYDAY_C", "_REPEATBY_C", "_ENDS_C", "_SUMM_C"],
			"show" : []
		},
		"DAILY" : {
			"label": "Daily",
			"summary" : "Daily",
			"RRULE": "DAILY",
			"units": "days",
			"hide" : ["_BYDAY_C", "_REPEATBY_C"],
			"show" : ["_INTERVAL_C", "_ENDS_C", "_SUMM_C"]
		},
		"WEEKLY1" : {
			"label": "Every weekday (Monday to Friday)",
			"summary" : "Weekly on weekdays",
			"RRULE": "WEEKLY",
			"byday": "MO,TU,WE,TH,FR",
			"hide" : ["_INTERVAL_C", "_BYDAY_C", "_REPEATBY_C"],
			"show" : ["_ENDS_C", "_SUMM_C"]
		},
		"WEEKLY2" : {
			"label": "Every Monday, Wednesday and Friday",
			"summary" : "Weekly on Monday, Wednesday, Friday",
			"RRULE": "WEEKLY",
			"byday": "MO,WE,FR",
			"hide" : ["_INTERVAL_C", "_BYDAY_C", "_REPEATBY_C"],
			"show" : ["_ENDS_C", "_SUMM_C"]
		},
		"WEEKLY3" : {
			"label": "Every Tuesday and Thursday",
			"summary" : "Weekly on Tuesday, Thursday",
			"RRULE": "WEEKLY",
			"byday": "TU,TH",
			"hide" : ["_INTERVAL_C", "_BYDAY_C", "_REPEATBY_C"],
			"show" : ["_ENDS_C", "_SUMM_C"]
		},
		"WEEKLY" : {
			"label": "Weekly",
			"summary" : "Weekly",
			"RRULE": "WEEKLY",
			"units": "weeks",
			"hide" : ["_REPEATBY_C"],
			"show" : ["_INTERVAL_C", "_BYDAY_C", "_ENDS_C", "_SUMM_C"]
		},
		"MONTHLY" : {
			"label": "Monthly",
			"summary" : "Monthly",
			"RRULE": "MONTHLY",
			"units": "months",
			"hide" : ["_BYDAY_C"],
			"show" : ["_INTERVAL_C", "_REPEATBY_C", "_ENDS_C", "_SUMM_C"]
		},
		"YEARLY" : {
			"label": "Yearly",
			"summary" : "Annually",
			"RRULE": "YEARLY",
			"units": "years",
			"hide" : ["_BYDAY_C", "_REPEATBY_C"],
			"show" : ["_INTERVAL_C", "_ENDS_C", "_SUMM_C"]
		}
	},

	WeekdayOptions: {
		"SU" : {
			"label" : "Sunday",
			"short_label" : "S"
		},
		"MO" : {
			"label" : "Monday",
			"short_label" : "M"
		},
		"TU" : {
			"label" : "Tuesday",
			"short_label" : "T"
		},
		"WE" : {
			"label" : "Wednesday",
			"short_label" : "W"
		},
		"TH" : {
			"label" : "Thursday",
			"short_label" : "T"
		},
		"FR" : {
			"label" : "Friday",
			"short_label" : "F"
		},
		"SA" : {
			"label" : "Saturday",
			"short_label" : "S"
		}
	},

	Days: ["SU", "MO", "TU", "WE", "TH", "FR", "SA"],

	MakeRadioSpan: function(base_name, id, value, labelElement, block)
	{
		var span = document.createElement("SPAN");
		var radio = document.createElement("INPUT");
		radio.name = base_name + id;
		radio.id = base_name + id + value;
		radio.type = "radio";
		radio.value = value;
		radio.onclick = RecurrenceEditor.RefreshRule;
		radio.setAttribute("RecurrenceEditorBase", base_name);
		span.appendChild(radio);
		var label = document.createElement("LABEL");
		label.setAttribute("for", radio.id);
		label.appendChild(labelElement);
		if (block)
		{
			span.style.display = "block";
		}
		span.appendChild(label);

		return span;
	},

	CopyContainer: function(original, labelText, id)
	{
		var tr = original.cloneNode(true);
		if (id)
		{
			tr.id = id;
		}
		var labelTd = tr.firstChild;
		labelTd.innerHTML = labelText;
		var contentTd = labelTd.nextElementSibling;
		contentTd.innerHTML = "";
		Utilities_InsertAfterNode(original.parentNode, tr, original);
		return contentTd;
	},

	OnChooseFrequency: function()
	{
		var freq = this.options[this.selectedIndex].value;
		var base_name = this.name.replace("_FREQ", "");
		var opts = RecurrenceEditor.FreqOptions[freq];
		opts["hide"].forEach(function(hide){
			var element = document.getElementById(base_name + hide);
			Visibility_HideByElement(element);
		});
		opts["show"].forEach(function(show){
			var element = document.getElementById(base_name + show);
			Visibility_ShowByElement(element);
		});

		var span = document.getElementById(base_name + "_INTERVAL_UNITS");
		span.innerHTML = " " + opts["units"];

		RecurrenceEditor.RefreshRule.call(this);
	},

	RefreshRule: function()
	{
		var	base_name = this.getAttribute("RecurrenceEditorBase");
		var base_element = RecurrenceEditor.Editors[base_name];

		var start_name = base_element.getAttribute("StartDate");
		var els = document.getElementsByName(start_name);
		var start_date = null;
		if (els.length > 0)
		{
			start_date = DateAndTime_FromString(els[0].value);
		}
		else
		{
			start_date = new DateAndTime();
		}

		var RRULE = "";
		var select = document.getElementById(base_name + "_FREQ");
		var freq = select.options[select.selectedIndex].value;

		if (freq != "NONE")
		{
			var opts = RecurrenceEditor.FreqOptions[freq];
			RRULE = RRULE + "FREQ=" + opts["RRULE"] + ";";
			var freqStr = opts["summary"];

			if (opts["show"].indexOf("_INTERVAL_C") >= 0)
			{
				var select = document.getElementById(base_name + "_INTERVAL");
				var interval = select.options[select.selectedIndex].value;
				interval = parseInt(interval);
				if (interval > 1)
				{
					RRULE = RRULE + "INTERVAL=" + interval + ";";
					freqStr = "Every " + interval + " " + opts["units"];
				}
			}

			RRULE = RRULE + "WKST=SU;";

			if (opts["show"].indexOf("_BYDAY_C") >= 0)
			{
				var byday = [];
				var bydayname = [];
				for (var weekday in RecurrenceEditor.WeekdayOptions)
				{
					var short_label = RecurrenceEditor.WeekdayOptions[weekday]["short_label"];
					var checkbox = document.getElementById(base_name + "_BYDAY_" + weekday);
					if (checkbox.checked)
					{
						byday.push(weekday);
						bydayname.push(RecurrenceEditor.WeekdayOptions[weekday]["label"]);
					}
				}
				if (byday.length > 0)
				{
					RRULE = RRULE + "BYDAY=" + byday.join(",") + ";";
					freqStr = freqStr + " on " + bydayname.join(", ");
				}
				else
				{
					// default the summary string to the day of the start date
					var weekday = RecurrenceEditor.Days[start_date.ToNative().getDay()];
					freqStr = freqStr + " on " + RecurrenceEditor.WeekdayOptions[weekday]["label"];
				}
			}
			else
			{
				if (opts["byday"])
				{
					RRULE = RRULE + "BYDAY=" + opts["byday"] + ";";
				}
			}

			if (opts["show"].indexOf("_REPEATBY_C") >= 0)
			{
				var radio = document.getElementById(base_name + "_REPEATBYDOM");
				if (radio.checked)
				{
					RRULE = RRULE + "BYMONTHDAY=" + start_date.ToNative().getDate() + ";";
					freqStr = freqStr + " on day " + start_date.ToNative().getDate();
				}
				var radio = document.getElementById(base_name + "_REPEATBYDOW");
				if (radio.checked)
				{
					var dayStr = RecurrenceEditor.Days[start_date.ToNative().getDay()];
					var nth = Math.ceil(start_date.ToNative().getDate()/7);
					RRULE = RRULE + "BYDAY=" + nth + dayStr + ";";

					var nthStr = ["first", "second", "third", "fourth", "last"];
					freqStr = freqStr + " on the " + nthStr[nth-1] + " " + RecurrenceEditor.WeekdayOptions[dayStr]["label"];
				}
			}

			if (opts["show"].indexOf("_ENDS_C") >= 0)
			{
				var radio = document.getElementById(base_name + "_ENDSAFTER");
				if (radio.checked)
				{
					var count = document.getElementById(base_name + "_COUNT");
					if (!count.value)
					{
						count.value = 1;
					}
					RRULE = RRULE + "COUNT=" + count.value + ";";
					freqStr = freqStr + ", " + count.value + " times";
				}
				var radio = document.getElementById(base_name + "_ENDSUNTIL");
				if (radio.checked)
				{
					var until = document.getElementById(base_name + "_UNTIL");
					var untildt = DateAndTime_FromElement(until);
					// until date has no time component
					RRULE = RRULE + "UNTIL=" + untildt.ToFormat("%D") + "T000000Z;";
					freqStr = freqStr + ", until " + untildt.ToFormat(RecurrenceEditor.UntilDateFormat);
				}
			}
			// update the summary
			var summ = document.getElementById(base_name + "_SUMM");
			summ.innerHTML = freqStr;
		}
		// remove the trailing ;
		if (RRULE.charAt(RRULE.length - 1) == ';') {
  		RRULE = RRULE.substr(0, RRULE.length - 1);
		}
		base_element.value = RRULE;

		// don't fire the change event on init
		if (base_element.initialized) {
			Utilities_FireEvent(base_element, 'change');
		}


	},

	LoadFromRule: function(base_name)
	{
		var rule = RecurrenceEditor.Editors[base_name].value;
		if (!rule.isEmpty())
		{
			try {
				var ruleObj = new Object();
				rule.split(";").forEach(function(subrule) {
					ruleObj[subrule.split("=")[0]] = subrule.split("=")[1];
				});

				// find the matching freq option
				var maxScore = 0;
				var matchingKey = null;
				for (var key in RecurrenceEditor.FreqOptions)
				{
					var score = 0;
					var opt = RecurrenceEditor.FreqOptions[key];
					if (opt["RRULE"] == ruleObj["FREQ"])
					{
						if (opt["byday"] && opt["byday"] == ruleObj["BYDAY"])
						{
							// if matches both FREQ and BYDAY, higher priority
							score = 2;
						}
						if (!opt["byday"])
						{
							score = 1;
						}
						if (score > maxScore)
						{
							maxScore = score;
							matchingKey = key;
						}
					}
				}
				if (matchingKey != null)
				{
					var select = document.getElementById(base_name + "_FREQ");
					select.value = matchingKey;
				}
				var select = document.getElementById(base_name + "_INTERVAL");
				if (parseInt(ruleObj["INTERVAL"]) > 0)
				{
					select.value = parseInt(ruleObj["INTERVAL"]);
				}
				if (ruleObj["UNTIL"])
				{
					var radio = document.getElementById(base_name + "_ENDSUNTIL");
					radio.checked = true;
					var untildt = document.getElementById(base_name + "_UNTIL");
					var dt = new DateAndTime(ruleObj["UNTIL"].substring(0,4), ruleObj["UNTIL"].substring(4,6), ruleObj["UNTIL"].substring(6,8),
						0,0,0,0);
					untildt.value = dt.ToFormat(RecurrenceEditor.UntilDateFormat);
				}
				if (ruleObj["COUNT"])
				{
					var radio = document.getElementById(base_name + "_ENDSAFTER");
					radio.checked = true;
					document.getElementById(base_name + "_COUNT").value = ruleObj["COUNT"];
				}
				if (ruleObj["FREQ"] == "MONTHLY")
				{
					if (ruleObj["BYMONTHDAY"])
					{
						document.getElementById(base_name + "_REPEATBYDOM").checked = true;
					}
					if (ruleObj["BYDAY"])
					{
						document.getElementById(base_name + "_REPEATBYDOW").checked = true;
					}
				}
				if (ruleObj["FREQ"] == "WEEKLY" && ruleObj["BYDAY"])
				{
					ruleObj["BYDAY"].split(",").forEach(function(day) {
						document.getElementById(base_name + "_BYDAY_" + day).checked = true;
					});
				}
			} catch (e) {
				alert("Error loading values from rule!");
				return;
			}
		}
	},

	MakeRecurrenceEditor: function(element)
	{
		Visibility_HideByElement(element);
		var rowElement = Utilities_GetParentByTag(element, "TR");
		if (rowElement == null)
		{
			alert("RecurrenceEditor should be inside a TR");
			return;
		}

		var base_name = element.name;
		element.initialized = false;

		// Repeats
		var fselect = document.createElement("SELECT");
		fselect.id = base_name + "_FREQ";
		fselect.name = base_name + "_FREQ";
		for (var value in RecurrenceEditor.FreqOptions)
		{
			var label = RecurrenceEditor.FreqOptions[value]["label"];
			var opt = document.createElement("OPTION");
			opt.value = value;
			opt.appendChild(document.createTextNode(label));
			fselect.appendChild(opt);
		}
		fselect.onchange = RecurrenceEditor.OnChooseFrequency;
		fselect.setAttribute("RecurrenceEditorBase", base_name);
		Utilities_InsertBeforeNode(element.parentNode, fselect, element);

		var start_name = element.getAttribute("StartDate");
		var els = document.getElementsByName(start_name);
		if (els.length > 0)
		{
			// need to refresh the RRULE when the start date changes
			// for certain cases
			Utilities_AddEvent(els[0], "change", function(e) {
				// we invoke using the select, so that the instance can be found correctly
				RecurrenceEditor.RefreshRule.call(select);
			});
		}

		// all succeeding rows are added in reverse order so that we can just keep inserting after the first row

		// summary
		var contentTd = RecurrenceEditor.CopyContainer(rowElement, "Repeat Summary", base_name + "_SUMM_C");
		var span = document.createElement("SPAN");
		span.id = base_name + "_SUMM";
		contentTd.appendChild(span);

		// Ends
		var contentTd = RecurrenceEditor.CopyContainer(rowElement, "Ends", base_name + "_ENDS_C");
		contentTd.appendChild(
			RecurrenceEditor.MakeRadioSpan(base_name, "_ENDS", "NEVER",
				document.createTextNode("Never"), true)
		);
		document.getElementById(base_name + "_ENDSNEVER").checked = true;

		var span = document.createElement("SPAN");
		span.appendChild(document.createTextNode("After "));
		var input = document.createElement("INPUT");
		input.size = "3";
		input.name = base_name + "_COUNT";
		input.id = base_name + "_COUNT";
		input.value = 1;
		input.onchange = RecurrenceEditor.RefreshRule;
		input.onclick = function() {
			document.getElementById(base_name + "_ENDSAFTER").checked = true;
		}
		input.setAttribute("RecurrenceEditorBase", base_name);
		span.appendChild(input);
		span.appendChild(document.createTextNode(" occurrences"));
		contentTd.appendChild(
			RecurrenceEditor.MakeRadioSpan(base_name, "_ENDS", "AFTER",
				span, true)
		);

		var span = document.createElement("SPAN");
		span.appendChild(document.createTextNode("On "));
		var dinput = document.createElement("INPUT");
		dinput.className = 'datechooser timechooser showinline';
		dinput.type = 'text';
		dinput.setAttribute("format", RecurrenceEditor.UntilDateFormat);
		dinput.name = base_name + "_UNTIL";
		dinput.id = base_name + "_UNTIL";
		//dinput.onchange = RecurrenceEditor.RefreshRule;
		Utilities_AddEvent(dinput, "change", function(e) {
			// we invoke using the select, so that the instance can be found correctly
			RecurrenceEditor.RefreshRule.call(select);
		});
		dinput.setAttribute("RecurrenceEditorBase", base_name);

		// default to today's date
		var date = new Date();
		var dt = new DateAndTime(date.getUTCFullYear(), date.getUTCMonth()+1, date.getUTCDate(),
			0, 0, 0, 0);
		dinput.value = dt.ToFormat(RecurrenceEditor.UntilDateFormat);

		span.appendChild(dinput);
		contentTd.appendChild(
			RecurrenceEditor.MakeRadioSpan(base_name, "_ENDS", "UNTIL",
				span, true)
		);

		// Repeat by (monthly options)
		var contentTd = RecurrenceEditor.CopyContainer(rowElement, "Repeat by", base_name + "_REPEATBY_C");
		contentTd.appendChild(
			RecurrenceEditor.MakeRadioSpan(base_name, "_REPEATBY", "DOM",
				document.createTextNode("day of the month"))
		);
		contentTd.appendChild(
			RecurrenceEditor.MakeRadioSpan(base_name, "_REPEATBY", "DOW",
				document.createTextNode("day of the week"))
		);
		// default value
		document.getElementById(base_name + "_REPEATBYDOM").checked = true;

		// Repeats on (weekdays)
		var contentTd = RecurrenceEditor.CopyContainer(rowElement, "Repeat on", base_name + "_BYDAY_C");
		for (var weekday in RecurrenceEditor.WeekdayOptions)
		{
			// <span>S<input id="S1" type="checkbox"></input></span>
			var short_label = RecurrenceEditor.WeekdayOptions[weekday]["short_label"];
			var span = document.createElement("SPAN");
			var checkbox = document.createElement("INPUT");
			checkbox.id = base_name + "_BYDAY_" + weekday;
			checkbox.name = base_name + "_BYDAY_" + weekday;
			checkbox.type = "checkbox";
			checkbox.onchange = RecurrenceEditor.RefreshRule;
			checkbox.setAttribute("RecurrenceEditorBase", base_name);
			span.appendChild(checkbox);
			span.appendChild(document.createTextNode(short_label));
			contentTd.appendChild(span);
		}

		// Repeats every
		var contentTd = RecurrenceEditor.CopyContainer(rowElement, "Repeat Every", base_name + "_INTERVAL_C");
		var select = document.createElement("SELECT");
		select.id = base_name + "_INTERVAL";
		select.name = base_name + "_INTERVAL";
		for (var i=1; i<=30; i++)
		{
			var opt = document.createElement("OPTION");
			opt.value = i;
			opt.appendChild(document.createTextNode(i));
			select.appendChild(opt);
		}
		select.onchange = RecurrenceEditor.RefreshRule;
		select.setAttribute("RecurrenceEditorBase", base_name);
		// default value
		select.value = 1;
		contentTd.appendChild(select);
		var span = document.createElement("SPAN");
		span.id = base_name + "_INTERVAL_UNITS";
		contentTd.appendChild(span);

		// store a reference to the base element
		RecurrenceEditor.Editors[base_name] = element;

		RecurrenceEditor.LoadFromRule(base_name);
		DateAndTimeChooser.MakeDateAndTimeChooser(dinput, true, false);
		RecurrenceEditor.OnChooseFrequency.call(fselect);
		element.initialized = true;
	}

}

DocumentLoad.AddCallback(RecurrenceEditor.Init);

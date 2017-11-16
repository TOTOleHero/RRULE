// ========================================================================
//        Copyright � 2012 Dominique Lacerte, All Rights Reserved.
// 
// Redistribution and use in source and binary forms are prohibited without 
//   prior written consent from Dominique Lacerte (internet@lacerte.org).
// ========================================================================

// Allows an input control to display a calendar for choosing a date and time.
// Note that either can be selected independently by leaving out the other class.
// The time may be specified by Epoch or by string, use string when allowing null!
//
// <input class="datechooser timechooser" type="text" name="Date" value="1352101450"/>
//
// classes:
//
// datechooser 
// timechooser
// datenullable
// timenullable
// nullable
//
// Attributes include those for DateAndTime_FromElement as well as:
//
//	format:			the display format (default is 2006/03/04 11:00am)

// put our CSS into the document
document.write("<style type=\"text/css\">" +
".calendar" +
"{" +
"	font-family: 'Trebuchet MS', Tahoma, Verdana, Arial, sans-serif;" +
"	font-size: 0.9em;" +
"	background-color: #EEE;" +
"	color: #333;" +
"	border: 1px solid #DDD;" +
"	-moz-border-radius: 4px;" +
"	-webkit-border-radius: 4px;" +
"	border-radius: 4px;" +
"	padding: 0.2em;" +
"	width: 14em;" +
"}" +
".calendar a" +
"{" +
"	text-decoration: none;" +
"	outline: none;" +
"}" +
".calendar_enabler, .calendar_timerow" +
"{" +
"	padding: 0.5em;" +
"}" +
".calendar .months, .calendar_ok" +
"{" +
"	background-color: #F6AF3A;" +
"	border: 1px solid #E78F08;" +
"	-moz-border-radius: 4px;" +
"	-webkit-border-radius: 4px;" +
"	border-radius: 4px;" +
"	color: #FFF;" +
"	padding: 0.2em;" +
"	text-align: center;" +
"}" +
".calendar .prev-month," +
".calendar .next-month" +
"{" +
"	padding: 0;" +
"}" +
".calendar .prev-month" +
"{" +
"	float: left;" +
"}" +
".calendar .next-month" +
"{" +
"	float: right;" +
"}" +
".calendar .current-month" +
"{" +
"	margin: 0 auto;" +
"}" +
".calendar .months a" +
"{" +
"	color: #FFF;" +
"	padding: 0 0.4em;" +
"	-moz-border-radius: 4px;" +
"	-webkit-border-radius: 4px;" +
"	border-radius: 4px;" +
"}" +
".calendar .months a:hover" +
"{" +
"	background-color: #FDF5CE;" +
"	color: #C77405;" +
"}" +
".calendar table" +
"{" +
"	border-collapse: collapse;" +
"	padding: 0;" +
"	font-size: 0.8em;" +
"	width: 100%;" +
"}" +
".calendar th" +
"{" +
"	text-align: center;" +
"}" +
".calendar td" +
"{" +
"	text-align: right;" +
"	padding: 1px;" +
"	width: 14.3%;" +
"}" +
".calendar td a" +
"{" +
"	display: block;" +
"	color: #1C94C4;" +
"	background-color: #F6F6F6;" +
"	border: 1px solid #CCC;" +
"	text-decoration: none;" +
"	padding: 0.2em;" +
"}" +
".calendar td a:hover" +
"{" +
"	color: #C77405;" +
"	background-color: #FDF5CE;" +
"	border: 1px solid #FBCB09;" +
"}" +
".calendar td.selected a" +
"{" +
"	background-color: #FFF0A5;" +
"	border: 1px solid #FED22F;" +
"	color: #363636;" +
"}" +
".calendar td.today a" +
"{" +
"	color: #C77405;" +
"	background-color: #FDF5CE;" +
"	border: 1px solid #FBCB09;" +
"}" +
"</style>");


DateAndTimeChooser =
{
	// constants used throughout the class
	DaysInMonth: [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31],
	Hours: [' 1',' 2',' 3',' 4',' 5',' 6',' 7',' 8',' 9','10','11','12'],
	Minutes: [
		'00','01','02','03','04','05','06','07','08','09',
		'10','11','12','13','14','15','16','17','18','19',
		'20','21','22','23','24','25','26','27','28','29',
		'30','31','32','33','34','35','36','37','38','39',
		'40','41','42','43','44','45','46','47','48','49',
		'50','51','52','53','54','55','56','57','58','59'
		],
   Years: [],
	AmPm: ['am','pm'],
	FullCurrentMonth: true,
   
	// shortcuts to get date info
	GetYear: function(date)
   {
		return date.HasDate() ? date.Year() : DateAndTime_Now().Year();
	},
	GetMonth:
   {
		integer: function(date)
      {
			return (date.HasDate() ? date.Month() : DateAndTime_Now().Month()) - 1;
		},
		string: function(date, full)
      {
			return DateAndTime_GetMonthString(DateAndTimeChooser.GetMonth.integer(date), full);
		}
	},
	GetDay: function(date)
   {
		return date.HasDate() ? date.Day() : DateAndTime_Now().Day();
	},
	GetHour: function(date)
   {
		hours = date.HasTime() ? date.Hour() : 0;
		if (hours >= 12) hours -= 12;
		return hours;
	},
	GetMinute: function(date)
   {
		return date.HasTime() ? date.Minute() : 0;
	},
	GetAmPm: 
   {
		integer: function(date)
      {
			hours = date.HasTime() ? date.Hour() : 0;
			return hours < 12 ? 0 : 1;
		},
		string: function(date)
      {
			return DateAndTimeChooser.GetAmPm.integer(date) ? 'pm' : 'am';
		}
	},
	
   GetDisplayedMonth:
   {
		integer: function(currentMonthView)
      {
			return currentMonthView;
		},
		string: function(currentMonthView)
      {
			var date = currentMonthView+1;
			return DateAndTime_GetMonthString(date, DateAndTimeChooser.FullCurrentMonth);
		},
		numDays: function(currentMonthView, currentYearView)
      {
			// checks to see if february is a leap year otherwise return the respective # of days
			return (DateAndTimeChooser.GetDisplayedMonth.integer(currentMonthView) == 1 && !(currentYearView & 3) && (currentYearView % 1e2 || !(currentYearView % 4e2))) ? 29 : DateAndTimeChooser.DaysInMonth[DateAndTimeChooser.GetDisplayedMonth.integer(currentMonthView)];
		}
	},

   GetDisplayFormat: function(dataElement)
   {
		var format = dataElement.getAttribute('format');
		if (empty(format)) format = "%/D %l:%M%P";
      return format;
   },
   
   GetDateNullable: function(dataElement)
   {
   	return Class_HasByElement(dataElement, 'datenullable');
   },
   
   GetTimeNullable: function(dataElement)
   {
   	return Class_HasByElement(dataElement, 'timenullable');
   },
   
   GetNullable: function(dataElement)
   {
   	return Class_HasByElement(dataElement, 'nullable');
   },
   
	Init: function()
	{
		var elems = Utilities_GetElementsByTag('input');
      for (var i = 0; i < elems.length; i++)
		{
         var elem = elems[i];
			if (Class_HasByElement(elem, 'datechooser') || Class_HasByElement(elem, 'timechooser'))
			{
            //do not make multi select if its a template
            if (!Utilities_HasClassAsParent(elem, 'MultiItemTemplate'))
   				DateAndTimeChooser.MakeDateAndTimeChooser(elem);
			}
		}
	},
	
	GetHours: function(dataElementId)
   {
      var hoursElement = DateAndTimeChooser.GetHoursElement(dataElementId);
      var ampmElement = DateAndTimeChooser.GetAmPmElement(dataElementId);
      
		var hours = parseInt(hoursElement.value, 10);
		var temp = parseInt(ampmElement.value, 10);
		if (hours == 12) temp -= 1;
		hours += 12 * temp;
		return hours;
	},

	GetContainerElement: function(node)
   {
      while (node != null && (node.tagName != 'DIV' || !Class_HasByElement(node, 'calendar')))
      {
         node = node.parentNode;
      }
      if (node == null) Log_Die('Error finding container element!');
      return node;
	},
	
	GetDataElement: function(node)
   {
      return DateAndTimeChooser.GetContainerElement(node).previousElementSibling.previousElementSibling;
	},
	
	GetDisplayElement: function(node)
   {
      return DateAndTimeChooser.GetContainerElement(node).previousElementSibling;
	},
	
	GetElementByName: function(node, name)
   {
      var container = DateAndTimeChooser.GetContainerElement(node);
      var elem = Utilities_GetElementByName('DateAndTimeChooser_' + name, container);
      if (elem == null && name != 'AllNull' && name != 'HasDate' && name != 'HasTime' && 
         name != 'HasDateLabel' && name != 'HasTimeLabel')
      {
         Log_Die('Error finding ' + name + ' element!');
      }
      return elem;
	},
	
	GetAllNullElement: function(node)
   {
      return DateAndTimeChooser.GetElementByName(node, 'AllNull');
	},
	
	GetHasDateElement: function(node)
   {
      return DateAndTimeChooser.GetElementByName(node, 'HasDate');
	},
	
	GetHasDateLabelElement: function(node)
   {
      return DateAndTimeChooser.GetElementByName(node, 'HasDateLabel');
	},
	
	GetHasTimeElement: function(node)
   {
      return DateAndTimeChooser.GetElementByName(node, 'HasTime');
	},
	
	GetHasTimeLabelElement: function(node)
   {
      return DateAndTimeChooser.GetElementByName(node, 'HasTimeLabel');
	},
	
	GetDatePortion1Element: function(node)
   {
      return DateAndTimeChooser.GetElementByName(node, 'DatePortion1');
	},
	
	GetDatePortion2Element: function(node)
   {
      return DateAndTimeChooser.GetElementByName(node, 'DatePortion2');
	},
	
	GetTimePortionElement: function(node)
   {
      return DateAndTimeChooser.GetElementByName(node, 'TimePortion');
	},
	
	GetBodyElement: function(node)
   {
      return DateAndTimeChooser.GetElementByName(node, 'Body');
	},
	
	GetYearListElement: function(node)
   {
      return DateAndTimeChooser.GetElementByName(node, 'YearList');
	},
	
	GetMonthElement: function(node)
   {
      return DateAndTimeChooser.GetElementByName(node, 'Month');
	},
	
	GetHoursElement: function(node)
   {
      return DateAndTimeChooser.GetElementByName(node, 'Hours');
	},
	
	GetMinutesElement: function(node)
   {
      return DateAndTimeChooser.GetElementByName(node, 'Minutes');
	},
	
	GetAmPmElement: function(node)
   {
      return DateAndTimeChooser.GetElementByName(node, 'AmPm');
	},
	
	handleDoneClick: function(node)
   {
		DateAndTimeChooser.close(node);
		return false;
	},

	handleDayClick: function(node)
   {
      var dataElement = DateAndTimeChooser.GetDataElement(node)
      var hasDate = DateAndTimeChooser.GetHasDateElement(node);
      var allNull = DateAndTimeChooser.GetAllNullElement(node);
      var wantsTime = parseBool(dataElement.getAttribute('wantsTime'));
      
      if (hasDate)
         hasDate.checked = true;
      if (allNull)
         allNull.checked = false;
      
      DateAndTimeChooser.updateDataElement(node);
		
		if (!wantsTime)
			DateAndTimeChooser.close(node);
		
		// the selection has changed
		DateAndTimeChooser.rebuildCalendar(node);
	},
			
	handleTimeClick: function(node)
   {
      var hasTime = DateAndTimeChooser.GetHasTimeElement(node);
      var allNull = DateAndTimeChooser.GetAllNullElement(node);
      
      if (hasTime)
         hasTime.checked = true;
      if (allNull)
         allNull.checked = false;
      
      DateAndTimeChooser.updateDataElement(node);
	},
			
	handleDateChange: function(node)
   {
      var dataElement = DateAndTimeChooser.GetDataElement(node);
      var monthElement = DateAndTimeChooser.GetMonthElement(node);
      
      var currentYearView = parseInt(dataElement.getAttribute('currentYearView'));
      var currentMonthView = parseInt(dataElement.getAttribute('currentMonthView'));
      
		// if we go too far into the past
		if (currentMonthView < 0)
      {
			currentYearView--;
			
			// start our month count at 11 (11 = december)
			currentMonthView = 11;
		}
		
		// if we go too far into the future
		if (currentMonthView > 11)
      {
			currentYearView++;
			
			// restart our month count (0 = january)
			currentMonthView = 0;
		}
		
		monthElement.innerHTML = DateAndTimeChooser.GetDisplayedMonth.string(currentMonthView) + '&nbsp;';

      dataElement.setAttribute('currentYearView', currentYearView);
      dataElement.setAttribute('currentMonthView', currentMonthView);
		
      var yearList = DateAndTimeChooser.GetYearListElement(node);
      yearList.value = currentYearView;
      
		DateAndTimeChooser.rebuildCalendar(node);
		
		return false;
	},
	
	updateDataElement: function(node)
   {
      var dataElement = DateAndTimeChooser.GetDataElement(node);
      var displayElement = DateAndTimeChooser.GetDisplayElement(node);
      var hasDate = DateAndTimeChooser.GetHasDateElement(node);
      var hasTime = DateAndTimeChooser.GetHasTimeElement(node);
      var hasDateLabel = DateAndTimeChooser.GetHasDateLabelElement(node);
      var hasTimeLabel = DateAndTimeChooser.GetHasTimeLabelElement(node);
      var allNull = DateAndTimeChooser.GetAllNullElement(node);
      var datePortion1 = DateAndTimeChooser.GetDatePortion1Element(node);
      var datePortion2 = DateAndTimeChooser.GetDatePortion2Element(node);
      var timePortion = DateAndTimeChooser.GetTimePortionElement(node);

      var displayFormat = DateAndTimeChooser.GetDisplayFormat(dataElement);
      
      var wantsDate = parseBool(dataElement.getAttribute('wantsDate'));
      var wantsTime = parseBool(dataElement.getAttribute('wantsTime'));

      var currentYear = parseInt(dataElement.getAttribute('currentYear'));
      var currentMonth = parseInt(dataElement.getAttribute('currentMonth'));
      var currentDay = parseInt(dataElement.getAttribute('currentDay'));
      
		// the time/date might have changed so get the latest
		var selectedDate = DateAndTime_FromElement(dataElement);
      
      if (wantsDate && (!hasDate || hasDate.checked) && (!allNull || !allNull.checked))
      {
   		selectedDate.SetDate(currentYear, currentMonth+1, currentDay);
         Visibility_Show(datePortion1);
         Visibility_Show(datePortion2);
      }
      else
      {
         selectedDate.SetDate(null);
         Visibility_Hide(datePortion1);
         Visibility_Hide(datePortion2);
      }
		
		if (wantsTime && (!hasTime || hasTime.checked) && (!allNull || !allNull.checked))
		{
			var hours = DateAndTimeChooser.GetHours(node);
         var minutes = DateAndTimeChooser.GetMinutesElement(node).value;
			selectedDate.SetTime(hours, minutes, 0, 0);
         Visibility_Show(timePortion);
		}
      else
      {
         selectedDate.SetTime(null);
         Visibility_Hide(timePortion);
      }
      
      if (hasTimeLabel) Visibility_SetByElement(hasTimeLabel, !allNull || !allNull.checked);
      if (hasDateLabel) Visibility_SetByElement(hasDateLabel, !allNull || !allNull.checked);
				
		displayElement.innerHTML = selectedDate.ToFormat(displayFormat);
      if (displayElement.innerHTML.isEmpty())
         displayElement.innerHTML = '(none)';   // give the user something to click on
		
		selectedDate.ToElement(dataElement);
      Utilities_FireEvent(dataElement, 'change');
	},
	
	buildNode: function(nodeName, attributes, content)
   {
		var element;

// This didn't work in Firefox and I didn't have time to track it down.
// Error is "buildCache[nodeName].cloneNode is not a function".
//		if(!(nodeName in buildCache)) {
//			buildCache[nodeName] = document.createElement(nodeName);
//		}
//		element = buildCache[nodeName].cloneNode(false);
		element = document.createElement(nodeName);
		
		if (attributes != null)
      {
			for(var attribute in attributes)
         {
				element[attribute] = attributes[attribute];
			}
		}
		
		if (content != null)
      {
			if(typeof(content) == 'object')
         {
				element.appendChild(content);
			}
         else
         {
				element.innerHTML = content;
			}
		}
		
		return element;
	},
	
	buildSelectNode: function(values, selected)
   {
		elem = DateAndTimeChooser.buildNode('select', { size: 1 }, null);
		for (i = 0; i < values.length; i++)
		{
			val = parseInt(values[i], 10);
			if (val == selected)
				elem.appendChild(DateAndTimeChooser.buildNode('option', { value: val, selected: 1 }, values[i]));
			else
				elem.appendChild(DateAndTimeChooser.buildNode('option', { value: val }, values[i]));
		}
		return elem;
	},
	
	rebuildCalendar: function(node)
	{
      var dataElement = DateAndTimeChooser.GetDataElement(node);
      var body = DateAndTimeChooser.GetBodyElement(node);
      
		// rebuild the calendar
		while (body.hasChildNodes())
      {
			body.removeChild(body.lastChild);
		}
		body.appendChild(DateAndTimeChooser.buildCalendar(dataElement));
	},
	
	buildWeekdays: function()
   {
		var html = document.createDocumentFragment();
		// write out the names of each week day
		for(i = 0, x = DaysOfWeek.length; i < x; i++) {
			html.appendChild(DateAndTimeChooser.buildNode('th', {}, DaysOfWeek[i].substring(0, 2)));
		}
		return html;
	},
	
	buildCalendar: function(dataElement)
   {
      var currentYearView = parseInt(dataElement.getAttribute('currentYearView'));
      var currentMonthView = parseInt(dataElement.getAttribute('currentMonthView'));
      var currentYear = parseInt(dataElement.getAttribute('currentYear'));
      var currentMonth = parseInt(dataElement.getAttribute('currentMonth'));
      var currentDay = parseInt(dataElement.getAttribute('currentDay'));
      
		var html = document.createDocumentFragment();
		
		// get the first day of the month we are currently viewing
		var firstOfMonth = new Date(currentYearView, currentMonthView, 1).getDay();
		// get the total number of days in the month we are currently viewing
		var numDays = DateAndTimeChooser.GetDisplayedMonth.numDays(currentMonthView, currentYearView);
		// declare our day counter
		var dayCount = 0;
		
		var row = DateAndTimeChooser.buildNode('tr');
		
		// print out previous month's "days"
		for (i = 1; i <= firstOfMonth; i++)
      {
			row.appendChild(DateAndTimeChooser.buildNode('td', {}, '&nbsp;'));
			dayCount++;
		}
		
		for (i = 1; i <= numDays; i++)
      {
			// if we have reached the end of a week, wrap to the next line
			if (dayCount == 7)
         {
				html.appendChild(row);
				row = DateAndTimeChooser.buildNode('tr');
				dayCount = 0;
			}
			
			// create a clickable day element
			elem = DateAndTimeChooser.buildNode('a', { href: 'javascript:void(0)' }, i);
			elem.onclick = function(e)
         {
            var node = Utilities_GetEventTarget(e);
            var dataElement = DateAndTimeChooser.GetDataElement(node);
            var currentYearView = parseInt(dataElement.getAttribute('currentYearView'));
            var currentMonthView = parseInt(dataElement.getAttribute('currentMonthView'));
            dataElement.setAttribute('currentyear', currentYearView);
            dataElement.setAttribute('currentMonth', currentMonthView);
            dataElement.setAttribute('currentDay', this.innerHTML);
				DateAndTimeChooser.handleDayClick(node);
				return false;
			}

         var now = DateAndTime_Now();
         
			// output the text that goes inside each td
			// if the day is the selected day, add a class of "selected"
			className = '';
			if (now.Day() == i && now.Month()-1 == currentMonthView && now.Year() == currentYearView)
				className = 'today';
			if (currentDay == i && currentMonth == currentMonthView && currentYear == currentYearView)
				className = 'selected';
				
			row.appendChild(DateAndTimeChooser.buildNode('td', { className: className }, elem));

			dayCount++;
		}
		
		// if we haven't finished at the end of the week, start writing out the "days" for the next month
		for(i = 1; i <= (7 - dayCount); i++)
      {
			row.appendChild(DateAndTimeChooser.buildNode('td', {}, '&nbsp;'));
		}
		
		html.appendChild(row);
		
		return html;
	},
	
	open: function(node)
   {
/* DRL FIXIT! Add handler to close when user clicks outside the calendar.
      var wantsTime = parseBool(Utilities_GetElementById(dataElementId).getAttribute('wantsTime'));
      
		if (!wantsTime)
		{
			document.onclick = function(e)
         {
				e = e || window.event;
				var target = e.target || e.srcElement;
            var displayElement = DateAndTimeChooser.GetDisplayElement(dataElementId);
            
				var parentNode = target.parentNode;
				if (target != displayElement && parentNode != container)
            {
					while (parentNode != container)
               {
						parentNode = parentNode.parentNode;
						if (parentNode == null)
                  {
							DateAndTimeChooser.close(containerId);
							break;
						}
					}
				}
			}
		}
*/
		
      var scrollX = window.scrollX;
      var scrollY = window.scrollY;
      
      var container = DateAndTimeChooser.GetContainerElement(node);
		Visibility_Show(container);
      DateAndTimeChooser.updateDataElement(node);   // show/hide date and time as appropriate
      
      setTimeout( function() { window.scrollTo(scrollX, scrollY); }, 1);   // restore original scroll position
	},
	
	close: function(node)
   {
//		document.onclick = null;

      var scrollX = window.scrollX;
      var scrollY = window.scrollY;
      
      var container = DateAndTimeChooser.GetContainerElement(node);
		Visibility_Hide(container);
      
      setTimeout( function() { window.scrollTo(scrollX, scrollY); }, 1);   // restore original scroll position
	},
	
	MakeDateAndTimeChooser: function(dataElement)
	{
      var wantsDate = Class_HasByElement(dataElement, 'datechooser');
      var wantsTime = Class_HasByElement(dataElement, 'timechooser');
      
      if (DateAndTimeChooser.Years.length == 0)
         for (var i = 1950; i < 2050; i++)
            DateAndTimeChooser.Years[i-1950] = i + "";
            
      var displayFormat = DateAndTimeChooser.GetDisplayFormat(dataElement);
      var showInline = Class_HasByElement(dataElement, 'showinline');
      var dateNullable = DateAndTimeChooser.GetDateNullable(dataElement);
      var timeNullable = DateAndTimeChooser.GetTimeNullable(dataElement);
      var nullable = DateAndTimeChooser.GetNullable(dataElement);            
         
      var now = DateAndTime_Now();
         
		var selectedDate = DateAndTime_FromElement(dataElement);
      
      if (wantsDate && !dateNullable && (!nullable || selectedDate.HasTime()) && !selectedDate.HasDate())
      {
         selectedDate.SetDate(now.Year(), now.Month(), now.Day());
      }
      if (wantsTime && !timeNullable && (!nullable || selectedDate.HasDate()) && !selectedDate.HasTime())
      {
         selectedDate.SetTime(now.Hour(), now.Minute(), now.Second());
      }
      
		var currentYearView = DateAndTimeChooser.GetYear(selectedDate);
		var currentMonthView = DateAndTimeChooser.GetMonth.integer(selectedDate);
		var currentDay = DateAndTimeChooser.GetDay(selectedDate);

		dataElement.setAttribute('wantsDate', wantsDate);
		dataElement.setAttribute('wantsTime', wantsTime);
		dataElement.setAttribute('currentDay', currentDay);
		dataElement.setAttribute('currentMonth', currentMonthView);
		dataElement.setAttribute('currentYear', currentYearView);
		dataElement.setAttribute('currentMonthView', currentMonthView);
		dataElement.setAttribute('currentYearView', currentYearView);
         
		// initialize the display element
		var displayElement = DateAndTimeChooser.buildNode('a', { href: '#' });
		displayElement.onclick = function(e) 
      {
         var displayElement = Utilities_GetEventTarget(e);
         var container = displayElement.nextElementSibling;
         if (Visibility_IsShown(container))
            DateAndTimeChooser.close(container); 
         else 
            DateAndTimeChooser.open(container); 
      };

		// hide the data element
		Visibility_HideByElement(dataElement);
		
		// add display element
		Utilities_InsertAfterNode(dataElement.parentNode, displayElement, dataElement);

		var inputLeft = 0;
		var inputTop = 20;	// DRL FIXIT! Find the element height!
		obj = displayElement;
		if(obj.offsetParent)
      {
			do
         {
				inputLeft += obj.offsetLeft;
				inputTop += obj.offsetTop;
			} while (obj = obj.offsetParent);
		}
		
		var container = DateAndTimeChooser.buildNode('div', { className: 'calendar' });
		if (showInline)
			container.style.cssText = 'display: none;';
		else
			container.style.cssText = 'display: none; position: absolute; top: ' + 
            (inputTop + displayElement.offsetHeight) + 'px; left: ' + inputLeft + 'px; z-index: 9999;';

		var enabler = DateAndTimeChooser.buildNode('div', { className: 'calendar_enabler' });
      var allNull = null;
      var hasDate = null;
      var hasTime = null;
      if (nullable && (!timeNullable || !dateNullable))
      {
         allNull = DateAndTimeChooser.buildNode('input', { type: 'checkbox', name: 'DateAndTimeChooser_AllNull' }, '');
         if (!selectedDate.HasDate() && !selectedDate.HasTime()) allNull.checked = true;
   		allNull.onchange = function(e)
         {
            var node = Utilities_GetEventTarget(e);
            DateAndTimeChooser.updateDataElement(node);
   		}
         var label = DateAndTimeChooser.buildNode('label', { name: 'DateAndTimeChooser_AllNullLabel' }, '');
   		label.appendChild(allNull);
         label.appendChild(DateAndTimeChooser.buildNode('span', { }, ' None&nbsp;&nbsp;'));
   		enabler.appendChild(label);
      }
      if (wantsDate && dateNullable)
      {
         hasDate = DateAndTimeChooser.buildNode('input', { type: 'checkbox', name: 'DateAndTimeChooser_HasDate' }, '');
         hasDate.checked = selectedDate.HasDate();
   		hasDate.onchange = function(e)
         {
            var node = Utilities_GetEventTarget(e);
            DateAndTimeChooser.updateDataElement(node);
   		}
         var label = DateAndTimeChooser.buildNode('label', { name: 'DateAndTimeChooser_HasDateLabel' }, '');
   		label.appendChild(hasDate);
         label.appendChild(DateAndTimeChooser.buildNode('span', { }, ' Date&nbsp;&nbsp;'));
   		enabler.appendChild(label);
      }
      if (wantsTime && timeNullable)
      {
         hasTime = DateAndTimeChooser.buildNode('input', { type: 'checkbox', name: 'DateAndTimeChooser_HasTime' }, '');
         hasTime.checked = selectedDate.HasTime();
   		hasTime.onchange = function(e)
         {
            var node = Utilities_GetEventTarget(e);
            DateAndTimeChooser.updateDataElement(node);
   		}
         var label = DateAndTimeChooser.buildNode('label', { name: 'DateAndTimeChooser_HasTimeLabel' }, '');
   		label.appendChild(hasTime);
         label.appendChild(DateAndTimeChooser.buildNode('span', { }, ' Time'));
   		enabler.appendChild(label);
      }
      
		var months = DateAndTimeChooser.buildNode('div', { className: 'months', name: 'DateAndTimeChooser_DatePortion1' });
		prevMonth = DateAndTimeChooser.buildNode('span', { className: 'prev-month' }, 
         DateAndTimeChooser.buildNode('a', { href: 'javascript:void(0)' }, '&lt;'));
		prevMonth.onclick = function(e)
      {
         var node = Utilities_GetEventTarget(e);
         var dataElement = DateAndTimeChooser.GetDataElement(node);
			dataElement.setAttribute('currentMonthView', parseInt(dataElement.getAttribute('currentMonthView'))-1);
			return DateAndTimeChooser.handleDateChange(node);
		}
		nextMonth = DateAndTimeChooser.buildNode('span', { className: 'next-month' }, 
         DateAndTimeChooser.buildNode('a', { href: 'javascript:void(0)' }, '&gt;'));
		nextMonth.onclick = function(e)
      {
         var node = Utilities_GetEventTarget(e);
         var dataElement = DateAndTimeChooser.GetDataElement(node);
			dataElement.setAttribute('currentMonthView', parseInt(dataElement.getAttribute('currentMonthView'))+1);
			return DateAndTimeChooser.handleDateChange(node);
		}
		month = DateAndTimeChooser.buildNode('span', { className: 'current-month', name: 'DateAndTimeChooser_Month' }, 
         DateAndTimeChooser.GetDisplayedMonth.string(currentMonthView) + '&nbsp;');
		yearList = DateAndTimeChooser.buildSelectNode(DateAndTimeChooser.Years, currentYearView);
      yearList.name = 'DateAndTimeChooser_YearList';
   	yearList.onchange = function(e)
      {
         var node = Utilities_GetEventTarget(e);
         var yearList = DateAndTimeChooser.GetYearListElement(node);
         var currentYearView = yearList.options[yearList.selectedIndex].value;
			dataElement.setAttribute('currentYearView', currentYearView);
			DateAndTimeChooser.handleDateChange(node);
		}

		months.appendChild(prevMonth);
		months.appendChild(month);
		months.appendChild(yearList);
		months.appendChild(nextMonth);
		
		var calendar = DateAndTimeChooser.buildNode('table', { name: 'DateAndTimeChooser_DatePortion2' }, 
         DateAndTimeChooser.buildNode('thead', {}, 
            DateAndTimeChooser.buildNode('tr', { className: 'weekdays' }, DateAndTimeChooser.buildWeekdays())));
		var body = DateAndTimeChooser.buildNode('tbody', { name: 'DateAndTimeChooser_Body' }, DateAndTimeChooser.buildCalendar(dataElement));
		
		calendar.appendChild(body);
      
		container.appendChild(enabler);
		container.appendChild(months);
		container.appendChild(calendar);

      var row = DateAndTimeChooser.buildNode('div', { className: 'calendar_timerow' });
      var span = DateAndTimeChooser.buildNode('span', { name: 'DateAndTimeChooser_TimePortion' });
      row.appendChild(span);

      var hour = DateAndTimeChooser.GetHour(selectedDate);
      if (hour == 0)
         hour = 12;

      var hoursElement = DateAndTimeChooser.buildSelectNode(DateAndTimeChooser.Hours, hour);
      hoursElement.name = 'DateAndTimeChooser_Hours';
      hoursElement.onchange = function(e)
      {
         var node = Utilities_GetEventTarget(e);
         DateAndTimeChooser.handleTimeClick(node);
      }
      span.appendChild(hoursElement);

      var minutesElement = DateAndTimeChooser.buildSelectNode(DateAndTimeChooser.Minutes, DateAndTimeChooser.GetMinute(selectedDate));
      minutesElement.name = 'DateAndTimeChooser_Minutes';
      minutesElement.onchange = function(e)
      {
         var node = Utilities_GetEventTarget(e);
         DateAndTimeChooser.handleTimeClick(node);
      }
      span.appendChild(minutesElement);

      var ampmElement = DateAndTimeChooser.buildNode('select', { size: 1, name: 'DateAndTimeChooser_AmPm' }, null);
      for (i = 0; i < DateAndTimeChooser.AmPm.length; i++)
      {
         if (DateAndTimeChooser.GetAmPm.integer(selectedDate) == i)
            ampmElement.appendChild(DateAndTimeChooser.buildNode('option', { value: i, selected: 1 }, DateAndTimeChooser.AmPm[i]));
         else
            ampmElement.appendChild(DateAndTimeChooser.buildNode('option', { value: i }, DateAndTimeChooser.AmPm[i]));
      }
      ampmElement.onchange = function(e)
      {
         var node = Utilities_GetEventTarget(e);
         DateAndTimeChooser.handleTimeClick(node);
      }
      span.appendChild(ampmElement);
      var space = document.createElement('span');
      space.innerHTML = "&nbsp;&nbsp;";
      span.appendChild(space);

      var doneElement = DateAndTimeChooser.buildNode('span', { }, 
         DateAndTimeChooser.buildNode('a', { className: 'calendar_ok', href: 'javascript:void(0)' }, 'OK'));
      doneElement.onclick = function(e)
      {
         var node = Utilities_GetEventTarget(e);
         return DateAndTimeChooser.handleDoneClick(node);
      }
      row.appendChild(doneElement);

      container.appendChild(row);

		
//		if (showInline)
			Utilities_InsertAfterNode(dataElement.parentNode, container, displayElement);
//		else
//			document.body.appendChild(container);
		
		displayElement.innerHTML = selectedDate.ToFormat(displayFormat);
      if (displayElement.innerHTML.isEmpty())
   		displayElement.innerHTML = '(none)';
	},
}

DocumentLoad.AddCallback(DateAndTimeChooser.Init);

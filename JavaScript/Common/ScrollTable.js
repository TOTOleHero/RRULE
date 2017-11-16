// ========================================================================
//        Copyright � 2012 Dominique Lacerte, All Rights Reserved.
// 
// Redistribution and use in source and binary forms are prohibited without 
//   prior written consent from Dominique Lacerte (internet@lacerte.org).
// ========================================================================

// Allows the scrolling of rows in a table element while the title stays 
// fixed. The table must be enclosed in a scrollable DIV and if there is 
// a style applied to the header it must also be applied to the footer.
//
// <DIV>
// <TABLE class='scrolltable'>
// <THEAD>
// 	<TR><TH>Name</TH><TH>Age</TH>
// </THEAD>
// <TBODY>
// 	<TR><TD>Tanis Higgins</TD><TD>56</TD></TR>
// 	<TR><TD>Dom Test2</TD><TD>45</TD></TR>
// 	<TR><TD>faye keys</TD><TD>58</TD></TR>
// </TBODY>
// </TABLE>
// </DIV>


ScrollTable =
{
	Init: function()
	{
		forEach(Utilities_GetElementsByTag('table'), function(table)
		{
			if (Class_HasByElement(table, 'scrolltable'))
			{
				ScrollTable.MakeScrollTable(table);
			}
		});
	},
	
	Resize: function(table)
	{
		var header = table.children[0];
		var footer = table.children[2];

		var headerRow = header.children[0];
		var footerRow = footer.children[0];

		for (var i = 0; i < headerRow.childElementCount; i++)
		{
			var hd = headerRow.children[i];
			var fd = footerRow.children[i];
			
			// calculate the width of the data area in the table body cell
			var width = fd.clientWidth ? parseInt(fd.clientWidth) : 0 - 
				fd.clientLeft ? parseInt(fd.clientLeft) : 0 + 
				fd.style.paddingLeft ? parseInt(fd.style.paddingLeft) : 0 - 
				fd.style.paddingRight ? parseInt(fd.style.paddingRight) : 0 -
				fd.style.marginLeft ? parseInt(fd.style.marginLeft) : 0 - 
				fd.style.marginRght ? parseInt(fd.style.marginRght) : 0 -
				fd.style.borderLeftWidth ? parseInt(fd.style.borderLeftWidth) : 0 - 
				fd.style.borderRightWidth ? parseInt(fd.style.borderRightWidth) : 0;

			// set the table header and footer column widths so they 
			// continue to match the data rows
			hd.style.width = width + "px";
			// adjustment in case the above was off
			var newWidth = width + fd.offsetWidth - hd.offsetWidth;
			if (newWidth != width)
				hd.style.width = newWidth + "px";
		}
/*		
		var width = footerRow.clientWidth;
		headerRow.style.width = width + "px";
		// adjustment in case the above was off
		var newWidth = width + footerRow.offsetWidth - headerRow.offsetWidth;
		if (newWidth != width)
			headerRow.style.width = newWidth + "px";
*/
	},
	
	StartScrolling: function (event)
	{
	    if (!event) event = window.event;	// Internet Explorer before version 9
			
		var div = event.currentTarget;
	    var top = div.pageYOffset ? div.pageYOffset : div.scrollTop;
		if (top == 0) return;
		
		var table = div.children[0];
		var header = table.children[0];
		var body = table.children[1];

		// add enough space to account for the header that was removed
		// DRL FIXIT! The top row may not always be visible.
		var bodyRow = body.children[0];
		var headerRow = header.children[0];
		bodyRow.children[0].style.paddingTop = 
			(headerRow.children[0].clientHeight ? parseInt(headerRow.children[0].clientHeight) : 50) + "px";
		
		// set the header to a fixed location when scrolling
		header.style.position = "fixed";

		ScrollTable.Resize(table);
		
		div.onscroll = ScrollTable.StopScrolling;
	},
	
	StopScrolling: function (event)
	{
	    if (!event) event = window.event;	// Internet Explorer before version 9
			
		var div = event.currentTarget;
	    var top = div.pageYOffset ? div.pageYOffset : div.scrollTop;
		if (top != 0) return;

		var table = div.children[0];
		var header = table.children[0];
		var body = table.children[1];
		var footer = table.children[2];
			
		var headerRow = header.children[0];
		var bodyRow = body.children[0];
		var footerRow = footer.children[0];
	
		// remove the padding we added
		bodyRow.children[0].style.paddingTop = "";
		
		// set the header back to relative location when not scrolling
		header.style.position = "relative";

		for (i = 0; i < headerRow.childElementCount; i++)
		{
			var hd = headerRow.children[i];
			
			// remove the width calculations so things return to they way they were
			hd.style.width = "";
		}
		
		div.onscroll = ScrollTable.StartScrolling;
	},
	
	MakeScrollTable: function(table)
	{
		var div = table.parentNode;
		if (!div || div.nodeName.toLowerCase() != "div" ||
			div.childElementCount == 0 || 
			div.children[0].nodeName.toLowerCase() != 'table')
			return;

		if (!table || table.childElementCount < 2) return;
		
		var header = table.children[0];
		var body = table.children[1];
		if (header.nodeName.toLowerCase() != "thead" || 
			body.nodeName.toLowerCase() != "tbody" || 
			header.childElementCount == 0 || 
			body.childElementCount == 0)
			return;

		// we creat e a footer that matches the header so the columns stay 
		// the same width when we remove the header
		// creates footer if not existing
		var footer = table.createTFoot();
		var row = footer.insertRow(0);
		var headerRow = header.children[0];
		for (i = 0; i < headerRow.childElementCount; i++)
		{
			var cell = row.insertCell(0);
			cell.style.opacity = "0";	// hide the footer
			cell.innerHTML = headerRow.children[headerRow.childElementCount - i - 1].innerHTML;
		}

		div.onscroll = ScrollTable.StartScrolling;
	},
}

DocumentLoad.AddCallback(ScrollTable.Init);

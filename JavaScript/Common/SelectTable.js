// ========================================================================
//        Copyright © 2012 Dominique Lacerte, All Rights Reserved.
// 
// Redistribution and use in source and binary forms are prohibited without 
//   prior written consent from Dominique Lacerte (internet@lacerte.org).
// ========================================================================

// Allows the selection of rows in a table element. The selected rows will 
// be given the class 'selected_row'.
//
// <TABLE class='selectable'>
// <THEAD>
// 	<TR><TH>Name</TH><TH>Age</TH>
// </THEAD>
// <TBODY>
// 	<TR><TD>Tanis Higgins</TD><TD>56</TD></TR>
// 	<TR><TD>Dom Test2</TD><TD>45</TD></TR>
// 	<TR><TD>faye keys</TD><TD>58</TD></TR>
// </TBODY>
// </TABLE>


SelectTable =
{
	firstSelectedRow: null,
	callbacks: new Array(),
	
	Init: function()
	{
		forEach(Utilities_GetElementsByTag('table'), function(table)
		{
			if (Class_HasByElement(table, 'selectable'))
			{
				SelectTable.MakeSelectable(table);
			}
		});
	},
	
	AddCallback: function(callback)
	{
		SelectTable.callbacks.push(callback);
	},
	
	GetTable: function(elem)
	{
		while (elem && strtoupper(elem.tagName) != 'TABLE')
			elem = elem.parentNode;
		return elem;
	},
	
	ClearSelection: function(table)
	{
		forEach(SelectTable.GetRows(table), function(tr)
		{
			Class_RemoveByElement(tr, "selected_row");
		});
	},
	
	ExtendSelection: function(table, elem)
	{
		var selecting = false;
		forEach(SelectTable.GetRows(table), function(tr)
		{
			// DRL We don't want to select hidden rows.
			var visible = Visibility_IsShownByElement(tr);
			
			Class_SetByElement(tr, "selected_row", tr.id && selecting && visible);
			if (tr == SelectTable.firstSelectedRow || tr == elem)
				selecting = !selecting;
			if (tr.id && selecting && visible) {
				Class_AddByElement(tr, "selected_row");
			}
		});
	},

	HasMultipleRowsSelected: function(table)
	{
		var count = 0;
		forEach(SelectTable.GetRows(table), function(tr)
		{
			if (Class_HasByElement(tr, "selected_row")) {
				count++;
			}			
			if (count >= 2) {
				return true;
			}
		});
		return false;
	},
	
	HandleClick: function(event)
	{
      if (!event) event = window.event;	// Internet Explorer before version 9
      
		var target = event.currentTarget;
		if (!target.id)
			return;

		var table = SelectTable.GetTable(target);
		var shiftSelect = false;
			
		if (event.shiftKey)
		{
			SelectTable.ExtendSelection(table, target);
			shiftSelect = true;
		} 
		else if (event.ctrlKey || event.metaKey || Class_HasByElement(table, 'checkbox_selectable'))
		{
			Class_ToggleByElement(target, "selected_row");
		}
		else
		{
			if (SelectTable.HasMultipleRowsSelected(table)) {
				SelectTable.ClearSelection(table);
				Class_AddByElement(target, "selected_row");
				SelectTable.firstSelectedRow = null;
			} else {
				var rowIsCurrentlySelected = Class_HasByElement(target, "selected_row");
				SelectTable.ClearSelection(table);
				if (!rowIsCurrentlySelected) {
					Class_AddByElement(target, "selected_row");	
				}
			}
		}
		
		if (SelectTable.firstSelectedRow == null)
			SelectTable.firstSelectedRow = target;
		
		// shift clicking causes browser selection so we remove any selection here
      if (shiftSelect)
      {
   		if (document.selection && document.selection.empty)
   		{
   			document.selection.empty();
   		}
   		else if (window.getSelection)
   		{
   			var sel = window.getSelection();
   			sel.removeAllRanges();
   		}
      }

		forEach(SelectTable.callbacks, function(callback)
		{
			callback(table);
		});
	},
	
	SetSelection: function(table, elem)
	{
		SelectTable.ClearSelection(table);
		Class_AddByElement(elem, "selected_row");
		SelectTable.firstSelectedRow = elem;
		
		// clicking causes browser selection so we remove any selecton here
		if(document.selection && document.selection.empty)
		{
			document.selection.empty();
		}
		else if(window.getSelection)
		{
			var sel = window.getSelection();
			sel.removeAllRanges();
		}

		forEach(SelectTable.callbacks, function(callback)
		{
			callback();
		});
	},
	
	MakeSelectable: function(table)
	{
		forEach(SelectTable.GetRows(table), function(tr)
		{
			tr.onclick = SelectTable.HandleClick;
		});
		forEach(SelectTable.callbacks, function(callback)
		{
			callback();
		});
	},
	
	GetRows: function(table)
	{
/*
		elems = Utilities_GetElementsByTag('tbody', table);
		if (elems.length == 0)
			elems = new Array(table);
		return Utilities_GetElementsByTag('tr', elems[0]);
*/
      return table.rows;
	},
	
	GetSelectedIds: function(table)
	{
		var result = new Array();
		forEach(SelectTable.GetRows(table), function(row)
		{
			if (Class_HasByElement(row, "selected_row"))
				result.push(row.id);
		});
		return result;
	},
}

DocumentLoad.AddCallback(SelectTable.Init);

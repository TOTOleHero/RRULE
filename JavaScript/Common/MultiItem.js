// ========================================================================
//        Copyright � 2010 Dominique Lacerte, All Rights Reserved.
// 
// Redistribution and use in source and binary forms are prohibited without 
//   prior written consent from Dominique Lacerte (internet@lacerte.org).
// ========================================================================

// Convert a single DIV element into a 'multiple' so more can be added (and removed) 
// by the user. Note that the template elements can be DIV for vertical layout or 
// SPAN for horizontal layout.
//
//
//
// Usage:
//
//	<DIV class='MultiItem' max='4'>
//		<!-- always have at least one template element with no numbering in the field names, and
//		     make sure to ignore these as they will be submitted with the rest -->
//		<DIV class='MultiItemTemplate' AddButtonLabel='Add Title'><INPUT type='text' name='ValidTitles' value=''></DIV>
//
//		<!-- follow with the existing data elements, this time numbered however you like... -->
//		<DIV class='MultiItem_Item'><INPUT type='text' name='ValidTitles134' value='Novice'></DIV>
//		<DIV class='MultiItem_Item'><INPUT type='text' name='ValidTitles1564' value='Crew'></DIV>
//		<DIV class='MultiItem_Item'><INPUT type='text' name='ValidTitles234' value='Skipper'></DIV>
//
//		<!-- new items will be added with numbering starting at the next available value
//		     (1565 here) but the numbers may not be sequential if items have been deleted -->
//	</DIV>
//
// This module depends on: BusyIndicator.js, DocumentLoad.js, EnableDisable.js, Utilities.js, Visibility.js
//
//
// [2017 May] Now supports MultiItem on TR elements as well
//
// Sample Usage:
//
// <TR class='MultiItem' max='4'>
//   <!-- the wrapper element can have its own fields -->
//   <TD><INPUT type='text' name='ValidTitles134' value='Novice' /></TD>
// </TR>
// <!-- succeeding rows are new TRs -->
// <TR><TD><INPUT type='text' name='ValidTitles1564' value='Crew' /></TD></TR>
// <TR><TD><INPUT type='text' name='ValidTitles234' value='Crew' /></TD></TR>
//
//		<!-- new items will be added with numbering starting at the next available value
//		     (1565 here) but the numbers may not be sequential if items have been deleted -->
//
//		<!-- always have one or more template elements with no numbering in the field names, and
//		     make sure to ignore these as they will be submitted with the rest -->
// <TR class='MultiItemTemplate' AddButtonLabel='Add Title'><TD><INPUT type='text' name='ValidTitles' value='' /></TD></TR>
//
//    <!-- the MultiItemButton row is used to mark the end of the multi-item -->
// <TR class='MultiItemButton'><TD COLSPAN='2'>The "Add" button should go here.</TD></TR>
//


MultiItem =
{
   callbacks: new Array(),

	Init: function()
	{
      var elems = Utilities_GetElementsByTag('div');
      // using forEach below didn't work on Firefox because we're changing the select elements I believe?
		for (var i = 0; i < elems.length; i++)
		{
			if (Class_HasByElement(elems[i], 'MultiItem'))
			{
				var max = parseInt(elems[i].getAttribute('max'));
				MultiItem.MakeMultiItem(elems[i], max);
			}
		}
		elems = Utilities_GetElementsByTag('tr');
    // using forEach below didn't work on Firefox because we're changing the select elements I believe?
		for (var i = 0; i < elems.length; i++)
		{
			if (Class_HasByElement(elems[i], 'MultiItem'))
			{
				var max = parseInt(elems[i].getAttribute('max'));
				MultiItem.MakeMultiItemTable(elems[i], max);
			}
		}
	},

	NumberFields: function(parent, id)
	{
		var els = parent.children;
		for (var i = 0, count = els.length; i < count; i++)
		{
         var el = els[i];
			MultiItem.NumberFields(el, id);   // nested items (i.e. in a table)
         
			var name = el.name;
			var classes = el.className;
         if (classes != null && classes.indexOf('MultiItemSelector') >= 0)   // radio button handling
         {
				el.value = id;
         }
			else if (name != null && name != 'MultiItemAddButton' && name != 'MultiItemDeleteButton')
			{
				el.name = name + id;
			}
		}
	},
	
	GetIdFromFields: function(parent)
	{
		var id = null;
		var els = parent.children;
		for (var i = 0, count = els.length; i < count; i++)
		{
			var name = els[i].name;
			if (name != null)
			{
				for(var k = name.length-1; k > 0; k--)
				{
					var c = name.charAt(k);
					if (c < '0' || c > '9')
						break;
				}
				if (k < name.length-1)
				{
					id = parseInt(substr(name, k+1));
					break;
				}
			}
         
         id = MultiItem.GetIdFromFields(els[i]);   // nested items (i.e. in a table)
   		if (id != null)
   		{
   			break;
   		}
		}
		
		return id;
	},

	AddItem: function(multi_item, element, fireEvent, template)
	{
		if (multi_item.max && multi_item.count >= multi_item.max)
         return;
         
		if (element == null)
		{
			//cloneNode will not clone event listeners attached to the node
			element = template.cloneNode(true);

			//remove the template class from cloned node
         Class_RemoveByElement(element, "MultiItemTemplate");

			if (multi_item.wrapper_element.tagName == 'TR')
			{
				var node = multi_item.addButtons[0];
				// find the TR parent, so we can insert the new row before it
				while (node.tagName != 'TR')
				{
					node = node.parentNode;
				}
				Utilities_InsertBeforeNode(multi_item.wrapper_element.parentNode, element, node);
			}
			else
			{
				Utilities_InsertBeforeNode(multi_item.wrapper_element, element, multi_item.addButtons[0]);
			}
			
			//display the element before you call InitializeNodes to get it's offsetWidth
			Visibility_ShowByElement(element);

			//call numberfields after CustomClone to ensure its numbered
			MultiItem.NumberFields(element, multi_item.next_id++);
         
         // moved this last so we can support DateAndTimeChooser
         Utilities_InitializeNodes(element);
		}
		else
		{
			id = MultiItem.GetIdFromFields(element);

			if (element.tagName == 'TR')
			{
				var els = element.children;
				cols = 0;
				for (var i = 0, count = els.length; i < count; i++)
				{
					var el = els[i];
					if (el.tagName == 'TD' || el.tagName == 'TH')
					{
						cols++;
						break;
					}
				}
				if (cols == 0)
				{
					// we allow empty rows to be added (since the wrapper_element may be an empty row)
					// but we hide them so that they don't cause display issues
					Visibility_HideByElement(element);
					// no need to further process empty rows
					return;
				}
			}

			if (id == null)
				alert('Error: No item number found in element names. Make sure each MultiItem entry has a numeric ID added to the field names.');
			else if (id >= multi_item.next_id)
				multi_item.next_id = id+1;
		}
	
		multi_item.count++;
		if (multi_item.max && multi_item.count >= multi_item.max)
		{
			multi_item.addButtons.forEach(function(button)
		  {
				EnableDisable_DisableByElement(button);
			});
		}

      // add our display class
      Class_AddByElement(element, "MultiItem_Item");

      // Create an element that will create a bit of space between this and the delete button
//    var spaceElement = document.createElement('span');
//    spaceElement.innerHTML = "&nbsp;";
//    element.appendChild(spaceElement);

		element.deleteButton = document.createElement("BUTTON");
		element.deleteButton.name = 'MultiItemDeleteButton';
		element.deleteButton.type = 'BUTTON';   // required to not submit form
      Class_AddByElement(element.deleteButton, 'MultiItem_Delete');

//		element.deleteButton.appendChild(document.createTextNode("X"));
		element.deleteButton.onclick = MultiItem.OnRemoveItem;

		if (multi_item.wrapper_element.tagName == 'TR')
		{
			element.deleteButton.style.width = "auto";
			// find the last TD and append the button there
			var els = element.children;
			var lastCell = null;
			for (var i = 0, count = els.length; i < count; i++)
			{
				var el = els[i];
				if (el.tagName == 'TD' || el.tagName == 'TH')
				{
					lastCell = el;
				}
			}
			if (lastCell != null) {
				lastCell.appendChild(element.deleteButton);
			}
		}
		else
		{
// we add the button to the front so when it is "float: right" for multiple rows it shows at the top
//			element.appendChild(element.deleteButton);
         element.insertBefore(element.deleteButton, element.firstChild);

         // Add space after the element, for spacing and also to allow line breaks to occur
         var spaceElement = document.createTextNode(' ');
	      element.appendChild(spaceElement);
		}

		element.multi_item = multi_item;
      
      if (fireEvent)
         MultiItem.FireChanged(multi_item.wrapper_element);
	},

	// utlity function to find the parent node with the multi-item
	FindWrapperFromButton: function(button)
	{
		var node = button.parentNode;
		// for Table mode, parentNode is not necessarily the multi-item itself, so we have to navigate the DOM to find it
		if (!node.multi_item)
		{
			// find the TR parent
			while (node.tagName != 'TR')
			{
					node = node.parentNode;
			}
			// check previous elements until we find the multi item
			while (node != null)
			{
				if (node.multi_item)
				{
					return node;
				}
				node = node.previousElementSibling;
			}
		}
		else
		{
			// for DIV mode, the button parent already contains the multi-item
			return node;
		}
	},

	OnAddItem: function()
	{
		var multi_item = MultiItem.FindWrapperFromButton(this).multi_item;
		var template = null;
		var button_label = this.innerHTML;
		if (button_label in multi_item.templates)
		{
			template = multi_item.templates[button_label];
		}
		MultiItem.AddItem(multi_item, null, true, template);

		// Appease Safari
		//    without it Safari wants to reload the browser window
		//    which nixes your already queued uploads
		return false;
	},
	
	OnRemoveItem: function()
	{
		var element = MultiItem.FindWrapperFromButton(this);
		
		element.multi_item.count--;
		if (multi_item.max && multi_item.count < multi_item.max)
		{
			multi_item.addButtons.forEach(function(button)
			{
				EnableDisable_EnableByElement(button);
			});
		}

		// in TR mode it's possible for the row to be deleted to be the wrapper element
		// for this case, just delete all the TDs (so that we still have a wrapper element)
		if (element == element.multi_item.wrapper_element)
		{
			while (element.firstChild)
			{
    		element.removeChild(element.firstChild);
			}
			Visibility_HideByElement(element);
		}
		else
		{
			element.parentNode.removeChild(element);
		}
      
      MultiItem.FireChanged(multi_item.wrapper_element);
      
		// Appease Safari
		//    without it Safari wants to reload the browser window
		//    which nixes your already queued uploads
		return false;
	},
   
   FireChanged: function(wrapper_elem)
   {
      if (typeof OnElemChanged === 'function')
      {
         var e = new Object();
         e.target = multi_item.wrapper_element;
         OnElemChanged(e);
      }

      MultiItem.callbacks.forEach(function(callback)
      {
         try
         {
            callback(wrapper_elem);
         }
         catch(err)
         {
            alert("Exception: " + err.message);
         }
      });
   },
   
   AddCallback: function(callback)
   {
      MultiItem.callbacks.push(callback);
   },
	
	MakeMultiItem: function(wrapper_element, max)
	{
		if (wrapper_element.tagName != 'DIV')
		{
			alert('Error: not a DIV element');
         return;
      }
      
		multi_item = new Object();
	
		multi_item.count = 0;
		multi_item.next_id = 0;

		multi_item.wrapper_element = wrapper_element;
		wrapper_element.multi_item = multi_item;

		var default_label = wrapper_element.getAttribute('AddButtonLabel');
		if (default_label == null || default_label.length == 0) default_label = "Add";

		if (max)
		{
			multi_item.max = max;
		}
		else
		{
			multi_item.max = null;
		}

		multi_item.templates = new Object();

		var els = wrapper_element.children;
		for (var i = 0, count = els.length; i < count; i++)
		{
			var element = els[i];

			if (element.className.indexOf('MultiItemTemplate') != -1)
			{
				//element.className = element.className.replace('MultiItemTemplate', '');
				var label = element.getAttribute('AddButtonLabel');
            if (label == null || label.length == 0) label = default_label;
				multi_item.templates[label] = element;
				Visibility_HideByElement(element);
			}
			else
			{
				MultiItem.AddItem(multi_item, element, false, null);
			}
		}

		// create a button for each template
		multi_item.addButtons = new Array();
		for (var button_label in multi_item.templates)
		{
			var addButton = document.createElement("BUTTON");
			addButton.name = 'MultiItemAddButton';
			addButton.type = 'BUTTON';   // required to not submit form
			addButton.appendChild(document.createTextNode(button_label));
			addButton.onclick = MultiItem.OnAddItem;
//			addButton.style.width = "auto";
			multi_item.addButtons.push(addButton);
		}

		multi_item.addButtons.forEach(function(button)
		{
			multi_item.wrapper_element.appendChild(button);
			
			// add an element that will create some spacing between the buttons
         var spaceElement = document.createElement('span');
         spaceElement.innerHTML = " ";
         multi_item.wrapper_element.appendChild(spaceElement);
		});

		if (multi_item.templates.length == 0)
			alert('Error: no template element');
	},

	MakeMultiItemTable: function(wrapper_element, max)
	{
		if (wrapper_element.tagName != 'TR')
		{
			alert('Error: not a TR');
         return;
      }

		multi_item = new Object();

		multi_item.count = 0;
		multi_item.next_id = 0;

		multi_item.wrapper_element = wrapper_element;
		wrapper_element.multi_item = multi_item;

		var default_label = wrapper_element.getAttribute('AddButtonLabel');
		if (default_label == null || default_label.length == 0) default_label = "Add";

		if (max)
		{
			multi_item.max = max;
		}
		else
		{
			multi_item.max = null;
		}

		multi_item.templates = new Object();
		var element = multi_item.wrapper_element;
		// use nextElementSibling to iterate through the rows
		while (element != null)
		{
			if (element.className.indexOf('MultiItemTemplate') != -1)
			{
				//element.className = element.className.replace('MultiItemTemplate', '');
				var label = element.getAttribute('AddButtonLabel');
		    if (label == null || label.length == 0) label = default_label;
				multi_item.templates[label] = element;
				Visibility_HideByElement(element);
			}
			else if (element.className.indexOf('MultiItemButton') != -1)
			{
				multi_item.button_element = element;
				// use the buttom to indicate the end of the multi_item
				break;
			}
			else
			{
				MultiItem.AddItem(multi_item, element, false, null);
			}
			element = element.nextElementSibling;
		}

		if (!multi_item.templates.length == 0)
			alert('Error: no template element');

		// create a button for each template
		multi_item.addButtons = new Array();
		for (var button_label in multi_item.templates)
		{
			var addButton = document.createElement("BUTTON");
			addButton.name = 'MultiItemAddButton';
			addButton.type = 'BUTTON';   // required to not submit form
			addButton.appendChild(document.createTextNode(button_label));
			addButton.onclick = MultiItem.OnAddItem;
//			addButton.style.width = "auto";
			multi_item.addButtons.push(addButton);
		}

		// insert the buttons into the first cell of the button row
		var els = multi_item.button_element.children;
		for (var i = 0, count = els.length; i < count; i++)
		{
			var element = els[i];

			if (element.tagName == "TD" || element.tagName == "TH")
			{
				while (element.firstChild) {
		    	element.removeChild(element.firstChild);
				}
				multi_item.addButtons.forEach(function(button)
			  {
					element.appendChild(button);
				});
				break;
			}
		}
	}
}

DocumentLoad.AddCallback(MultiItem.Init);

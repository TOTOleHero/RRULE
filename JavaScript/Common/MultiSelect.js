// ========================================================================
// ========================================================================
//        Copyright � 2008 Dominique Lacerte, All Rights Reserved.
// 
// Redistribution and use in source and binary forms are prohibited without 
//   prior written consent from Dominique Lacerte (internet@lacerte.org).
// ========================================================================

//	Convert a multiple SELECT control into one that lists the selected 
// items in a column (or row with class MultiSelectHorizontal).
//
//	Usage:
//
//	<SELECT name='Whatever[]' class='MultiSelect MultiSelectHorizontal FilterSelect' size=4 AddButtonLabel='Add Document...'>
//		<OPTION>Web Authoring Reference</OPTION>
//		<OPTION>FAQ Archives</OPTION>
//		<OPTION>Feature Article</OPTION>
//	</SELECT>


MultiSelect =
{
	Init: function()
	{
      var elems = Utilities_GetElementsByTag('select');
      // using forEach below didn't work on Firefox because we're changing the select elements I believe?
		for (var i = 0; i < elems.length; i++)
		{
			if (Class_HasByElement(elems[i], 'MultiSelect'))
			{
				//do not make multi select if its a template
				if (!Utilities_HasClassAsParent(elems[i], 'MultiItemTemplate'))
				{
					MultiSelect.MakeMultiSelect(elems[i]);
				}
			}
		}
	},
   
   ClearSelections: function(targetSelect)
   {
      for (var i = 0; i < targetSelect.children.length; i++)
      {
         var option = targetSelect.children[i];
         if (option.selected)
         {
            MultiSelect.UnselectItem(targetSelect, option.value);
         }
      }
   },
   
	OnChange: function(newSelect)
	{
      var targetSelect = MultiSelect.GetTargetSelect(newSelect);
		var useFilter = MultiSelect.IsFilterSelect(targetSelect);
		var selectedItem = newSelect.selectedIndex
      if (!useFilter)
         selectedItem = selectedItem - 1;	// accounts for blank item only present in newSelect but not targetSelect
      
		// Don't do anything if the selected item is the empty one or the item is already selected
		if (selectedItem >= 0) // && !targetSelect.options[selectedItem].selected --- removed this condition since it did not allow same option to be selected in consecutive select boxes
		{
			// Select the item
			MultiSelect.OnSelectItem(targetSelect, selectedItem, true);
		}

		// Set the empty item as selected
		newSelect.selectedIndex = useFilter ? -1 : 0;
	},
	
	OnSelectItem: function(targetSelect, selectedItem, fireEvent)
	{
		// Select the item
		targetSelect.options[selectedItem].selected = true;

		var groupingElement;
		if (MultiSelect.IsHorizontal(targetSelect))
      {
   		groupingElement = document.createElement('span');
/* I took this out because it was making the toolbar filter go tall instead of wide. */
/*         groupingElement.className = 'mobile_block';    show vertical on mobile */
      }
      else
   		groupingElement = document.createElement('div');
		Class_AddByElement(groupingElement, 'MultiSelect_Item');

		// Delete button
		var deleteButton = document.createElement('button');
//		deleteButton.innerHTML = 'X';
      Class_AddByElement(deleteButton, 'MultiSelect_Delete');

		// Delete function and data it will use
		deleteButton.onclick = MultiSelect.OnDelete;
      deleteButton.setAttribute('MultiSelect_Name', targetSelect.name);   // save the name so we can find the target later
      deleteButton.setAttribute('MultiSelect_SelectedItem', selectedItem);// save the selected item so we can unselect it later

		// Set row value
        var _selectedItem = targetSelect.options[selectedItem];
        // NOTE: I switch &nbsp; to spaces and then remove surrounding spaces in
        // order to remove the spacing that is around tree lists.
        var itemText = _selectedItem.innerHTML.replace(/\&nbsp\;/g, ' ').replace(/^\s+|\s+$/g, '');
        if (_selectedItem.getAttribute("fullvalue")) {
            itemText = _selectedItem.getAttribute("fullvalue");
        }
		groupingElement.innerText = itemText;

//		// Create an element that will create a bit of space between this and the delete button
//      var spaceElement = document.createElement('span');
//      spaceElement.innerHTML = "&nbsp;";
//      groupingElement.appendChild(spaceElement);

		// Add button
		groupingElement.appendChild(deleteButton);

//		// Create an element that will create a bit of space between this and the next item (for horizontal layout)
//      spaceElement = document.createElement('span');
//      spaceElement.innerHTML = "&nbsp;&nbsp;";
//      groupingElement.appendChild(spaceElement);

		// Add new element before the input item
      newSelectElement = MultiSelect.GetNewSelect(targetSelect);
      targetSelect.parentNode.insertBefore(groupingElement, newSelectElement);

      // Add space after the element, for spacing and also to allow line breaks to occur
      var spaceElement = document.createTextNode(' ');
      targetSelect.parentNode.insertBefore(spaceElement, newSelectElement);

      if (fireEvent)
         Utilities_FireEvent(targetSelect, 'change');
	},
	
	OnDelete: function()
	{
      var selectedItem = this.getAttribute('MultiSelect_SelectedItem');
      var targetSelectName = this.getAttribute('MultiSelect_Name');
      var targetSelect = Utilities_GetElementByName(targetSelectName, this.parentNode.parentNode);

		// Unselect the item
		targetSelect.options[selectedItem].selected = false;

		// Remove this row from the list
		this.parentNode.parentNode.removeChild(this.parentNode);

      Utilities_FireEvent(targetSelect, 'change');

		// Appease Safari
		//    without it Safari wants to reload the browser window
		//    which nixes your already queued uploads
		return false;
	},
   
   UnselectItem: function(targetSelect, selectedItem)
   {
      var newSelectItem = targetSelect.nextSibling;
      
      // Unselect the item
      targetSelect.options[selectedItem].selected = false;
   
      var children = targetSelect.parentNode.children;
      for (var i = 0; i < children.length; i++)
      {
         var temp = children[i].getAttribute('MultiSelect_SelectedItem');
         if (temp == selectedItem)
         {
            // Remove this row from the list
            targetSelect.parentNode.removeChild(children[i]);
   
//            Utilities_FireEvent(targetSelect, 'change');
         }
      }
   },
   
   GetTargetSelect: function(newSelect)
   {
      var name = newSelect.name.substr(12);   // strip 'MultiSelect_' prefix
      return Utilities_GetElementByName(name, newSelect.parentNode);
   },
   
   GetNewSelect: function(targetSelect)
   {
      var name = 'MultiSelect_' + targetSelect.name;
      return Utilities_GetElementByName(name, targetSelect.parentNode);
   },
   
   IsHorizontal: function(targetSelect)
   {
      return Class_HasByElement(targetSelect, 'MultiSelectHorizontal');
   },
   
   IsFilterSelect: function(targetSelect)
   {
      return /*Class_HasByElement(targetSelect, 'FilterSelect') && */ targetSelect.options.length > FilterSelect.MinimumOptions;
   },
   
	MakeMultiSelect: function(targetSelect)
	{
      if (targetSelect.disabled)
         return;

		//do not make multi select if it is in a template
		if (Utilities_HasClassAsParent(targetSelect, 'MultiItemTemplate'))
			return;

      var targetSelectName = targetSelect.name;
      
		var children = targetSelect.options;
		var useFilter = MultiSelect.IsFilterSelect(targetSelect);

		Visibility_HideByElement(targetSelect);

		// Create the actual element that will be shown
		var newSelectElement = document.createElement('SELECT');

		if (!useFilter)
      {
   		// Add an empty element to be initially selected so we can identify the change
   		var option = document.createElement('OPTION');
         var label = targetSelect.getAttribute('AddButtonLabel');
         if (label == null || label.length == 0) label = "Add...";
			option.innerHTML = label;         
         option.selected = true;
         option.disabled = true;
         option.value = null; // so this can be identified as an "invalid" option
   //      option.hidden = true;
   		newSelectElement.appendChild(option);
      }

		// The new select item will have all the same options as the old one
		for (var i = 0; i < targetSelect.children.length; i++)
		{
/*
			var option = document.createElement('OPTION');
         var value = children[i].innerHTML;

         // DRL FIXIT! We should refresh the list when switching between portrait and landscape mode!
         var maxLen = targetSelect.parentNode.offsetWidth / 10;   // DRL FIXIT? A bit of a hack!
         value = Utilities_ShortenWithCenterEllipsis(value, maxLen);

			option.innerHTML = value;
*/
         var option = targetSelect.children[i].cloneNode(true);   // copy nesting items such as <optgroup>
			newSelectElement.appendChild(option);
		}
		
      // Add a name so we can find this element (and indirectly the target element) by name
      newSelectElement.name = 'MultiSelect_' + targetSelectName;
      
		// What to do when an item is selected
		newSelectElement.onchange = function(){MultiSelect.OnChange(newSelectElement);};

		// Add new element after the current one
		targetSelect.parentNode.insertBefore(newSelectElement, targetSelect.nextSibling);

		// Initialize with currently selected items
		for (var i = 0; i < children.length; i++)
		{
			if (children[i].selected)
			{
				MultiSelect.OnSelectItem(targetSelect, i, false);   // don't fire changed event on init
			}
		}

// Roy added this, not sure why but it unselects the first item in the list when the page is shown.
//		if (targetSelect.selectedIndex == 0)
//			targetSelect.selectedIndex = -1;
      // Set the empty item as selected
      newSelectElement.selectedIndex = useFilter ? -1 : 0;

		if (useFilter)
		{
			// Convert to FilterSelect
			FilterSelect.MakeFilterSelect(newSelectElement);
		}
	}
}

DocumentLoad.AddCallback(MultiSelect.Init);

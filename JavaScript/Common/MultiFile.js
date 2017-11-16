// ========================================================================
//        Copyright © 2008 Dominique Lacerte, All Rights Reserved.
// 
// Redistribution and use in source and binary forms are prohibited without 
//   prior written consent from Dominique Lacerte (internet@lacerte.org).
// ========================================================================

// Convert a single file-input element into a 'multiple' input list
// Usage:
//
//	<INPUT type="file" id="first_file_element" name="element0">
//	<SCRIPT type="text/javascript">new MultiFile(Utilities_GetElementById('first_file_element'), 3);</SCRIPT>
//
// Note above that the element name must end in 0 and that subsequent elements will be named Xxx1, Xxx2 etc. up
// to the maximum specified - 1.

// DRL FIXIT? I believe this can now be replaced with the generic MultiItem?
function MultiFile( fileElement, max )
{
	// Make sure it's a file input element
	if( fileElement.tagName == 'INPUT' && fileElement.type == 'file' )
	{
		// How many elements?
		this.count = 0;
		// How many elements?
		this.id = 0;
		// Is there a maximum?
		if( max )
		{
			this.max = max;
		}
		else
		{
			this.max = -1;
		};

		// strip the number from the name to get the base name for all elements
		var i = strrpos(fileElement.name, "0");
		if (i === FALSE) i = -1;
		this.elementName = substr(fileElement.name, 0, i);
		fileElement.name = this.elementName;	// number will be added back to name in addElement()

		// Where to write the list
		this.list_target = document.createElement('DIV');
		fileElement.parentNode.insertBefore(this.list_target, fileElement);

		// Add a new file input element
		this.addElement = function( element )
		{
			// Element name -- what number am I?
			element.name = element.name + this.id++;

			// Add reference to this object
			element.multi_file = this;

			// What to do when a file is selected
			element.onchange = function()
			{
				// New file input
				var new_element = document.createElement( 'input' );
				new_element.type = 'file';
				new_element.name = this.multi_file.elementName;

				// Add new element
				this.parentNode.insertBefore( new_element, this );

				// Apply 'update' to element
				this.multi_file.addElement( new_element );

				// Update list
				this.multi_file.addListRow( this );

				// Hide this: we can't use display:none because Safari doesn't like it
				this.style.position = 'absolute';
				this.style.left = '-1000px';

			};

			// If we've reached maximum number, disable input element
			if( this.max != -1 && this.count >= this.max )
			{
				element.disabled = true;
			}

			// File element counter
			this.count++;

			// Most recent element
			this.current_element = element;
		};

		/**
		 * Add a new row to the list of files
		 */
		this.addListRow = function( element )
		{
			// Row div
			var new_row = document.createElement( 'div' );

			// Delete button
			var new_row_button = document.createElement( 'input' );
			new_row_button.type = 'button';
			new_row_button.value = 'X';

			// References
			new_row.element = element;

			// Delete function
			new_row_button.onclick= function()
			{
				// Remove element from form
				this.parentNode.element.parentNode.removeChild( this.parentNode.element );

				// Remove this row from the list
				this.parentNode.parentNode.removeChild( this.parentNode );

				// Decrement counter
				this.parentNode.element.multi_file.count--;

				// Re-enable input element (if it's disabled)
				this.parentNode.element.multi_file.current_element.disabled = false;

				// Appease Safari
				//    without it Safari wants to reload the browser window
				//    which nixes your already queued uploads
				return false;
			};

			// Set row value
			new_row.innerHTML = element.value;

			// Add button
			new_row.appendChild( new_row_button );

			// Add it to the list
			this.list_target.appendChild( new_row );
		};

		this.addElement( fileElement );
	}
	else
	{
		alert( 'Error: not a file input element' );
	}
}
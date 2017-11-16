// ========================================================================
//        Copyright ? 2008 Dominique Lacerte, All Rights Reserved.
// 
// Redistribution and use in source and binary forms are prohibited without 
//   prior written consent from Dominique Lacerte (internet@lacerte.org).
// ========================================================================

//	Convert a long SELECT control into one that accepts text input to filter 
//  the listed options.
//	Usage:
//
//	<SELECT name='Whatever' class='FilterSelect'>
//		<OPTION>Web Authoring Reference</OPTION>
//		<OPTION>FAQ Archives</OPTION>
//		<OPTION>Feature Article</OPTION>
//			lots of OPTIONs here...
//	</SELECT>


Key = 
{
	/**
	 * Enumeration for the backspace key code
	 * @type {Number}
	 * @static
	 */
	KEY_BACKSPACE: 8,
	/**
	 * Enumeration for the tab key code
	 * @static
	 */
	KEY_TAB:       9,
	/**
	 * Enumeration for the return/enter key code
	 * @static
	 */
	KEY_RETURN:   13,
	/**
	 * Enumeration for the escape key code
	 * @static
	 */
	KEY_ESC:      27,
	/**
	 * Enumeration for the left arrow key code
	 * @static
	 */
	KEY_LEFT:     37,
	/**
	 * Enumeration for the up arrow key code
	 * @static
	 */
	KEY_UP:       38,
	/**
	 * Enumeration for the right arrow key code
	 * @static
	 */
	KEY_RIGHT:    39,
	/**
	 * Enumeration for the down arrow key code
	 * @static
	 */
	KEY_DOWN:     40,
	/**
	 * Enumeration for the delete key code
	 * @static
	 */
	KEY_DELETE:   46,
	/**
	 * Enumeration for the shift key code
	 * @static
	 */
	KEY_SHIFT:    16,
	/**
	 * Enumeration for the cotnrol key code
	 * @static
	 */
	KEY_CONTROL:  17,
	/**
	 * Enumeration for the capslock key code
	 * @static
	 */
	KEY_CAPSLOCK: 20,
	/**
	 * Enumeration for the space key code
	 * @static
	 */
	KEY_SPACE:	  32,

	/**
	 * A simple interface to get the key code of the key pressed based, browser sensitive.
	 * @param {Event} event They keyboard event
	 * @return {Number} the key code of the key pressed
	 */
	keyPressed: function(event)
	{
		return Browser.isIE ? window.event.keyCode : event.which;
	}
};

function filterselect(_elem)
{
	var 
	
	/**
	 * JavaScript callback function to execute upon selection
	 * @type {Function}
	 */
//	onchange: null,
	
	/**
	 * The select object
	 * @type {HTMLSelectElement}
	 */
	_selector = null,
	
	/**
	 * The component that triggers the suggest
	 * @type {HTMLInputElement}
	 */
	_input = null,
	
	/**
	 * The timeout between lookups
	 * @type {Number}
	 * @private
	 */
	_timeout = null,
	
	/**
	 * Visibility status of the selector object
	 * @type {Boolean}
	 */
	_visible = false,

	/**
	 * Flag indicating whether the components have been laid out
	 * @type {Boolean}
	 */
	_drawn = false,

	/**
	 * Hide timeout
	 * @type {Number}
	 * @private
	 */
	_hideTimeout = null,

	/**
	 * The original SELECT element
	 * @private
	 */
	_select = null,

	/**
	 * The configuration options for the instance
	 * @type {FilterSelect.Options}
	 */
	_options = 
	{
		/**
		 * Number of options to display before scrolling
		 * @type {Number}
		 */
		size: 10,
		/**
		 * CSS class name for autocomplete selector
		 * @type {String}
		 */
		cssClass: null,
		/**
		 * Minimum characters needed before an suggestion is executed
		 * @type {Number}
		 */
		threshold: 1,
		/**
		 * Time delay between key stroke and execution
		 * @type {Number}
		 */
		delay: .2,
		/**
		 * The request method to use when getting the suggestions
		 * @type {String}
		 */
	};

	/**
	 * @param {Object} input ID of form element, or dom element,  to _suggest on
	 */
	function initialize(elem)
	{
		_select = elem;

		// Create the actual element that will be shown
		_input = document.createElement('input');
		_input.type = "text";
		_input.name = "FilterSelect_Random_" + (new Date()).getTime();   // required in IE for the below to work
      _input.placeholder = "Type to search...";
      _input.setAttribute("autocomplete", "off");
		_input.onclick = function() { this.focus(); this.select(); };

		//include check for 0 as well since browsers set selectedIndex to 0 by default
		if (_select.selectedIndex != 0 &&_select.selectedIndex != -1)		
		{
			_input.value = _select.options[_select.selectedIndex].innerHTML;
			_input.select();
		}
		else
			_select.selectedIndex = -1; //-1 by default

		// a hidden element has no offsetWidth
      _input.style.width = Math.max(_select.offsetWidth, 125) + 'px';
		Utilities_InsertAfterNode(_select.parentNode, _input, _select);
		Visibility_HideByElement(_select);
		
		// Create the drop down selection list
		_selector = document.createElement('select');

		Utilities_AddEvent(_input, 'focus', _onInputFocus);
		//Utilities_AddEvent(_input, 'keyup', _onInputKeyUp);
		Utilities_AddEvent(_input, 'keydown', _onInputKeyDown);
		Utilities_AddEvent(_input, 'blur', _onInputBlur);
		Utilities_AddEvent(_selector, 'blur', _onSelectorBlur);
		Utilities_AddEvent(_selector, 'focus', _onSelectorFocus);
		Utilities_AddEvent(_selector, 'change', _onSelectorChange);

		Utilities_AddEvent(window, 'resize', _reposition);
		Utilities_AddEvent(window, 'scroll', _reposition);
	}

	/**
	 * The input fields focus event handler
	 */
	function _onInputFocus(event)
	{
		_onSelectorFocus(event);
	}

	/**
	 * The selector's blur event handler
	 * @param {Event} event
	 * @private
	 */
	function _onSelectorBlur(event)
	{
		_onInputBlur(event);
	}
	
	/**
	 * The input's blur event handler
	 * @param {Event} event
	 * @private
	 */
	function _onInputBlur(event)
	{
		_hideTimeout = setTimeout(_checkOnBlur, 100);
	}
	
	/**
	 * Complete's the blur event handlers. Used as a proxy to avoid event collisions when blurring from the input
	 * and focusing on the selector during a mouse navigation
	 * @private
	 */
	function _checkOnBlur()
	{
		_hideTimeout = null
		hide();
		
		// when the focus leaves the control show the selected item (if any)
		if (_select.selectedIndex == -1)
			_input.value = '';
		else
			_input.value = _select.options[_select.selectedIndex].innerHTML;
	}
	
	/**
	 * The input's key-up event handler
	 * @param {Event} event
	 * @private
	 */
	function _onInputKeyUp(event)
	{
		if (_suggest(event))
      {
			Utilities_StopEventPropagation(event);
			Utilities_PreventDefaultForEvent(event);
      }
	}
	
	/**
	 * The input's key-down event handler
	 * @param {Event} event
	 * @private
	 */
	function _onInputKeyDown(event)
	{
		_input = Utilities_GetEventTarget(event);
		if (_suggest(event))
      {
			Utilities_StopEventPropagation(event);
			Utilities_PreventDefaultForEvent(event);
      }
	}
	
	/**
	 * The selectors's focus event handler.
	 * @param {Event} event
	 * @private
	 */
	function _onSelectorFocus(event)
	{
		if (_hideTimeout)
		{
			clearTimeout(_hideTimeout);
			_hideTimeout = null;
		}
	}
	
	/**
	 * The selector's change event handler
	 * @param {Event} event
	 * @private
	 */
	function _onSelectorChange(event)
	{
		select();
	}
	
	/**
	 * Lays the UI elements of the control out, sets interaction options
	 * @param {Object} event Event
	 */
	function draw()
	{
		if (_drawn) return;
		if (_options.cssClass)
			_selector.className = _options.cssClass;
      _selector.style.display = 'none';
      _selector.style.position = 'absolute';
		// a hidden element has no offsetWidth
      _selector.style.width = Math.max(_input.offsetWidth, 125) + 'px';

		_selector.size = _options.size;
		document.body.appendChild(_selector);
		_drawn = true;
	}

	/**
	 * Hides the option box
	 */
	function hide()
	{
		if (!_drawn || !_visible) return;
		_visible = false;
      _selector.style.display = 'none';
		_selector.options.length = 0;
		// FF hack, wasn't selecting without this small delay for some reason
		//restore focus is not required. it messes with tabbing.
		//setTimeout(_restoreFocus,50);
	}
	
	/**
	 * Resores the focus to the input control to avoid the cursor getting lost somewhere.
	 * @private
	 */
	function _restoreFocus()
	{
		_input.focus();
	}
	
	/**
	 * Displays the select box
	 */
	function show()
	{
		if (!_drawn) draw();
		var trigger = null;
		if (_selector.options.length)
		{
         _selector.style.display = 'inline';
			_reposition();
			_visible = true;
		}
	}

	/**
	 * Removes the timeout function set by a suggest
	 * @private
	 */
	function _cancelTimeout()
	{
		if (_timeout)
		{
			clearTimeout(_timeout);
			_timeout = null;
		}
	}

	/**
	 * Triggers the suggest interaction
	 * @param {Object} event The interaction event (keyboard or mouse)
	 * @return {Boolean} Whether to stop the event
	 * @private
	 */
	function _suggest(event)
	{
		_cancelTimeout();
		var key = Key.keyPressed(event);
		var ignoreKeys = [
			20, // caps lock
			16, // shift
			17, // ctrl
			91, // Windows key
			121, // F1 - F12
			122,
			123,
			124,
			125,
			126,
			127,
			128,
			129,
			130,
			131,
			132,
			45, // Insert
			36, // Home
			35, // End
			33, // Page Up
			34, // Page Down
			144, // Num Lock
			145, // Scroll Lock
			44, // Print Screen
			19, // Pause
			93, // Mouse menu key
		];
		if (ignoreKeys.indexOf(key) > -1)
			return false;

		switch(key)
		{
			case Key.KEY_LEFT:
			case Key.KEY_RIGHT:
				return false;
				break;
			case Key.KEY_TAB:
				if (_visible)
				{
					//do not return true for Tab to allow the default tab behavior
					select();					
				}
				return false;
				break;
//			case Key.KEY_BACKSPACE:
//			case 46: //Delete
				//cancel();
				//return false;
				//break;
			case Key.KEY_RETURN:
				if (_visible)
				{
					select();
					return true;
				}
				return false;
				break;
			case Key.KEY_ESC:
				cancel();
				return true;
				break;
			case Key.KEY_UP:
			case Key.KEY_DOWN:
				_interact(event);
				return true;
				break;
			default:
				break;
		}

//		if (_input.value.length >= _options.threshold - 1)
		{
			_timeout = setTimeout(_updateSelector, 1000 * _options.delay);
		}
			
		return false;
	}

	/**
	 * Sends the suggestion request
	 * @private
	 */
	function _updateSelector()
	{
		_selector.options.length = 0;

		if (_input.value.length >= _options.threshold)
		{
			var children = _select.options;
			var str = new RegExp(_input.value, 'i');
			for (var i = 0; i < children.length; i++)
			{
				// clean the extra padding added for nested elements
				var label = children[i].innerHTML.replace(/\&nbsp\;/g, ' ').replace(/^\s+|\s+$/g, '');
				// use fullvalue if available
		        if (children[i].getAttribute("fullvalue")) {
		            label = children[i].getAttribute("fullvalue");
		        }
				if (label.search(str) != -1)
				{
					var value = typeof children[i].value !== 'undefined' ? children[i].value : label;
					_addOption(value, label);
				}
			}
		}

		if (_selector.options.length > (_options.size))
			_selector.size = _options.size;
		else
			_selector.size = _selector.options.length > 1 ? _selector.options.length : 2;

		if (_selector.options.length)
		{
			//none selected by default
			_selector.selectedIndex = -1;
			show();
		}
		else
			cancel();
	}

	/**
	 * Repositions the selector (if visible) to match the new
	 * coords of the input.
	 * @private
	 */
	function _reposition()
	{
		if (!_drawn) return;
      var pos = Utilities_GetOffset(_input);
      _selector.style.left =  pos.left + 'px';
      _selector.style.top = (pos.top + _input.offsetHeight) + 'px';
	}

	/**
	 * Creates a suggestion option for the given suggestion,
	 * adds it to the selector object.
	 * @param {String} suggestion The suggestion
	 */
	function _addOption(value, label)
	{
		var opt = new Option(label, value);
		Browser.isIE ? _selector.add(opt) : _selector.add(opt, null);
	}

	/**
	 * Clears and hides the suggestion box.
	 */
	function cancel()
	{
		hide();
	}

	/**
	 * Captures the currently selected suggestion option to the input field
	 */
	function select()
	{
		//check if there are any options and also if any option was selected
		if (_selector.options.length && _selector.selectedIndex >= 0)
		{
			var label = _selector.options[_selector.selectedIndex].innerHTML;
			var value = typeof _selector.options[_selector.selectedIndex].value !== 'undefined'
				? _selector.options[_selector.selectedIndex].value : label;
			
			_input.value = label;
			_input.select();
			
			var children = _select.options;
			var str = _input.value;
			for (var i = 0; i < children.length; i++)
			{
				if ((typeof children[i].value !== 'undefined' && children[i].value == value) ||
					(typeof children[i].value === 'undefined' && children[i].innerHtml == label))
				{
					_select.selectedIndex = i;
					break;
				}
			}
		}
		cancel();
		if (typeof _select.onchange == 'function')
		{
			// the onchange doesn't fire until the element loses focus
			// so we call it here
			_select['onchange'](_select);
		}
//		if (typeof onchange == 'function')
//		{
//			this['onchange'](_input);
//		}
		// MultiSelect changes the value of the selected item after we
		// change it so we need to check and update our edit box
		if (_select.selectedIndex == -1)
			_input.value = '';
		else
			_input.value = _select.options[_select.selectedIndex].innerHTML;
		_input.select();
	}

	/**
	 * Processes key interactions with the input field, including navigating the selected option
	 * with the up/down arrows, esc cancelling and selecting the option.
	 * @param {Event} event The interaction event
	 * @private
	 */
	function _interact(event)
	{
		if (!_visible) return;

		var key = Key.keyPressed(event);
		if (key != Key.KEY_UP && key != Key.KEY_DOWN) return;
		var mx = _selector.options.length;

		if (key == Key.KEY_UP)
		{
			if (_selector.selectedIndex == 0)
				_selector.selectedIndex = _selector.options.length - 1;
			else
				_selector.selectedIndex--;
		}
		else
		{
			if (_selector.selectedIndex == _selector.options.length - 1)
				_selector.selectedIndex = 0;
			else
				_selector.selectedIndex++;
		}
	}

	
	return (function() {
		initialize(_elem);
	})();
};

FilterSelect =
{
   MinimumOptions: 30,
   
	Init: function()
	{
		forEach(Utilities_GetElementsByTag('select'), function(elem)
		{
			if (Class_HasByElement(elem, 'FilterSelect'))
			{
				//do not make filter select if its a template
				if(!Utilities_HasClassAsParent(elem, 'MultiItemTemplate'))
				{
					FilterSelect.MakeFilterSelect(elem);
				}
			}
		});
	},

	/**
	 * @param {Object} input ID of form element, or dom element,  to _suggest on
	 */
	MakeFilterSelect: function(elem)
	{
		//do not make filter select if its a template
		if (elem.options.length > FilterSelect.MinimumOptions && !Utilities_HasClassAsParent(elem, 'MultiItemTemplate'))	// don't bother for small lists
		{
			new filterselect(elem);
		}
	}
}

DocumentLoad.AddCallback(FilterSelect.Init);

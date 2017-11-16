// ========================================================================
//        Copyright (c) 2010 Dominique Lacerte, All Rights Reserved.
// 
// Redistribution and use in source and binary forms are prohibited without 
//   prior written consent from Dominique Lacerte (internet@lacerte.org).
// ========================================================================

var TRUE = true;
var FALSE = false;

// array-like enumeration
if (!Array.forEach)	// mozilla already supports this
{
	Array.forEach = function(array, block, context)
	{
		for (var i = 0; i < array.length; i++)
		{
			block.call(context, array[i], i, array);
		}
	};
}

// generic enumeration
Function.prototype.forEach = function(object, block, context)
{
	for (var key in object)
	{
		if (typeof this.prototype[key] == "undefined")
		{
			block.call(context, object[key], key, object);
		}
	}
};

// character enumeration
String.forEach = function(string, block, context)
{
	Array.forEach(string.split(""), function(chr, index)
	{
		block.call(context, chr, index, string);
	});
};

// globally resolve forEach enumeration
var forEach = function(object, block, context)
{
	if (object)
	{
		var resolve = Object; // default
		if (object instanceof Function)
		{
			// functions have a "length" property
			resolve = Function;
		}
		else if (object.forEach instanceof Function)
		{
			// the object implements a custom forEach method so use that
			object.forEach(block, context);
			return;
		}
		else if (typeof object == "string")
		{
			// the object is a string
			resolve = String;
		}
		else if (typeof object.length == "number")
		{
			// the object is array-like
			resolve = Array;
		}
		resolve.forEach(object, block, context);
	}
};

if (!Array.prototype.indexOf)
{
	Array.prototype.indexOf = function(elt /*, from*/)
	{
		var len = this.length;
		var from = Number(arguments[1]) || 0;
		from = (from < 0) ? Math.ceil(from) : Math.floor(from);
		if (from < 0) from += len;		
		for (; from < len; from++)
		{
			if (from in this && this[from] === elt)
				return from;
		}
		return -1;
	};
}

var falsy = /^(?:f(?:alse)?|no?|0+)$/i;
function parseBool(val)
{ 
   return !falsy.test(val) && !!val;
}

function isset(value)
{
	return value !== undefined && value !== null;
}

function is_array(value)
{
	return typeof(value) == 'object' && (value instanceof Array);
}

function strlen(str)
{
	return str.length;
}

function strpos(haystack, needle, offset)
{
	var i = (haystack + '').indexOf(needle, (offset || 0));
	return i === -1 ? false : i;
}

function strrpos(haystack, needle, offset)
{
	var i = -1;
	if (offset)
	{
		i = (haystack + '').slice(offset).lastIndexOf(needle); // strrpos' offset indicates starting point of range till end,
		// while lastIndexOf's optional 2nd argument indicates ending point of range from the beginning
		if (i !== -1) i += offset;
	}
	else
		i = (haystack + '').lastIndexOf(needle);
	return i >= 0 ? i : false;
}

function substr(str, ind, len)
{
	return str.substr(ind, len);
}

function strtolower(str)
{
	return str.toLowerCase();
}

function strtoupper(str)
{
	return str.toUpperCase();
}

function strcmp(str1, str2)
{
	return (str1 == str2) ? 0 : ((str1 > str2) ? 1 : -1);
}

function empty(value)
{
	return !isset(value) || value.length == 0;
}

String.prototype.isEmpty = function()
{
   return (this.length == 0 || !this.trim());
}

function innerHTML(elem)
{
	value = elem.innerHTML;
	
	if (isset(value))
	{
		// replace any tags because on mobile devices you'll sometimes see <a href="tel:1234567">1234567</a>
		var regex = /(<([^>]+)>)/ig;
		value = value.replace(regex, "");
	}
	
	return value;
}

function Utilities_Any(array, callback)
{
//   if (array == null || !is_array(array)) { Log_Die("No array!"); }
      
	for (var i = 0; i < array.length; i++)
	{
		if (callback(array[i]))
         return true;
	}
   return false;
}

function Utilities_All(array, callback)
{
//   if (array == null || !is_array(array)) { Log_Die("No array!"); }
      
	for (var i = 0; i < array.length; i++)
	{
		if (!callback(array[i]))
         return false;
	}
   return true;
}

// NOTE: the callback will be passed an event ID, need to call Utilities_GetEvent() to get the actual event!
function Utilities_AddEvent(object, type, callback)
{
   if (object == null || typeof(object) == 'undefined')
      return;
   
   if (object.addEventListener)
   {
      object.addEventListener(type, callback, false);
   }
   else if (object.attachEvent)
   {
      object.attachEvent("on" + type, callback);
   }
   else
   {
      object["on" + type] = callback;
   }
}

function Utilities_RemoveEvent(object, type, callback)
{
   if (object == null || typeof(object) == 'undefined')
      return;
   
   if (object.removeEventListener)
   {
      object.removeEventListener(type, callback, false);
   }
   else if (object.detachEvent)
   {
      object.detachEvent("on" + type, callback);
   }
   else
   {
      object["on" + type] = null;
   }
}

function Utilities_FireEvent(object, type)
{
   if (object == null || typeof(object) == 'undefined')
      return;
      
   if (typeof window.Event == "function")
   {
      var evt = new Event(type);
      if (evt == null)
      {
         Log_Die('Unsupported event type: ' + type);
         return;
      }
      object.dispatchEvent(evt);
   }
   else if ("createEvent" in document)
   {
      var evt = document.createEvent("Event");
      evt.initEvent(type, false, true);
      element.dispatchEvent(evt);
   }
   else
   {
      element.fireEvent("on" + type);
   }
}

// some browsers don't pass the event into the callback so we must use this helper
function Utilities_GetEvent(e)
{
   return e || window.event;
}

function Utilities_GetEventTarget(e)
{
   var event = Utilities_GetEvent(e);
   return event.currentTarget || event.srcElement || event.target;
}

function Utilities_StopEventPropagation(e)
{
   var event = Utilities_GetEvent(e);
   
   //IE9 & Other Browsers
   if (event.stopPropagation)
   {
      event.stopPropagation();
   }
   //IE8 and Lower
   else
   {
      event.cancelBubble = true;
   }
}

function Utilities_PreventDefaultForEvent(e)
{
   var event = Utilities_GetEvent(e);
   
   //IE9 & Other Browsers
   if (event.preventDefault)
   {
      event.preventDefault();
   }
   //IE8 and Lower
   else
   {
      event.defaultPrevented = true;
   }
}

function Utilities_GetElementById(id)
{
	var result;
	if (document.getElementById)			// this is the way the standards work
		result = document.getElementById(id);
	if (!result && document.all)			// this is the way old msie versions work
		result = document.all[id];
	else if (!result && document.layers)	// this is the way nn4 works
		result = document.layers[id];
	return result;
}

function Utilities_GetElementsByClass(className, tagName, root)
{
	if (root == null) root = document;
	if (tagName == null) tagName='*';
	
	var results = [];
	var elem = Utilities_GetElementsByTag(tagName, root);
	for (var i = 0; i < elem.length; i++)
	{
		if (Class_HasByElement(elem[i], className))
			results.push(elem[i]);
	}
	return results;
}

function Utilities_GetElementsByTag(tagName, root)
{
   var manualSearch = false;
   var top = root;
	if (root == null)
   {
		top = document;
   }
	else if (typeof top.getElementsByTagName != 'function')
   {
      top = document;
      manualSearch = true;
   }
	
	var elems = Array.from(top.getElementsByTagName(tagName));
	if (manualSearch)
   {
      for (var i = 0; i < elems.length; i++)
      {
         if (!Utilities_HasAsParent(elems[i], root))
         {
            elems.splice(i, 1);
            i--;
         }
      }
   }
   return elems;
}

function Utilities_GetElementByName(name, root)
{
	var elems = Utilities_GetElementsByTag('*', root);
	for (var i = 0; i < elems.length; i++)
	{
		if (elems[i].name == name)
			return elems[i];
	}
	return null;
}

function Utilities_GetElementsByName(name, root)
{
   var result = [];
   var elems = Utilities_GetElementsByTag('*', root);
	for (var i = 0; i < elems.length; i++)
	{
		if (elems[i].name == name)
			result.push(elems[i]);
	}
	return result;
}

function Utilities_GetElement(item)
{
   if (typeof item == 'string' || item instanceof String)
   {
      if (item.substring(0,1) == '#')
         item = item.substring(1);
   	var elem = Utilities_GetElementById(item);
      if (elem == null)
   	   elem = Utilities_GetElementByName(item);
      assert(elem != null);
      item = elem;
   }
   
   return item;
}

function Utilities_GetThisOrParentByTag(elem, tagName)
{
   while (elem)
   {
      if (elem.tagName == tagName)
         return elem;
      elem = elem.parentElement;
   }
   return null;
}

function Utilities_GetParentByTag(elem, tagName)
{
   while (elem)
   {
      elem = elem.parentElement;
      if (elem != null && elem.tagName == tagName)
         return elem;
   }
   return null;
}

// NOTE: className can be an array
function Utilities_GetParentByClass(elem, className)
{
   if (Object.prototype.toString.call(className) !== '[object Array]')
      className = [className];
   var result = null;
   while (elem && !result)
   {
      elem = elem.parentElement;
      if (elem != null)
      {
         forEach(className, function(name)
         {
            if (Class_HasByElement(elem, name))
               result = elem;
         });
      }
   }
   return result;
}

// NOTE: className can be an array
function Utilities_GetThisOrParentByClass(elem, className)
{
   if (Object.prototype.toString.call(className) !== '[object Array]')
      className = [className];
   var result = null;
   while (elem && !result)
   {
      forEach(className, function(name)
      {
         if (Class_HasByElement(elem, name))
            result = elem;
      });
      elem = elem.parentElement;
   }
   return result;
}

function Utilities_SetNodeText(node, text)
{
   while (node.firstChild)
   {
       node.removeChild(node.firstChild);
   }
   node.appendChild(document.createTextNode(text));
}

function Utilities_CreateHtmlNode(htmlStr)
{
	var frag = document.createDocumentFragment(),
	temp = document.createElement('div');
	temp.innerHTML = htmlStr;
	while (temp.firstChild)
	{
		frag.appendChild(temp.firstChild);
	}
	return frag;
}

function Utilities_CloneNode(orgNode)
{
   var orgNodeEvents = Utilities_GetElementsByTag('*', orgNode);
   var cloneNode = orgNode.cloneNode( true );
   var cloneNodeEvents = Utilities_GetElementsByTag('*', cloneNode);

   var allEvents = new Array('onabort','onbeforecopy','onbeforecut','onbeforepaste','onblur','onchange','onclick',
      'oncontextmenu','oncopy','ondblclick','ondrag','ondragend','ondragenter', 'ondragleave' ,
      'ondragover','ondragstart', 'ondrop','onerror','onfocus','oninput','oninvalid','onkeydown',
      'onkeypress', 'onkeyup','onload','onmousedown','onmousemove','onmouseout',
      'onmouseover','onmouseup', 'onmousewheel', 'onpaste','onreset', 'onresize','onscroll','onsearch', 'onselect','onselectstart','onsubmit','onunload');


   // The node root
   for (var j = 0; j < allEvents.length ; j++)
   {
      eval('if (orgNode.'+allEvents[j]+') cloneNode.'+allEvents[j]+' = orgNode.'+allEvents[j]);
   }

   // Node descendants
   for (var i = 0; i < orgNodeEvents.length; i++)
   {
      for (var j = 0; j < allEvents.length; j++)
      {
         eval('if (orgNodeEvents[i].'+allEvents[j]+') cloneNodeEvents[i].'+allEvents[j]+' = orgNodeEvents[i].'+allEvents[j]);
      }
   }

   return cloneNode;
}

function Utilities_InsertAfterNode(parent, node, referenceNode)
{
	parent.insertBefore(node, referenceNode.nextSibling);
}

function Utilities_InsertBeforeNode(parent, node, referenceNode)
{
	parent.insertBefore(node, referenceNode);
}

function Utilities_Div(numerator, denominator)
{
	var remainder = Math.abs(numerator) % Math.abs(denominator);
	if (numerator * denominator < 0) { remainder = -remainder; }
	var quotient = (numerator - remainder) / denominator;
	return quotient;
}

function Utilities_RemoveQuotes(str)
{
	return Utilities_ReplaceInString(str, '"', '');
}

function Utilities_RemoveSurroundingSpaces(str)
{
	var expr = new RegExp(/^[\r\n\t ]+/g);
	str = str.replace(expr,"");
	expr = new RegExp(/[\r\n\t ]+$/g);
	return str.replace(expr,"");
}

function Utilities_RemoveSurroundingAngleBrackets(str)
{
	var expr = new RegExp(/^</g);
	str = str.replace(expr,"");
	expr = new RegExp(/>/g);
	return str.replace(expr,"");
}

function Utilities_NormalizeSpaces(str)
{
	str = RemoveSurroundingSpaces(str);
	var expr = new RegExp(/[\r\n\t ]+/g);
	return str.replace(expr," ");
}

function Utilities_CombineStrings(separator, item1, item2)
{
	if (!empty(item1) && !empty(item2))
		return item1 + separator + item2;
	if (!empty(item2))
		return item2;
	return item1;
}

function Utilities_ShortenWithEllipsis(str, len)
{
	if (str.length <= len)
		return str;
	return Utilities_RemoveSurroundingSpaces(substr(str, 0, len-3)) + '...';
}

function Utilities_ShortenWithCenterEllipsis(str, len)
{
	if (str.length <= len)
		return str;
	len -= 3;
   i = Utilities_Div(len, 2);
	return Utilities_RemoveSurroundingSpaces(substr(str, 0, i)) + '...' + 
      Utilities_RemoveSurroundingSpaces(substr(str, str.length-i));
}

function Utilities_ReplaceInString(str, find, replace)
{
	find = Utilities_RegexpEscape(find);
	var expr = new RegExp(find,'g');
	return str.replace(expr,replace);
}

function Utilities_ReplaceCharsInString(str, findChars, replace)
{
	findChars = Utilities_RegexpEscape(findChars);
	var expr = new RegExp("[" + findChars + "]",'g');
	return str.replace(expr,replace);
}

function Utilities_StringContains(string, value)
{
	value = Utilities_RegexpEscape(value);
	var expr = new RegExp(value);
	return expr.test(string);
}

function Utilities_StringContainsAny(string, array)
{
	for (var i = 0; i < array.length; i++)
	{
		var value = Utilities_RegexpEscape(array[i]);
		var expr = new RegExp(value);
		if (expr.test(string))
		{
			return 1;
		}
	}
	return 0;
}

function Utilities_SortArray(ref_array, ignoreCase)
{
	if (!Utilities_empty(ignoreCase) && ignoreCase)
	{
		ref_array.sort(function(x,y){ 
			var a = strtoupper(String(x)); 
			var b = strtoupper(String(y)); 
			if (a > b) 
				return 1 
			if (a < b) 
				return -1 
			return 0; 
    	});
	}
	else
	{
		ref_array.sort();
	}
}

function Utilities_OrderArray(ref_array)
{
	ref_array.sort(function(a,b){return a-b});
}

function Utilities_StringToArray(str, seperator)
{
	if (empty(seperator)) separator = ',';
	if (empty(str)) return new Array();
	return str.split(seperator);
}

function Utilities_ConcatArrays()
{
	var result = arguments[0].slice();	// make a copy of the first array
	
	// isn't there a more efficient way then pushing each individual item?
	for (var i = 1; i < arguments.length; i++)
	{
		for (var j = 0; j < arguments[i].length; j++)
		{
			result.push(arguments[i][j]);	// push each element from the other arrays
		}
	}
	
	return result;
}

function Utilities_ArrayEquals(array1, array2, ignoreOrder, ignoreCase)
{
	if (ignoreOrder == null) { ignoreOrder = 0; }
	if (ignoreCase == null) { ignoreCase = 0; }
	
	if (array1.length != array2.length)
	{
		return 0;
	}
	
	if (ignoreOrder)
	{
		var temp = array2.slice();	// make a copy
		
		for (var j = 0; j < array1.length; j++)
		{
			var item = array1[j];
			var i;
			var found = 0;
			
			for (i = 0; i < temp.length; i++)
			{
				if (Utilities_ValuesEqualOrBothUndef(temp[i], item, ignoreOrder, ignoreCase))
				{
					found = 1;
					break;
				}
			}

			if (found)
			{
				temp.splice(i, 1);
			}
			else
			{
				return 0;
			}
		}
	}
	else
	{
		for (var i = 0; i < array1.length; i++)
		{
			if (!Utilities_ValuesEqualOrBothUndef(array1[i], array2[i], ignoreOrder, ignoreCase))
			{
				return 0;
			}
		}
	}
	
	return 1;
}

function Utilities_ArrayContains(array, value, ignoreCase)
{
	if (ignoreCase == null) { ignoreCase = 0; }

	if (array == null) { Log_Die("No array!"); }

	for (var i = 0; i < array.length; i++)
	{
		var item = array[i];
		if (Utilities_ValuesEqualOrBothUndef(item, value, 0, ignoreCase))
		{
			return 1;
		}
	}
	
	return 0;
}

function Utilities_ArraysMeet(ref_array1, ref_array2, ignoreCase)
{
	if (ignoreCase == null) { ignoreCase = 0; }
	
	for (var i = 0; i < ref_array1.length; i++)
	{
		var item1 = ref_array1[i];
		for (var j = 0; j < ref_array2.length; j++)
		{
			var item2 = ref_array2[j];
			if (Utilities_ValuesEqualOrBothUndef(item1, item2, 0, ignoreCase))
			{
				return 1;
			}
		}
	}
	
	return 0;
}

function Utilities_ArrayIndexOf(ref_array, value)
{
	for (i = 0; i < ref_array.length; i++)
	{
		if (ValuesEqualOrBothUndef(ref_array[i], value))
		{
			return i;
		}
	}
	
	return -1;
}

function Utilities_MergeIntoArray(ref_array1, ref_array2)
{
	for (var i = 0; i < ref_array2.length; i++)
	{
		var item = ref_array2[i];
		if (!Utilities_ArrayContains(ref_array1, item))
		{
			ref_array1.push(item);
		}
	}
}

function Utilities_UnionArrays(ref_array1, ref_array2)
{
	var result = new Array();
	for (var i = 0; i < ref_array2.length; i++)
	{
		var item = ref_array2[i];
		if (Utilities_ArrayContains(ref_array1, item))
		{
			result.push(item);
		}
	}
	return result;
}

function Utilities_ArrayEquals(ref_array1, ref_array2, ignoreOrder, ignoreCase)
{
	if (ignoreOrder = null) { ignoreOrder = 0; }
	if (ignoreCase == null) { ignoreCase = 0; }
	
	if (ref_array1.length != ref_array2.length)
	{
		return 0;
	}
	
	if (ignoreOrder)
	{
		var temp = ref_array2;
		
		for (var x = 0; x < ref_array1.length; x++)
		{
			var item = ref_array1[x];
			var i;
			var found = 0;
			
			for (i = 0; i < temp.length; i++)
			{
				if (Utilities_ValuesEqualOrBothUndef(temp[i], item, ignoreOrder, ignoreCase))
				{
					found = 1;
					break;
				}
			}

			if (found)
			{
				temp.splice(i, 1);
			}
			else
			{
				return 0;
			}
		}
	}
	else
	{
		for (i = 0; i < ref_array1.length; i++)
		{
			if (!Utilities_ValuesEqualOrBothUndef(ref_array1[i], ref_array2[i], ignoreOrder, ignoreCase))
			{
				return 0;
			}
		}
	}
	
	return 1;
}

// avoids adding duplicates
function Utilities_AddToArray(array, item)
{
	if (!Utilities_ArrayContains(array, item))
	{
		array.push(item);
		return true;
	}
	
	return false;
}

function Utilities_RemoveFromArray(array, value)
{
	var count = 0;
	
	for (var i = 0; i < array.length; i++)	
	{
		if (Utilities_ValuesEqualOrBothUndef(array[i], value))
		{
			array.splice(i, 1);
			i--;
			count++;
		}
	}
	
	return count;
}

function Utilities_ValuesEqualOrBothUndef(item1, item2, ignoreOrder, ignoreCase)
{
	if (ignoreOrder == null) { ignoreOrder = 0; }
	if (ignoreCase == null) { ignoreCase = 0; }
	
	if (item1 == null && item2 == null)
		return true;
	if (item1 == null || item2 == null)
		return false;
	
	if (typeof item1 == "object" && typeof item2 == "object")
	{
		return Utilities_ArrayEquals(item1, item2, ignoreOrder, ignoreCase);
	}
	if (typeof item1 == "object" || typeof item2 == "object")
	{
		return 0;
	}
	
	if (ignoreCase)
	{
		item1 = strtoupper(item1);
		item2 = strtoupper(item2);
	}
	return item1 == item2;
}

function Utilities_RegexpEscape(str)
{
	var chars = ["\\", '.', '[', '^', "\x00", '|', '*', '+', '-', '?', '('];
	
	for (var i = 0; i < chars.length; i++)
	{
		var ch = chars[i];
		ch = "\\" + ch;
		var expr = new RegExp(ch,'g');
		str = str.replace(expr,ch);
	}
	
	return str;
}

function Utilities_IsAlphaNumeric(value)
{
	var expr = /^[0-9a-zA-Z\s]+/;
	return expr.test(value);
}

function Utilities_IsInteger(value)
{
	var expr = /^\-?[0-9]+/;
	return expr.test(value);
}

function Utilities_IsNumeric(value)
{
	var expr = /^\-?[0-9]*\.?[0-9]+/;
	return expr.test(value);
}

function Utilities_IsAlphabetic(value)
{
	var expr = /^[a-zA-Z]+/;
	return expr.test(value);
}

function Utilities_IsWhiteSpace(value)
{
	var expr = /^[\s\r\n\t]+/;
	return expr.test(value);
}

function Utilities_IsArray(value)
{
	if (strpos(value.constructor.toString(), "Array") === FALSE)
		return false;
	else
		return true;
}

function Utilities_IsString(value)
{
	// test for string native type
	if (typeof value == 'string')
		return true;
	// test for object wrapping a string native type
	if (typeof value == 'object' && value.constructor.toString().match(/string/i) != null)
		return true;
	return false;
}

function Utilities_IsObject(value)
{
  return Object.prototype.toString.call(value) === '[object Object]';
}

function Utilities_Chomp(str)
{
  return str.replace(/(\n|\r)+/, '');
}

// returns the elements ID, giving it one if it doesn't have one
var LastUniqueID = (new Date()).getTime();
function Utilities_ElementId(elem)
{
   if (elem.id == '')
   {
      // if the element does not have an ID we'll generate one
      elem.id = 'id_' + LastUniqueID;
      LastUniqueID++;
   }
   return elem.id;
}

function Utilities_AddCssStyle(style)
{
   var sheet = document.createElement('style');
   sheet.innerHTML = style;

   document.head.appendChild(sheet);
}

function Utilities_GetElementsByAttribute(attribute)
{
   var matchingElements = [];
   var allElements = Utilities_GetElementsByTag('*');
   for (var i = 0, n = allElements.length; i < n; i++)
   {
      if (allElements[i].getAttribute(attribute) !== null)
      {
         // Element exists with attribute. Add to array.
         matchingElements.push(allElements[i]);
      }
   }
   return matchingElements;
}

function Utilities_ViewportWidth()
{
   return window.innerWidth||document.documentElement.clientWidth||document.body.clientWidth||0;
}

function Utilities_ViewportHeight()
{
   return window.innerHeight||document.documentElement.clientHeight||document.body.clientHeight||0;
}

function Utilities_GetOffset(elem)
{
   var _x = 0;
   var _y = 0;
   while (elem && !isNaN(elem.offsetLeft) && !isNaN(elem.offsetTop))
   {
      _x += elem.offsetLeft /*- elem.scrollLeft*/;
      _y += elem.offsetTop /*- elem.scrollTop*/;
      elem = elem.offsetParent;
   }
   return {top: _y, left: _x};
}

function Utilities_GetOffset2(elem)
{
   var r = elem.getBoundingClientRect();
   return { left: r.left /*+ window.scrollX*/, top: r.top /*+ window.scrollY*/};
}

function Utilities_IsElementScrollable(elem)
{
   var overflowY = window.getComputedStyle(elem)['overflow-y'];
   var overflowX = window.getComputedStyle(elem)['overflow-x'];
   
   return ((overflowY === 'scroll' || overflowY === 'auto') && elem.scrollHeight > elem.clientHeight) || 
          ((overflowX === 'scroll' || overflowX === 'auto') && elem.scrollWidth > elem.clientWidth);
}

function Utilities_GetScrollableParentElement(elem)
{
   while (elem && !Utilities_IsElementScrollable(elem))
   {
      elem = elem.parentElement;
   }
   
   if (elem == null)
      elem = document.documentElement;
//      elem = document.body;
   
   return elem;
}

function Utilities_HasAsParent(elem, parent)
{
   do
   {
      elem = elem.parentElement;
   } while (elem != null && elem != parent);
   
   return elem != null;
}



function Utilities_HasClassAsParent(elem, className)
{
	var isClassNamePresent = false;	
   do
   {
      elem = elem.parentElement;
	  if(elem && Class_HasByElement(elem, className))
	  {
		  isClassNamePresent = true;
		  break;
	  }
   } while (elem != null);
   
   return isClassNamePresent;
}


function Utilities_HasAsChild(elem, child)
{
	for (var i = 0; i < elem.childNodes.length; i++)
	{
		if (elem.childNodes[i] == child)
			return true;
      if (Utilities_HasAsChild(elem.childNodes[i], child))
         return true;
	}
   
   return false;
}

function Utilities_HasAsAnyChild(elem, child)
{
	for (var i = 0; i < child.length; i++)
	{
      if (Utilities_HasAsChild(elem, child[i]))
         return true;
	}
   
   return false;
}

function Utilities_InitializeNodes(template)
{
	var childNodes = template.childNodes;
	for(var i = 0; i < childNodes.length; i++)
	{
		if (Class_HasByElement(childNodes[i], 'FilterSelect'))
			FilterSelect.MakeFilterSelect(childNodes[i]);
      if (Class_HasByElement(childNodes[i], 'MultiSelect'))
         MultiSelect.MakeMultiSelect(childNodes[i]);
      if (Class_HasByElement(childNodes[i], 'datechooser') || Class_HasByElement(childNodes[i], 'timechooser'))
         DateAndTimeChooser.MakeDateAndTimeChooser(childNodes[i]);
	}
}

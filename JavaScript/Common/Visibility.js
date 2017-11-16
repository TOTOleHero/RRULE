// ========================================================================
//        Copyright © 2008 Dominique Lacerte, All Rights Reserved.
// 
// Redistribution and use in source and binary forms are prohibited without 
//   prior written consent from Dominique Lacerte (internet@lacerte.org).
// ========================================================================

// Allows the showing and hiding of elements in a Web page.
//
// Usage (note use of showhide):
//
// <p><a href="#data" onclick="Visibility_ToggleById('data')">Toggle</a></p>
// <div id="data" class="showhide">
// <p>This is some text that is initially hidden.</p>
// </div>
//
// <p><a href="#data" onclick="Visibility_ToggleByClass('data')">Toggle</a></p>
// <div class="data showhide">
// <p>This is some text that is initially hidden.</p>
// </div>
//
// Alternative uses:
// <p><a href="#data" onclick="Visibility_Show('data')">Show</a></p>
// <p><a href="#data" onclick="Visibility_Hide('data')">Hide</a></p>

/* This didn't work when we have other display CSS style that is more specific, so we switched to below...
// This is a class that can be used to initially hide an element. We use a class 
// so that if the feature to SHOW is not available these elemenst won't be hidden.
// if we can change an elements visibility...
if (document.getElementById || document.all || document.layers)
{
	// hide all elements we need to up front
	document.write('<style type="text/css">.showhide { display: none; } </style>');
}
*/
DocumentLoad.AddCallback(function ()
{
   Visibility_SetByClass('showhide', false);
});

function Visibility_IsShownByElement(elem)
{
/*   if (HtmlTextArea.IsHtmlTextArea(elem))
   {
      return HtmlTextArea.IsShown(elem);
   }*/

	var vis;
	var val;
/* Really old browsers.
	if (document.all)
	{
		vis = elem.style.visibility;
		val = "hidden";
	}
	else
*/
	{
		vis = elem.style.display;
		val = 'none';
	}
	// if the style value is blank we try to figure it out
	if (vis == '' && elem.offsetWidth != undefined && elem.offsetHeight != undefined)
		return (elem.offsetWidth !=0 && elem.offsetHeight !=0 ) ? true : false;
	return (vis == '' || vis != val) ? true : false;
}

function Visibility_SetByElement(elem, visible)
{
/*   if (HtmlTextArea.IsHtmlTextArea(elem))
   {
      if (visible)
         HtmlTextArea.Show(elem);
      else
         HtmlTextArea.Hide(elem);

      return true;
   }*/

/* Really old browsers.
	if (document.all)
	{
		elem.style.visibility = visible ? 'visible' : 'hidden';
		return true;
	}
	else
*/
	{
		var value;
		var tagName = strtolower(elem.tagName);
		if (tagName == 'tr')
			value = visible ? 'table-row' : 'none';
		else if (tagName == 'div')
			value = visible ? 'block' : 'none';
		else
			value = visible ? '' : 'none';
		elem.style.display=value;
	}
	return true;
}

function Visibility_ShowByElement(elem)
{
	return Visibility_SetByElement(elem, 1);
}

function Visibility_HideByElement(elem)
{
	return Visibility_SetByElement(elem, 0);
}

function Visibility_ToggleByElement(elem)
{
	return Visibility_SetByElement(elem, !Visibility_IsShownByElement(elem));
}

function Visibility_IsShownById(id)
{
   var elem = Utilities_GetElementById(id);
   if (elem)
      return Visibility_IsShownByElement(elem);
   return false;
}

function Visibility_SetById(id, visible)
{
   var elem = Utilities_GetElementById(id);
   if (elem)
      return Visibility_SetByElement(elem, visible);
   return false;
}

function Visibility_ShowById(id)
{
   var elem = Utilities_GetElementById(id);
   if (elem)
      return Visibility_ShowByElement(elem);
   return false;
}

function Visibility_HideById(id)
{
   var elem = Utilities_GetElementById(id);
   if (elem)
      return Visibility_HideByElement(elem);
   return false;
}

function Visibility_ToggleById(id)
{
   var elem = Utilities_GetElementById(id);
   if (elem)
      return Visibility_ToggleByElement(elem);
   return false;
}

function Visibility_IsShownByName(name)
{
   var elem = Utilities_GetElementByName(name);
   if (elem)
      return Visibility_IsShownByElement(elem);
   return false;
}

function Visibility_SetByName(name, visible)
{
   var elem = Utilities_GetElementByName(name);
   if (elem)
      return Visibility_SetByElement(elem, visible);
   return false;
}

function Visibility_ShowByName(name)
{
   var elem = Utilities_GetElementByName(name);
   if (elem)
      return Visibility_ShowByElement(elem);
   return false;
}

function Visibility_HideByName(name)
{
   var elem = Utilities_GetElementByName(name);
   if (elem)
      return Visibility_HideByElement(elem);
   return false;
}

function Visibility_ToggleByName(name)
{
   var elem = Utilities_GetElementByName(name);
   if (elem)
      return Visibility_ToggleByElement(elem);
   return false;
}

function Visibility_IsShownByClass(className, root)
{
	var result = false;
	var elems = Utilities_GetElementsByClass(className, null, root);
	for(var i = 0, len = elems.length; i < len; i++)
	{
		if (Visibility_IsShownByElement(elems[i]))
			result = true;
	}
	return result;
}

function Visibility_SetByClass(className, visible, root)
{
	var result = false;
	var elems = Utilities_GetElementsByClass(className, null, root);
	for(var i = 0, len = elems.length; i < len; i++)
	{
		if (Visibility_SetByElement(elems[i], visible))
			result = true;
	}
	return result;
}

function Visibility_ShowByClass(className, root)
{
	var result = false;
	var elems = Utilities_GetElementsByClass(className, null, root);
	for(var i = 0, len = elems.length; i < len; i++)
	{
		if (Visibility_ShowByElement(elems[i]))
			result = true;
	}
	return result;
}

function Visibility_HideByClass(className, root)
{
	var result = false;
	var elems = Utilities_GetElementsByClass(className, null, root);
	for(var i = 0, len = elems.length; i < len; i++)
	{
		if (Visibility_HideByElement(elems[i]))
			result = true;
	}
	return result;
}

function Visibility_ToggleByClass(className, root)
{
	var result = false;
	var elems = Utilities_GetElementsByClass(className, null, root);
	for(var i = 0, len = elems.length; i < len; i++)
	{
		if (Visibility_ToggleByElement(elems[i]))
			result = true;
	}
	return result;
}

function Visibility_IsShown(item)
{
   return Visibility_IsShownByElement(Utilities_GetElement(item));
}

function Visibility_SetIsShown(item, visible)
{
   Visibility_SetIsShownByElement(Utilities_GetElement(item), visible);
}

function Visibility_Show(item)
{
   Visibility_ShowByElement(Utilities_GetElement(item));
}

function Visibility_Hide(item)
{
   Visibility_HideByElement(Utilities_GetElement(item));
}

function Visibility_Toggle(item)
{
   return Visibility_ToggleByElement(Utilities_GetElement(item));
}


DocumentLoad.AddCallback(function()
{
   var nodes = document.getElementsByClassName('toggler_id');
   [].forEach.call(nodes, function(a)
   {
      // start the item initially hidden
      var sel = a.getAttribute('href').substr(1);
      Visibility_SetById(sel, false);
      
      a.addEventListener('click', function(event)
      {
         event.preventDefault();
         a.classList.toggle("toggler_opened");
         var sel = this.getAttribute('href').substr(1);
         Visibility_ToggleById(sel);
      });
   });

   var nodes = document.getElementsByClassName('toggler_class');
   [].forEach.call(nodes, function(a)
   {
      // start the item initially hidden
      var sel = a.getAttribute('href').substr(1);
      Visibility_SetByClass(sel, false);
      
      a.addEventListener('click', function(event)
      {
         event.preventDefault();
         a.classList.toggle("toggler_opened");
         var sel = this.getAttribute('href').substr(1);
         Visibility_ToggleByClass(sel);
      });
   });
});

// ========================================================================
//        Copyright � 2008 Dominique Lacerte, All Rights Reserved.
// 
// Redistribution and use in source and binary forms are prohibited without 
//   prior written consent from Dominique Lacerte (internet@lacerte.org).
// ========================================================================

// Allows the applying and removing of classes to elements in a Web page.
//
// <p><a href="#data" onclick="Class_ToggleById('data','someclass')">Toggle</a></p>
// <div id="data" class="temp">
// <p>This is some text that.</p>
// </div>
//
// Alternative uses:
// <p><a href="#data" onclick="Class_Add('data','someclass)">Apply</a></p>
// <p><a href="#data" onclick="Class_Remove('data''someclass')">Remove</a></p>


function Class_HasByElement(elem, className)
{
   assert(elem != null);
   // older versions of FIrefox don't support classList so we do this instead...
	var myclass = new RegExp('\\b'+className+'\\b');
	return myclass.test(elem.className);
}

function Class_AddByElement(elem, className)
{
	if (!Class_HasByElement(elem, className))
	{
   // older versions of FIrefox don't support classList so we do this instead...
		elem.className = Utilities_CombineStrings(' ', elem.className, className);
		return true;
	}
	return false;
}

function Class_RemoveByElement(elem, className)
{
   assert(elem != null);
   // older versions of FIrefox don't support classList so we do this instead...
	classes = Utilities_StringToArray(elem.className, ' ');
	if (Utilities_RemoveFromArray(classes, className, true) > 0)
	{
		elem.className = classes.join(' ');
		return true;
	}
	return false;
}

function Class_ToggleByElement(elem, className)
{
	if (Class_HasByElement(elem, className))
		return Class_RemoveByElement(elem, className);
	else
		return Class_AddByElement(elem, className);
}

function Class_SetByElement(elem, className, enable)
{
	if (enable)
		return Class_AddByElement(elem, className);
	else
		return Class_RemoveByElement(elem, className);
}

function Class_HasById(id, className)
{
	var elem = Utilities_GetElementById(id);
	if (elem)
		return Class_HasByElement(elem, className);
	return false;
}

function Class_AddById(id, className)
{
	var elem = Utilities_GetElementById(id);
	if (elem)
		return Class_AddByElement(elem, className);
	return false;
}

function Class_RemoveById(id, className)
{
	var elem = Utilities_GetElementById(id);
	if (elem)
		return Class_RemoveByElement(elem, className);
	return false;
}

function Class_ToggleById(id, className)
{
	var elem = Utilities_GetElementById(id);
	if (elem)
		return Class_ToggleByElement(elem, className);	
	return false;
}

function Class_SetById(id, className, enable)
{
	var elem = Utilities_GetElementById(id);
	if (elem)
		return Class_SetByElement(elem, className, enable);	
	return false;
}

function Class_RemoveClassFromAll(className)
{
   var result = false;
	var elems = Utilities_GetElementsByClass(className);
	for (var i = 0; i < elems.length; i++)
	{
		if (Class_RemoveByElement(elems[i], className))
         result = true;
   }
	return result;
}


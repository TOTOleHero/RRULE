// ========================================================================
//        Copyright © 2008 Dominique Lacerte, All Rights Reserved.
// 
// Redistribution and use in source and binary forms are prohibited without 
//   prior written consent from Dominique Lacerte (internet@lacerte.org).
// ========================================================================

// Allows the showing and hiding of elements in a Web page.
// The elements with the class "showhide" are initially hidden (leave it out to initially show).
// A link is required to invoke the toggleVisibility method with the id of the element to be toggled.
//
// Usage:
//
// <p><a href="#data" onclick="toggleVisibility('data')">Toggle</a></p>
// <div id="data" class="showhide">
// <p>This is some text that is initially hidden.</p>
// </div>
//
// <p><a href="#data" onclick="toggleVisibilityByClass('data')">Toggle</a></p>
// <div class="data showhide">
// <p>This is some text that is initially hidden.</p>
// </div>
//
// Alternative uses:
// <p><a href="#data" onclick="setVisibility('data',1)">Show</a></p>
// <p><a href="#data" onclick="setVisibility('data',0)">Hide</a></p>


// if we can change an elements visibility...
if (document.getElementById || document.all || document.layers)
{
   // hide all elements we need to up front
   document.write('<style type="text/css">.showhide { display: none; } </style>');
}

function setVisibility(id,visible)
{
   var elem = Utilities_GetElement(id);
   if (elem)
   {
      var vis = elem.style;
      vis.display = visible ? 'block' : 'none';
      return true;
   }

   return false;
}

function toggleVisibility(id)
{
   var elem = Utilities_GetElement(id);
   if (elem)
   {
      var vis = elem.style;  // if the style.display value is blank we try to figure it out here
      if (vis.display=='' && elem.offsetWidth!=undefined && elem.offsetHeight!=undefined)
         vis.display = (elem.offsetWidth!=0 && elem.offsetHeight!=0) ? 'block' : 'none';
      vis.display = (vis.display=='' || vis.display=='block') ? 'none' : 'block';
      return true;
   }

   return false;
}

function toggleVisibilityByClass(className)
{
	var result = false;
	var elems = Utilities_GetElementsByClassName(className);
	for(var i=0, il=elems.length; i<il; i+=1)
	{
		elem = elems[i];
		var vis = elem.style;  // if the style.display value is blank we try to figure it out here
		if (vis.display=='' && elem.offsetWidth!=undefined && elem.offsetHeight!=undefined)
			vis.display = (elem.offsetWidth!=0 && elem.offsetHeight!=0) ? 'block' : 'none';
		vis.display = (vis.display=='' || vis.display=='block') ? 'none' : 'block';
		result = true;
	}

   return result;
}

// ========================================================================
//        Copyright � 2017 Dominique Lacerte, All Rights Reserved.
// 
// Redistribution and use in source and binary forms are prohibited without 
//   prior written consent from Dominique Lacerte (internet@lacerte.org).
// ========================================================================

// Utilities used in conjunction with FilterTable and SelectTable.

// return "row_" or "node_" style prefix from an ID (without the underscore), or null if no prefix
function GetPrefix(id)
{
   var i = id.lastIndexOf("_");
   if (i == -1) return null;
   return id.substr(0, i);
}

// remove "row_" or "node_" style prefix from an ID
function StripPrefix(id)
{
   var i = id.lastIndexOf("_");
   if (i == -1) return id;
   return id.substr(i+1);
}

// remove "row_" or "node_" style prefix from IDs
function StripPrefixes(ids)
{
	for (var i = 0; i < ids.length; i++)
	{
    	ids[i] = StripPrefix(ids[i]);
	}
}

function SendSelectedItemsEmail()
{
	var table = Utilities_GetElementById("content_table");
	var selectedIds = SelectTable.GetSelectedIds(table);
	var filteredIds = FilterTable.GetFilteredIds(table);
	selectedIds = Utilities_UnionArrays(selectedIds, filteredIds);
	StripPrefixes(selectedIds);
   window.location.href = "mailto:" + selectedIds.join(",");
}

function DisplaySelectedItemsForm(form, itemsName, itemName1, itemValue1, itemName2, itemValue2)
{
   if (Form_MainUri == null)
   {
      DisplayErrorMessage("Your page failed to initialize Form_MainUri.");
   }
   if (Form_ThisUri == null)
   {
      DisplayErrorMessage("Your page failed to initialize Form_ThisUri.");
   }
 
    var selectedIds = [];
    var filteredIds = [];  
	var table = Utilities_GetElementById("content_table");
    if (table)
    {
    	selectedIds = SelectTable.GetSelectedIds(table);
    	filteredIds = FilterTable.GetFilteredIds(table);
    }
	selectedIds = Utilities_UnionArrays(selectedIds, filteredIds);

	forEach(Utilities_GetElementsByClass("editable_tree"), function(tree)
	{
      var ids = EditTree.GetSelectedIds(tree);
   	  Utilities_MergeIntoArray(selectedIds, ids);
	});
   
	StripPrefixes(selectedIds);
	var url = Form_MainUri + "?FormProcessor=" + form;
	url += "&" + itemsName + "=" + encodeURIComponent(selectedIds.join(","));
	if (itemName1 != null)
		url += "&" + itemName1 + "=" + encodeURIComponent(itemValue1);
	if (itemName2 != null)
		url += "&" + itemName2 + "=" + encodeURIComponent(itemValue2);
   
//   DisplayWebPage(url);

   url += "&ReferralUrl=" + encodeURIComponent(Form_ThisUri);
   SubmitForm(url);
}

function DisplaySelectedItemsWebPage(url, replace, size)
{
	if (size == null) size = 'narrow';
	var table = Utilities_GetElementById("content_table");
	var selectedIds = SelectTable.GetSelectedIds(table);
	StripPrefixes(selectedIds);
	url = url.replace(replace, encodeURIComponent(selectedIds.join(",")));	
	DisplayWebPage(url, size);
}

// ========================================================================
//        Copyright � 2013 Dominique Lacerte, All Rights Reserved.
// 
// Redistribution and use in source and binary forms are prohibited without 
//   prior written consent from Dominique Lacerte (internet@lacerte.org).
// ========================================================================

function BusyIndicatorStart(message)
{
	if (!Utilities_GetElementById('busy_indicator'))
	{
		main = Utilities_CreateHtmlNode(
			"<DIV id='busy_indicator' style='width:100%; height:100%; position:fixed; top:0; left:0; z-index:999;'></DIV>" +
			"<DIV id='busy_indicator_message' style='text-align:center; position:fixed; width:50%; margin-left:-25%; top:30%; left:50%; z-index:1000;'></DIV>"
			);
		document.body.insertBefore(main,document.body.childNodes[0]);
	}
	if (!message)
		message = 'Please wait...';
	Utilities_GetElementById('busy_indicator_message').innerHTML = message;
	Visibility_ShowById('busy_indicator');
	Visibility_ShowById('busy_indicator_message');
}

function BusyIndicatorStop()
{
	Visibility_HideById('busy_indicator_message');
	Visibility_HideById('busy_indicator');
}

DocumentLoad.AddCallback(function()
{
	forEach(Utilities_GetElementsByTag('a'), function(elem)
	{
		if (Class_HasByElement(elem, 'busy_onclick'))
		{
         Utilities_AddEvent(elem, "click", function(e) 
         { 
            BusyIndicatorStart('Working...'); 
         });
		}
	});
});

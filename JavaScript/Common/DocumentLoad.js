// ========================================================================
//        Copyright � 2012 Dominique Lacerte, All Rights Reserved.
// 
// Redistribution and use in source and binary forms are prohibited without 
//   prior written consent from Dominique Lacerte (internet@lacerte.org).
// ========================================================================

// Allows the registration of methods that will be called after document load.


DocumentLoad =
{
	callbacks: new Array(),
	done: false,
	timer: null,

	TimerFunc: function()
	{
		// kill the timer
		if (DocumentLoad.timer)
		{
			clearTimeout(DocumentLoad.timer);
			DocumentLoad.timer = null;
		}

		if (DocumentLoad.callbacks.length == 0)
		{
         if (typeof BusyIndicatorStop === "function")
   			BusyIndicatorStop();
		}
		else
		{
         callback = DocumentLoad.callbacks.shift();
         
         try
         {
            callback();
         }
         catch(err)
         {
            alert("Exception: " + err.message);
         }

         DocumentLoad.timer = setTimeout(DocumentLoad.TimerFunc, 100);
		}
    },
	Init: function()
	{
		// quit if this function has already been called
		if (DocumentLoad.done) return;
		// flag this function so we don't do the same thing twice
		DocumentLoad.done = true;
      
      if (typeof BusyIndicatorStart === "function")
         BusyIndicatorStart('Initializing...');
	    DocumentLoad.timer = setTimeout(DocumentLoad.TimerFunc, 500);
	},
	
	AddCallback: function(callback)
	{
		if (DocumentLoad.done)
			callback();	// document already loaded
		else
			DocumentLoad.callbacks.push(callback);
	}
}

// for Mozilla/Opera9
if (document.addEventListener)
{
    document.addEventListener("DOMContentLoaded", DocumentLoad.Init, false);
}

// for Safari
if (/WebKit/i.test(navigator.userAgent))
{
    var timer = setTimeout(function()
	{
        if (/loaded|complete/.test(document.readyState))
		{
			// kill the timer
			if (timer) clearTimeout(timer);
			
            DocumentLoad.Init();	// call the onload callback
        }
		else if (DocumentLoad.done && timer)
			clearTimeout(timer);	// not needed, loading done
    }, 100);
}
	
// for other browsers

// This function adds DocumentLoad.init to the window.onload event,
// so it will run after the document has finished loading.
var oldOnLoad = window.onload;
window.onload = function() 
{
	DocumentLoad.Init();

	if (typeof oldOnload == 'function')
		oldOnLoad();
};

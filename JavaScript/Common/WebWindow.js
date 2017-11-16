// ========================================================================
//        Copyright � 2012 Dominique Lacerte, All Rights Reserved.
// 
// Redistribution and use in source and binary forms are prohibited without 
//   prior written consent from Dominique Lacerte (internet@lacerte.org).
// ========================================================================

// Uses PopUpWindow.js which must also be included!!!
//
// Allows the showing of a Web page in a pop-up window.
//
// Usage:
//
//   <a onclick="DisplayWebPage('http://www.meetup.com');">Open page</a><br>


var webWindowFrameID = null;

function DisplayWebPage(url, size, hidden)
{
	// It looks like if we open a URL from a different domain it will sometimes not load
	// such as if the destination specifies the header "X-Frame-Options:sameorigin" so
	// we check here for matching domains and if they don't match we open in a new window.
	
	curDomain = window.location.hostname;
	newDomain = url.split("/")[2];
	if (empty(size)) size = 'large';
	
   var tempForm = CreateFormFromUrl(url);
   
   document.body.appendChild(tempForm);
   
   if ((newDomain == null || strcmp(strtolower(curDomain), strtolower(newDomain)) == 0) &&
		strcmp(size, 'new') != 0)
	{
        var web_window_id = "web_window";
        if (hidden) 
        {
            // hidden processes should use a different id so that the user can still open other pages while hidden processing is going on
            web_window_id = "web_window_hidden";
        }
		if (!Utilities_GetElementById(web_window_id))
		{
		   // make it unique even across parent documents because some browsers require it
         var milliseconds = new Date().getTime();
         webWindowFrameID = 'web_window_iframe_' + milliseconds;
         
         // undo the CSS styling for iframes
         var iframeStyle =
            "box-sizing: content-box;" +
            "border: none;" +
            "border-radius: 0px;" +
            "padding: 0px;" +
            "width: auto;" +
            "margin: 0px;" +
            "height:100%;" +
            "width:100%;";
         
         var main = Utilities_CreateHtmlNode(
				"<DIV id=\"" + web_window_id + "\" style=\"display: none;\">" +
				"  <IFRAME id=\"" + webWindowFrameID + "\" name=\"" + webWindowFrameID + "\" style=\"" + iframeStyle + "\" frameborder=\"0\"></IFRAME>" +
				"</DIV>");
			document.body.insertBefore(main, document.body.childNodes[0]);
		}
        tempForm.target = webWindowFrameID;
        if (!hidden)
        {
          DisplayPopUp(web_window_id, size, function() { Utilities_GetElementById(webWindowFrameID).src=null; });
        }
	}
	else
	{
        var milliseconds = new Date().getTime();
        tempForm.target = 'new_web_window_' + milliseconds;
        var temp = window.open('', tempForm.target, 'width=1000,height=600,left=150,top=50,screenX=150,screenY=50,resizable=1,scrollbars=1,toolbar=1,location=1,directories=1');
		if (window.focus)
		{
			temp.focus();
		}
	}
   
   tempForm.submit();
   document.body.removeChild(tempForm);
}

function CloseWebWindow()
{
	ClosePopUp();
}

function CloseParentWebWindow()
{
	CloseParentPopUp();
}

function Redirect(url)
{
	document.location.href=url; 
}

function RedirectParent(url)
{
   if (window.parent != window)
   	window.parent.document.location.href=url; 
}

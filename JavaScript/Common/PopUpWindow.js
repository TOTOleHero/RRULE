// ========================================================================
//        Copyright � 2012 Dominique Lacerte, All Rights Reserved.
// 
// Redistribution and use in source and binary forms are prohibited without 
//   prior written consent from Dominique Lacerte (internet@lacerte.org).
// ========================================================================

// Allows the showing of a pop-up window with just about any HTML content.
//
// Usage (shows a form, hides form on cancel, note the DIV isn't 
// copied just its content):
//
// <DIV id='content_id' style='display:none;'>
//     <FORM action='/SomeScript.php'>
//         Type Something <INPUT type='text' size='50' name='Text'><BR>
//         <INPUT type='submit' value='Save'><INPUT type='button' value='Cancel' onClick='ClosePopUp();'>
//     </FORM>
// </DIV>
// <A onclick="DisplayPopUp('content_id');">Open form</A>


// put our CSS into the document
document.write('<style type="text/css">' + 
/* This is the pop-up into which we display another Web site. */
'#popup_window' +
'{ ' +
'	display: none;' +
'	position: fixed; ' +
'	z-index: 500; ' +
'	box-shadow: 8px 8px 5px #555; ' +
'	border: 2px solid #888;' +
'	background: #FFFFFF;' +	/* DRL FIXIT! If I don't specify a color it goes transparent. */
'}' +
'.popup_window_small' +
'{ ' +
'	left: 18%; top: 28%; right: 18%; bottom: 38%; ' +
'}' +
'.popup_window_medium' +
'{ ' +
'	left: 9%; top: 14%; right: 9%; bottom: 17%; ' +
'}' +
'.popup_window_narrow' +
'{ ' +
'	left: 22%; top: 8%; right: 22%; bottom: 12%; ' +
'}' +
'.popup_window_large' +
'{ ' +
'	left: 38px; top: 38px; right: 38px; bottom: 38px; ' +
'}' +
'#popup_window_shim' +
'{ ' +
'	width: 1px; min-height: 101vh; ' +
'}' +
'#popup_window_content' +
'{ ' +
'	position: absolute; left: 0px; top: 32px; right: 0px; bottom: 0px; overflow:hidden;' +
'}' +
'#popup_frame_content' +
'{ ' +
'	position: absolute; left: 0px; top: 32px; right: 0px; bottom: 0px; overflow:hidden;' +
'}' +
'.popup_window_full' +
'{ ' +
'	left: 0px; top: 0px; right: 0px; bottom: 0px; ' +
'}' +
'.popup_window_full #popup_header' +
'{ ' +
'	display: none; ' +
'}' +
'.popup_window_full #popup_window_content' +
'{ ' +
'	position: absolute; left: 0px; top: 0px; right: 0px; bottom: 0px; /*overflow-y: auto; -webkit-overflow-scrolling: touch;*/ ' +
'}' +
   /* See MainLayout.css for this.
'body.has_popup_window' +
'{ ' +
'	overflow:hidden; ' +
'}' +
'body.has_popup_window #content_wrapper' +
'{ ' +
'	display:none;' +
'}' +
*/
'</style>'
);

var popup_window_content_elem = null;
var popup_window_close_handler = null;

// size: small, medium, narrow, large
function DisplayPopUp(id, size, closeHandler)
{
	var elem = Utilities_GetElementById(id);
   DisplayPopUpElement(elem, size, closeHandler);
}

function DisplayPopUpElement(elem, size, closeHandler)
{
   if (popup_window_content_elem != null)
      ClosePopUp();
   
   if (!Utilities_GetElementById('popup_window'))
   {
      var main = Utilities_CreateHtmlNode(
         "<DIV id=\"popup_window\" class=\"popup_window_large\">" +
         "	<DIV align=\"right\" id=\"popup_header\" style=\"background: #AAAAFF; border-bottom: 2px solid #888; position: absolute; left: 0px; top: 0px; right: 0px; height: 30px;\">" +
         "		<TABLE><TR><TD valign=\"center\"><INPUT type='button' value='X' onClick='ClosePopUp();'></TD></TR></TABLE>" +
         "	</DIV>" +
         "	<DIV id=\"popup_window_content\"></DIV><!--<DIV id=\"popup_window_shim\"></DIV>-->" +
         "</DIV>"
      );
      document.body.insertBefore(main,document.body.childNodes[0]);
   }
   
   if (!size)
      size = "medium";
   
   // set the style of the popup to match the desired size
   Utilities_GetElementById('popup_window').className = 'popup_window_' + size;
   
   // move the display data from the document into the popup
   var dest = Utilities_GetElementById('popup_window_content');
   while (elem.hasChildNodes())
      dest.appendChild(elem.childNodes[0]);
   
   // save the ID and handler so we can access it later
   popup_window_content_elem = elem;
   popup_window_close_handler = closeHandler;
   
   Visibility_ShowById('popup_window');

//	if (size == "full") {
   Class_AddByElement(document.body, "has_popup_window");
//	}
}

function DisplayPopUpValue(id, size, closeHandler)
{
   if (popup_window_content_elem != null)
      ClosePopUp();
   
   if (!Utilities_GetElementById('popup_window'))
   {
      var main = Utilities_CreateHtmlNode(
         "<DIV id=\"popup_window\" class=\"popup_window_large\">" +
         "	<DIV align=\"right\" id=\"popup_header\" style=\"background: #AAAAFF; border-bottom: 2px solid #888; position: absolute; left: 0px; top: 0px; right: 0px; height: 30px;\">" +
         "		<TABLE><TR><TD valign=\"center\"><INPUT type='button' value='X' onClick='ClosePopUp();'></TD></TR></TABLE>" +
         "	</DIV>" +
         "	<DIV id=\"popup_window_content\"></DIV><!--<DIV id=\"popup_window_shim\"></DIV>-->" +
         "</DIV>"
      );
      document.body.insertBefore(main,document.body.childNodes[0]);
   }
   
   if (!size)
      size = "medium";
   
   // set the style of the popup to match the desired size
   Utilities_GetElementById('popup_window').className = 'popup_window_' + size;
   
   // copy the display data from the document into the popup
   var html = Utilities_GetElementById(id).value;
   var dest = Utilities_GetElementById('popup_window_content');
   dest.innerHTML = html;
   
   // save the ID and handler so we can access it later
   popup_window_content_elem = "INJECTED_CONTENT";
   popup_window_close_handler = closeHandler;
   
   Visibility_ShowById('popup_window');

//	if (size == "full") {
      Class_AddByElement(document.body, "has_popup_window");
//	}
}

// DisplayPopUp moves the iframe into a different container, so we cant just pluck the id from web_window
// Let's just store it as global instead
var _popupFrameId = null;
// id should be the id of a form control with value attribute
function DisplayPopUpFrame(id, size, closehandler)
{
	var web_window = Utilities_GetElementById('popup_web_window');
	if (!web_window)
	{
	   // make it unique even across parent documents because some browsers require it
     var milliseconds = new Date().getTime();
     _popupFrameId = 'popup_web_window_iframe_' + milliseconds;
      
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
			"<DIV id=\"popup_web_window\" style=\"display: none;\">" +
			"  <IFRAME id=\"" + _popupFrameId + "\" name=\"" + _popupFrameId + "\" style=\"" + iframeStyle + "\" frameborder=\"0\"></IFRAME>" +
			"</DIV>");
		document.body.insertBefore(main, document.body.childNodes[0]);
	}
	var iframe = Utilities_GetElementById(_popupFrameId);
	if (!iframe)
   {
      alert("Can't find iFrame with id: " + _popupFrameId);
      return;
   }
   
	DisplayPopUp('popup_web_window', size);
	// moving the iframe after writing the content seems to erase the content
	// so instead let's write the content after DisplayPopUp
	var html = Utilities_GetElementById(id).value;
	iframe.contentWindow.document.open();
	iframe.contentWindow.document.write(html);
	iframe.contentWindow.document.close();	
}

function InitializeAutoLoadedFrames()
{
	var iframes = Utilities_GetElementsByClass('AutoLoadedFrame');
	for (var i = 0; i < iframes.length; i++)
	{	
		var iframe = iframes[i];
		var contentSourceId = iframe.getAttribute("ContentSourceID");
		var contentSource = Utilities_GetElementById(contentSourceId);
		if (contentSource)
		{
			iframe.contentWindow.document.open();
			iframe.contentWindow.document.write(contentSource.value);
			iframe.contentWindow.document.close();
		}
	}
}

DocumentLoad.AddCallback(InitializeAutoLoadedFrames);

// adjust iframe height according to content height
function AutoAdjustFrameHeight(elem)
{
   elem.height = "";
   // DRL I had to add a little more below so we don't get scrollbars when the iframe is styled with a border, etc.
   elem.height = (elem.contentWindow.document.body.scrollHeight + 30) + "px";
}

function DisplayPopUpContent(content, size, closeHandler)
{
	if (popup_window_content_elem != null)
		ClosePopUp();
		
	if (!Utilities_GetElementById('popup_window'))
	{
		main = Utilities_CreateHtmlNode(
			"<DIV id=\"popup_window\" class=\"popup_window_large\">" +
			"	<DIV align=\"right\" id=\"popup_header\" style=\"background: #AAAAFF; border-bottom: 2px solid #888; position: absolute; left: 0px; top: 0px; right: 0px; height: 30px;\">" +
			"		<TABLE><TR><TD valign=\"center\"><INPUT type='button' value='X' onClick='ClosePopUp();'></TD></TR></TABLE>" +
			"	</DIV>" +
			"	<DIV id=\"popup_window_content\"></DIV><DIV id=\"popup_window_shim\"></DIV>" +
			"</DIV>"
			);
		document.body.insertBefore(main,document.body.childNodes[0]);
	}
	
	if (!size)
		size = "medium";
	
	// set the style of the popup to match the desired size
	Utilities_GetElementById('popup_window').className = 'popup_window_' + size;
	
	// copy the display data from the document into the popup
	data = Utilities_CreateHtmlNode(content);
	dest = Utilities_GetElementById('popup_window_content');
	dest.appendChild(data);

	// save the ID and handler so we can access it later
	popup_window_content_elem = "DYNAMIC_CONTENT";
	popup_window_close_handler = closeHandler;
	
	Visibility_ShowById('popup_window'); 
}

function GetPopUpContent()
{
	return Utilities_GetElementById('popup_window_content');
}

function ClosePopUp()
{
//	// This is support for a popup containing an iFrame and this 
//	// call wanting to close the popup in the parent window hosting 
//	// that iFrame. 
//	if (!Visibility_IsShownById('popup_window'))
//	{
//		if (!window.parent || !window.parent == window || !window.parent.ClosePopUp) return;	// can't do it!
//		window.parent.ClosePopUp();
//		return;
//	}
   
   Visibility_HideById('popup_window');
   Visibility_HideById('popup_frame');

	src = Utilities_GetElementById('popup_window_content');
   
   if (popup_window_content_elem == "INJECTED_CONTENT")
   {
   }
   else if (popup_window_content_elem == "DYNAMIC_CONTENT")
   {
      // remove the content added to the popup window
      while (src.hasChildNodes())
         src.removeChild(src.childNodes[0]);
   }
	else
	{
		// restore the display data back to where it was in the document
		dest = popup_window_content_elem;
		while (src.hasChildNodes())
			dest.appendChild(src.childNodes[0]);
	}
	
	popup_window_content_elem = null;
	
	if (popup_window_close_handler)
	{
		popup_window_close_handler();
		popup_window_close_handler = null;
	}

	Class_RemoveByElement(document.body, "has_popup_window");
}

function CloseParentPopUp()
{
	// This is support for a popup containing an iFrame and this 
	// call wanting to close the popup in the parent window hosting 
	// that iFrame. 
	if (window.parent != window && window.parent.ClosePopUp)
   	window.parent.ClosePopUp();
}
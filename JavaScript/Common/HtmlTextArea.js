// ========================================================================
//        Copyright � 2012 Dominique Lacerte, All Rights Reserved.
// 
// Redistribution and use in source and binary forms are prohibited without 
//   prior written consent from Dominique Lacerte (internet@lacerte.org).
// ========================================================================

// Allows WYSIWYG editing of TEXTAREA controls, just by adding class='HtmlTextArea'.

// Depends on ckeditor in ThirdParty folder - MUST BE INCLUDED (see Constants.php)

HtmlTextArea =
{
   config:
   {
      extraPlugins: 'imageeditor,widget,lineutils,placeholder',
      filebrowserBrowseUrl: '/PHP/NetBizBoom/ImageBrowser.php',
      filebrowserImageWindowWidth : '850',
      filebrowserImageWindowHeight : '480',
      removeButtons: 'Save,NewPage,Preview,Print,Templates,PageBreak',
      allowedContent:
      {
         $1:
         {
            // Use the ability to specify elements as an object.
            elements: CKEDITOR.dtd,
            attributes: true,
            styles: true,
            classes: true
         }
      }
/*      disallowedContent: 'script; *[on*]'   we need onclick for CC processing */
   },

	Init: function()
	{
      CKEDITOR.replaceClass = null; // Disable replacing by class
      
		var elems = Utilities_GetElementsByClass('HtmlTextArea');
      for (var i = 0; i < elems.length; i++)
		{
         HtmlTextArea.MakeHtmlTextArea(elems[i]);
		}
	},

   Show: function(elem)
   {
      if (typeof CKEDITOR.instances[Utilities_ElementId(elem)] == 'undefined')
      {
         elem.style.display='';
         HtmlTextArea.MakeHtmlTextArea(elem);
      }
   },

   Hide: function(elem)
   {
      if (typeof CKEDITOR.instances[Utilities_ElementId(elem)] != 'undefined')
      {
         CKEDITOR.instances[Utilities_ElementId(elem)].updateElement();
         CKEDITOR.instances[Utilities_ElementId(elem)].destroy();
         elem.style.display='none';
      }
   },

   IsShown: function(elem)
   {
      return typeof CKEDITOR.instances[Utilities_ElementId(elem)] != 'undefined';
   },

   IsHtmlTextArea: function(elem)
   {
      return Class_HasByElement(elem, 'HtmlTextArea');
   },

   MakeHtmlTextArea: function(elem)
   {
      var editor = CKEDITOR.replace(Utilities_ElementId(elem), HtmlTextArea.config);
      editor.on('change', function()
      {
         // call the onchange method so our form code knows things have changed
         var evt = new Object();
         evt.currentTarget = elem;
         OnElemChanged(evt);
      });
   }
}

DocumentLoad.AddCallback(HtmlTextArea.Init);

// The Safari browser on iOS makes input and textarea elements too wide when specifying a % width.
if (Browser.GetOS() == "iOS" && Browser.GetName() == "Safari")
{
   var style = 
"@media only screen and (max-device-width: 480px)" +
"{" +
"   table.entry_table > tbody > tr > td > input," +
"   table.simple_table > tbody > tr > td > input" +
"   {" +
"      width: 96%;" +
"   }" +
"   table.entry_table > tbody > tr > td > textarea," +
"   table.simple_table > tbody > tr > td > textarea" +
"   {" +
"      width: 98.5%;" +
"   }" +
"   table.entry_table > tbody > tr > td > input.three_way_split," +
"   table.simple_table > tbody > tr > td > input.three_way_split" +
"   {" +
"      width: 26%;" +
"   }" +
"   @media (orientation: portrait)" +
"   {" +
"      table.entry_table > tbody > tr > td > input," +
"      table.simple_table > tbody > tr > td > input" +
"      {" +
"         width: 93.5%;" +
"      }" +
"      table.entry_table > tbody > tr > td > textarea," +
"      table.simple_table > tbody > tr > td > textarea" +
"      {" +
"         width: 97.5%;" +
"      }" +
"      table.entry_table > tbody > tr > td > input.three_way_split," +
"      table.simple_table > tbody > tr > td > input.three_way_split" +
"      {" +
"         width: 26%;" +
"      }" +
"   }" +
"}";
   Utilities_AddCssStyle(style);
}
// The Android browser makes textarea elements too wide when specifying a % width.
if (Browser.GetOS() == "Android")
{
   var style = 
"table.entry_table > tbody > tr > td > textarea," +
"table.simple_table > tbody > tr > td > textarea" +
"{" +
"   width: 98%;" +
"}";
   Utilities_AddCssStyle(style);
}

Visibility_HideByClass('filter_reveal');

function initMenuPath()
{
   var root = Utilities_GetElementById('main_menu');
   if (root == null)
   {
      return;   // no main menu
   }
      
   var node = Utilities_GetElementsByClass('active')[0];
//   var activeNode = null;
   var depth = 0;
   while (node != null && node != root)
   {
      if (node.tagName == 'LI')   // skip UL elements
      {
//         if (depth == 1)         // one up from "current" node
//            activeNode = node.children[0];
         Class_AddByElement(node, 'active');
         depth++;
      }
      
      node = node.parentNode;
   }
}

function setMenuOptions()
{
   var mainMenu = Utilities_GetElementById('pull_down_menu');
   if (mainMenu != null)
   {
/*
      var topItems = Utilities_GetElementsByTag('UL', mainMenu);
      if (topItems.length > 0)
         topItems = topItems[0].children;
      else
         topItems = [];
      forEach(topItems, function (elem)
      {
         if (elem.tagName == 'LI')
            Class_AddByElement(elem, 'menu_top');
      });
*/
/*
      var anchorLinks = Utilities_GetElementsByTag('LI', mainMenu);
      forEach(anchorLinks, function (elem)
      {
         Utilities_AddEvent(elem.firstChild, "click", function(e)
         {
            var anchorLinks = Utilities_GetElementsByTag('li', Utilities_GetElementById('pull_down_menu'));
            forEach(anchorLinks, function (elem)
            {
               Class_RemoveByElement(elem, 'active');
            });
            
            Class_AddByElement(Utilities_GetEventTarget(e).parentNode, 'active');
         
            initMenuPath();
         });
      });
*/    
      initMenuPath();
   }

/*
   var moreMenu = Utilities_GetElementById('pop_up_menu');
   if (moreMenu != null)
   {
      anchorLinks = Utilities_GetElementsByTag('li', moreMenu);
      forEach(anchorLinks, function (elem)
      {
         Utilities_AddEvent(elem.firstChild, "click", function(e)
         {
            var anchorLinks = Utilities_GetElementsByTag('li', Utilities_GetElementById('pop_up_menu'));
            forEach(anchorLinks, function (elem)
            {
               Class_RemoveByElement(elem, 'active');
            });
            
            Class_AddByElement(Utilities_GetEventTarget(e).parentNode, 'active');
         });
      });
   }
*/
}

function setFilterOptions()
{
   var filterAnchors = Utilities_GetElementById('filter_anchors');
   if (filterAnchors != null)
   {
      var anchorLinks = Utilities_GetElementsByTag('a', filterAnchors);
      forEach(anchorLinks, function (elem)
      {
         if (elem.getAttribute('href')[0] == '#')
         {
            Utilities_AddEvent(elem, "click", function(e)
            {
               Utilities_PreventDefaultForEvent(e);
               var target = Utilities_GetEventTarget(e);
               
               if (Class_HasByElement(target, 'active'))
               {
                  Class_RemoveByElement(target, 'active');
               }
               else
               {
                  Class_AddByElement(target, 'active');
               }

               updateFilterDisplay();
            });
         }
      });
   
      updateFilterDisplay();
   }
}

function updateFilterDisplay()
{
   var size = 0;
   var anchorLinks = Utilities_GetElementsByTag('a', Utilities_GetElementById('filter_anchors'));
   forEach(anchorLinks, function (elem)
   {
      var thisHref = elem.getAttribute('href');
      if (thisHref[0] == '#')
      {
         if (Class_HasByElement(elem, 'active'))
         {
            Visibility_Show(thisHref);
            size += 60;
         }
         else
         {
            Visibility_Hide(thisHref);
         }
      }
   });
   
   var content = Utilities_GetElementById('content_wrapper');
   if (content != null)
   {
      var padding = (40 + size).toString() + 'px 0 40px 0';
      content.style.padding = padding;
   }
}

DocumentLoad.AddCallback(function ()
{
	setMenuOptions();
	setFilterOptions();
});

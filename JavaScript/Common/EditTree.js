// ========================================================================
//        Copyright © 2017 Dominique Lacerte, All Rights Reserved.
// 
// Redistribution and use in source and binary forms are prohibited without 
//   prior written consent from Dominique Lacerte (internet@lacerte.org).
// ========================================================================

// Allows the selection of tree nodes and the moving of tree nodes to 
// reorganize the tree.
//
// NOTE: IE requires that the draggable element be an <A> tag with the HREF set (even if just to #) or an IMG tag!
//
// DRL FIXIT! I was still unable to get this to work for IE, look at the following which does work:
// https://developer.mozilla.org/en-US/docs/Web/API/DataTransfer/dropEffect

/* Sample HTML:

   These items are templated:<br />
   <span id="file_added" class="edittree_draggable edittree_templated" style="border: solid"><img class="iconsmall" src="img/FileIcon.png" /> New File</span><br />
   <span id="folder_added" class="edittree_folder edittree_draggable edittree_templated" style="border: solid"><img class="iconsmall" src="img/FolderIcon.png" /> New Folder</span><br />
   <br />
   These items are NOT templated:<br />
   <span id="file_added2" class="edittree_draggable edittree_nottemplated" style="border: solid"><img class="iconsmall" src="img/FileIcon.png" /> New File</span><br />
   <span id="folder_added2" class="edittree_folder edittree_draggable edittree_nottemplated" style="border: solid"><img class="iconsmall" src="img/FolderIcon.png" /> New Folder</span><br />
   
   <!-- put the templates in a hidden DIV... -->
   <div style="display: none;">
      <span id="file_added_template" class="edittree_draggable edittree_droppable">
         <img class="iconsmall" src="img/FileIcon.png" />
         <select name="Icon">
            <option value="Contact">Contact</option>
            <option value="Event">Event</option>
            <option value="Task">Task</option>
            <option value="Resource">Resource</option>
         </select>
         <input type="text" name="Title" placeholder="Title" />
      </span>
      <span id="folder_added_template" class="edittree_folder edittree_draggable edittree_droppable">
         <img class="iconsmall" src="img/FolderIcon.png" />
         <select name="Icon">
            <option value="Contact">Contact</option>
            <option value="Event">Event</option>
            <option value="Task">Task</option>
            <option value="Resource">Resource</option>
         </select>
         <input type="text" name="Title" placeholder="Title" />
      </span>
   </div>
   
   <br /><br />

   <div class="editable_tree">
      <ul>
         <li><span id="node_1003" class="edittree_folder edittree_draggable edittree_droppable"><img class="iconsmall" src="img/ContactIcon.png" /> Contacts</span></li>
         <li>
            <span id="node_1004" class="edittree_folder edittree_draggable edittree_droppable"><img class="iconsmall" src="img/EventIcon.png" /> <span>Events</span></span>
            <ul>
               <li><span id="node_1081" class="edittree_draggable edittree_droppable"><img class="iconsmall" src="img/EventIcon.png" /> <span>Event 1</span></span></li>
               <li><span id="node_1082" class="edittree_draggable edittree_droppable"><img class="iconsmall" src="img/EventIcon.png" /> <span>Event 2</span></span></li>
               <li><span id="node_1083" class="edittree_draggable edittree_droppable"><img class="iconsmall" src="img/EventIcon.png" /> <span>Event 3</span></span></li>
               <li><span id="node_1084" class="edittree_draggable edittree_droppable"><img class="iconsmall" src="img/EventIcon.png" /> <span>Event 4</span></span></li>
               <li><span id="node_1085" class="edittree_draggable edittree_droppable"><img class="iconsmall" src="img/EventIcon.png" /> <span>Event 5</span></span></li>
            </ul>
         </li>
         <li><span id="node_1007" class="edittree_folder edittree_draggable edittree_droppable"><img class="iconsmall" src="img/TaskIcon.png" /> Tasks</span></li>
         <li>
            <span id="node_1006" class="edittree_folder edittree_draggable edittree_droppable"><img class="iconsmall" src="img/VentureIcon.png" /> Ventures</span>
            <ul>
               <li>
                  <span id="node_1002" class="edittree_folder edittree_draggable edittree_droppable"><img class="iconsmall" src="img/SubItemIcon.png" />Venture 1</span>
                  <ul>
                     <li>
                        <span id="node_1001" class="edittree_folder edittree_draggable edittree_droppable"><img class="iconsmall" src="img/ResourceIcon.png" />Resources</span>
                        <ul>
                           <li><span id="node_1021" class="edittree_draggable edittree_droppable"><img class="iconsmall" src="img/ResourceIcon.png" /> Resource 1</span></li>
                           <li><span id="node_1022" class="edittree_draggable edittree_droppable"><img class="iconsmall" src="img/ResourceIcon.png" /> Resource 2</span></li>
                           <li><span id="node_1023" class="edittree_draggable edittree_droppable"><img class="iconsmall" src="img/ResourceIcon.png" /> Resource 3</span></li>
                           <li><span id="node_1024" class="edittree_draggable edittree_droppable"><img class="iconsmall" src="img/ResourceIcon.png" /> Resource 4</span></li>
                           <li><span id="node_1025" class="edittree_draggable edittree_droppable"><img class="iconsmall" src="img/ResourceIcon.png" /> Resource 5</span></li>
                        </ul>
                     </li>
                     <li><span id="node_1005" class="edittree_folder edittree_draggable edittree_droppable"><img class="iconsmall" src="img/TagIcon.png" /> Tags</span>
                        <ul>
                           <li><span id="node_1031" class="edittree_draggable edittree_droppable"><img class="iconsmall" src="img/ResourceIcon.png" /> Tags 1</span></li>
                           <li><span id="node_1032" class="edittree_draggable edittree_droppable"><img class="iconsmall" src="img/ResourceIcon.png" /> Tags 2</span></li>
                           <li><span id="node_1033" class="edittree_draggable edittree_droppable"><img class="iconsmall" src="img/ResourceIcon.png" /> Tags 3</span></li>
                           <li><span id="node_1034" class="edittree_draggable edittree_droppable"><img class="iconsmall" src="img/ResourceIcon.png" /> Tags 4</span></li>
                           <li><span id="node_1035" class="edittree_draggable edittree_droppable"><img class="iconsmall" src="img/ResourceIcon.png" /> Tags 5</span></li>
                        </ul>
                     </li>
                  </ul>
               </li>
               <li><span id="node_1041" class="edittree_folder edittree_draggable edittree_droppable"><img class="iconsmall" src="img/SubItemIcon.png" />Venture 2</span></li>
               <li><span id="node_1042" class="edittree_folder edittree_draggable edittree_droppable"><img class="iconsmall" src="img/SubItemIcon.png" />Venture 3</span></li>
               <li><span id="node_1043" class="edittree_folder edittree_draggable edittree_droppable"><img class="iconsmall" src="img/SubItemIcon.png" />Venture 4</span></li>
               <li><span id="node_1044" class="edittree_folder edittree_draggable edittree_droppable"><img class="iconsmall" src="img/SubItemIcon.png" />Venture 5</span></li>
            </ul>
         </li>
      </ul>
 */

EditTree =
{
   // constants for the drag callback
   DropNone: 0,
   DropCopy: 1,
   DropMove: 2,
   
   updateCallbacks: new Array(),
   dragCallbacks: new Array(),
   dropCallbacks: new Array(),
   dropTarget: null,
   dropType: 0,

   Init: function()
   {
      var draggables = document.querySelectorAll('.edittree_draggable,.edittree_droppable,.edittree_selectable');
      forEach(draggables, function (draggable)
      {
         EditTree.setupDragElement(draggable);
      });
   },
   
   AddUpdateCallback: function(callback)
   {
      EditTree.updateCallbacks.push(callback);
   },
   
   AddDragCallback: function(callback)
   {
      EditTree.dragCallbacks.push(callback);
   },
   
   AddDropCallback: function(callback)
   {
      EditTree.dropCallbacks.push(callback);
   },
   
   // ============================================================
   // Scroll handling...
   
   scrollableElem: null,
   scrollTimer: "",
   scrollDX: 0,
   scrollDY: 0,

   StartScroll: function(node)
   {
      if (!node) return;
      if (EditTree.scrollableElem) return;

      EditTree.scrollableElem = node;
      
      // we don't get mouse move events while dragging so this is how we get the mouse position
      document.addEventListener('dragover', EditTree.scrollHandler);

      EditTree.scrollTimer = window.setInterval(function()
      {
//console.log("Scrolling " + scrollDX + "," + scrollDY + " from " + scrollableElem.scrollLeft + "," + scrollableElem.scrollTop);
// had to do this to get it to work in all browser whether maximized or not...
         EditTree.scrollableElem.scrollLeft += EditTree.scrollDX;
         EditTree.scrollableElem.scrollTop += EditTree.scrollDY;
         document.documentElement.scrollLeft += EditTree.scrollDX;
         document.documentElement.scrollTop += EditTree.scrollDY;
         document.body.scrollLeft += EditTree.scrollDX;
         document.body.scrollTop += EditTree.scrollDY;
      }, 100);
   },
   
   StopScroll: function()
   {
       if (EditTree.scrollTimer != "") {
           window.clearInterval(EditTree.scrollTimer);
           document.removeEventListener('dragover', EditTree.scrollHandler);
       }
       EditTree.scrollTimer = "";
       EditTree.scrollableElem = null;
   },
   
   scrollHandler: function(e)
   {
      e = e || window.e;
      
      var boundaries = {width: Utilities_ViewportWidth(), height: Utilities_ViewportHeight()};

      // DRL FIXIT? We may want to turn off scrolling when the mouse is outside the scrollable area?

      EditTree.scrollDX = 0;
      if (e.clientX < 75)
      {
         EditTree.scrollDX = -(75-e.clientX);
      }
      else if (e.clientX > boundaries.width - 75)
      {
         EditTree.scrollDX = e.clientX - (boundaries.width - 75);
      }
      EditTree.scrollDY = 0;
      if (e.clientY < 75)
      {
         EditTree.scrollDY = -(75-e.clientY);
      }
      else if (e.clientY > boundaries.height - 75)
      {
         EditTree.scrollDY = e.clientY - (boundaries.height - 75);
      }
   },

   // ============================================================
   // Dragging helpers...
   
   setupDragElement: function(span)
   {
      span.setAttribute("draggable", "true");   // for Firefox
      forEach(span.children, function (child)
      {
         // This seems required for MS Edge?
         child.setAttribute("draggable", "true");
      });

      if (Class_HasByElement(span, 'edittree_draggable'))
      {
         span.addEventListener('dragstart', EditTree.handleDragStart, false);
         span.addEventListener('dragend', EditTree.handleDragEnd, false);
      }
      if (Class_HasByElement(span, 'edittree_draggable') || Class_HasByElement(span, 'edittree_selectable'))
      {
         span.addEventListener('click', EditTree.handleClick, false);
         span.addEventListener('touchstart', EditTree.handleTouchStart, false);
         span.addEventListener('touchend', EditTree.handleTouchEnd, false);
      }
      if (Class_HasByElement(span, 'edittree_droppable'))
      {
         span.addEventListener('dragenter', EditTree.handleDragEnter, false);
         span.addEventListener('dragover', EditTree.handleDragOver, false);
         span.addEventListener('dragleave', EditTree.handleDragLeave, false);
         span.addEventListener('drop', EditTree.handleDrop, false);
      }
   },

   updateDropType: function(target)
   {
      EditTree.dropType = EditTree.DropMove;
      forEach(EditTree.dragCallbacks, function(callback)
      {
         var temp = callback(EditTree.draggingItems, target);
         if (temp < EditTree.dropType)
            EditTree.dropType = temp;
      });
   },
   
   // ============================================================
   // Selection helpers...
   
   selected: [],
   touched: false,
   dragged: false,

   handleClick: function(e)
   {
      e.preventDefault();
      // DRL added these to prevent multiple events getting here for the same click
      Utilities_StopEventPropagation(e);
      e.stopImmediatePropagation();
     
      if (!EditTree.touched)
      {
         var target = Utilities_GetThisOrParentByClass(e.target, ['edittree_draggable','edittree_selectable']);
         if (e.shiftKey)
         {
            // shift mouse click extends the selection
            EditTree.multiSelectByShift(target);
         }
         else
         {
            // - mouse click toggles selection
            // - ctrl-mouse click toggles selection
            EditTree.multiSelectByCtrl(target);
         }

         // Reset border to selected element
         EditTree.resetBorder();
      }
   },

   handleTouchStart: function(e)
   {
      EditTree.touched = true;

      setTimeout(function()
      {
         if (!EditTree.dragged)
         {
            // - touch toggles the selection (same as mouse click)
            var target = Utilities_GetThisOrParentByClass(e.target, ['edittree_draggable','edittree_selectable']);
            EditTree.multiSelectByCtrl(target);

            // Reset border to selected element
            EditTree.resetBorder();
         }
      }, 200);      
   },

   handleTouchEnd: function(e)
   {
      setTimeout(function()
      {
         EditTree.touched = false;
         EditTree.dragged = false;
      }, 200);
   },

   multiSelectByCtrl: function(target)
   {
      var include = -1;
      for (var i = 0; i < EditTree.selected.length; i++)
      {
         if (EditTree.selected[i] == target)
         {
            include = i;
            break;
         }
      }

      if (include > -1)
      {
         EditTree.selected.splice(include, 1);
      }
      else
      {
         EditTree.addToSelection(target);
      }
   },

   multiSelectByShift: function(target)
   {
      if (EditTree.selected.length == 0)
      {
         EditTree.addToSelectionAndRemoveNonSiblings(target);
      }
      else
      {
         // Recreate array between previous element and current element
         var element = EditTree.selected[0];
         EditTree.removeNonSiblingsFromSelection(target);
         
         var spans = document.querySelectorAll('.edittree_draggable,.edittree_selectable');
         
         var first = null;
         var found = false;
         forEach(spans, function (span)
         {
            if (first != null)
            {
               EditTree.addToSelectionIfSibling(span);
            }

            // if we find the original start item, or we find the current item
            if (span == element || span == target)
            {
               if (first != null)
               {
                  first = null;
               }
               else if (!found)   // don't start selection again a second time
               {
                  first = span;
                  found = true;
                  EditTree.addToSelectionIfSibling(span);
               }
            }
         });
      }
   },

   removeNonSiblingsFromSelection: function(target)
   {
      var parent = Utilities_GetParentByTag(target, "UL");
      var idx = 0;
      while (idx < EditTree.selected.length)
      {
         if (parent != Utilities_GetParentByTag(EditTree.selected[idx], "UL"))
         {
            EditTree.selected.splice(idx, 1);
         }
         else
         {
            idx++;
         }
      }

      EditTree.addToSelection(target);
   },

   addToSelectionIfSibling: function(target)
   {
      if (EditTree.selected.length && Utilities_GetParentByTag(target, "UL") != Utilities_GetParentByTag(EditTree.selected[0], "UL")) {
         return;
      }

      EditTree.addToSelection(target);
   },

   addToSelection: function(target)
   {
      for (idx = 0; idx < EditTree.selected.length; idx++) {
         if (EditTree.selected[idx] == target)
            return;
      }

      EditTree.selected.push(target);
   },

   // ============================================================
   // ghost element for drag...

   ghostElements: [],

   deleteGhostElements: function()
   {
		var ghostEl = document.querySelectorAll('.ghostElement');
		forEach(ghostEl, function(element)
      {
			element.parentNode.removeChild(element);
		});

      EditTree.ghostElements = [];
   },
   
   addGhostEvent: function(clon)
   {
      clon.addEventListener('drop', EditTree.handleDrop, false);
      clon.addEventListener('dragover', EditTree.handleDragOver, false);
   },

   createGhostElements: function()
   {
      EditTree.deleteGhostElements();

      var length = 0;

      forEach(EditTree.draggingItems, function (span, i) 
      {
         var clon;
         if (Class_HasByElement(span, 'edittree_templated'))
         {
            clon = document.querySelector('#' + span.id + "_template").cloneNode(true);
         }
         else
         {
            clon = span.cloneNode(true);
         }
         Class_AddByElement(clon, 'ghostElementNode');
         Class_AddByElement(clon, 'edittree_droppable');   // you can drop on ghost nodes
         clon.style.opacity = '0.5';    
         clon.id = 'ghost_' + length;
         var li = document.createElement('li');
         Class_AddByElement(li, 'ghostElement');
         li.appendChild(clon);
         EditTree.addGhostEvent(clon);
         EditTree.ghostElements.push(li);
         length++;
      });
   },

   repositionGhostElement: function()
   {
      var target = EditTree.dropTarget;
      assert(!Class_HasByElement(target, 'ghostElement'));
      assert(!Class_HasByElement(target, 'ghostElementNode'));
      
      if (EditTree.isDragOnMiddle)
      {
         EditTree.addItemsToFolder(target, EditTree.ghostElements);
      }
      else if (EditTree.isDragOnTop)
      {
         EditTree.insertItemsBefore(target, EditTree.ghostElements);
      }
      else
      {
         EditTree.insertItemsAfter(target, EditTree.ghostElements);
      }
   },
   

   // ============================================================
   // Dragging event handlers...
   
   draggingItems: [],

   handleDragStart: function(e)
   {
      EditTree.dragged = true;

      var dt = e.dataTransfer;
      dt.effectAllowed = 'copyMove';
      
      var userAgent = window.navigator.userAgent,
      msie = userAgent.indexOf('MSIE '),       //Detect IE
      trident = userAgent.indexOf('Trident/'); //Detect IE 11

      if (msie > 0 || trident > 0)
      {
         // this is required by IE...
         dt.setData('text', '');
      }
      else
      {
         // this is required by Firefox...
         dt.setData('text/html', '');
      }

      EditTree.draggingItems = [];

      var target = Utilities_GetThisOrParentByClass(e.target, 'edittree_draggable');
      assert(target != null);
      
      // see if the dragged item is one of the selected items
      var include = -1;
      for (var i = 0; i < EditTree.selected.length; i++)
      {
         if (EditTree.selected[i] == target)
         {
            include = i;
            break;
         }
      }
      
      // if the dragged item is one of the selected items, then drag all the selected items
      if (include > -1)
      {
         forEach(EditTree.selected, function (span)
         {
            EditTree.draggingItems.push(span);
//            span.style.visibility = 'hidden';
//            span.style.transition = '0.3s';  
         });
      }
      else
      {
         EditTree.draggingItems.push(target);
      }

      assert(EditTree.draggingItems.length > 0);

/*
      // if we are dragging multiple items, use a special icon
      if (EditTree.selected.length > 1)
      {
         var img = new Image();
         img.src = "img/multiple.jpeg";
         dt.setDragImage(img, -20, -20);
      }
*/
//      if (dt.setDragImage instanceof Function)
//         dt.setDragImage(new Image(), 0, 0);   // don't show anything while dragging

      EditTree.initDragItems();

      EditTree.StartScroll(Utilities_GetScrollableParentElement(target));
      
      EditTree.createGhostElements();
   },

   initDragItems: function()
   {
      // make the dragging items not droppable so you can't drop to self or child of self
      forEach(EditTree.draggingItems, function (item)
      {
         // the children of the dragged item are next to it in the UL 
         // element so we'll go up to the parent to get them all, unless
         // this is a an item dragged from somewhere off the tree in
         // which case the parent won't be an LI element
         if (item.parentNode.tagName == 'LI')
            item = item.parentNode;
            
         var spans = item.querySelectorAll('.edittree_droppable');
         forEach(spans, function (span)
         {
            Class_AddByElement(span, 'edittree_droppable_temp');
            Class_RemoveByElement(span, 'edittree_droppable');
         });
         // I'm not sure why we make them not draggable?
         spans = item.querySelectorAll('.edittree_draggable');
         forEach(spans, function (span)
         {
            Class_AddByElement(span, 'edittree_draggable_temp');
            Class_RemoveByElement(span, 'edittree_draggable');
            span.setAttribute("draggable", "false");   // for Firefox
         });
      });
   },
   
   // NOTE: This can be called multiple times on the same objects!
   uninitDragItems: function()
   {
      // restore the drag/drop settings of the dragging items
      forEach(EditTree.draggingItems, function (item)
      {
         // the children of the dragged item are next to it in the UL 
         // element so we'll go up to the parent to get them all, unless
         // this is a an item dragged from somewhere off the tree in
         // which case the parent won't be an LI element
         if (item.parentNode.tagName == 'LI')
            item = item.parentNode;
            
         var spans = item.querySelectorAll('.edittree_droppable_temp');
         forEach(spans, function (span)
         {
            Class_AddByElement(span, 'edittree_droppable');
            Class_RemoveByElement(span, 'edittree_droppable_temp');
         });
         spans = item.querySelectorAll('.edittree_draggable_temp');
         forEach(spans, function (span)
         {
            Class_AddByElement(span, 'edittree_draggable');
            Class_RemoveByElement(span, 'edittree_draggable_temp');
            span.setAttribute("draggable", "true");   // for Firefox
         });
      });
   },
   
   handleDragEnd: function(e)
   {
      assert(EditTree.dragged);

      e.preventDefault();

      if (EditTree.dropTarget)
      {
//         // remove dotted line from target
//         Class_RemoveByElement(EditTree.dropTarget, 'edittree_droptarget');
         EditTree.dropTarget = null;
      }
      
      // remove selection from elements
      EditTree.selected = [];
      EditTree.resetBorder();

      EditTree.deleteGhostElements();
      
      EditTree.uninitDragItems();
      EditTree.draggingItems = [];
      
      EditTree.StopScroll();
   },

   isDragOnTop: false,
   isDragOnMiddle: false,

   handleDragOver: function(e)
   {
      if (!EditTree.dragged) return;

      assert(EditTree.draggingItems.length > 0);

      var found = false;

      // if we are dragging over a ghost item or one of its children just keep the same drop info from the target
      forEach(EditTree.ghostElements, function (span)
      {
         if (span == e.target || Utilities_HasAsChild(span, e.target))
         {
            found = true;
         }
      });
      
      if (!found)
      {
         var target = Utilities_GetThisOrParentByClass(e.target, 'edittree_droppable');
         if (target == null)
         {
            target = Utilities_GetThisOrParentByClass(e.target, 'edittree_draggable');
            if (target == null)
            {
               return;
            }
         }
   
         var temp = null;
         if (e.hasOwnProperty('offsetY'))
         {
            temp = {top: e.offsetX, left: e.offsetY};
         }
         else
         {
            // DRL FIXIT? DragDropTouch.js doesn't seem to provide the offsetY...
            temp = Utilities_GetOffset2(target);
            temp.top = e.clientY - temp.top;
            temp.left = e.clientX - temp.left;
         }
   
         var isDragOnMiddle = false;
         var isDragOnTop = false;
         if (Class_HasByElement(target, 'edittree_folder') &&
            temp.top >= target.offsetHeight/3 &&
            temp.top <= target.offsetHeight*2/3)
         {
            isDragOnMiddle = true;
      
   //         // add dotted line around target
   //         Class_AddByElement(target, 'edittree_droptarget');
         }
   
         if (EditTree.isDragOnMiddle || temp.top > target.offsetHeight/2)
         {
            isDragOnTop = false;
         }
         else
         {
            isDragOnTop = true;
         }
   
         // if we're not dropping in the middle then the parent is the actual target
         EditTree.updateDropType(isDragOnMiddle ? target : EditTree.GetParentNode(target));
         if (EditTree.dropType == EditTree.DropNone)
            return;
         
   //      if (EditTree.dropTarget != null)
   //      {
   //         // remove dotted line from target
   //         Class_RemoveByElement(EditTree.dropTarget, 'edittree_droptarget');
   //      }
         EditTree.isDragOnMiddle = isDragOnMiddle;
         EditTree.isDragOnTop = isDragOnTop;
         EditTree.dropTarget = target;
      
         EditTree.repositionGhostElement();
      }
   
      if (EditTree.dropType == EditTree.DropCopy)
         e.dataTransfer.dropEffect = 'copy';
      else
         e.dataTransfer.dropEffect = 'move';
   
      e.preventDefault();   // allow drop
   },

   handleDragEnter: function(e)
   {
   },

   handleDragLeave: function(e)
   {
   },

   handleDrop: function(e)
   {
      assert(EditTree.dragged);

      var target = EditTree.dropTarget;
      assert(target != null);
      
      assert(!Class_HasByElement(target, "ghostElement"));
      assert(!Class_HasByElement(target, "ghostElementNode"));
   
      // if we're not dropping in the middle then the parent is the actual target
      EditTree.updateDropType(EditTree.isDragOnMiddle ? target : EditTree.GetParentNode(target));
      if (EditTree.dropType != EditTree.DropNone)
      {
         // we must restore the dragging items to their original state before we copy them below
         // and we should also do this before we hand them off to the callbacks as we don't know
         // what they'll be used for then
         EditTree.uninitDragItems();
         
         var newItems = [];
         var draggingItems = [];
         if (EditTree.dropType == EditTree.DropCopy)
         {
            forEach(EditTree.draggingItems, function (span)
            {
               span = span.cloneNode(true);
               if (span.id)
                  span.dataset.source_id = span.id; // save the source ID so the callback can access it
               EditTree.new_id++;
               span.id = "new_" + EditTree.new_id;
   
               var li = document.createElement("LI");
               li.appendChild(span);
               EditTree.setupDragElement(span);
               newItems.push(span);
               draggingItems.push(li);
            });
         }
         else
         {
            draggingItems = EditTree.draggingItems;
         }
         
         if (EditTree.isDragOnMiddle)
         {
            EditTree.addItemsToFolder(target, draggingItems, newItems);
         }
         else if (EditTree.isDragOnTop)
         {
            EditTree.insertItemsBefore(target, draggingItems, newItems);
         }
         else
         {
            EditTree.insertItemsAfter(target, draggingItems, newItems);
         }
   
         forEach(EditTree.dropCallbacks, function(callback)
         {
            callback(draggingItems, newItems, target);
         });
         
         // allow this to occur asynchronously to allow the page to settle first
         setTimeout(function()
         {
            if (typeof OnElemAdded === 'function')
            {
               forEach(newItems, function (span)
               {
                  OnElemAdded(span, EditTree.dropType);
               });
            }
            if (typeof OnElemChanged === 'function')
            {
               OnElemChanged(null);
            }
         }, 200);
      }

//      Utilities_StopEventPropagation(e);
//      e.stopImmediatePropagation();
      e.preventDefault();      // prevent default action which could be open link, etc.
   },

   // ============================================================
   // Dragging helpers...
   
   // ============================================================
   // Tree insertion helpers...
   
   new_id: 0,
   
   getListElementToPaste: function(span, newItems)
   {
      var addedTarget = null;
      
      if (Class_HasByElement(span, 'edittree_templated') || Class_HasByElement(span, 'edittree_nottemplated'))
      {
         if (Class_HasByElement(span, 'edittree_templated'))
         {
            span = document.querySelector('#' + span.id + "_template").cloneNode(true);
            Class_RemoveByElement(span, "edittree_templated");
         }
         else
         {
            span = span.cloneNode(true);
            Class_RemoveByElement(span, "edittree_nottemplated");
            
            // non-templated items can't have the droppable attribute otherwise you'd be able to drop onto them so we add it here
            Class_AddByElement(span, "edittree_droppable");
         }
   
         if (span.id)
            span.dataset.source_id = span.id; // save the source ID so the callback can access it
         EditTree.new_id++;
         span.id = "new_" + EditTree.new_id;

         addedTarget = document.createElement("LI");
         addedTarget.appendChild(span);
         EditTree.setupDragElement(span);
         
         if (newItems != null)
            newItems.push(span);
      }
      else 
      {
         // already on the tree
         addedTarget = Utilities_GetThisOrParentByTag(span, "LI");
      }

      return addedTarget;
   },

   insertItemsAfter: function(node, items, newItems)
   {
      var ul = null;
      var dest_li = Utilities_GetThisOrParentByTag(node, "LI");
      if (dest_li == null)
      {
         // tree root
         ul = node.parentElement.querySelector('ul');
      }
      else
      {
         ul = Utilities_GetParentByTag(dest_li, "UL");
      }
      
      forEach(items, function (span)
      {
         var new_li = EditTree.getListElementToPaste(span, newItems);
         if (dest_li != null && dest_li.nextElementSibling)
         {
            ul.insertBefore(new_li, dest_li.nextElementSibling);
         } 
         else 
         {
            ul.appendChild(new_li);
         }
      });
   },

   insertItemsBefore: function(node, items, newItems)
   {
      // don't allow inserting above the root node
      if (!Class_HasByElement(node, "edittree_draggable"))
      {
         return;
      }
      var ul = null;
      var dest_li = Utilities_GetThisOrParentByTag(node, "LI");
      if (dest_li == null)
      {
         // tree root
         ul = node.parentElement;
         if (ul.tagName != 'UL')
            ul = ul.querySelector('ul');
      }
      else
      {
         ul = Utilities_GetParentByTag(dest_li, "UL");
      }
      
      forEach(items, function (span) 
      {
         var new_li = EditTree.getListElementToPaste(span, newItems);
         if (dest_li != null) 
         {
            ul.insertBefore(new_li, dest_li);
         }
         else
         {
            ul.appendChild(new_li);
         }
      });
   },
   
   addItemsToFolder: function(node, items, newItems)
   {
      var ul = null;
      var dest_li = Utilities_GetThisOrParentByTag(node, "LI");
      if (dest_li == null)
      {
         // tree root
         dest_li = node.parentElement;
      }
      
      ul = dest_li.querySelector('ul');
      if (ul == null)
      {
         ul = document.createElement("UL");
         dest_li.appendChild(ul);
      }
      
      forEach(items, function (span) 
      {
         var new_li = EditTree.getListElementToPaste(span, newItems);
         if (new_li != node)   // skip dropping on self
         {
            ul.appendChild(new_li);
         }
      });
   },

   resetBorder: function()
   {
      var temp = document.querySelectorAll('.edittree_draggable,.edittree_selectable');
      
      // Remove borders
      forEach(temp, function (span) 
      {
         if (span.id.search('_template') == -1)    // skip templates
         {
            Class_RemoveByElement(span, "edittree_selecteditem");
         }
      });
      
      // Add border to new selected elements
      forEach(EditTree.selected, function (span) 
      {
         Class_AddByElement(span, "edittree_selecteditem");
      });

      forEach(EditTree.updateCallbacks, function(callback)
      {
         callback(null);
      });
   },
   
   _GetElementID: function(elem, strip_id_prefix)
   {
      if (strip_id_prefix === false) return elem.id;

      var substrs = elem.id.split("_");
      var id = substrs[substrs.length - 1];
      if (substrs[0] == "new") 
      {
         id = "-" + id;   // use negative number for newly added items
      }
      return id;
   },

   // the cell passed in is the LI element (or the DIV for the root)
   _SaveNode: function(cell, strip_id_prefix, result)
   {
      var children = [];
      var ulNode = null;
      
      if (cell.tagName == 'LI')   // if first item is LI then this isn't the root
      {
         var dataNode = cell.firstElementChild;

         var dataset = dataNode.dataset;
         var config = dataset.hasOwnProperty('config') ? Json_FromString(dataset.config) : {};

/* DRL FIXIT? I think this was added so a node cold have an edit box for the label and other properties that
   the user can edit but this is not the correct place for this logic. If we need this behavior the logic
   should be pushed into a callback.
         // take the existing configuration and add/replace any input fields to it
         var fields = Form_GetValues(dataNode);
         for (var property in fields) 
         {
            if (fields.hasOwnProperty(property)) 
            {
               config[property] = fields[property];
            }
         }
*/
         var nodeData = 
         {
            NodeID: EditTree._GetElementID(dataNode, strip_id_prefix),
            Config: config,
            Children: children
         };
         
         result.push(nodeData);   // push it ahead so the nodes are in the same order as parsed
         
         ulNode = dataNode.nextElementSibling;
         assert(ulNode == null || ulNode.tagName == 'UL');
      }
      else
      {
         // root node
         ulNode = cell;
         while (ulNode != null && ulNode.tagName != 'UL')
         {
            ulNode = ulNode.nextElementSibling;
         }
      }
      
      if (ulNode != null)
      {
         for (var i = 0; i < ulNode.children.length; i++)
         {
            var childNode = ulNode.children[i];
            
            children.push(EditTree._GetElementID(childNode.firstElementChild, strip_id_prefix));

            EditTree._SaveNode(childNode, strip_id_prefix, result);
         }
      }
   },
   
   // ============================================================
   // Public methods...
   
   GetNodes: function(root)
   {
      var temp = root.querySelectorAll('.edittree_draggable');
      
      var result = [];
      forEach(temp, function(elem)
      {
         if (elem.id.search('_template') == -1)   // skip templates
         {
            result.push(elem.id);
         }
      });
      return result;
   },

   GetSelectedIds: function(root)
   {
      var temp = root.querySelectorAll('.edittree_selecteditem');
      
      var result = [];
      forEach(temp, function(elem)
      {
         if (elem.id.search('_template') == -1)   // skip templates
         {
            result.push(elem.id);
         }
      });
      return result;
   },
   
   UnselectIds: function(ids, root)
   {
      forEach(EditTree.selected, function(elem)
      {
         forEach(ids, function(id)
         {
            if (elem.id == id)
               Utilities_RemoveFromArray(EditTree.selected, elem);
         });
      });

      // Reset border to selected elements
      EditTree.resetBorder();
   },

   // item passed is the tree div, returns JSON
   SaveTree: function(root, strip_id_prefix)
   {
      var result = [];
      EditTree._SaveNode(root.firstElementChild, strip_id_prefix, result);
      return result;
   },
   
   // this handles templated items too
   AddNewItem: function(elem, parentElem, root, isCopy)
   {
      assert(Class_HasByElement(parentElem, 'edittree_droppable'));
      var target = Utilities_GetThisOrParentByClass(parentElem, 'edittree_droppable');
      assert(target != null);
      
      var newItems = [];
      if (Class_HasByElement(target, "edittree_folder"))
      {
         EditTree.addItemsToFolder(target, [elem], newItems);
      }
      else
      {
         EditTree.insertItemsAfter(target, [elem], newItems);
      }
      
      // allow this to occur asynchronously to allow the page to settle first
      setTimeout(function()
      {
         if (typeof OnElemAdded === 'function')
         {
            forEach(newItems, function (span)
            {
               OnElemAdded(span, isCopy ? EditTree.DropCopy : null);
            });
         }
         if (typeof OnElemChanged === 'function')
         {
            OnElemChanged(null);
         }
      }, 200);
      
      return Utilities_GetElementById("new_" + EditTree.new_id);
   },
   
   CopyAndAddNewItem: function(elem, parentElem, root)
   {
      assert(!Class_HasByElement(elem, "edittree_nottemplated"));
      assert(!Class_HasByElement(elem, "edittree_templated"));
      Class_AddByElement(elem, "edittree_nottemplated");                    // force the call below to create a copy
      var newElem = EditTree.AddNewItem(elem, parentElem, root, true);            // returns new cloned element with new ID
      Class_RemoveByElement(elem, "edittree_nottemplated");                 // restore classes
      return newElem;
   },
   
   DeleteItems: function(ids, root)
   {
      if (ids.length == 0) return;
      
      forEach(ids, function (id)
      {
         var span = Utilities_GetElementById(id);
         if (span)
         {
            span.parentElement.remove(span);
            Utilities_RemoveFromArray(EditTree.selected, span);
         }
      });
      
      // Reset border to selected elements
      EditTree.resetBorder();

      if (typeof OnElemChanged === 'function')
      {
         OnElemChanged(null);
      }
   },
   
   GetParentNode: function(elem)
   {
      // check that the element is in a tree!
      assert(Utilities_GetParentByClass(elem, 'editable_tree') != null);
      
      var ul = Utilities_GetParentByTag(elem, "UL");
      if (ul == null)
         return null;
      return ul.previousElementSibling;
   },
   
   GetChildNodes: function(elem)
   {
      var children = [];
   
      // check that the element is in a tree!
      // OR the element is a template (templates aren't under a tree)
      assert(Utilities_GetParentByClass(elem, 'editable_tree') != null || Utilities_GetParentByClass(elem, 'header_templates') != null);
      
      var ulNode = elem;
      while (ulNode != null && ulNode.tagName != 'UL')
      {
         ulNode = ulNode.nextElementSibling;
      }
      
      if (ulNode != null)
      {
         for (var i = 0; i < ulNode.children.length; i++)
         {
            var childNode = ulNode.children[i];
            
            children.push(childNode.firstElementChild);
         }
      }
      
      return children;
   },
   
   Filter: function(searchString, searchClasses)
   {
      if (searchClasses == null)
         searchClasses = new Array();
         
      if (empty(searchString) && searchClasses.length == 0)
      {
         EditTree.ClearFilter();
      }
      else
      {
         if (EditTree._timer)
            clearTimeout(EditTree._timer);            
         
         EditTree._searchClasses = searchClasses;
         EditTree._searchString = strtolower(searchString);

         EditTree._rows = [];
         var treeRoot = Utilities_GetElementsByClass('editable_tree', 'DIV', document.body);
         if (treeRoot.length > 0) 
         {
            // only include rows inside the treeroot, so that template rows don't get counted
            EditTree._rows = Utilities_GetElementsByClass('edittree_file', 'DIV', treeRoot[0]);
         }
         EditTree._iRow = 0;
         EditTree._timer = setTimeout(EditTree._FilterFunc, 100);
      }
   },

   ClearFilter: function()
   {
      if (EditTree._timer)
         clearTimeout(EditTree._timer);            
      
      EditTree._searchClasses = null;
      EditTree._searchString = null;
      EditTree._rows = [];
      var treeRoot = Utilities_GetElementsByClass('editable_tree', 'DIV', document.body);
      if (treeRoot.length > 0) 
      {
         // only include rows inside the treeroot, so that template rows don't get counted
         EditTree._rows = Utilities_GetElementsByClass('edittree_file', 'DIV', treeRoot[0]);
      }
      EditTree._iRow = 0;
      EditTree._timer = setTimeout(EditTree._ClearFunc, 100);
   },

   _FilterFunc: function()
   {
      if (EditTree._timer)
      {
         // kill the timer
         clearTimeout(EditTree._timer);
         EditTree._timer = null;          

         var count = 0;
         while (count < 200 && EditTree._iRow < EditTree._rows.length)
         {
            var tr = EditTree._rows[EditTree._iRow];
            var visible = EditTree._MatchesClasses(tr, EditTree._searchClasses) && 
               EditTree._MatchesString(tr, EditTree._searchString);
            Visibility_SetByElement(tr.parentNode, visible);
            count++;
            EditTree._iRow++;
         }

         if (EditTree._iRow < EditTree._rows.length)
            EditTree._timer = setTimeout(EditTree._FilterFunc, 100);
         else
         {
            forEach(EditTree.updateCallbacks, function(callback)
            {
               callback(EditTree._table);
            });
         }
      }
   },

   _MatchesClasses: function(tr, searchClasses)
   {
      if (searchClasses.length == 0) return true;
      
      // if we're dealing with an array of arrays then ALL of them must be true
      if (is_array(searchClasses[0]))
      {
         var result = true;
         forEach(searchClasses, function(subClasses)
         {
            if (!EditTree._MatchesClasses(tr, subClasses))
               result = false;
         });
         return result;
      }
      
      // if we're dealing with a simple array then ANY of the items must be true
      return Utilities_ArraysMeet(Utilities_StringToArray(tr.className, ' '), searchClasses);
   },
   
   _MatchesString: function(tr, searchString)
   {
      var result = false;
      forEach(Utilities_GetElementsByTag('span', tr), function(span)
      {
         if (Class_HasByElement(span, 'resource_name') &&
            Utilities_StringContains(strtolower(span.innerText), searchString))
            result = true;
      });
      return result;
   },
   
   
   _ClearFunc: function()
   {
      if (EditTree._timer)
      {
         // kill the timer
         clearTimeout(EditTree._timer);            
         EditTree._timer = null;          

         var count = 0;
         while (count < 200 && EditTree._iRow < EditTree._rows.length)
         {
            var tr = EditTree._rows[EditTree._iRow];
            Visibility_ShowByElement(tr.parentNode);
            count++;
            EditTree._iRow++;
         }

         if (EditTree._iRow < EditTree._rows.length)
            EditTree._timer = setTimeout(EditTree._ClearFunc, 100);
         else
         {
            forEach(EditTree.updateCallbacks, function(callback)
            {
               callback(EditTree._table);
            });
         }
      }
   },

   GetFilteredIds: function(table)
   {
      result = new Array();
      forEach(Utilities_GetElementsByClass("edittree_draggable", "DIV", document.body), function(row)
      {
         if (Visibility_IsShownByElement(row.parentNode))
         {
            if (row.id.search('_template') == -1)   // skip templates
            {
               result.push(row.id);
            }
         }
      });
      return result;
   },


}

DocumentLoad.AddCallback(EditTree.Init);

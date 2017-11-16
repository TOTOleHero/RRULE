/* Add dropdowns */
DocumentLoad.AddCallback(function()
{
   if (document.getElementById("main_menu") == null)
      return;
   
	var items = document.querySelectorAll("#main_menu ul li");
	for (var i = 0; i < items.length; i++)
	{
		var hasChild = items[i].querySelector("ul");
		if(hasChild)
		{
			items[i].classList.add("dropdown");
		}
    }

   /* Listen for click outside menu and Close Menu */

   window.addEventListener("click", function()
   {
      var elem = document.querySelector("#main_menu input");
      if (elem != null)
         elem.checked = false;
   });
   window.addEventListener("touchstart", function()
   {
      var elem = document.querySelector("#main_menu input");
      if (elem != null)
         elem.checked = false;
   });
   
   /* Click on menu won't be considered as Close Menu */
   document.getElementById("main_menu").addEventListener("click", function(e)
   {
      e.stopPropagation();
   });
   document.getElementById("main_menu").addEventListener("touchstart", function(e)
   {
      e.stopPropagation();
   });

    /* Listen for click outside sub menu and Close Sub Menu */

    var elemSubMenu = document.getElementById("sub_menu");

    if(elemSubMenu != null){

        var elemSubMenuInput = document.querySelector("#sub_menu input");
        var elemMainMenuInput = document.querySelector("#main_menu input");

        window.addEventListener("click", function()
        {
            elemSubMenuInput.checked = false;
        });

        window.addEventListener("touchstart", function()
        {
            elemSubMenuInput.checked = false;
        });

        /* Click on menu won't be considered as Close Menu */
        elemSubMenu.addEventListener("click", function(e)
        {
            e.stopPropagation();
            elemMainMenuInput.checked = false;
        });
        elemSubMenu.addEventListener("touchstart", function(e)
        {
            e.stopPropagation();
            elemMainMenuInput.checked = false;
        });

        /* Click on menu Close another Menu */
        document.getElementById("main_menu").addEventListener("click", function(e)
        {
            elemSubMenuInput.checked = false;
        });
        document.getElementById("main_menu").addEventListener("touchstart", function(e)
        {
            elemSubMenuInput.checked = false;
        });
    }

    updateMenu();
});

/* Update menu when window is resized */
window.addEventListener("resize", function()
{
	resetMenu();
	updateMenu();
});


function updateMenu()
{
	var items = document.querySelectorAll(".dropdown");
   for (var i = 0; i < items.length; i++)
   {
      var item = items[i];
		item.onclick = function(e)
      {
         var parent = item.closest('ul');
         var sibling = parent.querySelector(".dropdown.clicked");
         if (sibling)
         {
            sibling.classList.remove("clicked");
            if (sibling == item)
            {
               item.classList.remove("clicked");
               e.stopPropagation();
               return;
            }
         }
         openIt(item);
         checkInvert(item);
         e.stopPropagation();
		};
		item.onmouseover = function(e)
      {
         checkInvert(item);
         e.stopPropagation();
		};
	}
}

function openIt(item)
{
	item.classList.add("clicked");
}	

function resetMenu()
{
	var items = document.querySelectorAll(".inverse");
   for (var i = 0; i < items.length; i++)
   {
		items[i].classList.remove("inverse");
	}
}

/* Fix submenu near window right side */

function checkInvert(item)
{
	var drop = item.querySelector("ul");		
	var wind =  window.innerWidth;
	var width = drop.offsetWidth;
	var left = drop.getBoundingClientRect().left;
	var right = wind -  width - left;	
	
	if (right < 20)
	{
		item.classList.add("inverse");
	}
}

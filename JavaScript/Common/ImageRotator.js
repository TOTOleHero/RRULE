// ========================================================================
//        Copyright � 2015 Dominique Lacerte, All Rights Reserved.
// 
// Redistribution and use in source and binary forms are prohibited without 
//   prior written consent from Dominique Lacerte (internet@lacerte.org).
// ========================================================================

//	Convert a single IMG element into one that rotates through multiple images.
//	Usage:
//
//	<IMG src='Whatever.jpg' class='ImageRotator' others='Whatever2.jpg;Whatever3.jpg' delay=5>

ImageRotator =
{
	items: new Array(),
	timercount: 0,
	timer: null,
	
	Init: function()
	{
		forEach(Utilities_GetElementsByTag("img"), function(elem)
		{
			if (Class_HasByElement(elem, 'ImageRotator'))
			{
				ImageRotator.MakeImageRotator(elem);
			}
		});
		
		setInterval(ImageRotator.OnTimer, 1000);
	},

	OnTimer: function()
	{
		ImageRotator.timercount++;
		if (ImageRotator.timercount == 10000)
			ImageRotator.timercount = 0;
		
		for	(var i = 0; i < ImageRotator.items.length; i++)
		{
			var target = ImageRotator.items[i];
			var delay = target.getAttribute("delay");
			if (!delay)
				delay = 5;		// default delay if none specified
			var timercount = target.getAttribute("timercount");
			if (!timercount)
				timercount = 0;
			else
				timercount = +timercount;	// convert to integer
			if (timercount > ImageRotator.timercount)
				timercount = ImageRotator.timercount;
			if (timercount + delay <= ImageRotator.timercount)
			{
				ImageRotator.NextImage(target);
			}
		}
 	},
	
	NextImage: function(target)
	{
		target.setAttribute("timercount", ImageRotator.timercount);
		
		var images = target.getAttribute("others").split(";");
		for	(var j = 0; j < images.length; j++)
		{
			var image = images[j];
			// the image.src may have the full URL prepended so we strip that before comparing
			if (target.src.length >= image.length &&
				image == target.src.substring(target.src.length - image.length))
			{
				if (j < images.length-1)
				{
					j++;
				}
				else
				{
					j = 0;
				}
			
				target.src = images[j];
				break;
			}
		}
	},
	
//	FullImage: function(target)
//	{
//		content = "<DIV style='overflow:auto; height:100%; width:100%'><IMG src='" + target.src + "'></DIV>";
//		DisplayPopUpContent(content);
//	},
	
	MakeImageRotator: function(target)
	{
		target.setAttribute("others", target.src + ";" + target.getAttribute("others"));
		target.onclick = function() { ImageRotator.NextImage(this); };
		ImageRotator.items.push(target);
	}
}

DocumentLoad.AddCallback(ImageRotator.Init);

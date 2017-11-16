// ========================================================================
//        Copyright � 2012 Dominique Lacerte, All Rights Reserved.
// 
// Redistribution and use in source and binary forms are prohibited without 
//   prior written consent from Dominique Lacerte (internet@lacerte.org).
// ========================================================================


var Browser = 
{
	browser: "",
	version: "",
	OS: "",
	isIE: false,
	isGecko: false,
	isWebKit: false,
	
	init: function () 
	{
		this.browser = this.searchString(this.dataBrowser) || "unknown";
		this.version = this.searchVersion(navigator.userAgent)
			|| this.searchVersion(navigator.appVersion)
			|| "unknown";
		this.OS = this.searchString(this.dataOS) || "unknown";
		
		// initialize some common uses...
		this.isIE = this.browser == 'Explorer';
		this.isGecko = this.browser == 'Gecko';
		this.isWebKit = this.browser == 'WebKit';
	},
	searchString: function (data) 
	{
		for (var i=0;i<data.length;i++)	
		{
			var dataString = data[i].string;
			var dataProp = data[i].prop;
			this.versionSearchString = data[i].versionSearch || data[i].identity;
			if (dataString) 
			{
				if (dataString.indexOf(data[i].subString) != -1)
					return data[i].identity;
			}
			else if (dataProp)
				return data[i].identity;
		}
	},
	searchVersion: function (dataString) 
	{
		var index = dataString.indexOf(this.versionSearchString);
		if (index == -1) return;
		return parseFloat(dataString.substring(index+this.versionSearchString.length+1));
	},
	dataBrowser: 
	[
		{
			string: navigator.userAgent,
			subString: "Chrome",
			identity: "Chrome"
		},
		{ 	string: navigator.userAgent,
			subString: "OmniWeb",
			versionSearch: "OmniWeb/",
			identity: "OmniWeb"
		},
		{
			string: navigator.vendor,
			subString: "Apple",
			identity: "Safari",
			versionSearch: "Version"
		},
		{
			prop: window.opera,
			identity: "Opera",
			versionSearch: "Version"
		},
		{
			string: navigator.vendor,
			subString: "iCab",
			identity: "iCab"
		},
		{
			string: navigator.vendor,
			subString: "KDE",
			identity: "Konqueror"
		},
		{
			string: navigator.userAgent,
			subString: "Firefox",
			identity: "Firefox"
		},
		{
			string: navigator.vendor,
			subString: "Camino",
			identity: "Camino"
		},
		{		// for newer Netscapes (6+)
			string: navigator.userAgent,
			subString: "Netscape",
			identity: "Netscape"
		},
		{
			string: navigator.userAgent,
			subString: "MSIE",
			identity: "Explorer",
			versionSearch: "MSIE"
		},
		{
			string: navigator.userAgent,
			subString: "Gecko",
			identity: "Gecko",
			versionSearch: "rv"
		},
		{
			string: navigator.userAgent,
			subString: "AppleWebKit/",
			identity: "WebKit",
			versionSearch: ""	//DRL FIXIT????
		},
		{ 		// for older Netscapes (4-)
			string: navigator.userAgent,
			subString: "Mozilla",
			identity: "Netscape",
			versionSearch: "Mozilla"
		}
	],
	dataOS : 
	[
		{
			string: navigator.platform,
			subString: "Win",
			identity: "Windows"
		},
		{
			string: navigator.platform,
			subString: "Mac",
			identity: "Mac"
		},
		{
		   string: navigator.userAgent,
		   subString: "iPhone",         // DRL FIXIT? I believe now we need to check for iPad and iPod? 
		   identity: "iOS"
      },
		{
			string: navigator.userAgent,
			subString: "Android",
			identity: "Android"
		},
      // this should come after the above since it may show up in addition to the above
		{
			string: navigator.platform,
			subString: "Linux",
			identity: "Linux"
		}
	],
	
	GetName: function() { return this.browser; },
	GetVersion: function() { return this.version; },
	GetOS: function() { return this.OS; },

	// detect Flash Player PlugIn version information
	GetFlashVersion: function ()
	{
		// NS/Opera version >= 3 check for Flash plugin in plugin array
		if (navigator.plugins != null && navigator.plugins.length > 0)
		{
			if (navigator.plugins["Shockwave Flash 2.0"] || navigator.plugins["Shockwave Flash"])
			{
				var swVer2 = navigator.plugins["Shockwave Flash 2.0"] ? " 2.0" : "";
				var flashDescription = navigator.plugins["Shockwave Flash" + swVer2].description;
				var descArray = flashDescription.split(" ");
				var tempArrayMajor = descArray[2].split(".");
				var versionMajor = tempArrayMajor[0];
				if ( descArray[3] != "" )
				{
					tempArrayMinor = descArray[3].split("r");
				}
				else
				{
					tempArrayMinor = descArray[4].split("r");
				}
				var versionMinor = tempArrayMinor[1] > 0 ? tempArrayMinor[1] : 0;
				flashVer = parseFloat(versionMajor + "." + versionMinor);
			}
			else
			{
				flashVer = -1;
			}
		}
		// MSN/WebTV 2.6 supports Flash 4
		else if (strpos(strtolower(navigator.userAgent), "webtv/2.6") !== FALSE) flashVer = 4;
		// WebTV 2.5 supports Flash 3
		else if (strpos(strtolower(navigator.userAgent), "webtv/2.5") !== FALSE) flashVer = 3;
		// older WebTV supports Flash 2
		else if (strpos(strtolower(navigator.userAgent), "webtv") !== FALSE) flashVer = 2;
		// Can't detect in all other cases
		else flashVer = -1;

		return flashVer;
	}
};

Browser.init();

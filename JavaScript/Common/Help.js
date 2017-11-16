// ========================================================================
//        Copyright � 2013 Dominique Lacerte, All Rights Reserved.
// 
// Redistribution and use in source and binary forms are prohibited without 
//   prior written consent from Dominique Lacerte (internet@lacerte.org).
// ========================================================================



// put our CSS into the document
document.write('<style type="text/css">' + 
'.help_bubble' +
'{' +
'	cursor: pointer;' +
'	display: none;' +
'	z-index: 800;' +
'	position: absolute;' +
'	text-align: left;' +
'	font-size:75%;' +
'	width: 250px;' +
'	padding: 15px;' +
'/*	margin:1em 0 3em; */' +
'	color: #fff;' +
'	background: #075698;' +
'	/* css3 */' +
'	-webkit-border-radius: 10px;' +
'	-moz-border-radius: 10px;' +
'	border-radius: 10px;' +
'	background-image: url("/Images/Common/Close13.gif");' +
'	background-repeat: no-repeat;' +
'	background-position: 263px 5px;' +
'}' +
'.help_bubble.top' +
'{' +
'	margin-top: 50px;' +
'	margin-left:30%;	/* would love to center this guy! */' +
'}' +
'.help_bubble.bottom' +
'{' +
'	bottom: 45px;		/* would love to have this element top based on the height */' +
'	margin-left:30%;	/* would love to center this guy! */' +
'}' +
'.help_bubble.left' +
'{' +
'	margin-top:10px;' +
'	right: -260px;' +
'}' +
'.help_bubble.right' +
'{' +
'	margin-left:0px;' +
'	left: -200px;' +
'}' +
'.help_bubble.center' +
'{' +
'	margin-top:40%;		/* would love to center this guy! */' +
'	margin-left:30%;	/* would love to center this guy! */' +
'}' +
'.help_bubble:after' +
'{' +
'	content:"";' +
'	position:absolute;' +
'	border-style:solid;' +
'	/* reduce the damage in FF3.0 */' +
'	display:block;' +
'	width:0;' +
'}' +
'.help_bubble.top:after' +
'{' +
'	top:-20px; /* value = - border-top-width - border-bottom-width */' +
'	right:50px; /* controls horizontal position */' +
'	bottom:auto;' +
'	left:auto;' +
'	border-width:20px 20px 0 0; /* vary these values to change the angle of the vertex */' +
'	border-color:transparent #075698;' +
'}' +
'.help_bubble.bottom:after' +
'{' +
'	bottom:-20px; /* value = - border-top-width - border-bottom-width */' +
'	left:50px; /* controls horizontal position */' +
'	border-width:20px 0 0 20px; /* vary these values to change the angle of the vertex */' +
'	border-color:#075698 transparent;' +
'}' +
'.help_bubble.left:after' +
'{' +
'	top:16px;' +
'	left:-40px; /* value = - border-left-width - border-right-width */' +
'	bottom:auto;' +
'	border-width:15px 40px 0 0; /* vary these values to change the angle of the vertex */' +
'	border-color:transparent #075698;' +
'}' +
'.help_bubble.right:after' +
'{' +
'	top:16px;' +
'	right:-40px; /* value = - border-left-width - border-right-width */' +
'	bottom:auto;' +
'	left:auto;' +
'	border-width:15px 0 0 40px; /* vary these values to change the angle of the vertex */' +
'	border-color:transparent #075698;' +
'}' +
'</style>');

function CloseHelp()
{
	Visibility_HideByElement(this);
	var ids = GetCookie('HelpIds');
	if (ids)
	{
		ids = Utilities_StringToArray(ids, ',');
		Utilities_RemoveFromArray(ids, this.id);
		ids = ids.join(',');
		// DRL FIXIT? Should keep the existing cookie expiry?
		SetCookie('HelpIds', ids);
	}
}

function InitializeHelp()
{
	var ids = GetCookie('HelpIds');
	if (ids)
	{
		ids = Utilities_StringToArray(ids, ',');
		for (var i = 0; i < ids.length; i++)
		{
			var id = ids[i];
			var elem = Utilities_GetElementById(id);
			if (elem && HelpIds.indexOf(id) != -1)
			{
				elem.onclick = CloseHelp;
				elem.innerHTML = HelpText[HelpIds.indexOf(id)];
				Visibility_ShowByElement(elem);
			}
		}
	}
}

DocumentLoad.AddCallback(InitializeHelp);

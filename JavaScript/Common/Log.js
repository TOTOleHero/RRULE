// ========================================================================
//        Copyright (c) 2010 Dominique Lacerte, All Rights Reserved.
// 
// Redistribution and use in source and binary forms are prohibited without 
//   prior written consent from Dominique Lacerte (internet@lacerte.org).
// ========================================================================

// ===================================================================
//
//	Implementation.
//
// ===================================================================

function Log_WriteInfo(msg)
{
	if (window.console && window.console.log)
		window.console.log(msg);
	else if (window.opera && window.opera.postError)
		window.opera.postError(msg);
}

function Log_WriteError(msg)
{
	if (window.console && window.console.log)
		window.console.log(msg);
	else if (window.opera && window.opera.postError)
		window.opera.postError(msg);
}

function Log_Die(msg)
{
	alert(msg);
}

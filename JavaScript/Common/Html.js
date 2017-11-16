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

function Html_Encode(s)
{
	//return s.replace(/&(?!\w+([;\s]|))/g, "&amp;")
	if (s)
		return s.replace(/</g, "&lt;").replace(/>/g, "&gt;");

	return "";
}


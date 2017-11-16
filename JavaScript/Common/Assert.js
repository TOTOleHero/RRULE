// ========================================================================
//        Copyright � 2016 Dominique Lacerte, All Rights Reserved.
// 
// Redistribution and use in source and binary forms are prohibited without 
//   prior written consent from Dominique Lacerte (internet@lacerte.org).
// ========================================================================

// Checks for a condition and throws an exception if it isn't met.
//


function assert(condition, message)
{
   if (!condition || 
      // added these to catch when I mistakenly pass a string like I do in PHP
      typeof condition == 'string' || condition instanceof String)
   {
      message = message || "Assertion failed";
      if (typeof Error !== "undefined")
      {
         throw new Error(message);
      }
      throw message; // Fallback
   }
}
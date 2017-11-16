// ========================================================================
//        Copyright � 2008 Dominique Lacerte, All Rights Reserved.
// 
// Redistribution and use in source and binary forms are prohibited without 
//   prior written consent from Dominique Lacerte (internet@lacerte.org).
// ========================================================================


// DRL FIXIT? This fixed a problem with JSON.stringify() adding slashes to escape the resulting data.
// It doesn't seem to work here so it needs to be copied into your script code somehwere.
//if (window.Prototype)
//{
//    delete Array.prototype.toJSON;
//}

(function(){
   // Convert array to object
   var convArrToObj = function(array){
      var thisEleObj = new Object();
      if(typeof array == "object"){
         for(var i in array){
            var thisEle = convArrToObj(array[i]);
            thisEleObj[i] = thisEle;
         }
      }else {
         thisEleObj = array;
      }
      return thisEleObj;
   };
   var oldJSONStringify = JSON.stringify;
   JSON.stringify = function(input){
      if(oldJSONStringify(input) == '[]')
         return oldJSONStringify(convArrToObj(input));
      else
         return oldJSONStringify(input);
   };
})();

function Json_ToString(values)
{
   return JSON.stringify(values);
}

function Json_FromString(str)
{
   if (str == null || str.length == 0)
      return null;
      
   return JSON.parse(str, function (key, value)
   {
      var type;
      if (value && typeof value === 'object')
      {
         type = value.type;
         if (typeof type === 'string' && typeof window[type] === 'function')
         {
            return new (window[type])(value);
         }
      }
      return value;
   });
}

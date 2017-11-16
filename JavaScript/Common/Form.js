// ========================================================================
//        Copyright � 2008 Dominique Lacerte, All Rights Reserved.
// 
// Redistribution and use in source and binary forms are prohibited without 
//   prior written consent from Dominique Lacerte (internet@lacerte.org).
// ========================================================================

var Form_ThisUri = window.location.href;   // this can be changed later as needed as it may not be correct for POST results
var Form_MainUri = window.location.protocol + '//' + window.location.hostname + '/Main.php';   // DRL FIXIT! This should be initialized properly!

// local variables
var _Form_CheckToSave = true;

// takes a URL and converts it to a form that can be submit, getting around the URL length limitation 
function CreateFormFromUrl(url)
{
   var tempForm = document.createElement("form");
   tempForm.method = "POST";
   tempForm.action = url.split('?')[0];

   var qd = {};
   if (url.includes('?'))
   {
      url.split('?')[1].split("&").forEach(function(item)
      {
         var s = item.split("="),
         k = s[0],
         v = s[1] && decodeURIComponent(s[1]);
         qd[k] = v;
      });
   }

   for (var property in qd)
   {
      if (qd.hasOwnProperty(property))
      {
         var tempInput = document.createElement("input");
         tempInput.type = "hidden";
         tempInput.name = property;
         tempInput.value = qd[property];
         tempForm.appendChild(tempInput);
      }
   }
   
   return tempForm;
}

var _BackgroundExecutionQueue = [];
// wrapper for FormValuesUpdated. Should be called if the form was submitted via FormAction=SubmitBackground
function _BackgroundExecutionCallback(values)
{
   // this function should be called at the parent window because the queue is there
   if (window.parent != window && window.parent._BackgroundExecutionCallback)   
   {
      window.parent._BackgroundExecutionCallback(values);
      return;
   }
   // dequeue the task that just finished
   var task = _BackgroundExecutionQueue.shift();
   // send details back to the form
   FormValuesUpdated(Json_FromString(values), task.callbackItem);
   // process the next item in the queue
   if (_BackgroundExecutionQueue.length > 0)
   {
      task = _BackgroundExecutionQueue[0];
      // give the user some time to see the last "animation" before editing the next item
      setTimeout(function()
      {
         _DisplayItemForm(task.form, true, task.itemName1, task.itemValue1, task.itemName2, task.itemValue2, task.itemName3, task.itemValue3, 
            task.itemName4, task.itemValue4, "FormAction", "Silent", true);
      }, 1000);
   }

}
// we support only 4 item-value pairs here because we reserve the fifth one for FormAction
// callbackItem - an object that the callback will pass back to FormValuesUpdated as the secondparam
function ExecuteBackgroundForm(form, callbackItem, itemName1, itemValue1, itemName2, itemValue2, itemName3, itemValue3, itemName4, itemValue4)
{
   var taskDetails = 
   {
      form: form,
      callbackItem: callbackItem,
      itemName1: itemName1,
      itemValue1: itemValue1,
      itemName2: itemName2,
      itemValue2: itemValue2,
      itemName3: itemName3,
      itemValue3: itemValue3,
      itemName4: itemName4,
      itemValue4: itemValue4
   };
   _BackgroundExecutionQueue.push(taskDetails);
   if (_BackgroundExecutionQueue.length > 1)
      return;  // configure it when we're done with the current item

   setTimeout(function()
   {
      _DisplayItemForm(form, true, itemName1, itemValue1, itemName2, itemValue2, itemName3, itemValue3, itemName4, itemValue4, "FormAction", "Silent", true);
   }, 500);

}


function _DisplayItemForm(form, inline, itemName1, itemValue1, itemName2, itemValue2, itemName3, itemValue3, itemName4, itemValue4, itemName5, itemValue5, backgroundExecution)
{
   if (Form_MainUri == null)
   {
      DisplayErrorMessage("Your page failed to initialize Form_MainUri.");
   }
   if (Form_ThisUri == null)
   {
      DisplayErrorMessage("Your page failed to initialize Form_ThisUri.");
   }
   
	url = Form_MainUri + "?FormProcessor=" + form;
	if (itemName1 != null)
		url += "&" + itemName1 + "=" + encodeURIComponent(itemValue1);
	if (itemName2 != null)
		url += "&" + itemName2 + "=" + encodeURIComponent(itemValue2);
	if (itemName3 != null)
		url += "&" + itemName3 + "=" + encodeURIComponent(itemValue3);
	if (itemName4 != null)
		url += "&" + itemName4 + "=" + encodeURIComponent(itemValue4);
	if (itemName5 != null)
		url += "&" + itemName5 + "=" + encodeURIComponent(itemValue5);

   if (inline)
   {
      DisplayWebPage(url, "full", backgroundExecution);
   }
   else
   {
      url += "&ReferralUrl=" + encodeURIComponent(Form_ThisUri);
      GoToUrl(url);
   }
}

function DisplayItemForm(form, itemName1, itemValue1, itemName2, itemValue2, itemName3, itemValue3, itemName4, itemValue4, itemName5, itemValue5)
{
   _DisplayItemForm(form, false, itemName1, itemValue1, itemName2, itemValue2, itemName3, itemValue3, itemName4, itemValue4, itemName5, itemValue5);
}

function DisplayInlineItemForm(form, itemName1, itemValue1, itemName2, itemValue2, itemName3, itemValue3, itemName4, itemValue4, itemName5, itemValue5)
{
   _DisplayItemForm(form, true, itemName1, itemValue1, itemName2, itemValue2, itemName3, itemValue3, itemName4, itemValue4, itemName5, itemValue5);
}



function Form_GetValues(elForm)
{
   if (!elForm instanceof Element) return;
   var fields = elForm.querySelectorAll('input, select, textarea');
   var o = {};
   for (var i = 0; i < fields.length; i++)
   {
      var field = fields[i];
      var sKey = field.name || field.id;
      if (field.type==='button' || field.type==='image' || field.type==='submit' || !sKey) 
         continue;
         
      switch (field.type)
      {
         case 'checkbox':
            o[sKey] = +field.checked;
            break;
         case 'radio':
            if (o[sKey]===undefined) o[sKey] = '';
            if (field.checked) o[sKey] = field.value;
            break;
         case 'select-multiple':
            var a = [];
            for (var j = 0; j < field.options.length; j++)
            {
               if (field.options[j].selected) 
                  a.push(field.options[j].value);
            }
            o[sKey] = a;
            break;
         default:
            o[sKey] = field.value;
            break;
      }
   }
   return o;
}

function Form_SetValues(elForm, o)
{
   if (!Utilities_IsObject(o)) return;
   for (var i in o)
   {
      var el = elForm.querySelector('[name=' + i + ']');
      if (el == null)
      {
         assert(0);
         continue;
      }
      if (el.type==='radio') 
         el = elForm.querySelectorAll('[name=' + i + ']');
      switch (typeof o[i])
      {
         case 'number':
            el.checked = o[i];
            break;
         case 'object':
            if (el.options && o[i] instanceof Array)
            {
               for (var j = 0; j < el.options.length; j++)
               {
                  if (o[i].indexOf(el.options[j].value) > -1) 
                     el.options[j].selected = true;
               }
            }
            break;
         default:
            if (el instanceof NodeList)
            {
               for (var j = 0; j < el.length; j++)
               {
                  if (el[j].value === o[i])
                     el[j].checked = true;
               }
            }
            else
            {
               el.value = o[i];
            }
            break;
      }
   }
}

// this sends some values to the parent windows FormValuesUpdated() function if there is one
function ParentWindowFormValuesUpdated(values, callbackItem)
{
   if (window.parent != window && window.parent.FormValuesUpdated)
      window.parent.FormValuesUpdated(Json_FromString(values), callbackItem);
}

function ConfirmActionUrl(message, url)
{
	if (confirm(message))
	{
      SubmitForm(url);
	}
}

function OnElemChanged(e)
{
   var form = Utilities_GetElementById("main_form");
   if (!form)
   {
      form = Utilities_GetEventTarget(e);
      while (form.parentNode != null && form.tagName != "FORM")
         form = form.parentNode;
   }
   var changed = Utilities_GetElementByName("FormHasChanged", form);
   if (changed != null && changed.value == "0") changed.value = "1";   // do not change a value of "2"
   
   return false;
}

function RefreshForm(formId, action)
{
   if (action == null) action = "Refresh";
   
   if (formId == null) formId = "main_form";
   var form = Utilities_GetElementById(formId);
   
   var formAction = Utilities_GetElementByName("FormAction", form);
   if (formAction != null)
      formAction.value = action;
      
   SubmitForm();
}

function ValidateForm(form)
{
   FormPrepValues.Prepare();   // make sure all form values have been "set" in the input elements
   
   var isValid = true;
   var inputs = Utilities_GetElementsByAttribute("required");
   for (var i = 0; i < inputs.length; i++)
   {
      // ignore elements in templates as these don't get used, and also remove the
      // "required" attribute so it doesn't trigger browser validation errors
      if (Utilities_GetParentByClass(inputs[i], 'MultiItemTemplate') != null)
      {
         inputs[i].required = false;
         continue;
      }
      
      var isError = inputs[i].value.length == 0;
      Class_SetByElement(inputs[i], "validation_error", isError);
      Visibility_SetById(inputs[i].name + "_validation_error", isError);
      if (isError)
         isValid = false;
   }
   if (!isValid)
   {
      DisplayErrorMessage("You are missing a required value, please correct.");
      return false;
   }
   
   return true;
}

function StripePaymentResponseHandler(status, response)
{
   // DRL FIXIT! We should store the form in case the ID is not main_form!
   var form = Utilities_GetElementById("main_form");
   var errors = Utilities_GetElementById("payment-errors", form);

   if (response.error)
   { 
      // Problem!

      // Show the errors on the form:
      if (errors)
      {
         Class_AddByElement(errors, "error_row");
         errors.innerHTML = response.error.message;
      }
      
      var submit = Utilities_GetElementByName("Submit", form);
      if (submit)
         submit.disabled = false; // Re-enable submission
   }
   else
   {
       // Token was created!

      // Remove the errors from the form:
      if (errors)
      {
         Class_RemoveByElement(errors, "error_row");
         errors.innerHTML = "";
      }

      // Get the token ID:
      var token = response.id;

      // Insert the token ID into the form so it gets submitted to the server:
      var input = document.createElement("input");
      input.type = "hidden";
      input.name = "payment_StripePaymentToken";
      input.value = token;
      form.appendChild(input);

      SubmitForm();
   }
   
   return false;
}

function SubmitForm(nextUrl, formId, formAction)
{
   var forms = Utilities_GetElementsByTag("form");

   if (formId == null) formId = "main_form";
   var form = Utilities_GetElementById(formId);

   if (forms.length > 0 && form == null)
   {
      DisplayErrorMessage("Your page is missing a form with ID " + formId + "! This is required for form processing.");
      return false;
   }

   var submitAction = Utilities_GetElementByName("FormAction", form);
   if (submitAction != null && formAction != null)
      submitAction.value = formAction;
      
   if (submitAction != null && submitAction.value == "Refresh")
      submitAction = false;
   else
      submitAction = true;
       
   var submitForm = false;
   var changed = null;
   var referralUrl = null;
   if (form != null && submitAction == true)
   {
      if (!ValidateForm(form))
         return false;
         
      var apiKey = Utilities_GetElementByName("payment_StripePublicKey", form);
      if (apiKey != null && Utilities_GetElementByName("payment_StripePaymentToken") == null)
      {
         Stripe.setPublishableKey(apiKey.value);
         
         // Disable the submit button to prevent repeated clicks:
         var submitButton = Utilities_GetElementByName("Submit", form);
         if (submitButton)
            submitButton.disabled = true;

         // Request a token from Stripe:
         Stripe.card.createToken(form, StripePaymentResponseHandler);

         // Prevent the form from being submitted:
         return false;
      }
      
      changed = Utilities_GetElementByName("FormHasChanged", form);
      if (changed != null)
      {
         if (changed.value == "0")
            changed = null;
         else
            changed = changed.value;
      }
      referralUrl = Utilities_GetElementByName("ReferralUrl", form);
      if (referralUrl != null) referralUrl = referralUrl.value;
   }
   
   // prevent onbeforeunload from showing message
   _Form_CheckToSave = false;
   
   if (form != null && changed != null)   // changed could be "1" or "2"
   {
      if (submitAction && nextUrl != null)
      {
         var input = document.createElement("INPUT");
         input.type = "hidden";
         input.name = "NextUrl";
         input.value = nextUrl;
         form.appendChild(input);
      }
      
      submitForm = true;
   }
   else if (nextUrl != null)
   {
      CancelForm(nextUrl, formId);
   }
   else if (referralUrl != null)
   {
      // handle same as cancel button in a form
      CancelForm(referralUrl, formId);
   }
   else if (form != null)
   {
      submitForm = true;
   }
   else
   {
      Log_Die("No URL to go to!");
   }
   
   if (submitForm)
   {
      if (_BackgroundExecutionQueue.length > 0) {
         alert("Please wait while your changes are being saved, then try again.");
         return;
      }

      // Firefox would not always submit the form using "form.submit()" so I action a submit button instead.
      var elems = Utilities_GetElementsByTag("input", form);
      var button = null;
   	for (var i = 0; i < elems.length; i++)
      {
         if (elems[i].getAttribute("type") == "submit")
         {
            button = elems[i];
            break;
         }
      }
   	if (button == null)
      {
         button = document.createElement('input');
         button.type = "submit";
         button.name = "Temp";
         button.value = "Temp";
         button.style.display = "none";
         form.appendChild(button);                              
      }
      button.click();
   }
}

function CancelForm(url, formId)
{
   var forms = Utilities_GetElementsByTag("form");

   if (formId == null) formId = "main_form";
   var form = Utilities_GetElementById(formId);

   if (forms.length > 0 && form == null)
   {
      DisplayErrorMessage("Your page is missing a form with ID " + formId + "! This is required for form processing.");
      return false;
   }

   var action = Utilities_GetElementByName("FormAction", form);
   if (action != null)
      action.value = "Cancel";
   
   // prevent onbeforeunload from showing message
   _Form_CheckToSave = false;
   
   if (url != null && url.length > 0)
   {
      GoToUrl(url);
   }
   else
   {
      ParentWindowFormValuesUpdated(null);   // notify parent window that the form was cancelled
      CloseParentWebWindow();
   }
   
   return false;
}

function GoToUrl(url)
{
   if (!SaveChangedForms(url))
   {
      if (typeof BusyIndicatorStart === "function")
         BusyIndicatorStart();
      
      // DRL FIXIT? I had to delay page load on iOS in order for the busy indicator to show!
      setTimeout(function() 
      {
         var tempForm = CreateFormFromUrl(url);
         document.body.appendChild(tempForm);
         tempForm.submit();
         document.body.removeChild(tempForm);
      }, 1);
   }
}

function InitializeForm()
{
   // initialize the enable/disabled state of the form controls...
	forEach(Utilities_GetElementsByClass('onchange_init'), function(elem)
	{
      var event = new Object();
      event.currentTarget = elem;
      if (typeof(elem.onchange) == 'function')
   		elem.onchange(event);
	});
   
   // hook up the form controls to set a flag when they are changed...
   var forms = Utilities_GetElementsByTag('form');
   for (var i = 0; i < forms.length; i++)
   {
      forEach(forms[i].elements, function(elem)
      {
         if (!('name' in elem) || elem.name != 'FormHasChanged')
            Utilities_AddEvent(elem, 'change', OnElemChanged);
      });
   }

   // initialize the clipboard support, alert on error
   forEach(Utilities_GetElementsByClass('clipboard_copy'), function(elem)
   {
      var clipboard = new Clipboard(elem);
      clipboard.on('error', function(e) {
         alert(e);
      });
   });
}

function SaveChangedForms(nextUrl)
{
   var result = false; 
   if (_BackgroundExecutionQueue.length > 0) {
      return true;
   }
   if (_Form_CheckToSave)
   {
      var forms = Utilities_GetElementsByTag("form");
   	for (var i = 0; i < forms.length; i++)
   	{
         var changed = Utilities_GetElementByName("FormHasChanged", forms[i]);
         if (changed != null && changed.value != "0")   // value of "2" means a form that always needs saving
         {
            var formId = forms[i].id;
            setTimeout(function()
            {
               SubmitForm(nextUrl, formId);
            }, 500);
            result = true;
         }
      }
   }
   return result;
}

function FormGetView()
{
   var url = location.search;
   if (url == null || url.length == 0)       // this could have been a POST so in that case
      url = Form_ThisUri;                    // use the URL that is saved in the page by the server
   var name = 'View';
   return decodeURIComponent((new RegExp('[?|&]' + name + '=' + '([^&;]+?)(&|#|;|$)').exec(url) || [null, ''])[1].replace(/\+/g, '%20')) || null;
}


Utilities_AddEvent(window, "beforeunload", function(e)
{
   if (!SaveChangedForms()) return;
   
   var message = "Please wait while your changes are being saved, then try again.";
   
   // For IE and Firefox
   var event = Utilities_GetEvent(e);
   event.returnValue = message;

   // For Safari
   return message;
});

Utilities_AddEvent(window, "submit", function(e)
{
   // prevent onbeforeunload from showing message
   _Form_CheckToSave = false;
   
   var form = Utilities_GetEventTarget(e);
   var changed = Utilities_GetElementByName("FormHasChanged", form);
   var formAction = Utilities_GetElementByName("FormAction", form);

   var label = "Please wait";
   if (formAction != null && formAction.value == "Refresh")
      label = "Refreshing page";
   else if (changed != null && changed.value == "1")
      label = "Saving changes";
   
   if (typeof BusyIndicatorStart === "function")
      BusyIndicatorStart(label + "...");
});

Utilities_AddEvent(window, "unload", function(e)
{
   // make sure we turn off the busy indicator in case the user navigates "back" to this page
   setTimeout(function()
   {
      if (typeof BusyIndicatorStart === "function")
         BusyIndicatorStop();
   }, 8000);
});

// for iOS when user hits "back" button it reloads page from cache...
Utilities_AddEvent(window, "pageshow", function(e)
{
   // make sure we turn off the busy indicator in case the user navigates "back" to this page
   var event = Utilities_GetEvent(e);
   if (event.persisted)  // means reloaded from cache
   {
      if (typeof BusyIndicatorStart === "function")
         BusyIndicatorStop();
   }
});

FormPrepValues =
{
	callbacks: new Array(),

	Prepare: function()
	{
      for (var i = 0; i < FormPrepValues.callbacks.length; i++)
      {
         FormPrepValues.callbacks[i]();
      }
    },
	
	AddCallback: function(callback)
	{
      for (var i = 0; i < FormPrepValues.callbacks.length; i++)
      {
         if (FormPrepValues.callbacks[i] == callback)
            return;   // already in the list
      }
		FormPrepValues.callbacks.push(callback);
	}
}

DocumentLoad.AddCallback(InitializeForm);

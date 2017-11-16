// ========================================================================
//        Copyright � 2012 Dominique Lacerte, All Rights Reserved.
// 
// Redistribution and use in source and binary forms are prohibited without 
//   prior written consent from Dominique Lacerte (internet@lacerte.org).
// ========================================================================

// Uses PopUpWindow.js which must also be included!!!
//
// Allows the showing of a message (error, info, etc.). 
//
// On-load usage (message window is shown when page loads):
//
//  <script type="text/javascript">
// 	  DisplayErrorMessage('This is an error message!');
//  </script>
//
// Javascript usage (message window is shown when call is made):
//
// 	DisplayErrorMessage('This is an error message!');
//
// Available methods:
//
// 	DisplayInfoMessage('This is an info message!');
// 	DisplaySuccessMessage('This is a success message!');
// 	DisplayWarningMessage('This is a warning message!');
// 	DisplayErrorMessage('This is an error message!');
// 	DisplayValidationMessage('This is a validation message!');
//
// Inline usage (message is shown in-line):
//
//  <div class="info">Info message</div>
//


// put our CSS into the document
document.write('<style type="text/css">' + 
/* These are the common styles for our messages. */
'.info_message, .success_message, .warning_message, .error_message, .validation_message' +
'{' +
//'	border: 1px solid;' +
'	padding:15px 10px 15px 50px;' +
'	background-repeat: no-repeat;' +
'	background-position: 10px 5px;' +
'}' +
/* This is for displaying an information message. */
'.info_message' +
'{' +
'	color: #00529B;' +
'	background-color: #BDE5F8;' +
'	background-image: url("/Images/Common/Info.png");' +
'}' +
/* This is for displaying a success message as a result of an action. */
'.success_message' +
'{' +
'	color: #4F8A10;' +
'	background-color: #DFF2BF;' +
'	background-image:url("/Images/Common/Valid.png");' +
'}' +
/* This is for displaying a warning message as a result of an action. */
'.warning_message' +
'{' +
'	color: #9F6000;' +
'	background-color: #FEEFB3;' +
'	background-image: url("/Images/Common/Attention.png");' +
'}' +
/* This is for displaying an error message as a result of an action. */
'.error_message' +
'{' +
'	color: #D8000C;' +
'	background-color: #FFBABA;' +
'	background-image: url("/Images/Common/Cancel.png");' +
'}' +
/* This is for displaying a validation error message as a result of submitting a form. */
'.validation_message' +
'{' +
'	color: #D63301;' +
'	background-color: #FFE0A0;' +
'	background-image: url("/Images/Common/Attention.png");' +
'}' +
'</style>');

function DisplayMessage(message, type)
{
	if (!Utilities_GetElementById('message_window'))
	{
		main = Utilities_CreateHtmlNode(
			"<DIV id=\"message_window\" style=\"display: none;\">" +
			"	<TABLE id=\"message_window_group\" style=\"height:100%;width:100%;table-layout:static;\"><TR><TD>" +
			"		<DIV id=\"message_window_content\" style=\"position: absolute; left: 50px; top: 0px; right: 0px; bottom: 30px; overflow:auto;\"></DIV>" +
			"		<DIV style=\"position: absolute; left: 0; right: 0; bottom: 0; height: 30px; text-align: center;\">" +
			"			<INPUT type='button' value='Close' onclick=\"CloseMessage();\">&nbsp;&nbsp;" +
			"		</DIV>" +
			"	</TD></TR></TABLE>" +
			"</DIV>");
		document.body.insertBefore(main,document.body.childNodes[0]);
	}
	Utilities_GetElementById('message_window_group').className = type + '_message';
	Utilities_GetElementById('message_window_content').innerHTML = message;
	DisplayPopUp('message_window', 'small');
}
function DisplayInfoMessage(message) { DisplayMessage(message, 'info'); }
function DisplaySuccessMessage(message) { DisplayMessage(message, 'success'); }
function DisplayWarningMessage(message) { DisplayMessage(message, 'warning'); }
function DisplayErrorMessage(message) { DisplayMessage(message, 'error'); }
function DisplayValidationMessage(message) { DisplayMessage(message, 'validation'); }

function CloseMessage()
{
	ClosePopUp();
}

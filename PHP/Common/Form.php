<?php

// ========================================================================
//        Copyright (c) 2012 Dominique Lacerte, All Rights Reserved.
// 
// Redistribution and use in source and binary forms are prohibited without 
//   prior written consent from Dominique Lacerte (internet@lacerte.org).
// ========================================================================


// ==============================================================
//
// Form Input Field Handling
//
// ==============================================================

function FormIsSet($name)
{
	return isset($_GET[$name]) || isset($_POST[$name]) || isset($_FILES[$name]);
}

function FormPrepInt($name, $value, $nullable = true)
{
	$value = Html::Escape($value);
	return "<INPUT type='text' name='$name' value='$value' size='20'>";
}

function FormParseIntValue($value, $nullable = true)
{
	if (is_array($value)) $value = join(',', $value);
	if ($nullable && (strlen($value) == 0 || strcmp($value, '_NULL_') == 0)) return NULL;
	return intval($value);
}

function FormParseInt($name, $nullable = true)
{
	$value = isset($_GET[$name]) ? $_GET[$name] : $_POST[$name];
	return FormParseIntValue($value, $nullable);
}

function FormPrepDec($name, $value, $nullable = true)
{
	$value = Html::Escape($value);
	return "<INPUT type='text' name='$name' value='$value' size='20'>";
}

function FormParseDecValue($value, $nullable = true)
{
	if (is_array($value)) $value = join(',', $value);
	if ($nullable && (strlen($value) == 0 || strcmp($value, '_NULL_') == 0)) return NULL;
	return floatval($value);
}

function FormParseDec($name, $nullable = true)
{
	$value = isset($_GET[$name]) ? $_GET[$name] : $_POST[$name];
	return FormParseDecValue($value, $nullable);
}

function FormPrepStr($name, $value, $nullable = true)
{
	$value = Html::Escape($value);
	return "<INPUT type='text' name='$name' value='$value' size='60'>";
}

function FormParseStrValue($value, $nullable = true)
{
	if (is_array($value)) $value = join(',', $value);
	if ($nullable && strlen($value) == 0) return NULL;
	return $value;
}

function FormParseStr($name, $nullable = true)
{
	$value = isset($_GET[$name]) ? $_GET[$name] : $_POST[$name];
	return FormParseStrValue($value, $nullable);
}

function FormParseFile($name)
{
   $result = array(
      'Error' => NULL,
//      'Data' => NULL,
      'DataFile' => NULL,
      'Name' => NULL,
      'MimeType' => NULL
   );
   
	if (!isset($_FILES[$name])) return $result;
   
	if (!isset($_FILES[$name]['error']) || is_array($_FILES[$name]['error']))
	{
		WriteError("File upload is missing 'error' value");
		return $result;
	}
   
   $error = NULL;
//   $data = NULL;
   $dataFile = NULL;
   $filename = NULL;
   $mimeType = NULL;
   
	switch ($_FILES[$name]['error'])
	{
	case UPLOAD_ERR_OK:
      $dataFile = $_FILES[$name]['tmp_name'];
//		$data = File::ReadTextFile($_FILES[$name]['tmp_name']);
      $filename = $_FILES[$name]['name'];
      $mimeType = isset($_FILES[$name]['type']) ? $_FILES[$name]['type'] : NULL;
      if (empty($mimeType))
         $mimeType = MimeTypes::GetMimeTypeFromExtension(File::GetExtension($filename));
		break;
	case UPLOAD_ERR_NO_FILE:
      // form entry field was left empty, not really an error
		break;
	case UPLOAD_ERR_INI_SIZE:
	case UPLOAD_ERR_FORM_SIZE:
		$error = 'File too big';
		break;
	default:
		$error = 'Unknown file upload error';
		break;
	}
   
   $result['Error'] = $error;
//   $result['Data'] = $data;
   $result['DataFile'] = $dataFile;
   $result['Name'] = $filename;
   $result['MimeType'] = $mimeType;
   
   return $result;
}

function FormPrepSelectKeyValueOptions($name, $value, $options, $htmlOptions = 0, $class = NULL, $onChangeScript = NULL)
{
	$htmlOptions |= Html::$SelectDropDown | Html::$SelectKeyValue;
	return Html::SelectOptions($name, $value, $options, $htmlOptions, $class, NULL, $onChangeScript);
}

function FormPrepSelectMultiKeyValueOptions($name, $value, $options, $htmlOptions = 0, $class = NULL, $onChangeScript = NULL)
{
	$htmlOptions |= Html::$SelectMultiple | Html::$SelectCheckBoxes | Html::$SelectKeyValue;
	return Html::SelectOptions($name, $value, $options, $htmlOptions, $class, NULL, $onChangeScript);
}

function FormPrepSelectArrayOptions($name, $value, $options, $htmlOptions = 0, $class = NULL, $onChangeScript = NULL)
{
	$htmlOptions |= Html::$SelectDropDown;
	return Html::SelectOptions($name, $value, $options, $htmlOptions, $class, NULL, $onChangeScript);
}

function FormPrepSelectMultiArrayOptions($name, $value, $options, $htmlOptions = 0, $class = NULL, $onChangeScript = NULL)
{
	$htmlOptions |= Html::$SelectMultiple | Html::$SelectCheckBoxes;
	return Html::SelectOptions($name, $value, $options, $htmlOptions, $class, NULL, $onChangeScript);
}

function FormParseSelectOptionsValue($value, $nullable = true)
{
	if (is_array($value)) $value = join(',', $value);
	// Html::SelectOptions() uses this special value for NULL
	if ($nullable && strcmp($value,'_NULL_') == 0) return NULL;
	return $value;
}

function FormParseSelectOptions($name, $nullable = true)
{
	$value = isset($_GET[$name]) ? $_GET[$name] : $_POST[$name];
	return FormParseSelectOptionsValue($value, $nullable);
}

function FormPrepStrArray($name, $values, $class = NULL)
{
	if (is_null($class))
		$class = "";
	else
		$class = "class='$class'";
		
	$html = "<DIV id='$name' class='MultiItem'>" .
		"<DIV class='MultiItemTemplate'><INPUT type='text' name='$name' value='' $class></DIV>";
	$i = 0;
	$values = Utilities::StringToArray($values);
	foreach ($values as $value)
	{
		$html .= "<DIV><INPUT type='text' name='$name$i' value='$value' $class></DIV>";
		$i++;
	}
	$html .= "</DIV>";
	
	return $html;
}

// handles both MultiItem (above) and SelectOptions type lists
function FormParseStrArray($name, $nullable = true)
{
	// check for SelectOptions type
	if (isset($_GET[$name]))
   {
		return FormParseStrValue($_GET[$name], $nullable);
   }
	if (isset($_POST[$name]) && !empty($_POST[$name]) && $_POST[$name] != '_NULL_')	// check for empty as MultiSelect has one empty item
   {
		return FormParseStrValue($_POST[$name], $nullable);
   }
	
	$values = '';
	$i = 0;
	$missedCount = 0;	// we'll stop looking when we don't find anything after 20 attempts
	while ($missedCount < 20)
	{
		if (isset($_GET["$name$i"]) || isset($_POST["$name$i"]))
		{
			if (strlen($values) > 0) $values .= ',';
			$values .= isset($_GET["$name$i"]) ? $_GET["$name$i"] : $_POST["$name$i"];
			
			$missedCount = 0;
		}
		else
			$missedCount++;
		$i++;
	}
	return FormParseStrValue($values, $nullable);
}

function FormParseBoolValue($value, $nullable = true)
{
	if (is_array($value)) $value = join(',', $value);
	if ($nullable && (strlen($value) == 0 || strcmp($value, '_NULL_') == 0)) return NULL;
	if (strcasecmp($value, 'TRUE') == 0) return true;
	if (strcmp($value, '1') == 0) return true;
	if ($value === true) return true;
	return false;
}

function FormParseBool($name, $nullable = true)
{
	$value;
	if (isset($_GET[$name]))
		$value = $_GET[$name];
	elseif (isset($_POST[$name]))
		$value = $_POST[$name];
	else
		$value = 'FALSE';
	return FormParseBoolValue($value, $nullable);
}

function FormParseDateValue($value, $nullable = true)
{
	if (is_array($value)) $value = join(',', $value);
	if ($nullable && strlen($value) == 0) return NULL;
	return $value;
}

function FormParseDate($name, $nullable = true)
{
	$value = isset($_GET[$name]) ? $_GET[$name] : $_POST[$name];
	return FormParseDateValue($value, $nullable);
}

function FormParseEpochMilliValue($value, $nullable = true)
{
	if (is_array($value)) $value = join(',', $value);
	if ($nullable && strlen($value) == 0) return NULL;
	return $value;
}

function FormParseEpochMilli($name, $nullable = true)
{
	$value = isset($_GET[$name]) ? $_GET[$name] : $_POST[$name];
	return FormParseEpochMilliValue($value, $nullable);
}

function FormParseEpochValue($value, $nullable = true)
{
	if (is_array($value)) $value = join(',', $value);
	if ($nullable && strlen($value) == 0) return NULL;
	return $value;
}

function FormParseEpoch($name, $nullable = true)
{
	$value = isset($_GET[$name]) ? $_GET[$name] : $_POST[$name];
	return FormParseEpochValue($value, $nullable);
}

// ==============================================================
//
// Form Helpers
//
// ==============================================================

// takes a hash and converts the name=vaue pairs into form fields
function FormSaveAsFields($values)
{
   $temp = '';
   
   foreach ($values as $name => $value)
   {
      $temp .= "<INPUT type='hidden' name='$name' value='$value'>";
   }
   
   return $temp;
}

// returns an array of name=value pairs with all the form values
function FormParseFields()
{
   $result = array();
   
	if (count($_GET) > count($_POST))
   {
      foreach ($_GET as $name => $value)
      {
         $result[$name] = FormParseStrValue($value);
      }
   }
   else
   {
      foreach ($_POST as $name => $value)
      {
         $result[$name] = FormParseStrValue($value);
      }
   }
   
	return $result;
}

// ==============================================================
//
// Form Buttons
//
// ==============================================================

function ActionButton($label, $classes, $action, $icon = NULL, $optionalLabel = true)
{
   global $ImagesBaseUri;
   
   if (!empty($label))
      $label = ' ' . $label;      
   if (!empty($icon))
   {
      if (strpos(strtolower($icon), 'http') === false)
         $icon = $ImagesBaseUri . $icon;
         
      $icon = "<img class='iconsmall' title='$label' alt='$label' src='$icon'>";
      if (!empty($label) && $optionalLabel)
         $label = "<span class='optional'>$label</span>";
   }
   else
      $icon = '';
   return "<button class='$classes' onclick='$action; return false;'>$icon$label</button>";
}

function MenuAction($label, $classes, $action, $icon = NULL)
{
   global $ImagesBaseUri;
   
   if (!empty($icon))
   {
      if (strpos(strtolower($icon), 'http') === false)
         $icon = $ImagesBaseUri . $icon;
         
      $icon = "<img class='iconsmall' src='$icon'>";
      if (!empty($label))
         $label = " $label";
   }
   else
      $icon = '';
   return "<li><button class='$classes' onclick='$action; return false;'>$icon$label</button></li>";
}

function AppIconLink($label, $url = NULL, $icon = NULL)
{
   global $ImagesBaseUri;
   
   if (!empty($icon))
   {
      if (strpos(strtolower($icon), 'http') === false)
         $icon = $ImagesBaseUri . $icon;
         
      $icon = "<img class='tile_icon' src='$icon'>";
      if (!empty($label))
         $label = " $label";
   }
   else
//      $icon = '';
      $icon = "<img class='tile_icon' src='$ImagesBaseUri/Common/SubItemIcon.png'>";
   
   $action = "return SubmitForm(\"$url\");";
   return "<A class='tile_button' onclick='$action'>$icon<BR>$label</A>";
}

function AppIconAction($label, $action = NULL, $icon = NULL)
{
   global $ImagesBaseUri;
   
   if (!empty($icon))
   {
      if (strpos(strtolower($icon), 'http') === false)
         $icon = $ImagesBaseUri . $icon;
         
      $icon = "<img class='tile_icon' src='$icon'>";
      if (!empty($label))
         $label = " $label";
   }
   else
//      $icon = '';
      $icon = "<img class='tile_icon' src='$ImagesBaseUri/Common/SubItemIcon.png'>";
      
   return "<A class='tile_button' onclick='$action; return false;'>$icon<BR>$label</A>";
}

function MenuLink($label, $url = NULL, $icon = NULL, $targetName = NULL)
{
   global $ImagesBaseUri;
   
   if (!empty($icon))
   {
      if (strpos(strtolower($icon), 'http') === false)
         $icon = $ImagesBaseUri . $icon;
         
      $icon = "<img class='iconsmall' src='$icon'>";
      if (!empty($label))
         $label = " $label";
   }
   else
//      $icon = '';
      $icon = "<img class='iconsmall' src='$ImagesBaseUri/Common/SubItemIcon.png'>";
   
   $prot = Url::GetProtocol($url);
   if (empty($url))
   {
      $url = 'return false;';
   }
   else if (!empty($targetName))
   {
      $url = "window.open(\"$url\", \"$targetName\").focus();";
   }
   else if ($prot != 'http' && $prot != 'https')
   {
      // off-site links don't require submitting the form
      $url = "BusyIndicatorStart(); window.location=\"$url\";";
   }
   else
   {
      $url = "return SubmitForm(\"$url\");";
   }
      
   return "<button onclick='$url'>$icon$label</button>";
}

function MiscLink($label, $url, $icon = NULL, $optionalLabel = true, $targetName = NULL)
{
   global $ImagesBaseUri;
   
   if (!empty($label))
      $label = ' ' . $label;      
   if (!empty($icon))
   {
      if (strpos(strtolower($icon), 'http') === false)
         $icon = $ImagesBaseUri . $icon;
         
      $icon = "<img class='iconsmall' title='$label' alt='$label' src='$icon'>";
      if (!empty($label) && $optionalLabel)
         $label = "<span class='optional'>$label</span>";
   }
   else
      $icon = '';
   
   $prot = Url::GetProtocol($url);
   if (empty($url))
   {
      $url = 'return false;';
   }
   else if (!empty($targetName))
   {
      $url = "window.open(\"$url\", \"$targetName\").focus();";
   }
   else if ($prot != 'http' && $prot != 'https')
   {
      // off-site links don't require submitting the form
      $url = "BusyIndicatorStart(); window.location=\"$url\";";
   }
   else
   {
      $url = "return SubmitForm(\"$url\");";
   }
   
   return "<a onclick='$url'>$icon$label</a>";
}

function MiscAction($label, $action, $icon = NULL, $optionalLabel = true)
{
   global $ImagesBaseUri;
   
   if (!empty($label))
      $label = ' ' . $label;
   if (!empty($icon))
   {
      if (strpos(strtolower($icon), 'http') === false)
         $icon = $ImagesBaseUri . $icon;
      
      $icon = "<img class='iconsmall' title='$label' alt='$label' src='$icon'>";
      if (!empty($label) && $optionalLabel)
         $label = "<span class='optional'>$label</span>";
   }
   else
      $icon = '';
   return "<a onclick='$action; return false;'>$icon$label</a>";
}

function CopyToClipboardAction($label, $classes, $text, $icon = NULL)
{
   global $ImagesBaseUri;
   
   $classes .= ' clipboard_copy';   // hooks up to Clipboard support in Form.js
   
   if (!empty($label))
      $label = ' ' . $label;      
   if (!empty($icon))
   {
      if (strpos(strtolower($icon), 'http') === false)
         $icon = $ImagesBaseUri . $icon;
         
      $icon = "<img class='iconsmall' title='$label' alt='$label' src='$icon'>";
      if (!empty($label))
         $label = "<span class='optional'>$label</span>";
   }
   else
      $icon = '';
   $text = Utilities::ReplaceInString($text, "'", '&#39;');
   return "<a class='$classes' onclick='return false;' data-clipboard-text='$text'>$icon$label</a>";
}

function SubmitButton($label, $formID=NULL, $formAction=NULL, $icon = NULL)
{
   global $ImagesBaseUri;

   $formID = $formID == NULL ? $formID = 'null' : "\"$formID\"";

   if ($formAction === NULL)
      $formAction = 'null';
   else
      $formAction = '"' . $formAction . '"';

   if (empty($icon))
      $icon = '/Common/OkIcon.png';
   if (strpos(strtolower($icon), 'http') === false)
      $icon = $ImagesBaseUri . $icon;
   
   $icon = "<img class='iconsmall' title='$label' alt='$label' src='$icon'>";
   if (!empty($label))
      $label = "<span class='optional'>$label</span>";

   return "<button onclick='return SubmitForm(null, $formID, $formAction);'>$label $icon</button>";
}

function CancelButton($label, $formID=NULL, $referralUrl=NULL, $icon = NULL)
{
   global $ImagesBaseUri;
   
   $formID = $formID == NULL ? $formID = 'null' : "\"$formID\"";

   if ($referralUrl == NULL)
   {
      // referral URL is not provided if the form is in an iFrame in which case we just close the iFrame
      $referralUrl = FormIsSet('ReferralUrl') ? FormParseStr('ReferralUrl') : '';
   }
   if ($referralUrl)
      $referralUrl = "\"$referralUrl\"";
   else
      $referralUrl = 'null';
   
   if (empty($icon))
      $icon = '/Common/CancelIcon.png';
   if (strpos(strtolower($icon), 'http') === false)
      $icon = $ImagesBaseUri . $icon;
   
   $icon = "<img class='iconsmall' title='$label' alt='$label' src='$icon'>";
   if (!empty($label))
      $label = "<span class='optional'>$label</span>";

   return "<button onclick='return CancelForm($referralUrl, $formID);'>$label $icon</button>";
}

// ==============================================================
//
// Form Setup
//
// ==============================================================

// the "alwaysSubmit" parameter is used in cases where you want the form to submit even 
// if there are no changes, such as a form with no fields but with an action on submit
function FormTop($session, $alwaysSubmit=NULL, $formClasses=NULL)
{
	global $MainUri;
   
   $hasChanged = null;
   if ($alwaysSubmit == NULL)   // standard form
   {
      // if the form has been submit and we're sending it out again with changes
      $hasChanged = FormIsSet('FormAction') ? '1' : '0';
   }
   else
   {
      $hasChanged = $alwaysSubmit ? '2' : '0';   // use "2" for this special case as it has different handling
   }
   
	$formProcessor = FormIsSet('FormProcessor') ? FormParseStr('FormProcessor') : '';
	$referralUrl = FormIsSet('ReferralUrl') ? FormParseStr('ReferralUrl') : '';
   
   if (!empty($formProcessor)) $formProcessor = "<INPUT type='hidden' name='FormProcessor' value='$formProcessor'>";
   if (!empty($referralUrl)) $referralUrl = "<INPUT type='hidden' name='ReferralUrl' value='$referralUrl'>";
   
   if ($formClasses === NULL) $formClasses = 'content_wrapper';
   
   return "
      <FORM id='main_form' class='$formClasses' action='$MainUri' method='POST' enctype='multipart/form-data'>
      <INPUT type='hidden' name='FormAction' value='Submit'>
      <input type='hidden' name='FormHasChanged' value='$hasChanged' />
      $formProcessor
      $referralUrl" .
      $session->GetFieldsForForm();
}

function FormBottom($actionHtml='')
{
   return "<div class='form_actions'>$actionHtml</div></FORM>";
}

function FormFullPageMessage($message, $message_type='error', $isHtml=false)
{
   if (!$isHtml)
      $message = Html::Encode($message);
   
   $actionHtml = CancelButton('OK', NULL, NULL, '/Common/OkIcon.png');
   
   return "
<div class='content_wrapper'>
   <div class='content_dialog'>
      <div class='" . $message_type . "_message'>$message</div>
   </div>
   <div class='form_actions'>$actionHtml</div>
</div>";
}

function FormMessage($message, $message_type='error', $isHtml=false)
{
   if (empty($message))
      return '';
   
   if (!$isHtml)
      $message = Html::Encode($message);

   return "<div class='" . $message_type . "_message'>$message</div>";
}

?>
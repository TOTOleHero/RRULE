<?php

// ========================================================================
//        Copyright (c) 2010 Dominique Lacerte, All Rights Reserved.
// 
// Redistribution and use in source and binary forms are prohibited without 
//   prior written consent from Dominique Lacerte (internet@lacerte.org).
// ========================================================================

require_once(dirname(__FILE__) . '/Utilities.php');
require_once(dirname(__FILE__) . '/../ThirdParty/html2text/src/Html2Text.php');

class Html
{
// select options may be combined
	// capabilities:
	static $SelectDisabled = 1;		// the control is disabled
	static $SelectNullable = 2;		// the selection may be empty (or special value "_NULL_" selected)
	static $SelectMultiple = 4;		// multiple items may be selected
	// type of control:
	static $SelectDropDown = 8;		// the list is shown in a drop-down
	static $SelectCheckBoxes = 16;	// the list is shown as checkboxes
	// data values:
	static $SelectKeyValue = 32;	   // the keys are used as the "values" and the values are displayed
	static $SelectIgnoreCase = 64;	// ignore the case of the key
	static $SelectHorizontal = 128;	// show list of items going horizontally instead of vertically
   static $SelectNoFilter = 256;    // don't use the FilterSelect class as it doesn't work in some scenarios
   static $SelectSort = 512;        // display the list of options sorted
   static $SelectIgnoreSlash = 1024;// don't treat a slash (/) as a path seperator

	private static $DEBUGGING = 1;

	// when the page loads, immediately redirects to another page
	static function InLineRedirect($url)
	{
		return "
			<SCRIPT language=\"javascript\" type=\"text/javascript\"> 
			function RedirectToPrevious()
			{
				document.location.href=\"$url\"; 
			}
			DocumentLoad.AddCallback(RedirectToPrevious);
			</SCRIPT>";
	}
	
	// when the page loads, immediately redirects the parent (i.e. iFrame host) to another page
	static function InLineParentRedirect($url)
	{
		return "
			<SCRIPT language=\"javascript\" type=\"text/javascript\"> 
			function RedirectParentToPrevious()
			{
				parent.document.location.href=\"$url\"; 
			}
			DocumentLoad.AddCallback(RedirectParentToPrevious);
			</SCRIPT>";
	}
	
	static function GetMessageWindowHtml($message, $type)
	{
		// this code makes use of MessageWindow.js which must be included in the page
		// $type: info, success, warning, error, validation
		
		$message = Utilities::ReplaceInString($message, "'", "\'");
		$message = Utilities::ReplaceInString($message, "\n", "");
		$message = Utilities::ReplaceInString($message, "\r", "");
		return "
			<script type=\"text/javascript\">
			DisplayMessage('$message','$type');
			</script>";
	}
	
	//    If the LINE_BREAKS parameter is present and true then
	//    line breaks in the input will be converted to html <br />
	//    tags in the output.
	//
	//    If the ALLOW parameter is present and true then most
	//    harmless tags will be left in, otherwise all tags will be
	//    removed.
	static function MakeSafe($text, $line_breaks = NULL, $allow_html = NULL)
	{
      // DRL FIXIT! Some parameters are ignored!
      return preg_replace("/\n\s+/", "\n", rtrim(html_entity_decode(strip_tags($text)))); 
	}
	
	// subroutine to escape the necessary characters to the appropriate HTML
	// entities
	static function Encode($str, $escapeLineFeeds=true)
	{
	  $str = Html::Escape($str);
     if ($escapeLineFeeds)
   	  $str = Utilities::ReplaceInString($str, "\n", "<BR>\n");
	
	  return $str;
	}
	
	// subroutine to escape the necessary characters to the appropriate HTML
	// entities
	static function Escape($str)
	{
		return htmlspecialchars($str, ENT_QUOTES);	// encode single quotes too
	}
	
	// Note that some entites have no 8-bit character equivalent,
	// see "http://www.w3.org/TR/xhtml1/DTD/xhtml-symbol.ent"
	// for some examples.  unescape() leaves these entities
	// in their encoded form.
	static function Unescape($str)
	{
		// DRL FIXIT!
		return $str;
	}
   
   static function HtmlToText($str)
   {
//      return preg_replace("/\n\s+/", "\n", rtrim(html_entity_decode(strip_tags($str)))); 

      $html2Text = new Html2Text\Html2Text($str);
      return trim($html2Text->getText());   // the conversion leaves blank lines before and after the text so we trim it
   }
   
   // remove everything outside of and including the HTML <BODY>
   static function GetBodyContent($html)
   {
      $temp = strtolower($html);
      $iStart = strpos($temp, '<body');
      $iEnd = strlen($temp);
      if ($iStart !== false)
      {
         $iStart = strpos($temp, '>', $iStart) + 1;
         $iEnd = strpos($temp, '</body>');
         if ($iEnd === false)
            $iEnd = strlen($temp);
      }
      else
      {
         $iStart = 0;
      }
      return substr($html, $iStart, $iEnd - $iStart);
   }

   static function DisplayLink($link, $protocol=NULL, $label=NULL, $maxLength=NULL, $newTab=false, $optional=false, $icon=NULL)
   {
      global $ImagesBaseUri;   // DRL FIXIT! This is set in Constants.php for the particular Web site!

      if (empty($link)) return '';
      if (empty($label))
      {
         $label = Url::StripParams($link);
         $label = Utilities::ReplaceInString($label, '/', '/ ');   // add spaces so the string can wrap
         $label = Utilities::ReplaceInString($label, '/ / ', '//');// but keep http://domain all together
      }
      $title = $label;
      $label2 = '';
      if ($maxLength !== NULL)
      {
         if ($maxLength == 0)
            $label = '';   // no label is wanted
         else if ($maxLength == -1)
         {
            $label2 = $label;   // post label is wanted
            $label = '';
         }
         else
            $label = Utilities::ShortenWithEllipsis($label, $maxLength);
      }
      if ($label != $title && $label2 != $title)
         $title = " title='$title'";
      else
         $title = '';      // label is same as title, no need to show it
      $prot = Url::GetProtocol($link);
      if ($prot != NULL)
         $link = Url::StripProtocol($link);
      else
         $prot = $protocol;
      $prot = strtolower($prot) . ':';
      if ($prot == 'tel:' || $prot == 'sip:')
      {
         if (empty($icon)) $icon = '/Common/PhoneIcon.png';
      }
      else if ($prot == 'mailto:')
      {
         if (empty($icon)) $icon = '/Common/EmailButtonIcon.png';
      }
      else if ($prot == 'sms:')
      {
         if (empty($icon)) $icon = '/Common/ChatIcon.png';
      }
      else if ($prot == 'skype:')
      {
         if (empty($icon)) $icon = '/Common/SkypeIcon.png';
      }
      else
      {
         $prot .= '//';
         if (!$newTab)
            $title .= " class='busy_onclick'";   // don't show busy msg if opening in a new window
//         $icon = '/Common/DocumentIcon.png';
      }
      if (empty($icon))
      {
         $icon = '';
      }
      else
      {
         if (strpos(strtolower($icon), 'http') === false)
            $icon = $ImagesBaseUri . $icon;
         $icon = "<img class='iconsmall' src='$icon'>";
         if (!empty($label))
            $label .= ' '; // space between label and icon
         if (!empty($label2))
            $label2 = ' ' . $label2; // space between label and icon
      }
      if ($optional && !empty($label))
         $label = "<span class='optional'>$label</span>";
      if ($optional && !empty($label2))
         $label2 = "<span class='optional'>$label2</span>";

      $tab = $newTab ? ' target=\'_blank\'' : '';
      $linkStart = "<a href='$prot$link'$tab$title>";
      $linkEnd = '</a>';
      if (!$newTab && strpos(strtolower($prot), 'http') === 0)
      {
         // DRL FIXIT? I had to delay page load on iOS in order for the busy indicator to show, so call SubmitForm() instead.
         $linkStart = "<a onclick='return SubmitForm(\"$prot$link\");'>";
      }
      if (empty($link))
         $linkStart = $linkEnd = '';
      return "$linkStart$label$icon$label2$linkEnd";
   }
   
   static function _Padding($count)
   {
      $result = '';
      while ($count > 0)
      {
         $result .= '&nbsp;&nbsp;&nbsp;';
         $count--;
      }
      return $result;
   }
	
	static function SelectOptions($name, $selectedItemOrItems, &$ref_items, $options = NULL, $class = NULL, $extra = NULL, $onChangeScript = NULL)
	{
		// the "extra" parameter is added to each item with the following string replacements:
		// %value% replaced with the item value (only for checkboxes)
		// %id% replaced with the item id

		$selectedItems = NULL;	// use array only for multiselect
		$items = $ref_items;
		$result = "";
		$multiple = "";
		$disabled = "";
		$type = NULL;
	
      if ($options & Html::$SelectHorizontal)
         $lineBreak = "<BR class='mobile_only'/>\n";   // can't use horizontal on mobile
      else
         $lineBreak = "<BR/>\n";
         
		if (is_null($options)) $options = 0;
		if (is_null($class))
			$class = "";
		else
			$class = "class='$class'";
		if (is_null($extra)) $extra = "";
		if (is_null($onChangeScript))
			$onChangeScript = "";
		else
			$onChangeScript = "onchange=\"$onChangeScript\"";

      $ignoreCase = false;
		if ($options & Html::$SelectIgnoreCase)
			$ignoreCase = true;
         
		if ($options & Html::$SelectDisabled)
			$disabled = "DISABLED";
	
		if ($options & Html::$SelectMultiple)
		{
			// should be passing a reference to an array
			$selectedItems = $selectedItemOrItems;
			$multiple = "MULTIPLE";
			$type = "CHECKBOX";
		}
		else
		{
			// should be passing a simple item
			$selectedItems = array($selectedItemOrItems);
			$type = "RADIO";
		}
	
		if ($options & Html::$SelectSort)
		{
   		if ($options & Html::$SelectKeyValue)
   		{
            asort($items, SORT_STRING | SORT_FLAG_CASE);
         }
         else
         {
            sort($items, SORT_STRING | SORT_FLAG_CASE);
         }
      }
      
		if ($options & Html::$SelectCheckBoxes)
		{
			if ($options & Html::$SelectKeyValue)
			{
				$value = NULL;
				foreach ($items as $key => $value)
				{
					// for checkboxes, NULL is not a selection, it's an absence of any selection
					if (strcmp($key, '_NULL_') != 0)
					{
						$id = $name . '_' . Utilities::RemoveNonAlphanumericCharacters($key);
						$temp = Utilities::ReplaceInString($extra, '%value%', $value);
						$temp = Utilities::ReplaceInString($temp, '%id%', $id);
						$temp .= ' ' . $class . ' ' . $onChangeScript;
						$value = Html::Escape($value);
						if (Utilities::ArrayContains($selectedItems, $key, $ignoreCase))
						{
							$result .= "<LABEL><INPUT TYPE=$type ID=\"$id\" NAME=\"$name" . "[]\" VALUE=\"$key\" $temp $disabled CHECKED>$value</LABEL>";
						}
						else
						{
							$result .= "<LABEL><INPUT TYPE=$type ID=\"$id\" NAME=\"$name" . "[]\" VALUE=\"$key\" $temp $disabled>$value</LABEL>";
						}
                  
                  $result .= $lineBreak;
					}
				}
			}
			else
			{
				$value = NULL;
				foreach ($items as $value)
				{
					$id = $name . '_' . Utilities::RemoveNonAlphanumericCharacters($value);
					$temp = Utilities::ReplaceInString($extra, '%value%', $value);
					$temp = Utilities::ReplaceInString($temp, '%id%', $id);
					$temp .= ' ' . $class . ' ' . $onChangeScript;
					$value = Html::Escape($value);
					if (Utilities::ArrayContains($selectedItems, $value, $ignoreCase))
					{
						$result .= "<LABEL><INPUT TYPE=$type ID=\"$id\" NAME=\"$name" . "[]\" VALUE=\"$value\" $temp $disabled CHECKED>$value</LABEL>";
					}
					else
					{
						$result .= "<LABEL><INPUT TYPE=$type ID=\"$id\" NAME=\"$name" . "[]\" VALUE=\"$value\" $temp $disabled>$value</LABEL>";
					}
                  
               $result .= $lineBreak;
				}
			}
		}
		else
		{
			$size = '';
         $shorten = true;
			
			if ($options & Html::$SelectMultiple)
			{
				$size = count($items);
				if ($size > 10)
				{
					$size = 10;
				}
				$size = "SIZE=" . $size;
			}
			
			if ($options & Html::$SelectDropDown)
			{
				if ($options & Html::$SelectMultiple)
				{
               // multiselect uses FilterSelect automatically for us
               
					if (empty($class))
						$class = "class='MultiSelect'";
					else
						$class = substr($class, 0, -1) . " MultiSelect'";
                  
               if ($options & Html::$SelectHorizontal)
                  $class = substr($class, 0, -1) . " MultiSelectHorizontal'";
                  
               $shorten = false;    // multiselect script shortens for us
				}
				else
				{
               if (!($options & Html::$SelectNoFilter))
               {
   					if (empty($class))
   						$class = "class='FilterSelect'";
   					else
   						$class = substr($class, 0, -1) . " FilterSelect'";
               }
		
					$size = "SIZE=1";

					if ($options & Html::$SelectNullable && 
						// don't add our NULL item if the options already contain one
						!Utilities::ArrayContains($items, NULL) &&
						!array_key_exists('_NULL_', $items))
					{
						// DRL FIXIT? There's no real way for the form to return 
						// NULL versus a string containing NULL so we use a special
						// value which the handling code will have to look for.
						if (!Utilities::ArrayContains($selectedItems, NULL) && 
							!Utilities::ArrayContains($selectedItems, '_NULL_'))
						{
							$result .= "<OPTION VALUE=\"_NULL_\">---</OPTION>\n";
						}
						else
						{
							$result .= "<OPTION VALUE=\"_NULL_\" SELECTED>---</OPTION>\n";
						}
					}
				}
			}
	
			if ($options & Html::$SelectKeyValue)
			{
            $path = NULL;
            $display = NULL;
            $lastPath = '';
            $padding = '';
				foreach ($items as $key => $value)
				{
					// if NULL is not an option but it appears in the list, skip it
					if (!($options & Html::$SelectNullable) && (is_null($key) || strcmp($key, '_NULL_') == 0))
						continue;
					if ($options & Html::$SelectIgnoreSlash)
               {
                  $display = $value;
               }
               else
               {
                  $path = Html::_GetPath($value);
                  if ($path != $lastPath)
                  {
                     $oldSegments = !empty($lastPath) ? explode('/', $lastPath) : array();
                     $newSegments = !empty($path) ? explode('/', $path) : array();
                     
                     $i = 0;
                     while ($i < count($oldSegments) && $i < count($newSegments) &&
                        $oldSegments[$i] == $newSegments[$i]) 
                        $i++;
                     
                     for ($j = $i; $j < count($oldSegments); $j++)
      						$result .= "</optgroup>\n";
                     for ($j = $i; $j < count($newSegments); $j++)
                     {
                        $display = $newSegments[$j];
                        if ($shorten)
                           $display = Utilities::ShortenWithCenterEllipsis($display, 32);   // 32 is about max for mobile in portrait mode
                        $padding = Html::_Padding($j);
      						$result .= "<optgroup label=\"$padding$display\">\n";
                     }
                     
                     $padding = Html::_Padding(count($newSegments));
                     $lastPath = $path;
                  }

                  $display = Html::_GetFilename($value);
               }
               if ($shorten)
                  $display = Utilities::ShortenWithCenterEllipsis($display, 32);   // 32 is about max for mobile in portrait mode
					$display = Html::Escape($display);
                    $escapedValue = Html::Escape($value);
				    if (Utilities::ArrayContains($selectedItems, $key) || 
						(is_null($key) && Utilities::ArrayContains($selectedItems, '_NULL_')) ||
						(Utilities::StringEqualsOrBothUndef($key, '_NULL_') && Utilities::ArrayContains($selectedItems, NULL)))
					{
						$result .= "<OPTION VALUE=\"$key\" fullvalue=\"$escapedValue\" SELECTED>$padding$display</OPTION>\n";
					}
					else
					{
						$result .= "<OPTION VALUE=\"$key\" fullvalue=\"$escapedValue\">$padding$display</OPTION>\n";
					}
				}
            
            $oldSegments = !empty($lastPath) ? explode('/', $lastPath) : array();
            for ($j = 0; $j < count($oldSegments); $j++)
   				$result .= "</optgroup>\n";
			}
			else
			{
            $path = NULL;
            $display = NULL;
            $lastPath = '';
            $padding = '';
				foreach ($items as $value)
				{
					if ($options & Html::$SelectIgnoreSlash)
               {
                  $display = $value;
               }
               else
               {
                  $path = Html::_GetPath($value);
                  if ($path != $lastPath)
                  {
                     $oldSegments = !empty($lastPath) ? explode('/', $lastPath) : array();
                     $newSegments = !empty($path) ? explode('/', $path) : array();
                     
                     $i = 0;
                     while ($i < count($oldSegments) && $i < count($newSegments) &&
                        $oldSegments[$i] == $newSegments[$i]) 
                        $i++;
                     
                     for ($j = $i; $j < count($oldSegments); $j++)
      						$result .= "</optgroup>\n";
                     for ($j = $i; $j < count($newSegments); $j++)
                     {
                        $display = $newSegments[$j];
                        if ($shorten)
                           $display = Utilities::ShortenWithCenterEllipsis($display, 32);   // 32 is about max for mobile in portrait mode
                        $padding = Html::_Padding($j);
      						$result .= "<optgroup label=\"$padding$display\">\n";
                     }
                     
                     $padding = Html::_Padding(count($newSegments));
                     $lastPath = $path;
                  }

                  $display = Html::_GetFilename($value);
               }
               if ($shorten)
                  $display = Utilities::ShortenWithCenterEllipsis($display, 32);   // 32 is about max for mobile in portrait mode
					$display = Html::Escape($display);
                    $escapedValue = Html::Escape($value);
					if (Utilities::ArrayContains($selectedItems, $value) || 
						(is_null($value) && Utilities::ArrayContains($selectedItems, '_NULL_')) ||
						(Utilities::StringEqualsOrBothUndef($value, '_NULL_') && Utilities::ArrayContains($selectedItems, NULL)))
					{
						$result .= "<OPTION VALUE=\"$value\" fullvalue=\"$escapedValue\" SELECTED>$padding$display</OPTION>\n";
					}
					else
					{
						$result .= "<OPTION VALUE=\"$value\" fullvalue=\"$escapedValue\">$padding$display</OPTION>\n";
					}
				}
            
            $oldSegments = !empty($lastPath) ? explode('/', $lastPath) : array();
            for ($j = 0; $j < count($oldSegments); $j++)
   				$result .= "</optgroup>\n";
			}
	
			$id = $name;
         // pass the ID into the script if required
			$temp = Utilities::ReplaceInString($extra, '%id%', $id);
			$temp .= ' ' . $class . ' ' . $onChangeScript;
	      
		  	// for PHP the name must be suffixed by square brackets to get multiple items
		  	$nameSuffix = !empty($multiple) ? '[]' : '';
			
         // the disabled control will not send its value to the server on form submit so we add a hidden
         // control that has the values and a dummy disabled one to show the values
         $hiddenControl = '';
         if (!empty($disabled))
         {
   			$hiddenControl = "<SELECT ID=\"$name\" NAME=\"$name$nameSuffix\" $size $multiple $temp style=\"display: none;\">\n$result\n</SELECT>\n";
            $name .= '_visual';
         }
			$result = "<SELECT ID=\"$name\" NAME=\"$name$nameSuffix\" $size $multiple $temp $disabled>\n$result\n</SELECT>$hiddenControl\n";
		}
	
		return $result;
	}
	
	// ref_options are a hash where the name is the menu label and 
	// the value is the URL to go to when that item is selected. The 
	// hash may contain references to other hashes for nested menus.
	static function DropDownMenu($title, &$ref_options)
	{
		$result = "<select size=1 onChange=\"if (event.target.selectedOptions.length > 0) document.location.href = event.target.selectedOptions[event.target.selectedOptions.length-1].value;\">\n<option selected value=\"\">$title\n";
	  
		$result .= Html::_SubMenu(""/*"&nbsp;&nbsp;&nbsp;"*/, $ref_options);
		
		$result .= "</select>\n";
		
		return $result;
	}
	
	static function _SubMenu($nesting, &$ref_options)
	{
		$result = "";
		
		$options = $ref_options;
		foreach ($options as $key => $value)
		{
			if (is_array($value))
			{
				$result .= "<option value=\"\">$nesting$key\n" . _SubMenu($nesting . ""/*"&nbsp;&nbsp;&nbsp;"*/, $value);
			}
			else
			{
				$result .= "<option value=\"$value\">$nesting$key\n";
			}
		}
		
		return $result;
	}
   
	static function MapFromLatLong($lat, $long, $closeUp, $icon = NULL)
	{
      global $ImagesBaseUri;   // DRL FIXIT! This is set in COnstants.php for the particular Web site!

		$zoom = $closeUp ? 14 : 12;
      if (!empty($icon))
      {
         if (strpos(strtolower($icon), 'http') === false)
            $icon = $ImagesBaseUri . $icon;
         $icon = "<img class='iconsmall' src='$icon'>";
      }
      else
      {
         $icon = '<img src="http://maps.googleapis.com/maps/api/staticmap?center=' . 
   			$lat . ',' . $long . '&zoom=' . ($zoom-2) . ' &size=200x200&sensor=false">';
      }
		return "<A href='https://maps.google.com/?ll=$lat,$long&z=$zoom' target='map'>$icon</A>";
	}

	static function MapFromAddress($address, $closeUp, $icon = NULL)
	{
      global $ImagesBaseUri;   // DRL FIXIT! This is set in Constants.php for the particular Web site!
      if (empty($address)) return NULL;
      
      $label = '<span class="optional">' . Html::Escape($address) . '</span>';
      $address = Url::EncodeURIComponent($address, true);
		$zoom = $closeUp ? 14 : 12;
      if (!empty($icon))
      {
         if (strpos(strtolower($icon), 'http') === false)
            $icon = $ImagesBaseUri . $icon;
         $icon = "<img class='iconsmall' src='$icon'>";
      }
      else
      {
         $icon = "<img class='iconsmall' src='http://maps.googleapis.com/maps/api/staticmap?center=$address&zoom=" . 
            ($zoom-2) . "&size=200x200&sensor=false'>";
      }
		return "<A href='http://maps.google.com/?q=$address&z=$zoom' target='map'>$label $icon</A>";
	}
 
	// very simple method that only splits the path by slash
   static function _GetFilename($path)
   {
      $i = strrpos($path, '/');
      if ($i === false)
         return $path;
      return substr($path, $i+1);
   }
   
   // very simple method that only splits the path by slash
   static function _GetPath($path)
   {
      $i = strrpos($path, '/');
      if ($i === false)
         return "";
      return substr($path, 0, $i);
   }
}

?>

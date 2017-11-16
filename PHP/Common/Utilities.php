<?php

require_once(dirname(__FILE__) . '/../Common/vCardAddress.php');

function _NumericCompare($a, $b)
{
	if ($a == $b) return 0;
    return $a > $b;
}

class Utilities
{
	// ToString() format values for all our classes:
	static $ToStringFormatDefault = 1;		// returns a basic text display format
	static $ToStringFormatSerialize = 2;	// returns a format that is compatible with FromString()
	static $ToStringFormatHTML = 3;			// returns an HTML enriched display format
	
	// ToString() option values for all our classes (ORed together):
	static $ToStringOptionCondensed = 1;	// returns a shorter (possibly lossy) display (default is verbose)
	static $ToStringOptionButtons = 2;		// returns hyperlinks as buttons (defaut is text link)

	static function Mod($numerator, $denominator)
	{
      return $numerator % $denominator;
	}

	static function Div($numerator, $denominator)
	{
//		$remainder = abs($numerator) % abs($denominator);
//		if ($numerator * $denominator < 0) { $remainder = -$remainder; }
//		$quotient = ($numerator - $remainder) / $denominator;
//		return intVal($quotient);
      return ($numerator - $numerator % $denominator) / $denominator;
	}

	static function RemoveQuotes($str)
	{
		return Utilities::ReplaceInString(Utilities::ReplaceInString($str, "'", ''), '"', '');
	}
	
	static function RemoveSurroundingSpaces($str)
	{
		$str = preg_replace('/^[\r\n\t ]+/','',$str);
		$str = preg_replace('/[\r\n\t ]+$/','',$str);
		
		return $str;
	}
	
	static function RemoveSurroundingAngleBrackets($str)
	{
		$str = preg_replace('/^</','',$str);
		$str = preg_replace('/>$/','',$str);
		
		return $str;
	}
	
	static function RemoveNonAlphanumericCharacters($str)
	{
		return preg_replace('/[^a-z\d]/i', '', $str);
	}

	static function NormalizeSpaces($str)
	{
		$str = Utilities::RemoveSurroundingSpaces($str);
		$str = preg_replace('/[\r\n\t ]+/',' ',$str);
		
		return $str;
	}

	static function IsEmpty($str)
	{
		return is_null($str) || trim($str) == "";
	}

	static function CombineStrings($separator, $item1, $item2)
	{
		if (!empty($item1) && !empty($item2))
		{
			if ($item1[strlen($item1)-1] != $separator && $item2[0] != $separator)
				return $item1 . $separator . $item2;
			if ($item1[strlen($item1)-1] == $separator && $item2[0] == $separator)
				return substr($item1, 0, strlen($item1)-1) . $item2;
			return $item1 . $item2;
		}
		if (!empty($item2))
			return $item2;
		return $item1;
	}

	static function ShortenWithEllipsis($str, $len, $ellipsis = NULL)
	{
		if (is_null($ellipsis)) $ellipsis = '...';
		
        if ($len === NULL || strlen($str) <= $len)
           return $str;
         
		$len -= strlen($ellipsis);
		return Utilities::RemoveSurroundingSpaces(substr($str, 0, $len)) . $ellipsis;
	}
	
	static function ShortenWithCenterEllipsis($str, $len, $ellipsis = NULL)
	{
		if (is_null($ellipsis)) $ellipsis = '...';
		
      $strlen = strlen($str);
      if ($len === NULL || $strlen <= $len)
         return $str;
         
		$len -= strlen($ellipsis);
      $i = Utilities::Div($len, 2);
		return Utilities::RemoveSurroundingSpaces(substr($str, 0, $i)) . $ellipsis . 
         Utilities::RemoveSurroundingSpaces(substr($str, $strlen-$i));
	}
	
	static function ReplaceInString($str, $find, $replace)
	{
		return str_replace($find, $replace, $str);
	}
	
	static function ReplaceCharsInString($str, $findChars, $replace)
	{
		$findChars = Utilities::RegexpEscape($findChars);
		$str = preg_replace("/[$findChars]/",$replace,$str);
		return $str;
	}

	static function ReplaceCharsNotInString($str, $findChars, $replace)
	{
		$findChars = Utilities::RegexpEscape($findChars);
		$str = preg_replace("/[^$findChars]/",$replace,$str);
		return $str;
	}

	static function StringContains($string, $value)
	{
		return strpos($string, $value) !== false;
	}
	
	static function StringContainsAny($string, &$ref_array)
	{
		foreach ($ref_array as $value)
		{
			if (strpos($string, $value) !== false)
			{
				return 1;
			}
		}
		return 0;
	}

	// the default is to wrap lines
	static function EncodeBase64($value, $wrapLines = NULL)
	{
		if (is_null($value)) { WriteDie("No value!"); }
		$result = base64_encode($value);
		if (!is_null($wrapLines) && !$wrapLines)
		{
			$result = Utilities::ReplaceInString($result, "\r", "");
			$result = Utilities::ReplaceInString($result, "\n", "");
		}
		return $result;
	}
	
	static function DecodeBase64($value)
	{
		return base64_decode($value);
	}
	
	static function DecodeQuotedPrintable($res)
	{
		if (function_exists('imap_qprint')) {
         return imap_qprint($res);
		}

		return quoted_printable_decode($res);
	}
	
	static function GetAsCurrency($value)
	{
		// DRL FIXIT? We could add support for different currency signs?
		$currency = '$';
		$sign = "";
		while (strlen($value) > 0)
		{
			$ch = substr($value, 0, 1);
			if (strcmp($ch, '$') == 0)
			{
				$value = substr($value, 1);
			}
			elseif (strcmp($ch, '-') == 0)
			{
				$sign = '-';
				$value = substr($value, 1);
			}
			else
			{
				break;
			}
		}
		
		$dollars;
		$pennies;
		$i = strpos($value, ".");
		if ($i === false)
		{
			$dollars = $value;
			$pennies = ".";
		}
		else
		{
			$dollars = substr($value, 0, $i);
			$pennies = substr($value, $i, 3);
		}
		while (strlen($dollars) > 0 && strcmp(substr($dollars, 0, 1), "0") == 0)
		{
			$dollars = substr($dollars, 1);
		}
		while (strlen($pennies) < 3)
		{ $pennies .= "0"; }
		
		return $currency . $sign . $dollars . $pennies;
	}
	
	static function SortArray(&$ref_array, $ignoreCase = NULL)
	{
		if (!empty($ignoreCase) && $ignoreCase)
		{
			uksort($ref_array, 'strcasecmp');
		}
		else
		{
			uksort($ref_array, 'strcmp');
		}
	}
	
	static function OrderArray(&$ref_array)
	{
		uksort($ref_array, '_NumericCompare');
	}

	static function StringToArray($str, $seperator = ',')
	{
		if (empty($str)) return array();
		return explode($seperator, $str);
	}
   
   static function ArrayFirstKey($array)
   {
      reset($array);
      return key($array);
   }

/*	
	static function FromArrayReference(&$ref_array)
	{
		if (empty($ref_array))
		{
			return array();
		}
		
		return @{$ref_array};
	}
*/

	static function ArrayContains($ref_array, $value, $ignoreCase = NULL)
	{
		if (is_null($ignoreCase)) { $ignoreCase = 0; }
	
		if (!is_array($ref_array))
		{ WriteDie("No array!"); }
		
		$array = $ref_array;
	
		foreach ($array as $item)
		{
			if (Utilities::ValuesEqualOrBothUndef($item, $value, 0, $ignoreCase))
			{
				return 1;
			}
		}
		
		return 0;
	}

	static function ArraysMeet($ref_array1, $ref_array2, $ignoreCase = NULL)
	{
		if (is_null($ignoreCase)) { $ignoreCase = 0; }
		
		foreach ($ref_array1 as $item1)
		{
			foreach ($ref_array2 as $item2)
			{
				if (Utilities::ValuesEqualOrBothUndef($item1, $item2, 0, $ignoreCase))
				{
					return 1;
				}
			}
		}
		
		return 0;
	}

	static function ArrayItem($array, $index)
   {
      if ($index < 0)
         $index = count($array)+$index;
      assert($index >= 0 && $index < count($array));
      return $array[$index];
   }
   
	static function ArrayIndexOf($ref_array, $value)
	{
		for ($i = 0; $i < count($ref_array); $i++)
		{
			if (Utilities::ValuesEqualOrBothUndef($ref_array[$i], $value))
			{
				return $i;
			}
		}
		
		return -1;
	}
	
	static function ArrayFirstMatchOf($ref_array1, $ref_array2, $ignoreCase = NULL)
	{
		if (is_null($ignoreCase)) { $ignoreCase = 0; }
		
		foreach ($ref_array1 as $item1)
		{
			foreach ($ref_array2 as $item2)
			{
				if (Utilities::ValuesEqualOrBothUndef($item1, $item2, 0, $ignoreCase))
				{
					return $item2;
				}
			}
		}
		
		return NULL;
	}
	
	static function MergeArrays($ref_array1, $ref_array2)
	{
      $result = $ref_array1;
      
		foreach ($ref_array2 as $item)
		{
			if (!Utilities::ArrayContains($result, $item))
			{
				array_push($result, $item);
			}
		}
      
      return $result;
	}
	
	static function MergeIntoArray(&$ref_array1, &$ref_array2)
	{
		foreach ($ref_array2 as $item)
		{
			if (!Utilities::ArrayContains($ref_array1, $item))
			{
				array_push($ref_array1, $item);
			}
		}
	}
	
	// adds array 2 to array 1, doesn't check for duplicate entries
	static function ConcatArray(&$ref_array1, &$ref_array2)
	{
		foreach ($ref_array2 as $item)
		{
			array_push($ref_array1, $item);
		}
	}
	

   // given any number of arrays, will return an array containing all the keys from all 
   // the arrays, with all their values combined into arrays (only one level deep)
   static function MergeArrayKeys()
   {
      $arr = func_get_args();
      $num = func_num_args();

      $keys = array();
      $i = 0;
      for ($i=0; $i<$num; ++$i)
      {
         $keys = array_merge($keys, array_keys($arr[$i]));
      }
      $keys = array_unique($keys);

      $merged = array();

      foreach ($keys as $key)
      {
         $result = array();
         for($i=0; $i<$num; ++$i)
         {
            if (isset($arr[$i][$key]))
            {
               $val = $arr[$i][$key];
               
               if (is_array($val))
               {
                  Utilities::MergeIntoArray($result, $val);
               }
      			else if (!Utilities::ArrayContains($result, $val))
      			{
                  $result[] = $val;
               }
            }
         }
         $merged[$key] = $result;
      }
      return $merged;
   }
   
	static function ArrayEquals($ref_array1, $ref_array2, $ignoreOrder = NULL, $ignoreCase = NULL)
	{
		if (is_null($ignoreOrder)) { $ignoreOrder = 0; }
		if (is_null($ignoreCase)) { $ignoreCase = 0; }
		
		if (count($ref_array1) != count($ref_array2))
		{
			return 0;
		}
		
		if ($ignoreOrder)
		{
			$temp = $ref_array2;
			
			foreach ($ref_array1 as $item)
			{
				$i;
				$found = 0;
				
				for ($i = 0; $i < count($temp); $i++)
				{
					if (Utilities::ValuesEqualOrBothUndef($temp[$i], $item, $ignoreOrder, $ignoreCase))
					{
						$found = 1;
						break;
					}
				}
	
				if ($found)
				{
					array_splice($temp, $i, 1);
				}
				else
				{
					return 0;
				}
			}
		}
		else
		{
			for ($i = 0; $i < count($ref_array1); $i++)
			{
				if (!Utilities::ValuesEqualOrBothUndef($ref_array1[$i], $ref_array2[$i], $ignoreOrder, $ignoreCase))
				{
					return 0;
				}
			}
		}
		
		return 1;
	}

	// avoids adding duplicates, skips if key is provided and already exists
   // item can be an array, but then key must be null
	static function AddToArray(&$array, $item, $key=NULL)
	{
      if ($key !== NULL && array_key_exists($key, $array))
         return false;
      
      if (!is_array($item)) $item = array($item);
      
      $changed = false;
      foreach ($item as $it)
      {
         if ($key === NULL && Utilities::ArrayContains($array, $it))
            continue;
            
         if ($key === NULL)
      		array_push($array, $it);
         else
      		$array[$key] = $it;
         $changed = true;
      }
         
		return $changed;
	}

	static function AddToArrayIfNotEmpty(&$array, $item, $key=NULL)
   {
      if (empty($item)) return false;
	   return Utilities::AddToArray($array, $item, $key);
   }
   
	static function ReplaceInArray(&$array, $old, $new)
	{
      $ret = false;
      
      for ($j = 0; $j < count($array); $j++)
      {
         if (Utilities::ValuesEqualOrBothUndef($array[$j], $old))
         {
            $array[$j] = $new;
            $ret = true;
         }
      }
		
		return $ret;
	}
	
   // "item" can be a single item or an array
	static function RemoveFromArray(&$array, $item, $ignoreCase = NULL)
	{
		$ret = false;
		
      if (!is_array($item))
         $item = array($item);
         
   	for ($i = 0; $i < count($item); $i++)
   	{
   		for ($j = 0; $j < count($array); $j++)
   		{
   			if (Utilities::ValuesEqualOrBothUndef($array[$j], $item[$i], NULL, $ignoreCase))
   			{
   				array_splice($array, $j, 1);
      			$j--;
               $ret = true;
   			}
         }
		}
		
		return $ret;
	}
	
   // "key" can be a single item or an array of keys
	static function RemoveFromArrayByKey(&$array, $key)
	{
		$ret = false;
		
      if (!is_array($key))
         $key = array($key);
         
   	for ($i = 0; $i < count($key); $i++)
   	{
         if (array_key_exists($key[$i], $array))
         {
            unset($array[$key[$i]]);
            $ret = true;
         }
		}
		
		return $ret;
	}
	
   // "key" can be a single item or an array of keys
	static function TrimArrayByKey(&$array, $key)
	{
		$ret = false;
		
      if (!is_array($key))
         $key = array($key);
         
   	foreach (array_keys($array) as $i)
   	{
         if (!Utilities::ArrayContains($key, $i))
         {
            unset($array[$i]);
            $ret = true;
         }
		}
		
		return $ret;
	}
	
	static function IntersectArrayKeys($ref_array1, $ref_array2)
	{
      $keys1 = array_keys($ref_array1);
		foreach ($keys1 as $key1)
		{
         if (!array_key_exists($key1, $ref_array2))
            unset($ref_array1[$key1]);
		}
		
		return $ref_array1;
	}
	
   // the items are associative arrays with ID elements named dataName
	static function RemoveFromArrayByDataValue(&$array1, $array2, $dataName)
	{
		$ret = false;
		
   	for ($i = 0; $i < count($array2); $i++)
   	{
   		for ($j = 0; $j < count($array1); $j++)
   		{
   			if (Utilities::ValuesEqualOrBothUndef($array1[$j][$dataName], $array2[$i][$dataName]))
   			{
   				array_splice($array1, $j, 1);
      			$j--;
               $ret = true;
   			}
         }
		}
		
		return $ret;
	}

	static function ArrayRemoveDuplicates($array)
   {
      return array_values(array_unique($array)); // remove duplicates and return consecutive indices
   }
	
   // makes a deep copy of nested arrays
	static function CopyArray($array)
	{
      $result = array();
      
      foreach ($array as $key => $val)
		{
			if (is_array($val))
			{
            $result[$key] = Utilities::CopyArray($val);
			}
         else
         {
            $result[$key] = $val;
         }
		}
	}
	
	static function FlattenNestedArrays(&$ref_array)
	{
		for ($i = 0; $i < count($ref_array); $i++)	
		{
			if (is_array($ref_array[$i]))
			{
				FlattenNestedArrays($ref_array[$i]);
				array_splice($ref_array, $i, 1, $ref_array[$i]);
			}
/* DRL FIXIT?
			elseif ($type eq 'HASH')
			{
				my %temp = %{$ref_array->[$i]};
				my @temp = %temp;		# convert hash to array
				FlattenNestedArrays(\@temp);
				splice(@{$ref_array}, $i, 1, @temp);
			}
*/
		}
	}
	
/* DRL FIXIT?
	static function HashEquals($ref_hash1, $ref_hash2, $ignoreOrder = NULL, $ignoreCase = NULL)
	{
		if (is_null($ignoreOrder)) { $ignoreOrder = 0; }
		if (is_null($ignoreCase)) { $ignoreCase = 0; }
		
		my %hash1 = %{$ref_hash1};
		my %hash2 = %{$ref_hash2};
		
		my @keys1 = keys %hash1;
		my @keys2 = keys %hash2;
		
		if (!ArrayEquals(\@keys1, \@keys2, 1, $ignoreCase))
		{
			return 0;
		}
		
		foreach my $key (@keys1)
		{
			my $item1 = $hash1{$key};
			my $item2 = $hash2{$key};
			
			if (!Utilities::ValuesEqualOrBothUndef($item1, $item2, $ignoreOrder, $ignoreCase))
			{
				return 0;
			}
		}
		
		return 1;
	}
*/

	static function _GetTypeForComparison($value)
	{
		if (is_object(($value))) return get_class($value);
		if (is_array(($value))) return 'array';
		return 'scalar';
	}
	
	static function ValuesEqualOrBothUndef($item1, $item2, $ignoreOrder = NULL, $ignoreCase = NULL)
	{
		if (is_null($ignoreOrder)) { $ignoreOrder = 0; }
		if (is_null($ignoreCase)) { $ignoreCase = 0; }
		
		$type1 = Utilities::_GetTypeForComparison($item1);
		$type2 = Utilities::_GetTypeForComparison($item2);
		
		if (!Utilities::StringEqualsOrBothUndef($type1, $type2, $ignoreCase))
		{
			return 0;
		}
		
		if (is_array($item1))
		{
			return Utilities::ArrayEquals($item1, $item2, $ignoreOrder, $ignoreCase);
		}
		else if (is_numeric($item1))
		{
			return Utilities::NumericEqualsOrBothUndef($item1, $item2);	// use == to compare in case object overrides it
		}
      else if (is_object($item1))
      {
         return $item1 == $item2;
      }
		
		return Utilities::StringEqualsOrBothUndef($item1, $item2, $ignoreCase);
	}

	static function NumericEqualsOrBothUndef($item1, $item2)
	{
		if (is_null($item1))
		{
			return is_null($item2);
		}
		else if (is_null($item2))
		{
			return 0;
		}
		
		return $item1 == $item2;
	}
	
	static function StringEqualsOrBothUndef($item1, $item2, $ignoreCase = NULL)
	{
		if (is_null($item1))
		{
			return is_null($item2);
		}
		else if (is_null($item2))
		{
			return 0;
		}
		
		if ($ignoreCase)
		{
			return strcmp(strtolower($item1), strtolower($item2)) == 0;
		}
		else
		{
			return strcmp($item1, $item2) == 0;
		}
	}

/* DRL FIXIT!
	static function FromDisplayToValue($display, &$ref_valueDisplayArray)
	{
		my @valueDisplayArray = @{$ref_valueDisplayArray};
	
		for (my $i = 0; $i <= $#valueDisplayArray; $i += 2)
		{
			my $val = $valueDisplayArray[$i];
			my $disp = $valueDisplayArray[$i+1];
			
			if ($disp eq $display)
			{
				return $val;
			}
		}
		
		return undef;
	}
	
	static function FromValueToDisplay($valueOrValues, &$ref_valueDisplayArray)
	{
		my @values;
		if (ref $valueOrValues)
		{
			@values = @$valueOrValues;
		}
		else
		{
			@values = ($valueOrValues);
		}
	
		my @valueDisplayArray = @{$ref_valueDisplayArray};
		my $result;
	
		for (my $i = 0; $i <= $#valueDisplayArray; $i += 2)
		{
			my $val = $valueDisplayArray[$i];
			my $disp = $valueDisplayArray[$i+1];
			
			if (Utilities::ArrayContains(\@values, $val))
			{
				# DRL FIXIT? Tree items have space characters preceding the text, we don't want these.
				$disp = ReplaceInString($disp, "&nbsp;", "");
				
				$result = Utilities::CombineStrings(", ", $result, $disp);
			}
		}
		
		return $result;
	}
	
	static function IDListToArray($idList)
	{
		if (length($idList) >= 2)
		{
			if (substr($idList, 0, 1) eq '.')
			{
				$idList = substr($idList, 1);
			}
			if (substr($idList, -1) eq '.')
			{
				$idList = substr($idList, 0, -1);
			}
		}
		my @array = split(/\./, $idList);
		
		return @array;
	}
	
	static function ArrayToIDList(&$ref_array)
	{
		my @idList = @{$ref_array};
		if ($#idList == -1)
		{
			return "";
		}
	
		my $idList = '.' . join(".", @idList) . '.';
		
		return $idList;
	}
*/
   // since we can't use a bool as an array index, we'll convert it to int
   static function BoolToInt($value)
   {
      if (!is_null($value)) $value = $value ? 1 : 0;
      return $value;
   }
   
	static function IsInteger($value)
	{
		return preg_match('/^\-?[0-9]+$/', $value) == 1;
	}
	
	static function IsNumeric($value)
	{
		return preg_match('/^\-?[0-9]*\.?[0-9]+$/', $value) == 1;
	}
	
	static function IsAlphabetic($value)
	{
		return preg_match('/^[a-zA-Z]+$/', $value) == 1;
	}
	
	static function IsWhiteSpace($value)
	{
		return preg_match('/^[\s\r\n\t]+$/', $value) == 1;
	}

	static function RegexpEscape($str)
	{
		static $chars = array("\\", '.', '[', '^', '$', '|', '*', '+', '-', '?', '(', '/');
		
		foreach ($chars as $char)
		{
			$char2 = "\\" . $char;
			$str = str_replace($char, $char2, $str);
		}
		
		return $str;
	}

	static function IsAndroid()
	{
      return preg_match("/Android/", $_SERVER['HTTP_USER_AGENT']) == 1;
	}

	static function iOSVersion()
	{
      if (preg_match("/iPhone|iPad|iPod/", $_SERVER['HTTP_USER_AGENT']) != 1)
         return 0;
         
      return preg_replace("/(.*) OS ([0-9]*)_(.*)/","$2", $_SERVER['HTTP_USER_AGENT']);
	}
   
   static function IntRand($min, $max)   // value are inclusive
   {
      return rand($min, $max);
   }


   /**
    * This method is used by the various sync objects (vCard, iCal, Messages, etc.) in 
    * their CopyFrom() method to perform some common handling of the fields.
    * @param $source
    * @param $fields
    * @param $replace
    * @return array
    */
   public static function ExecuteCallbacksAndGetRemainingFields($target, $source, $fields, $replace)
   {
      $original = [];
      if (is_array($fields) && is_string(array_keys($fields)[0]))
      {
         $original = $fields;
         $fields = [];
         foreach ($original as $k => $v)
         {
            if (is_null($v))
            {
               $fields[] = $k;
               unset($original[$k]);
            }
         }
      }

      foreach ($original as $k => $v)
      {
         call_user_func($v, $target, $source, $replace);
      }
      return $fields;
   }

/* DRL FIXIT!	
	static function BinaryCompare($bin1, $bin2)
	{
		// it appears this is how we compare binary data in perl?	
		return $bin1 eq $bin2;
	}
	
	static function SerializeHash(&$ref_hash)
	{ 
		my $result;
		
		eval
		{
			if (defined($ref_hash))
			{
				$result = nfreeze($ref_hash);
			}
		};
		if ($@)
		{
			Log::Die("Error calling nfreeze(): " . $@);
			
	# This was the old way, no longer used.
	#		my @values = %{$ref_hash};
	#		
	#		# encode the separator and escape character
	#		for (my $i = 0; $i <= $#values; $i++)
	#		{
	#			$values[$i] =~ s/[%]/%25/g;	# must come first
	#			$values[$i] =~ s/[|]/%7C/g;
	#		}
	#		
	#		$result = join("|", @values);
		}
		
		return $result;
	}
	
	static function DeserializeHash($string)
	{ 
		my %values = ();
		my $result = \%values;
		
		eval
		{
			if (defined($string) && length($string) > 0)
			{
				$result = \%{thaw($string)};
			}
		};
		if ($@)
		{
			Log::WriteError("Error calling thaw() on '$string': " . $@);
		
			# backwards compatibility
		
			my @values = split("[|]", $string);
			
			# decode the separator and escape character
			for (my $i = 0; $i <= $#values; $i++)
			{
				$values[$i] =~ s/[%]7C/|/g;
				$values[$i] =~ s/[%]25/%/g;	# must come last
			}
			
			%values = @values;
			$result = \%values;
		}
		
		return $result;
	}
	
	static function Max($array)
	{ 
		my $result = undef;
		foreach my $val (@array)
		{
			if (!defined($result) || $val > $result)
			{
				$result = $val;
			}
		}
		
		return $result;
	}
	
	static function Min($array)
	{ 
		my $result = undef;
		foreach my $val (@array)
		{
			if (!defined($result) || $val < $result)
			{
				$result = $val;
			}
		}
		
		return $result;
	}
*/
}

if (0)
{
   $ar1 = array(
      '1 250 516-2080' => array('mobile'),
      );
   $ar2 = array(
      '1 250 516-2080' => array('mobile', 'personal'),
      '111-222-3333' => array('other'),
      );
   $ar3 = Utilities::MergeArrayKeys($ar1, $ar2);
   $ar3 = $ar3;
}


?>

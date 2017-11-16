<?php

require_once(dirname(__FILE__) . '/Log.php');
require_once(dirname(__FILE__) . '/Utilities.php');
require_once(dirname(__FILE__) . '/../ThirdParty/File_IMC/File/IMC/Build.php');
require_once(dirname(__FILE__) . '/../ThirdParty/File_IMC/File/IMC/Exception.php');
require_once(dirname(__FILE__) . '/../ThirdParty/File_IMC/File/IMC/Parse.php');
require_once(dirname(__FILE__) . '/../ThirdParty/File_IMC/File/IMC/Build/Vcalendar.php');
require_once(dirname(__FILE__) . '/../ThirdParty/File_IMC/File/IMC/Build/Vcard.php');
require_once(dirname(__FILE__) . '/../ThirdParty/File_IMC/File/IMC/Parse/Vcalendar/Event.php');
require_once(dirname(__FILE__) . '/../ThirdParty/File_IMC/File/IMC/Parse/Vcalendar/Events.php');
require_once(dirname(__FILE__) . '/../ThirdParty/File_IMC/File/IMC/Parse/Vcalendar.php');
require_once(dirname(__FILE__) . '/../ThirdParty/File_IMC/File/IMC/Parse/Vcard.php');
require_once(dirname(__FILE__) . '/../ThirdParty/File_IMC/File/IMC.php');

/*
===================================================================

Implementation.

===================================================================

*/
	

function _MyWordWrap($string, $width, $glue)
{
   $lines = array();

   mb_internal_encoding('UTF-8');

   for ($i = 0, $length = mb_strlen($string); $i < $length; $i += $width)
   {
      $lines[] = mb_substr($string, $i, $width, 'UTF-8');
   }

   return implode($glue, $lines);
}


class vCardCalBase
{
	protected $object = null;		// must be initialized in the derived class constructor
	protected $ValueNames = null;	// set by derived objects, used to find nodes
   protected $isDeleted = false; // this is not persisted!
   protected $isReadOnly = false;// this is not persisted!
   protected $hasMemberships = false;   // this is not persisted! Used to indicate whether group memberships are contained in this object.
	
    /**
	 * SPL-compatible autoloader.
	 *
     * @param string $className Name of the class to load.
     *
     * @return boolean
     */
    public static function autoload($className)
    {
        if ($className != 'vCardCalBase') {
            return false;
		}
        return include str_replace('_', '/', $className) . '.php';
    }

   public function IsDeleted()
   {
      return $this->isDeleted;
   }
   
   public function SetIsDeleted($val)
   {
      $this->isDeleted = $val;
   }
   
   public function IsReadOnly()
   {
      return $this->isReadOnly;
   }
   
   public function SetIsReadOnly($val)
   {
      $this->isReadOnly = $val;
   }
   
   public function HasMemberships()
   {
      return $this->hasMemberships;
   }
   
   public function SetHasMemberships($val)
   {
      $this->hasMemberships = $val;
   }
   
	public function _CreateNode($id, $types, $value = null)
	{
		$node = 0;
		$obj = $this->object;	// DRL FIXIT? I found that if I didn't use the 
		$values = &$obj->value;	// $obj intermediate variable that this code 
		$params = &$obj->param;	// would change the value/param arrays
		$obj = null;			// which is a really weird behavior!

		settype($id, 'array');		// for $id of name only
		if (!is_array($id[0]))		// for $id of array(name, node, ...)
			$id = array($id);		// convert to array(array(name, node), ...)
		foreach ($id as $i)
		{
			$name = $i[0];
			$node = 0;
			$valueName = 'value';	// the default
			if (count($i) > 1)
			{
				$node = $i[1];
				if (count($i) > 2)
					$valueName = $i[2];
			}
			
         if (!isset($values[$name]))
            $values[$name] = array();
         else
            $node = max(array_keys($values[$name])) + 1;
         if (!isset($values[$name][$node]))
            $values[$name][$node] = array();
				
			$values = &$values[$name][$node];
				
			if ($types != null)
			{
            if (!isset($params[$name]))
               $params[$name] = array();
            if (!isset($params[$name][$node]))
               $params[$name][$node] = array();
					
				$params = &$params[$name][$node];
			}
		}
		
      if ($value != NULL)
      {
   		$part = 0;
   		if ($valueName != 'value')
   		{
            $part = array_search($valueName, $this->ValueNames[$name]);
            if ($part === false)
   				WriteDie("Item " . $valueName . " not found for " . $name);
   		}

         if (!isset($values[$part]))
            $values[$part] = array();
         $values[$part][] = $value;
      }
		
		if ($types != null)
		{
			settype($types, 'array');
			foreach ($types as $typeName => $type)
			{
				if (is_numeric($typeName))
					$typeName = 'TYPE';		// the default param name
					
            $params[$typeName][] = $type;
			}
		}
	
		return $node;
	}
	
	// looks for all matching nodes, returns empty array if not found
	public function _FindNodes($id, $typeOrTypes = null)
	{
		$obj = $this->object;	// DRL FIXIT? I found that if I didn't use the
		$values = &$obj->value;	// $obj intermediate variable that this code 
		$params = &$obj->param;	// would change the value/param arrays
		$obj = null;			// which is a really weird behavior!
		$lastValues = &$values;
		$lastParams = &$params;

		settype($id, 'array');		// for $id of name only
		if (!is_array($id[0]))		// for $id of array(name, node, ...)
			$id = array($id);		// convert to array(array(name, node), ...)
		foreach ($id as $i)
		{
			$name = $i[0];
			$node = 0;
			if (count($i) > 1)
				$node = $i[1];
	
			if (!isset($values[$name]) || !isset($values[$name][$node]))
				return array();
	
			$lastValues = &$values[$name];
			$values = &$values[$name][$node];
			if ($params != null)
			{
				$lastParams = &$params[$name];
				$params = &$params[$name][$node];
			}
         else
				$lastParams = NULL;
		}
		
		$indices = array();
		if ($typeOrTypes != null)
		{
			if ($lastParams != null)
			{
				if (!is_array($typeOrTypes))
					 $typeOrTypes = array($typeOrTypes);
					 
				foreach (array_keys($lastParams) as $node)
				{
					$count = 0;
					
					foreach ($typeOrTypes as $typeName => $type)
					{
						if (is_numeric($typeName))
							$typeName = 'TYPE';		// the default param name
							
						if (isset($lastParams[$node][$typeName]))
						{
							$types = $lastParams[$node][$typeName];
							
							if (Utilities::ArrayContains($types, $type, 1))
								$count++;
						}
					}
					
					if ($count == count($typeOrTypes))	// we found all the types required
						$indices[] = $node;
				}
			}
		}
		else if ($lastValues != null)
		{
			foreach (array_keys($lastValues) as $node)
			{
				$indices[] = $node;
			}
		}
		
		return $indices;
	}
		
	public function _RemoveNode($id)
	{
		$node = 0;
		$obj = $this->object;	// DRL FIXIT? I found that if I didn't use the 
		$values = &$obj->value;	// $obj intermediate variable that this code 
		$params = &$obj->param;	// would change the value/param arrays
		$obj = null;			// which is a really weird behavior!
		$lastValues = &$values;
		$lastParams = &$params;

		settype($id, 'array');		// for $id of name only
		if (!is_array($id[0]))		// for $id of array(name, node, ...)
			$id = array($id);		// convert to array(array(name, node), ...)
		foreach ($id as $i)
		{
			$name = $i[0];
			$node = 0;
			$valueName = 'value';	// the default
			if (count($i) > 1)
			{
				$node = $i[1];
				if (count($i) > 2)
					$valueName = $i[2];
			}
			
         if (!isset($values[$name]) || !isset($values[$name][$node]))
				return;

			$lastValues = &$values;
			$values = &$values[$name][$node];

			$lastParams = &$params;
			if ($params != null)
				$params = &$params[$name][$node];
		}
      
      $isEmpty = false;
		if ($valueName != 'value')
		{
         $part = array_search($valueName, $this->ValueNames[$name]);
         if ($part === false)
				WriteDie("Item " . $valueName . " not found for " . $name);
         
         // DRL I added this code so that our arrays always end up with the number of values
         // so that we can compare two instances for difference and get a better chance that
         // there won't be a false positive.
         $isEmpty = true;
         for ($i = 0; $i < count($this->ValueNames[$name]); $i++)
         {
            if ($i == $part || !isset($values[$i]))
               $values[$i] = array();
            else if ($values[$i])
               $isEmpty = false;
         }

         if ($params != null && isset($params[$part]))
      		unset($params[$part]);
		}
		
      if ($valueName == 'value' || $isEmpty)
      {
   		array_splice($lastValues[$name], $node, 1);
         if ($lastParams != null && isset($lastParams[$name]))
   		   array_splice($lastParams[$name], $node, 1);
         
         // if this is the last field in the node remove the node
   		if (count($lastValues[$name]) == 0)
   		{
   			unset($lastValues[$name]);
            if ($lastParams != null && isset($lastParams[$name]))
   				unset($lastParams[$name]);
   		}	
      }
   }
	
	// this method removes the value if passed an undefined or empty value
	// we need it because the underlying objects will assume that we're simply reading the value when we pass undefined
	// the value may be an array reference (for ORG, unit))
	public function _SetValue($id, $value)
	{
      if (!$value)
      {
         $this->_RemoveNode($id);
         return;
      }
      
		$node = 0;
		$obj = $this->object;	// DRL FIXIT? I found that if I didn't use the 
		$values = &$obj->value;	// $obj intermediate variable that this code 
		$params = &$obj->param;	// would change the value/param arrays
		$obj = null;			// which is a really weird behavior!

		settype($id, 'array');		// for $id of name only
		if (!is_array($id[0]))		// for $id of array(name, node, ...)
			$id = array($id);		// convert to array(array(name, node), ...)
		foreach ($id as $i)
		{
			$name = $i[0];
			$node = 0;
			$valueName = 'value';	// the default
			if (count($i) > 1)
			{
				$node = $i[1];
				if (count($i) > 2)
					$valueName = $i[2];
			}
			
	      if (!isset($values[$name]))
	         $values[$name] = array();
	      if (!isset($values[$name][$node]))
	         $values[$name][$node] = array();

			$values = &$values[$name][$node];

			if ($params != null)
				$params = &$params[$name][$node];
		}
      
      settype($value, 'array');

		$part = 0;
		if ($valueName != 'value')
		{
		   $part = array_search($valueName, $this->ValueNames[$name]);
			if ($part === false)
				WriteDie("Item " . $valueName . " not found for " . $name);
			
			// DRL I added this code so that our arrays always end up with the number of values
         // so that we can compare two instances for difference and get a better chance that
         // there won't be a false positive.
			for ($i = 0; $i < count($this->ValueNames[$name]); $i++)
         {
            if (!isset($values[$i]))
   			   $values[$i] = array();
         }
		}
		
      $values[$part] = $value;
	}
	
	public function _GetValue($id)
	{
		$valueName = 'value';	// the default
		$obj = $this->object;	// DRL FIXIT? I found that if I didn't use the 
		$values = &$obj->value;	// $obj intermediate variable that this code 
		$obj = null;			// would change the value/param arrays
								// which is a really weird behavior!

		settype($id, 'array');		// for $id of name only
		if (!is_array($id[0]))		// for $id of array(name, node, ...)
			$id = array($id);		// convert to array(array(name, node), ...)
		foreach ($id as $i)
		{
			$name = $i[0];
			$node = 0;
			$valueName = 'value';	// the default
			if (count($i) > 1)
			{
				$node = $i[1];
				if (count($i) > 2)
					$valueName = $i[2];
			}
			if (!isset($values[$name]) || !isset($values[$name][$node]))
				return null;
			$values = &$values[$name][$node];
		}
		
		$part = 0;
		if ($valueName != 'value')
		{
         $part = array_search($valueName, $this->ValueNames[$name]);
         if ($part === false)
				WriteDie("Item " . $valueName . " not found for " . $name);
		}
		
		if (!isset($values[$part]) || count($values[$part]) == 0)
			return null;
			
		if (count($values[$part]) > 1)
			return $values[$part];	// return array reference for ORG, unit
		return $values[$part][0];
	}

	public function _SetParams($id, $types)
	{
		$node = 0;
		$obj = $this->object;	// DRL FIXIT? I found that if I didn't use the 
		$values = &$obj->value;	// $obj intermediate variable that this code 
		$params = &$obj->param;	// would change the value/param arrays
		$obj = null;			// which is a really weird behavior!
		$lastParams = &$params;

		settype($id, 'array');		// for $id of name only
		if (!is_array($id[0]))		// for $id of array(name, node, ...)
			$id = array($id);		// convert to array(array(name, node), ...)
		foreach ($id as $i)
		{
			$name = $i[0];
			$node = 0;
			if (count($i) > 1)
				$node = $i[1];
			
	        if (!isset($values[$name]) || !isset($values[$name][$node]))
				WriteDie('Non-existant node: ' . $id);

	        if (!isset($params[$name]))
	            $params[$name] = array();
	        if (!isset($params[$name][$node]))
	            $params[$name][$node] = array();
				
			$values = &$values[$name][$node];

			$lastParams = &$params;
			$params = &$params[$name][$node];
		}
		
		if ($types != null)
		{
			foreach ($types as $typeName => $type)
			{
				if (is_numeric($typeName))
					$typeName = 'TYPE';		// the default param name
					
				if (!isset($params[$typeName]))
					$params[$typeName] = array();
	         $params[$typeName][] = $type;
			}
		}
		else
		{
			array_splice($lastParams[$name], $node, 1);
			
			// if this is the last field in the node remove the node
			if (count($lastParams[$name]) == 0)
				unset($lastParams[$name]);
		}
	}

	public function _GetParams($id)
	{
		$obj = $this->object;	// DRL FIXIT? I found that if I didn't use the
		$values = &$obj->value;	// $obj intermediate variable that this code 
		$params = &$obj->param;	// would change the value/param arrays
		$obj = null;			// which is a really weird behavior!

		settype($id, 'array');		// for $id of name only
		if (!is_array($id[0]))		// for $id of array(name, node, ...)
			$id = array($id);		// convert to array(array(name, node), ...)
		foreach ($id as $i)
		{
			$name = $i[0];
			$node = 0;
			if (count($i) > 1)
				$node = $i[1];
				
			if (!isset($values[$name]) || !isset($values[$name][$node]))
				WriteDie('Non-existant node: ' . $id);
			if (!isset($params[$name]) || !isset($params[$name][$node]))
				return array();
				
			$values = &$values[$name][$node];
			$params = &$params[$name][$node];
		}
		
	
		$result = array();
		foreach ($params as $typeName => $value)
		{
			$result[$typeName] = $value;
		}
		return $result;
	}
	
	public function _GetTypes($id, $typeName = 'TYPE')
	{
		$obj = $this->object;	// DRL FIXIT? I found that if I didn't use the
		$values = &$obj->value;	// $obj intermediate variable that this code 
		$params = &$obj->param;	// would change the value/param arrays
		$obj = null;			// which is a really weird behavior!

		settype($id, 'array');		// for $id of name only
		if (!is_array($id[0]))		// for $id of array(name, node, ...)
			$id = array($id);		// convert to array(array(name, node), ...)
		foreach ($id as $i)
		{
			$name = $i[0];
			$node = 0;
			if (count($i) > 1)
				$node = $i[1];
			if (!isset($values[$name]) || !isset($values[$name][$node]))
				WriteDie('Non-existant node: ' . $id);
			if (!isset($params[$name]) || !isset($params[$name][$node]))
				return array();
				
			$values = &$values[$name][$node];
			$params = &$params[$name][$node];
		}
		
		
		if (!isset($params[$typeName]))
			return array();
		return $params[$typeName];
	}

	protected function _NodeHasType($id, $type, $param = 'TYPE')
	{
		$values = &$this->object->value;
		$params = &$this->object->param;

		settype($id, 'array');		// for $id of name only
		if (!is_array($id[0]))		// for $id of array(name, node, ...)
			$id = array($id);		// convert to array(array(name, node), ...)
		foreach ($id as $i)
		{
			$name = $i[0];
			$node = 0;
			if (count($i) > 1)
				$node = $i[1];
			if (!isset($values[$name]) || !isset($values[$name][$node]))
				WriteDie('Non-existant node: ' . $id);
			if (!isset($params[$name]) || !isset($params[$name][$node]))
				return array();
				
			$values = &$values[$name][$node];
			$params = &$params[$name][$node];
		}
		
		if (!isset($params[$param]))
			return false;
		return vCardCalBase::_HasType($params[$param], $type);
	}

	static protected function _HasType($typeOrTypes, $type)
	{
		if (is_array($typeOrTypes))
		{
			foreach ($typeOrTypes as $key => $value)
			{
				if (strcmp(strtoupper($value), strtoupper($type)) == 0)
				{
					return 1;
				}
			}
			
			return 0;
		}
		else
		{
			return strcmp(strtoupper($typeOrTypes), strtoupper($type)) == 0;
		}
	}
	
	static protected function _AddType($typeOrTypes, $type)
	{
		if (is_array($typeOrTypes))
		{
			$temp = $typeOrTypes;
			
			if (!Utilities::ArrayContains($typeOrTypes, $type, 1))
			{
				$temp[] = $type;
			}
			
			return $temp;
		}
		else if (strcmp($typeOrTypes, $type) != 0)
		{
			$temp = $typeOrTypes;
			$temp[] = $type;
			
			return $temp;
		}
		
		return $type;
	}
	
	static protected function _RemoveType($typeOrTypes, $type)
	{
		if (is_array($typeOrTypes))
		{
			$temp = $typeOrTypes;
			
			foreach ($typeOrTypes as $key => $value)
			{
				if (strcmp(strtoupper($value), strtoupper($type)) == 0)
				{
					unset($temp[$key]);
				}
			}
			
			return $temp;
		}
		else
		{
			return array();
		}
	}

	private function _fetch(array &$lines, array &$values, array &$params, array $id)
	{
      // we want items ordered the same way each time so that if there aren't any 
      // real changes the resulting string will always be the same
      $names = array_keys($values);
      sort($names);
      
      // there are two values we want at the front of the list otherwise there are 
      // problems in some cases
      $i = array_search('VERSION', $names);
      if ($i !== false)
      {
         unset($names[$i]);
         array_unshift($names, 'VERSION');
      }
      $i = array_search('METHOD', $names);
      if ($i !== false)
      {
         unset($names[$i]);
         array_unshift($names, 'METHOD');
      }
      
		foreach ($names as $name)
		{
         $nodes = array_keys($values[$name]);
         sort($nodes);
      
			foreach ($nodes as $node)
			{
				$id[] = array($name, $node);
				if (in_array($name, $this->object->nestableTypes))
				{
			        $lines[] = 'BEGIN:' . $name;
					$p = array();
					if (isset($params[$name]) && isset($params[$name][$node]))
						$p = $params[$name][$node];
					$this->_fetch($lines, $values[$name][$node], $p, $id);
			        $lines[] = 'END:' . $name;
				}
				else
				{
               $meta = $this->_getMeta($id);
               $val = $this->_getValue2($id);
               if ($val)
               {
                  // it looks like ATTENDEE and ORGANIZER items should not have the values
                  // escaped as it screws up in some apps if they are
                  if (strtoupper($name) == 'ATTENDEE' ||
                     strtoupper($name) == 'ORGANIZER')
                  {
                     // unescape colon and ampersand (anything else?)
                     $val = Utilities::ReplaceInString($val, '\:', ':');
                     $val = Utilities::ReplaceInString($val, '\@', '@');
                  }
                  
			        $lines[] = $meta . $val;
               }
				}
				array_splice($id, count($id)-1, 1);
			}
		}
	}
	
	public function Fetch($name)
	{
        // initialize the vCard lines
        $lines = array();

        // begin (required)
        $lines[] = 'BEGIN:' . $name;

		$obj = $this->object;	// DRL FIXIT? I found that if I didn't use the 
		$values = &$obj->value;	// $obj intermediate variable that this code 
		$params = &$obj->param;	// would change the value/param arrays
		$obj = null;			// which is a really weird behavior!

		$id = array();
      if ($values == NULL) $values = array();
      if ($params == NULL) $params = array();
		$this->_fetch($lines, $values, $params, $id);

        // required
        $lines[] = 'END:' . $name;

        // version 3.0 uses \n for new lines,
        $newline = "\n";

        // fold lines at 75 characters
        $regex = "(.{1,75})";
           foreach ($lines as $key => $val) {
            if (strlen($val) > 75) {
                // we trim to drop the last newline, which will be added
                // again by the implode function at the end of fetch()
// DRL I changed this so we don't break apart multi-byte UTF-8 encoded characters.
//                $lines[$key] = trim(preg_replace("/$regex/i", "\\1$newline ", $val));
                  $lines[$key] = _MyWordWrap($val, 75, "$newline ");
            }
        }

        // compile the array of lines into a single text block
        // and return
        return implode($newline, $lines);
	}
	
    private function _getMeta($id)
    {
        $params = $this->_getParam($id);

		$name = $id[count($id)-1][0];
        if (trim($params) == '')
		{
            // no parameters
            $text = $name . ':';
        }
		else
		{
            // has parameters.  put an extra semicolon in.
            $text = $name . ';' . $params . ':';
        }
        return $text;
    }

    private function _getValue2($id)
    {
		$obj = $this->object;	// DRL FIXIT? I found that if I didn't use the
		$values = &$obj->value;	// $obj intermediate variable that this code 
		$obj = null;			// would change the value/param arrays
								// which is a really weird behavior!

		settype($id, 'array');		// for $id of name only
		if (!is_array($id[0]))		// for $id of array(name, node, ...)
			$id = array($id);		// convert to array(array(name, node), ...)
		foreach ($id as $i)
		{
			$name = $i[0];
			$node = 0;
			if (count($i) > 1)
				$node = $i[1];
			if (!isset($values[$name]) || !isset($values[$name][$node]))
				WriteDie('Non-existant node: ' . $id);
				
			$values = &$values[$name][$node];
		}
		
        $result = array();
        if (count($values))
        {
   		for ($i = 0; $i <= max(array_keys($values)); $i++)
   		{
   			if (array_key_exists($i, $values))
   			{
   	            $list = array();
   	            foreach ($values[$i] as $key => $val)
   				{
   	                $list[] = trim($val);
   	            }
   	
   	            $this->object->escape($list);
   	            $result[] = implode(',', $list);
   			}
   			else
   	            $result[] = '';
   		}
        }

        return implode(';', $result);
    }

    private function _getParam($id)
    {
		$obj = $this->object;	// DRL FIXIT? I found that if I didn't use the
		$values = &$obj->value;	// $obj intermediate variable that this code 
		$params = &$obj->param;	// would change the value/param arrays
		$obj = null;			// which is a really weird behavior!

		settype($id, 'array');		// for $id of name only
		if (!is_array($id[0]))		// for $id of array(name, node, ...)
			$id = array($id);		// convert to array(array(name, node), ...)
		foreach ($id as $i)
		{
			$name = $i[0];
			$node = 0;
			if (count($i) > 1)
				$node = $i[1];
			if (!isset($values[$name]) || !isset($values[$name][$node]))
				WriteDie('Non-existant node: ' . $id);
			if (!isset($params[$name]) || !isset($params[$name][$node]))
				return '';
				
			$values = &$values[$name][$node];
			if ($params != null)
				$params = &$params[$name][$node];
		}
		
        $text = '';

        if ($params == null)
		{
            // if there were no parameters, this will be blank.
            return $text;
        }

        // loop through the array of parameters for
        // the component

        foreach ($params as $param_name => $param_val)
		{
            // if there were previous parameter names, separate with
            // a semicolon
            if ($text != '')
                $text .= ';';

            if ($param_val === null)
			{
                // no parameter value was specified, which is typical
                // for vCard version 2.1 -- the name is the value.
                $this->escape($param_name);
                $text .= $param_name;

            }
			else
			{
                // set the parameter name...
                $text .= strtoupper($param_name) . '=';

                // ...then escape and comma-separate the parameter
                // values.
                $this->object->escape($param_val);
                $text .= implode(',', $param_val);
            }
        }
        // if there were no parameters, this will be blank.
        return $text;
    }
};

//spl_autoload_register(array('vCardCalBase', 'autoload'));

?>

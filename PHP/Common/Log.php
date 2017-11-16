<?php

static $_MyFolder = 'PHP\Common';
static $_LogFilename;

// this is just a handy way of only having to place breakpoints in one place (here)
function Brk()
{
   return 0;
}

function LogSetFile($path)
{	
	global $_LogFilename;
	
   $_LogFilename = $path;
}

function _LogWrite($string)
{
	global $_LogFilename;
	
	$string = date('Y-m-d H:i:s ') . str_replace("\n", "\r\n", $string);
	
	if (!$_LogFilename)
		$_LogFilename = dirname(__FILE__) . '/../debug.log';
		
	$fp = fopen($_LogFilename, 'a');
	if ($fp)
	{
		fwrite($fp, $string);
		fclose($fp);
	}
	else
	{
		echo('Can\'t open log file to write: ' . $string);
		exit;
	}
}

function WriteInfo($string)
{
	$string = "INFO: " . $string . "\r\n";
	_LogWrite($string);
}

function WriteWarning($string)
{
	$string = "WARNING: " . $string . "\r\n";
	_LogWrite($string);
}

function WriteError($string)
{
	$string = "ERROR: " . $string . "\r\n";
	_LogWrite($string);
}

function WriteHexString($str)
{
	$out = '';
	
	for($i=0; $i<strlen($str); $i++) 
	{
		$c = $str[$i];
		if (($c >= 'a' && $c <= 'z') || 
			($c >= 'A' && $c <= 'Z') || 
			($c >= '0' && $c <= '9') ||
			strpos('<>/\\=-_()*&%$#@!{}[]\'":;,.? ', $c) !== false)
		{
			$out .= $c;
		}
		else
		{
			$out .= '0x' . dechex(ord($c));
		}
	}
	
	_LogWrite($out . "\r\n");
}

function WriteVariable(&$var)
{
	$out = _LogVar($var, '', '', '', 0);
	_LogWrite($out . "\r\n");
}

function _LogVar(&$Var, $vn, $pit, $itw, $nlvl, $m = '')
{
	if($nlvl>=16) 
		return;
	if($nlvl==0)
	{
		$tv=serialize($Var);
		$tv=unserialize($tv);
	}
	else 
		$tv=&$Var; 
	$it=$pit.$itw;
	for($i=0; $i<$nlvl;$i++) 
		$it.='.'.$itw;
	$o='';
	$nl="\n";
	if(is_array($tv))
	{
		if(strlen($vn)>0)
			$o.=$it.$m.'<array> $'.$vn.' = (';
		else
			$o.="\r\n".$it.$m.'<array> = (';
		$o.= $nl;
		$AK=array_keys($tv);
		foreach($AK as $AN)
		{
			$AV=&$tv[$AN];
			$o.=_LogVar($AV,$AN,$pit,$itw,$nlvl+1);
		}
		$o.=$it.')'.$nl;
	}
	else if(is_string($tv))
	{
		if(strlen($vn)>0)
			$o.=$it.$m.'<string> $'.$vn.' = ';
		else
			$o.=' '.$m.'<string> = ';
		if($tv===null)
			$o.='NULL';
		else
			$o.='"'.$tv.'"';
		$o.=$nl;
	}
	else if(is_bool($tv))
	{
		if(strlen($vn) > 0) 
			$o.=$it.$m.'<boolean> $'.$vn.' = ';
		else 
			$o.=' '.$m.'<boolean> = ';
		if($tv===true) 
			$o.='TRUE';
		else 
			$o.='FALSE';
		$o.=$nl;
	}
	else if(is_object($tv))
	{
		if(strlen($vn)>0)
		{
			$o.=$pit.$itw;
			for($i=0;$i<$nlvl;$i++) 
				$o.='.'.$itw;
			$o.=$m.'<'.get_class($tv).'::$'.$vn.'> = {'.$nl;
		}
		else 
			$o.=' '.$m.'<'.get_class($tv).'::> = {'.$nl;
		$R=new ReflectionClass($tv);
		$o.=$it.'.'.$itw.'Class methods {'.$nl;
		$CM=$R->getMethods();
		foreach($CM as $MN => $MV)
		{
			$o.=$it.'.'.$itw.'.'.$itw.implode(' ',Reflection::getModifierNames($MV->getModifiers())).' '.$MV->getName().'(';
			$MP=$MV->getParameters(); $ct='';
			foreach($MP as $MPN => $MPV)
			{
				$o.=$ct; $o.=$MPV->isOptional()?'[':'';
				if($MPV->isArray()) 
					$o.='<array> ';
				else if($MPV->getClass()!==null) 
					$o.='<'.$MPV->getClass()->getName().'::> ';
				$o.=$MPV->isPassedByReference()?'&':''; $o.='$'.$MPV->getName();
				if($MPV->isDefaultValueAvailable())
				{
					if (is_array($MPV->getDefaultValue()))
						$o.=' = [ARRAY]';
					else if($MPV->getDefaultValue()===null) 
						$o.=' = NULL';
					else if($MPV->getDefaultValue()===true) 
						$o.=' = TRUE';
					else if($MPV->getDefaultValue()===false) 
						$o.=' = FALSE';    
               else
   					$o.=' = '.$MPV->getDefaultValue();    
				}
				$o.=$MPV->isOptional()?']':''; $ct=', ';
			}
			$o.=')'.$nl;
		}
		$o.=$it.'.'.$itw.'}'.$nl; $o.=$it.'.'.$itw.'Class properties {'.$nl;
		$CV=$R->getProperties();
		foreach($CV as $CN => $CV)
		{
			$M=implode(' ',Reflection::getModifierNames($CV->getModifiers())).' ';
			$CV->setAccessible(true);
         $_var = $CV->getValue($tv);
         $o.=_LogVar($_var,$CV->getName(),$pit,$itw,$nlvl+2,$M);
		}
		$o.=$it.'.'.$itw.'}'.$nl; $o.=$it.'.'.$itw.'Object variables {'.$nl;
		$OVs=get_object_vars($tv);    
		foreach($OVs as $ON => $OV) 
			$o.=_LogVar($OV,$ON,$pit,$itw,$nlvl+2);
		$o.=$it.'.'.$itw.'}'.$nl; $o.=$pit.$itw;
		for($i=0;$i<$nlvl;$i++)
			$o.='.'.$itw;
		$o.='}'.$nl;
	}
	else
	{
		if(strlen($vn)>0) 
			$o.=$it.$m.'<'.gettype($tv).'> $'.$vn.' = '.$tv;
		else 
			$o.=' '.$m.'<'.gettype($tv).'> = '.$tv;
		$o.=$nl;
	}          
	return $o;    
}

function _LogCallStack($callStack)
{
	global $_MyFolder;
	
	$path_strip_len = strlen(dirname(__FILE__)) - strlen($_MyFolder);
	
	$it = '';
	$itw = '  ';
	$Ts = array_reverse($callStack);
	foreach($Ts as $T)
	{  
		if($T['function'] != 'include' && $T['function'] != 'require' && $T['function'] != 'include_once' && $T['function'] != 'require_once')
		{
			$ft = $it;
			if (isset($T['file']))
			{
				$ft .= '<'. substr($T['file'], $path_strip_len) . '> on line ' . $T['line'];  
				if($T['function'] != 'LogTrace')
				{
					if(isset($T['class']))
						$ft .= ' in method ' . $T['class'] . $T['type'];
					else 
						$ft .= ' in function ';
					$ft .= $T['function'] . '(';
				}
				else
					$ft .= '(';
			}
			else
			{
				$ft .= $T['function'] . '(';
			}
/*
			if(isset($T['args'][0]))
			{
				if($T['function'] != 'LogTrace')
				{
					$ct = '';
					foreach($T['args'] as $A)
					{
						$ft .= $ct . _LogVar($A, '', $it, $itw, 0);
						$ct = $it . $itw . ',';
					}
				}
				else
					$ft .= _LogVar($T['args'][0], '', $it, $itw, 0);
			}
			$ft .= $it;
*/
			$ft .= ")\r\n";
			_LogWrite($ft); 
			$it .= $itw;
		}            
	}
}

function WriteCallStack($string)
{
	_LogWrite("[BEGIN TRACE: " . $string . "]\r\n"); 
	_LogCallStack(debug_backtrace());
	_LogWrite("[END TRACE]\r\n");
}

function WriteException($exception, $string='')
{
	_LogWrite("\r\n");
	_LogWrite(get_class($exception) . ": " . $exception->getMessage() . "\r\n");
	_LogWrite("[BEGIN TRACE: " . $string . "]\r\n"); 
	_LogCallStack($exception->getTrace());
	_LogWrite("[END TRACE]\r\n");
	_LogWrite("\r\n");
}

function WriteDie($string)
{
	$string = "ERROR: " . $string . "\r\n";
	WriteCallStack($string);
	exit;
}

function DebugOutput($message, $verbosity = 1, $limit = 600)
{
	if ($limit) $message = substr($message, 0, $limit);
   global $DEBUG_OUTPUT, $_verbosity;
   if ($DEBUG_OUTPUT === true and $verbosity <= $_verbosity)
   {
      _LogWrite($message . PHP_EOL);
   }
}

function DebugTitle($title, $chr = '*', $verbosity = 1)
{
   global $DEBUG_OUTPUT, $_verbosity;
   if ($DEBUG_OUTPUT === true and $verbosity <= $_verbosity)
   {
      $title = str_repeat($chr, 10) . strtoupper($title) . str_repeat($chr, 10);
      _LogWrite($title . PHP_EOL);
   }
}

function DebugSep($verbosity = 1)
{
   global $DEBUG_OUTPUT, $_verbosity;
   if ($DEBUG_OUTPUT === true and $verbosity <= $_verbosity)
   {
      $message = PHP_EOL . '---' . PHP_EOL;
      _LogWrite($message . PHP_EOL);
   }
}

?>

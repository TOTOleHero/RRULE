<?php

class SaferScript
{
   var $source, $allowedCalls, $parseErrors;
   
   function SaferScript($scriptText)
   {
      $this->source = $scriptText;
      $this->allowedCalls = array();
   }
   
   function allowHarmlessCalls()
   {
      $this->allowedCalls = explode(',', 
         'explode,implode,date,time,round,trunc,rand,ceil,floor,srand,'.
         'strtolower,strtoupper,substr,stristr,strpos,print,print_r');
   }
   
   function parse()
   {
      $this->parseErrors = array();
      $tokens = token_get_all('<?'.'php '.$this->source.' ?'.'>');
      $vcall = '';
      
      foreach ($tokens as $token)
      {
         if (is_array($token))
         {
            $id = $token[0];
            switch ($id)
            {
               case(T_VARIABLE): { $vcall .= 'v'; break; }
               case(T_STRING): { $vcall .= 's'; }
               case(T_REQUIRE_ONCE): case(T_REQUIRE): case(T_NEW): case(T_RETURN):
               case(T_BREAK): case(T_CATCH): case(T_CLONE): case(T_EXIT):
               case(T_PRINT): case(T_GLOBAL): case(T_ECHO): case(T_INCLUDE_ONCE):
               case(T_INCLUDE): case(T_EVAL): case(T_FUNCTION):
               {
                  if (array_search($token[1], $this->allowedCalls) === false)
                     $this->parseErrors[] = 'illegal call: '.$token[1];
               }
            }
         }
         else
         { 
            $vcall .= $token;
         }
      }
      
      if (stristr($vcall, 'v(') != '') 
         $this->parseErrors[] = array('illegal dynamic function call');
      
      return $this->parseErrors;
   }

   function execute($parameters = array(), &$message)
   {
      if (sizeof($this->parseErrors) > 0)
      {
         $message = implode("\r\n", $this->parseErrors);
         return NULL;
      }
         
      foreach ($parameters as $k => $v)
         $$k = $v;
         
      return eval('return ' . $this->source);
   }   
};

class Script
{
   static function Check($script, $params)
   {
      $ls = new SaferScript($script);
      $ls->allowHarmlessCalls();
      $message = '';
      $errors = $ls->parse();
      if ($errors)
      {
         $message = implode("\r\n", $errors);
      }
      return $message;
   }
   
   static function Run($script, $params, &$message)
   {
      $ls = new SaferScript($script);
      $ls->allowHarmlessCalls();
      $ls->parse();
      return $ls->execute($params, $message);
   }
};
   
if (0)
{
   $message = '';
   $test = Script::Run('$str . 1;', array('str'=>'val1'), $message);
}

?>
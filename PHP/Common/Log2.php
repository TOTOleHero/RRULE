<?php
   
   function WriteInfo($message)
   {
      file_put_contents('/home/uberfine/public_html/DebugLog.log', $message . '\n', FILE_APPEND | LOCK_EX);
   }
   
   function WriteError($message, $error = NULL, $page = NULL)
   {
      if (empty($page))
         $page = '';
      else
         $page .= ': ';
      if (empty($error))
         $error = '';
      else
         $error = ': ' . $error;
      error_log($page . $message . $error);
   }

?>
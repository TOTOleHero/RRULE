<?php

require_once(dirname(__FILE__) . '/Log.php');

// Active assert and make it quiet
assert_options(ASSERT_ACTIVE, 1);
assert_options(ASSERT_WARNING, 0);
assert_options(ASSERT_QUIET_EVAL, 1);

// Create a handler function
function my_assert_handler($file, $line, $code, $desc = null)
{
   if ($desc)
      $desc = ": $desc";
   WriteCallStack("Assertion failed at $file:$line: $code");
}

// Set up the callback
assert_options(ASSERT_CALLBACK, 'my_assert_handler');

if (0)
{
   // Make an assertion that should fail
   assert('2 < 1');
   assert('2 < 1', 'Two is less than one');
}

?>
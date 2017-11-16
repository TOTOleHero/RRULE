Background:

In the file PHP\Common\RecurrenceRule.php and a few support files starting with RecurrenceRule in their name, there is code implementing the iCalendar RRULE specification (see https://www.kanzaki.com/docs/ical/rrule.html). This code was originally written in Perl and converted to PHP so some of the issues you find may be relating to this conversion and the differences between the languages. This code was also converted to JavaScript into files of similar names found in the JavaScript\Common folder.

Near the bottom of the RecurrenceRule.php and RecurrenceRule.js files there is a section of unit tests currently disabled with an "if (0)". These unit tests call the method _CheckValues() to check for the correct results.

Tasks: 

1) In RecurrenceRule.php enable the unit test section and for each of the existing unit tests check that the result expected matches the rule (just to make sure the unit test is valid), then run the unit test and see if it passes. If it does not pass, fix whatever is broken.

2) Add some additional unit tests taken from the reference Web site provided above to have greater assurance that the code is correct.

3) Perform the same above steps for the JavaScript implementation. The two implementations should be almost identical when you are finished. There may be minor differences before you begin since the PHP version was more heavily used and supported.

<?php
/**
 * +----------------------------------------------------------------------+
 * | Copyright (c) 1997-2008 The PHP Group                                |
 * +----------------------------------------------------------------------+
 * | All rights reserved.                                                 |
 * |                                                                      |
 * | Redistribution and use in source and binary forms, with or without   |
 * | modification, are permitted provided that the following conditions   |
 * | are met:                                                             |
 * |                                                                      |
 * | - Redistributions of source code must retain the above copyright     |
 * | notice, this list of conditions and the following disclaimer.        |
 * | - Redistributions in binary form must reproduce the above copyright  |
 * | notice, this list of conditions and the following disclaimer in the  |
 * | documentation and/or other materials provided with the distribution. |
 * | - Neither the name of the The PEAR Group nor the names of its        |
 * | contributors may be used to endorse or promote products derived from |
 * | this software without specific prior written permission.             |
 * |                                                                      |
 * | THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS  |
 * | "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT    |
 * | LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS    |
 * | FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE       |
 * | COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,  |
 * | INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, |
 * | BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;     |
 * | LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER     |
 * | CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT   |
 * | LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN    |
 * | ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE      |
 * | POSSIBILITY OF SUCH DAMAGE.                                          |
 * +----------------------------------------------------------------------+
 *
 * PHP Version 5
 *
 * @category File_Formats
 * @package  File_IMC
 * @author   Paul M. Jones <pmjones@ciaweb.net>
 * @license  http://www.opensource.org/licenses/bsd-license.php The BSD License
 * @version  SVN: $Id: Vcalendar.php 318592 2011-10-30 12:07:39Z till $
 * @link     http://pear.php.net/package/File_IMC
 */

/**
* This class builds a single iCalendar (version 2.0 or 1.0).
*
* General note: we use the terms "set", "add", and "get" as function
* prefixes.
*
* "Set" means there is only one iteration of a component, and it has
* only one value repetition, so you set the whole thing at once.
*
* "Add" means eith multiple iterations of a component are allowed, or
* that there is only one iteration allowed but there can be multiple
* value repetitions, so you add iterations or repetitions to the current
* stack.
*
* "Get" returns the full iCalendar line for a single iteration.
*
* @category File_Formats
* @package  File_IMC
* @author   Paul M. Jones <pmjones@ciaweb.net>
* @license  http://www.opensource.org/licenses/bsd-license.php The BSD License
* @version  Release: 0.4.3
* @link     http://pear.php.net/package/File_IMC
*/
class File_IMC_Build_Vcalendar extends File_IMC_Build
{
	public $nestableTypes = array('VTIMEZONE','VALARM','VEVENT','VTODO','VJOURNAL','VFREEBUSY');
	
    /**
    * Constructor
    *
    * @param string $version The iCalendar version to build; affects which
    * parameters are allowed and which components are returned by
    * fetch().
    *
    * @return File_IMC_Build_Vcalendar
    *
    * @see  parent::fetch()
    * @uses parent::reset()
    */
    public function __construct($version = '2.0')
    {
        $this->reset($version);
    }

    /**
    * Sets the version of the the iCalendar.  Only one iteration.
    *
    * @param string $text The text value of the verson text ('2.0' or '1.0').
    *
    * @return mixed Void on success
    * @throws File_IMC_Exception on failure.
    */
    public function setVersion($text = '2.0')
    {
        $this->autoparam = 'VERSION';
        if ($text != '2.0' && $text != '1.0') {
            throw new File_IMC_Exception(
                'Version must be 2.0 or 1.0 to be valid.',
                FILE_IMC::ERROR_INVALID_VCALENDAR_VERSION);
        }
        $this->setValue('VERSION', 0, 0, $text);
    }

    /**
    * Validates parameter names and values based on the iCalendar version
    * (1.0 or 2.0).
    *
    * @param  string $name The parameter name (e.g., TYPE or ENCODING).
    *
    * @param  string $text The parameter value (e.g., HOME or BASE64).
    *
    * @param  string $comp Optional, the component name (e.g., ADR or
    *                      PHOTO).  Only used for error messaging.
    *
    * @param  string $iter Optional, the iteration of the component.
    *                      Only used for error messaging.
    *
    * @return mixed        Boolean true if the parameter is valid
    * @throws File_IMC_Exception ... if not.
    *
    * @uses self::validateParam10()
    * @uses self::validateParam20()
    */
    public function validateParam($name, $text, $comp = null, $iter = null)
    {
/*
        $name = strtoupper($name);
        $text = strtoupper($text);

        // all param values must have only the characters A-Z 0-9 and -.
        if (preg_match('/[^a-zA-Z0-9\-]/i', $text)) {

            throw new File_IMC_Exception(
                "iCalendar [$comp] [$iter] [$name]: The parameter value may contain only a-z, A-Z, 0-9, and dashes (-).",
                FILE_IMC::ERROR_INVALID_PARAM);
        }

        if ($this->value['VERSION'][0][0][0] == '1.0') {

            return $this->validateParam10($name, $text, $comp, $iter);

        } elseif ($this->value['VERSION'][0][0][0] == '2.0') {

            return $this->validateParam20($name, $text, $comp, $iter);

        }
		
        throw new File_IMC_Exception(
            "[$comp] [$iter] Unknown iCalendar version number or other error.",
            FILE_IMC::ERROR);
*/

		return true;
    }


    /**
    * Fetches a full iCalendar text block based on $this->value and
    * $this->param. The order of the returned components is similar to
    * their order in RFC 2426.  Honors the value of
    * $this->value['VERSION'] to determine which iCalendar components are
    * returned (1.0- or 2.0-compliant).
    *
    * @return string A properly formatted iCalendar text block.
    */
    public function fetch()
    {
        throw new File_IMC_Exception(
            'DRL FIXIT! Use derived version!',
            FILE_IMC::ERROR_PARAM_NOT_SET);
    }
}

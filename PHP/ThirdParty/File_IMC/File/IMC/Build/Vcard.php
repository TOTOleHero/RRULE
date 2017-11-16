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
 * @version  SVN: $Id: Vcard.php 318592 2011-10-30 12:07:39Z till $
 * @link     http://pear.php.net/package/File_IMC
 */

/**
* This class builds a single vCard (version 3.0 or 2.1).
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
* "Get" returns the full vCard line for a single iteration.
*
* @category File_Formats
* @package  File_IMC
* @author   Paul M. Jones <pmjones@ciaweb.net>
* @license  http://www.opensource.org/licenses/bsd-license.php The BSD License
* @version  Release: 0.4.3
* @link     http://pear.php.net/package/File_IMC
*/
class File_IMC_Build_Vcard extends File_IMC_Build
{
	public $nestableTypes = array('VCARD');
	
    /**
    * Constructor
    *
    * @param string $version The vCard version to build; affects which
    * parameters are allowed and which components are returned by
    * fetch().
    *
    * @return File_IMC_Build_Vcard
    *
    * @see  parent::fetch()
    * @uses parent::reset()
    */
    public function __construct($version = '3.0')
    {
        $this->reset($version);
    }

    /**
    * Sets the version of the the vCard.  Only one iteration.
    *
    * @param string $text The text value of the verson text ('3.0' or '2.1').
    *
    * @return mixed Void on success
    * @throws File_IMC_Exception on failure.
    */
    public function setVersion($text = '3.0')
    {
        $this->autoparam = 'VERSION';
        if ($text != '3.0' && $text != '2.1') {
            throw new File_IMC_Exception(
                'Version must be 3.0 or 2.1 to be valid.',
                FILE_IMC::ERROR_INVALID_VCARD_VERSION);
        }
        $this->setValue('VERSION', 0, 0, $text);
    }

    /**
    * Validates parameter names and values based on the vCard version
    * (2.1 or 3.0).
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
    * @uses self::validateParam21()
    * @uses self::validateParam30()
    */
    public function validateParam($name, $text, $comp = null, $iter = null)
    {
        $name = strtoupper($name);
        $text = strtoupper($text);

        // all param values must have only the characters A-Z 0-9 and -.
        if (preg_match('/[^a-zA-Z0-9\-]/i', $text)) {

            throw new File_IMC_Exception(
                "vCard [$comp] [$iter] [$name]: The parameter value may contain only a-z, A-Z, 0-9, and dashes (-).",
                FILE_IMC::ERROR_INVALID_PARAM);
        }

        if ($this->value['VERSION'][0][0][0] == '2.1') {

            return $this->validateParam21($name, $text, $comp, $iter);

        } elseif ($this->value['VERSION'][0][0][0] == '3.0') {

            return $this->validateParam30($name, $text, $comp, $iter);

        }

        throw new File_IMC_Exception(
            "[$comp] [$iter] Unknown vCard version number or other error.",
            FILE_IMC::ERROR);
    }

    /**
     * Validate parameters with 2.1 vcards.
     *
     * @param string $name The parameter name (e.g., TYPE or ENCODING).
     *
     * @param string $text The parameter value (e.g., HOME or BASE64).
     *
     * @param string $comp Optional, the component name (e.g., ADR or
     *                     PHOTO).  Only used for error messaging.
     *
     * @param string $iter Optional, the iteration of the component.
     *                     Only used for error messaging.
     *
     * @return boolean
     */
    protected function validateParam21($name, $text, $comp, $iter)
    {
        // Validate against version 2.1 (pretty strict)
        static $types = array (
            'DOM', 'INTL', 'POSTAL', 'PARCEL','HOME', 'WORK',
            'PREF', 'VOICE', 'FAX', 'MSG', 'CELL', 'PAGER',
            'BBS', 'MODEM', 'CAR', 'ISDN', 'VIDEO',
            'AOL', 'APPLELINK', 'ATTMAIL', 'CIS', 'EWORLD',
            'INTERNET', 'IBMMAIL', 'MCIMAIL',
            'POWERSHARE', 'PRODIGY', 'TLX', 'X400',
            'GIF', 'CGM', 'WMF', 'BMP', 'MET', 'PMB', 'DIB',
            'PICT', 'TIFF', 'PDF', 'PS', 'JPEG', 'QTIME',
            'MPEG', 'MPEG2', 'AVI',
            'WAVE', 'AIFF', 'PCM',
            'X509', 'PGP'
        );

        switch ($name) {

        case 'TYPE':
            if (!in_array($text, $types)) {
                throw new File_IMC_Exception(
                    "vCard 2.1 [$comp] [$iter]: $text is not a recognized TYPE.",
                    FILE_IMC::ERROR_INVALID_PARAM);
            }
            $result = true;
            break;

        case 'ENCODING':
            if ($text != '7BIT' &&
                $text != '8BIT' &&
                $text != 'BASE64' &&
                $text != 'QUOTED-PRINTABLE') {

                throw new File_IMC_Exception(
                    "vCard 2.1 [$comp] [$iter]: $text is not a recognized ENCODING.",
                    FILE_IMC::ERROR_INVALID_PARAM);
            }
            $result = true;
            break;

        case 'CHARSET':  // all charsets are OK
        case 'LANGUAGE': // all languages are OK
            $result = true;
            break;

        case 'VALUE':
            if ($text != 'INLINE' &&
                $text != 'CONTENT-ID' &&
                $text != 'CID' &&
                $text != 'URL' &&
                $text != 'VCARD') {

                throw new File_IMC_Exception(
                    "vCard 2.1 [$comp] [$iter]: $text is not a recognized VALUE.",
                    FILE_IMC::ERROR_INVALID_PARAM);
            }
            $result = true;
            break;

        default:
            throw new File_IMC_Exception(
                "vCard 2.1 [$comp] [$iter]: $name is an unknown or invalid parameter name.",
                FILE_IMC::ERROR_INVALID_PARAM);
            break;
        }

        return $result;
    }

    /**
     * Validate parameters with 3.0 vcards.
     *
     * @param string $name The parameter name (e.g., TYPE or ENCODING).
     *
     * @param string $text The parameter value (e.g., HOME or BASE64).
     *
     * @param string $comp Optional, the component name (e.g., ADR or
     *                     PHOTO).  Only used for error messaging.
     *
     * @param string $iter Optional, the iteration of the component.
     *                     Only used for error messaging.
     *
     * @return boolean
     * @throws File_IMC_Exception In case of unexpectiveness.
     */
    protected function validateParam30($name, $text, $comp, $iter)
    {

        // Validate against version 3.0 (pretty lenient)
        switch ($name) {

        case 'TYPE':     // all types are OK
        case 'LANGUAGE': // all languages are OK
            $result = true;
            break;

        case 'ENCODING':
 /* DRL FIXIT? We don't care about checking this. I want to use BASE64 usually.
            if ($text != '8BIT' &&
                $text != 'B') {
                throw new File_IMC_Exception(
                    "vCard 3.0 [$comp] [$iter]: The only allowed ENCODING parameters are 8BIT and B.",
                    FILE_IMC::ERROR_INVALID_PARAM);
            }
*/
            $result = true;
            break;

        case 'VALUE':
            if ($text != 'BINARY' &&
                $text != 'PHONE-NUMBER' &&
                $text != 'TEXT' &&
                $text != 'URI' &&
                $text != 'URL' &&		// DRL FIXIT? Added for PHOTO
                $text != 'UTC-OFFSET' &&
                $text != 'VCARD') {

                $msg  = "vCard 3.0 [$comp] [$iter]: The only allowed VALUE";
                $msg .= " parameters are BINARY, PHONE-NUMBER, TEXT, URI,";
                $msg .= " UTC-OFFSET, and VCARD.";

                throw new File_IMC_Exception($msg, FILE_IMC::ERROR_INVALID_PARAM);
            }
            $result = true;
            break;
        default:
            throw new File_IMC_Exception(
                "vCard 3.0 [$comp] [$iter]: Unknown or invalid parameter name ($name).",
                FILE_IMC::ERROR_INVALID_PARAM);
            break;

        }
        return $result;
    }

    /**
    * Fetches a full vCard text block based on $this->value and
    * $this->param. The order of the returned components is similar to
    * their order in RFC 2426.  Honors the value of
    * $this->value['VERSION'] to determine which vCard components are
    * returned (2.1- or 3.0-compliant).
    *
    * @return string A properly formatted vCard text block.
    */
    public function fetch()
    {
        throw new File_IMC_Exception(
            'DRL FIXIT! Use derived version!',
            FILE_IMC::ERROR_PARAM_NOT_SET);
    }
}

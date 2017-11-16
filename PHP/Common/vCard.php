<?php

require_once(dirname(__FILE__) . '/DateAndTime.php');
require_once(dirname(__FILE__) . '/Log.php');
require_once(dirname(__FILE__) . '/MimeTypes.php');
require_once(dirname(__FILE__) . '/Utilities.php');
require_once(dirname(__FILE__) . '/vCardAddress.php');
require_once(dirname(__FILE__) . '/vCardCalBase.php');

/*
===================================================================

Implementation.

===================================================================

This module implements a wrapper for RFC 2426 and includes extensions
for RFC 4770 (instant messaging).

Note that the HomeXxx and WorkXxx methods use HOME and PERSONAL as
well as WORK and BUSINESS as synonomous. Also CELL and MOBILE are
synonomous.

Note that the methods of this object which return a simple string
never return a NULL value since the storage of a vCard can't
distinguish between a NULL and an empty string.

===================================================================
These are the vCard (RFC 2426) fields we support:

	FN - display name
	N - family name; given name; additional names; prefixes; suffixes (semicolon delimited)
	NICKNAME - familiar form of the proper name
	PHOTO - binary inline or URI
	BDAY - birthday (as in "1996-04-15", "1953-10-15T23:10:00Z" or "1987-09-27T08:30:00-06:00")
DRL FIXIT? We don't currently handle the post office box below!
	ADR - post office box; extended address; street address; locality (city); region (state or province); postal code; country (semicolon delimited)
	LABEL - text value of delivery address
	TEL - canonical format of the number
	EMAIL - an email address
	TZ - time zone (as in "-05:00" or "-05:00; EST; Raleigh/North America")
	GEO - global position in latitude and longitude (as in "37.386013;-122.082932")
	TITLE - job title
	ORG - organizational information (as in "ABC\, Inc.;North American Division;Marketing")
	NOTE - free form text
	REV - vCard instance revision (as in "1995-10-31T22:27:10Z" or "1997-11-15")
	URL - a Web site
	UID - unique identifier
	VERSION - must be "3.0"

Non-standard:
	X-WAB-GENDER - (1=female, 2=male)
	X-ADDRESSBOOKSERVER-GROUP (for storing group membership in the contact instead of in the group)

DRL FIXIT? To be added?
	X-XXX-UID - to store Facebook uid and Google id where XXX can be FACEBOOK, GOOGLE, etc.
	X-AIM
	X-JABBER
	X-MSN
*/
	

class vCard extends vCardCalBase
{
   public static $TypeConversions = 
   array(
      'personal'  => 'home', 
      'business'  => 'work', 
      'mobile'    => 'cell'
   );
   
	public static $AttrLocations =	// used for phones, emails, IMs, addresses
	array(
		'home'      => 'Home',
		'work'      => 'Work',
		'other'     => 'Other'
	);
	
	public static $WebSiteTypes =
	array(
		'home'      => 'Home',
		'work'      => 'Work',
		'home-page' => 'Home Page',
		'ftp'       => 'FTP',
		'blog'      => 'Blog',
		'profile'   => 'Profile',
		'other'     => 'Other'
	);
	
	public static $AttrPhoneTypes =
	array(
		'landline'  => 'Landline',
		'cell'      => 'Cell',
		'fax'       => 'Fax',
		'pager'     => 'Pager'
	);
	
	public static $IMTypes =
	array(
		'msn'       => 'Windows Live (MSN) Messenger',
		'yahoo'     => 'Yahoo Messenger',
		'skype'     => 'Skype',
		'aim'       => 'AOL Instant Messenger',
		'qq'        => 'Tencent QQ',
		'google_talk'   => 'Google Talk',
		'icq'       => 'ICQ',
		'jabber'    => 'Jabber',
		'irc'       => 'Internet Relay Chat (IRC)',
		'sip'       => 'SIP IM'
	);

	public static $ObsoleteTypes =
	array(
		'voice'     => '',
		'msg'       => '',
		'bbs'       => '',
		'modem'     => '',
		'car'       => '',
		'video'     => ''
	);

	public static $Primary = 'pref';
   public static $Mobile = 'cell';
   public static $Home = 'home';
   public static $Work = 'work';
   public static $Other = 'other';
	
   // some values are indexed numerically in the following order
	protected $ValueNames =
	array(
	    'ADR' => array(
	        'po_box', 'extended',  'street', 'city',
	        'region', 'post_code', 'country'
	    ),
	    'N'   => array( 'family', 'given', 'middle', 'prefixes', 'suffixes' ),
	    'GEO' => array( 'lat',    'long' ),
	    'ORG' => array( 'name',   'unit' ),
	);

   private $variables = NULL;
   
    /**
	 * SPL-compatible autoloader.
	 *
     * @param string $className Name of the class to load.
     *
     * @return boolean
    public static function autoload($className)
    {
        if ($className != 'vCard') {
            return false;
		}
        return include str_replace('_', '/', $className) . '.php';
    }
     */

    /**
    * Builder factory
    *
    * Creates an instance of the correct parser class, based on the
    * parameter passed. For example, File_IMC::parse('vCard') creates
    * a new object to parse a vCard file.
    *
    * @return mixed
    * @throws File_IMC_Exception In case the driver is not found/available.
    */
	function __construct()
	{
		$this->object = File_IMC::build('vCard');

		// set some default/required fields...
		$this->object->setVersion();
	}
	
	function __destruct()
	{
		$this->object = NULL;
	}

	function ToBinary()
	{
		return $this->ToString();
	}
	
	function ToString()
	{
		$result = "";
		if ($this->object != NULL)
			$result = $this->Fetch('VCARD');
			
		return $result;
	}
	
	static function FromBinary($binary)
	{
		return vCard::FromString($binary);
	}
	
	static function FromString($string)
	{
		if ($string == NULL || strlen($string) == 0)
		{
			return NULL;
		}
		
		$result = new vCard();
		
		try
		{
			// create vCard parser
			$parse = File_IMC::parse('vCard');
			
			// parse a vCard file and store the data in $cardinfo
			$info = $parse->fromText($string);
			
			if ($result->object == NULL)
				$result->object = File_IMC::build('vCard');
			
			$key = array_keys($info);	// could be vCard or VCARD
			$result->object->setFromArray($info[$key[0]]);
         
         // fix some problems so they don't hit us later
         $result->_CheckAddressArray('ADR');
         $result->_CheckArray('TEL');
         $result->_CheckArray('URL');
         $result->_CheckArray('EMAIL');
         $result->_CheckArray('IMPP');
		}
		catch (Exception $e)
		{
			WriteError("Error evaluating vCard object: " . $e . "/nSTART...\n$string\nEND");
         $result = NULL;
		}
		
		return $result;
	}

	function LastName()
	{
		$node = $this->_FindNode('N');
		if ($node == -1) { return ""; }
		
		return $this->_GetValue(array('N', $node, 'family'));
	}
	
	function SetLastName($value)
	{
		$this->_SetValue(array('N', $this->_GetNode('N'), 'family'), $value);
	}
	
	function MiddleName()
	{
		$node = $this->_FindNode('N');
		if ($node == -1) { return ""; }
		
		return $this->_GetValue(array('N', $node, 'middle'));
	}
	
	function SetMiddleName($value)
	{
		$this->_SetValue(array('N', $this->_GetNode('N'), 'middle'), $value);
	}
	
	function FirstName()
	{
		$node = $this->_FindNode('N');
		if ($node == -1) { return ""; }
		
		return $this->_GetValue(array('N', $node, 'given'));
	}
	
	function SetFirstName($value)
	{
		$this->_SetValue(array('N', $this->_GetNode('N'), 'given'), $value);
	}
	
	function NamePrefix()
	{
		$node = $this->_FindNode('N');
		if ($node == -1) { return ""; }
		
		return $this->_GetValue(array('N', $node, 'prefixes'));
	}
	
	function SetNamePrefix($value)
	{
		$this->_SetValue(array('N', $this->_GetNode('N'), 'prefixes'), $value);
	}
	
	function NameSuffix()
	{
		$node = $this->_FindNode('N');
		if ($node == -1) { return ""; }
		
		return $this->_GetValue(array('N', $node, 'suffixes'));
	}
	
	function SetNameSuffix($value)
	{
		$this->_SetValue(array('N', $this->_GetNode('N'), 'suffixes'), $value);
	}
	
	function DisplayName()
	{
		$node = $this->_FindNode('FN');
		if ($node == -1) { return ""; }
		
		return $this->_GetValue(array('FN', $node));
	}
	
	function SetDisplayName($value)
	{
		$this->_SetValue(array('FN', $this->_GetNode('FN')), $value);
	}
	
	function Nickname()
	{
		$node = $this->_FindNode('NICKNAME');
		if ($node == -1) { return ""; }
		
		return $this->_GetValue(array('NICKNAME', $node));
	}
	
	function SetNickname($value)
	{
		$this->_SetValue(array('NICKNAME', $this->_GetNode('NICKNAME')), $value);
	}
	
	function CompanyName()
	{
		$node = $this->_FindNode('ORG');
		if ($node == -1) { return ""; }
		
		return $this->_GetValue(array('ORG', $node, 'name'));
	}
	
	function SetCompanyName($value)
	{
		$this->_SetValue(array('ORG', $this->_GetNode('ORG'), 'name'), $value);
	}
	
	function Department()
	{
		$node = $this->_FindNode('ORG');
		if ($node == -1) { return ""; }
		
		$values = $this->_GetValue(array('ORG', $node, 'unit'));	// returns an array reference
		if (is_array($values))
			$values = join(", ", $values);
			
		return $values;
	}
	
	function SetDepartment($value)
	{
		$temp = preg_split("/,\s*/", $value);
		// DRL FIXIT! The lower layer puts commas between the elements instead of semicolons!
		$this->_SetValue(array('ORG', $this->_GetNode('ORG'), 'unit'), $temp);
	}
	
	function JobTitle()
	{
		$node = $this->_FindNode('TITLE');
		if ($node == -1) { return ""; }
		
		return $this->_GetValue(array('TITLE', $node));
	}
	
	function SetJobTitle($value)
	{
		$this->_SetValue(array('TITLE', $this->_GetNode('TITLE')), $value);
	}
	
	function WorkPhone()
	{
		$node = $this->_FindNode('TEL', 'WORK');
		if ($node == -1) { return ""; }
		
		return $this->_GetValue(array('TEL', $node));
	}
	
	function SetWorkPhone($value)
	{
		$this->_SetValue(array('TEL', $this->_GetNode('TEL', 'WORK')), $value);
	}
	
	function WorkMobilePhone()
	{
		$node = $this->_FindNode('TEL', array('CELL','WORK'));
		if ($node == -1) { return ""; }
		
		return $this->_GetValue(array('TEL', $node));
	}
	
	function SetWorkMobilePhone($value)
	{
		$this->_SetValue(array('TEL', $this->_GetNode('TEL', array('CELL', 'WORK'))), $value);
	}
	
	function WorkFax()
	{
		$node = $this->_FindNode('TEL', array('WORK', 'FAX'));
		if ($node == -1) { return ""; }
		
		return $this->_GetValue(array('TEL', $node));
	}
	
	function SetWorkFax($value)
	{
		$this->_SetValue(array('TEL', $this->_GetNode('TEL', array('WORK', 'FAX'))), $value);
	}
	
	function WorkPager()
	{
		$node = $this->_FindNode('TEL', array('WORK', 'PAGER'));
		if ($node == -1) { return ""; }
		
		return $this->_GetValue(array('TEL', $node));
	}
	
	function SetWorkPager($value)
	{
		$this->_SetValue(array('TEL', $this->_GetNode('TEL', array('WORK', 'PAGER'))), $value);
	}
	
	function WorkEmail()
	{
		// DRL NOTE: The "WORK" tag is NOT standard!
		$node = $this->_FindNode('EMAIL', 'WORK');
		if ($node == -1) { return ""; }
		
		return $this->_GetValue(array('EMAIL', $node));
	}
	
	function SetWorkEmail($value)
	{
		// DRL NOTE: The "WORK" tag is NOT standard!
		$this->_SetValue(array('EMAIL', $this->_GetNode('EMAIL', 'WORK')), $value);
	}
	
	function OtherEmail()
	{
		// DRL NOTE: The "OTHER" tag is NOT standard!
		$node = $this->_FindNode('EMAIL', 'OTHER');
		if ($node == -1) { return ""; }
		
		return $this->_GetValue(array('EMAIL', $node));
	}
	
	function SetOtherEmail($value)
	{
		// DRL NOTE: The "OTHER" tag is NOT standard!
		$this->_SetValue(array('EMAIL', $this->_GetNode('EMAIL', 'OTHER')), $value);
	}
	
	function WorkPostOfficeBox()
	{
		$node = $this->_FindNode('ADR', 'WORK');
		if ($node == -1) { return ""; }
		
		return $this->_GetValue(array('ADR', $node, 'po_box'));
	}
	
	function SetWorkPostOfficeBox($value)
	{
		$this->_SetValue(array('ADR', $this->_GetNode('ADR', 'WORK'), 'po_box'), $value);
	}
	
	function WorkExtendedAddress()
	{
		$node = $this->_FindNode('ADR', 'WORK');
		if ($node == -1) { return ""; }
		
		return $this->_GetValue(array('ADR', $node, 'extended'));
	}
	
	function SetWorkExtendedAddress($value)
	{
		$this->_SetValue(array('ADR', $this->_GetNode('ADR', 'WORK'), 'extended'), $value);
	}
	
	function WorkStreetAddress()
	{
		$node = $this->_FindNode('ADR', 'WORK');
		if ($node == -1) { return ""; }
		
		return $this->_GetValue(array('ADR', $node, 'street'));
	}
	
	function SetWorkStreetAddress($value)
	{
		$this->_SetValue(array('ADR', $this->_GetNode('ADR', 'WORK'), 'street'), $value);
	}
	
	function WorkCity()
	{
		$node = $this->_FindNode('ADR', 'WORK');
		if ($node == -1) { return ""; }
		
		return $this->_GetValue(array('ADR', $node, 'city'));
	}
	
	function SetWorkCity($value)
	{
		$this->_SetValue(array('ADR', $this->_GetNode('ADR', 'WORK'), 'city'), $value);
	}
	
	function WorkRegion()
	{
		$node = $this->_FindNode('ADR', 'WORK');
		if ($node == -1) { return ""; }
		
		return $this->_GetValue(array('ADR', $node, 'region'));
	}
	
	function SetWorkRegion($value)
	{
		$this->_SetValue(array('ADR', $this->_GetNode('ADR', 'WORK'), 'region'), $value);
	}
	
	function WorkCountry()
	{
		$node = $this->_FindNode('ADR', 'WORK');
		if ($node == -1) { return ""; }
		
		return $this->_GetValue(array('ADR', $node, 'country'));
	}
	
	function SetWorkCountry($value)
	{
		$this->_SetValue(array('ADR', $this->_GetNode('ADR', 'WORK'), 'country'), $value);
	}
	
	function WorkPostalCode()
	{
		$node = $this->_FindNode('ADR', 'WORK');
		if ($node == -1) { return ""; }
		
		return $this->_GetValue(array('ADR', $node, 'post_code'));
	}
	
	function SetWorkPostalCode($value)
	{
		$this->_SetValue(array('ADR', $this->_GetNode('ADR', 'WORK'), 'post_code'), $value);
	}
	
	function HomePhone()
	{
		$node = $this->_FindNode('TEL', 'HOME');
		if ($node == -1) { return ""; }
		
		return $this->_GetValue(array('TEL', $node));
	}
	
	function SetHomePhone($value)
	{
		$this->_SetValue(array('TEL', $this->_GetNode('TEL', 'HOME')), $value);
	}
	
	function HomeMobilePhone()
	{
		$node = $this->_FindNode('TEL', array('CELL','HOME'));
		if ($node == -1) { return ""; }
		
		return $this->_GetValue(array('TEL', $node));
	}
	
	function SetHomeMobilePhone($value)
	{
		$this->_SetValue(array('TEL', $this->_GetNode('TEL', array('CELL', 'HOME'))), $value);
	}
	
	function OtherPhone()
	{
		$node = $this->_FindNode('TEL', 'OTHER');
		if ($node == -1) { return ""; }
		
		return $this->_GetValue(array('TEL', $node));
	}
	
	function SetOtherPhone($value)
	{
		$this->_SetValue(array('TEL', $this->_GetNode('TEL', 'OTHER')), $value);
	}
	
	function HomeFax()
	{
		$node = $this->_FindNode('TEL', array('HOME', 'FAX'));
		if ($node == -1) { return ""; }
		
		return $this->_GetValue(array('TEL', $node));
	}
	
	function SetHomeFax($value)
	{
		$this->_SetValue(array('TEL', $this->_GetNode('TEL', array('HOME', 'FAX'))), $value);
	}
	
	function HomeEmail()
	{
		// DRL NOTE: The "HOME" tag is NOT standard!
		$node = $this->_FindNode('EMAIL', 'HOME');
		if ($node == -1) { return ""; }
		
		return $this->_GetValue(array('EMAIL', $node));
	}
	
	function SetHomeEmail($value)
	{
		// DRL NOTE: The "HOME" tag is NOT standard!
		$this->_SetValue(array('EMAIL', $this->_GetNode('EMAIL', 'HOME')), $value);
	}
	
	function HomePostOfficeBox()
	{
		$node = $this->_FindNode('ADR', 'HOME');
		if ($node == -1) { return ""; }
		
		return $this->_GetValue(array('ADR', $node, 'po_box'));
	}
	
	function SetHomePostOfficeBox($value)
	{
		$this->_SetValue(array('ADR', $this->_GetNode('ADR', 'HOME'), 'po_box'), $value);
	}
	
	function HomeExtendedAddress()
	{
		$node = $this->_FindNode('ADR', 'HOME');
		if ($node == -1) { return ""; }
		
		return $this->_GetValue(array('ADR', $node, 'extended'));
	}
	
	function SetHomeExtendedAddress($value)
	{
		$this->_SetValue(array('ADR', $this->_GetNode('ADR', 'HOME'), 'extended'), $value);
	}
	
	function HomeStreetAddress()
	{
		$node = $this->_FindNode('ADR', 'HOME');
		if ($node == -1) { return ""; }
		
		return $this->_GetValue(array('ADR', $node, 'street'));
	}
	
	function SetHomeStreetAddress($value)
	{
		$this->_SetValue(array('ADR', $this->_GetNode('ADR', 'HOME'), 'street'), $value);
	}
	
	function HomeCity()
	{
		$node = $this->_FindNode('ADR', 'HOME');
		if ($node == -1) { return ""; }
		
		return $this->_GetValue(array('ADR', $node, 'city'));
	}
	
	function SetHomeCity($value)
	{
		$this->_SetValue(array('ADR', $this->_GetNode('ADR', 'HOME'), 'city'), $value);
	}
	
	function HomeRegion()
	{
		$node = $this->_FindNode('ADR', 'HOME');
		if ($node == -1) { return ""; }
		
		return $this->_GetValue(array('ADR', $node, 'region'));
	}
	
	function SetHomeRegion($value)
	{
		$this->_SetValue(array('ADR', $this->_GetNode('ADR', 'HOME'), 'region'), $value);
	}
	
	function HomeCountry()
	{
		$node = $this->_FindNode('ADR', 'HOME');
		if ($node == -1) { return ""; }
		
		return $this->_GetValue(array('ADR', $node, 'country'));
	}
	
	function SetHomeCountry($value)
	{
		$this->_SetValue(array('ADR', $this->_GetNode('ADR', 'HOME'), 'country'), $value);
	}
	
	function HomePostalCode()
	{
		$node = $this->_FindNode('ADR', 'HOME');
		if ($node == -1) { return ""; }
		
		return $this->_GetValue(array('ADR', $node, 'post_code'));
	}
	
	function SetHomePostalCode($value)
	{
		$this->_SetValue(array('ADR', $this->_GetNode('ADR', 'HOME'), 'post_code'), $value);
	}
	
	function OtherPostOfficeBox()
	{
		$node = $this->_FindNode('ADR', 'OTHER');
		if ($node == -1) { return ""; }
		
		return $this->_GetValue(array('ADR', $node, 'po_box'));
	}
	
	function SetOtherPostOfficeBox($value)
	{
		$this->_SetValue(array('ADR', $this->_GetNode('ADR', 'OTHER'), 'po_box'), $value);
	}
	
	function OtherExtendedAddress()
	{
		$node = $this->_FindNode('ADR', 'OTHER');
		if ($node == -1) { return ""; }
		
		return $this->_GetValue(array('ADR', $node, 'extended'));
	}
	
	function SetOtherExtendedAddress($value)
	{
		$this->_SetValue(array('ADR', $this->_GetNode('ADR', 'OTHER'), 'extended'), $value);
	}
	
	function OtherStreetAddress()
	{
		$node = $this->_FindNode('ADR', 'OTHER');
		if ($node == -1) { return ""; }
		
		return $this->_GetValue(array('ADR', $node, 'street'));
	}
	
	function SetOtherStreetAddress($value)
	{
		$this->_SetValue(array('ADR', $this->_GetNode('ADR', 'OTHER'), 'street'), $value);
	}
	
	function OtherCity()
	{
		$node = $this->_FindNode('ADR', 'OTHER');
		if ($node == -1) { return ""; }
		
		return $this->_GetValue(array('ADR', $node, 'city'));
	}
	
	function SetOtherCity($value)
	{
		$this->_SetValue(array('ADR', $this->_GetNode('ADR', 'OTHER'), 'city'), $value);
	}
	
	function OtherRegion()
	{
		$node = $this->_FindNode('ADR', 'OTHER');
		if ($node == -1) { return ""; }
		
		return $this->_GetValue(array('ADR', $node, 'region'));
	}
	
	function SetOtherRegion($value)
	{
		$this->_SetValue(array('ADR', $this->_GetNode('ADR', 'OTHER'), 'region'), $value);
	}
	
	function OtherCountry()
	{
		$node = $this->_FindNode('ADR', 'OTHER');
		if ($node == -1) { return ""; }
		
		return $this->_GetValue(array('ADR', $node, 'country'));
	}
	
	function SetOtherCountry($value)
	{
		$this->_SetValue(array('ADR', $this->_GetNode('ADR', 'OTHER'), 'country'), $value);
	}
	
	function OtherPostalCode()
	{
		$node = $this->_FindNode('ADR', 'OTHER');
		if ($node == -1) { return ""; }
		
		return $this->_GetValue(array('ADR', $node, 'post_code'));
	}
	
	function SetOtherPostalCode($value)
	{
		$this->_SetValue(array('ADR', $this->_GetNode('ADR', 'OTHER'), 'post_code'), $value);
	}
	
	function HomeWebSite()
	{
		$node = $this->_FindNode('URL','HOME');
		if ($node == -1) { return ""; }
		
		return $this->_GetValue(array('URL', $node));
	}
	
	function SetHomeWebSite($value)
	{
		$this->_SetValue(array('URL', $this->_GetNode('URL','HOME')), $value);
	}
	
	function WorkWebSite()
	{
		$node = $this->_FindNode('URL','WORK');
		if ($node == -1) { return ""; }
		
		return $this->_GetValue(array('URL', $node));
	}
	
	function SetWorkWebSite($value)
	{
		$this->_SetValue(array('URL', $this->_GetNode('URL','WORK')), $value);
	}
	
	function Birthday()
	{
      return $this->_GetDate('BDAY');
	}
	
	function SetBirthday($value)
	{
		$str = "";
		if ($value != NULL && $value->HasDate())
		{
			// only serialize the date portion
			$str = $value->ToFormat('%-D');
		}
		$this->_SetValue(array('BDAY', $this->_GetNode('BDAY')), $str);
	}
	
	function Anniversary()
	{
      return $this->_GetDate('X-ANNIVERSARY');
	}
	
	function SetAnniversary($value)
	{
		$str = "";
		if ($value != NULL && $value->HasDate())
		{
			// only serialize the date portion
			$str = $value->ToFormat('%-D');
		}
		$this->_SetValue(array('X-ANNIVERSARY', $this->_GetNode('X-ANNIVERSARY')), $str);
	}
	
	function _GetDate($key)
	{
		$node = $this->_FindNode($key);
		if ($node == -1) { return NULL; }
		
      $str = $this->_GetValue(array($key, $node));
      $params = $this->_GetParams(array($key, $node));
      if (isset($params['X-APPLE-OMIT-YEAR']))
      {
         // the apple year may be out of range so we replace it with something good and NULL it below
         $str = Utilities::ReplaceInString($str, $params['X-APPLE-OMIT-YEAR'][0], '2000');
      }
		$date = DateAndTime::FromString($str);
      if ($date && isset($params['X-APPLE-OMIT-YEAR']))
      {
         $date->SetDate(NULL, $date->Month(), $date->Day());
      }
      return $date;
	}
	
	// gender is either 'M', 'F' or NULL
	function Gender()
	{
		$item = 'X-GENDER';
		$node = $this->_FindNode($item);
		if ($node == -1)
		{
			$item = 'X-WAB-GENDER';
			$node = $this->_FindNode($item);
		}
		if ($node == -1) { return NULL; }
		
		$str = $this->_GetValue(array($item, $node));
		if ($str != NULL)
		{
			$temp = strtolower(substr($str, 0, 1));
			if (strcmp($temp, "1") == 0 || strcmp($temp, "f") == 0 || strcmp($temp, "female") == 0)
			{
				return "F";
			}
			else if (strcmp($temp, "2") == 0 || strcmp($temp, "m") == 0 || strcmp($temp, "male") == 0)
			{
				return "M";
			}
		}
		
		return NULL;
	}
	
	// gender is either 'M', 'F' or NULL
	function SetGender($value)
	{
		$temp = NULL;
		
		$node = $this->_FindNode('X-WAB-GENDER');
		if ($node != -1)
		{
			if ($value != NULL)
			{
				if (strcmp($value, "F") == 0)
				{
					$temp = "1";
				}
				else if (strcmp($value, "M") == 0)
				{
					$temp = "2";
				}
			}
		}
		else
		{
			$node = $this->_GetNode('X-GENDER');
			
			if ($value != NULL)
			{
				if (strcmp($value, "F") == 0)
				{
					$temp = "Female";
				}
				else if (strcmp($value, "M") == 0)
				{
					$temp = "Male";
				}
			}
		}
		$this->_SetValue(array('X-GENDER', $node), $temp);
	}
	
	function Location()
	{
		$node = $this->_FindNode('GEO');
		if ($node == -1) { return NULL; }
		
		return array($this->_GetValue(array('GEO', $node, 'lat')), $this->_GetValue(array('GEO', $node, 'long')));
	}
	
   // can pass both params or a two dimension array as the first param
	function SetLocation($latitude, $longitude = NULL)
	{
      if (is_array($latitude))
      {
         $longitude = $latitude[1];
         $latitude = $latitude[0];
      }
      
      if ($latitude == NULL && $longitude == NULL)
      {
			$this->_RemoveNode(array('GEO'));
         return;
      }
         
		$node = $this->_GetNode('GEO');
		$this->_SetValue(array('GEO', $node, 'lat'), $latitude);
		$this->_SetValue(array('GEO', $node, 'long'), $longitude);
	}
	
	// value is an offset in seconds from GMT
	function TimeZone()
	{
		$node = $this->_FindNode('TZ');
		if ($node == -1) { return NULL; }
		
		$offset = NULL;
		$str = $this->_GetValue(array('TZ', $node));
		if ($str != NULL && strcmp($str, "") != 0)
		{
			$str = preg_split("/;\s*/", $str);			// remove any extra parts
			$str = preg_split("/:/", $str[0]);		// split hours and minutes
			$hours = $str[0]; $minutes = $str[1];
			$offset = ((intval($hours) * 60) + intval($minutes)) * 60;
		}
		return $offset;
	}
	
	// value is an offset in seconds from GMT
	function SetTimeZone($value)
	{
		$str = NULL;
		if ($value != NULL && strcmp($value, "") != 0)
		{
			// convert to format -05:00
			$value = intval($value / 60);
			$hours = intval($value / 60);
			$minutes = $value - ($hours * 60);
			if ($minutes < 0) { $minutes = -$minutes; }
			$str = sprintf("%d:%02d", $hours, $minutes);
		}
		$this->_SetValue(array('TZ', $this->_GetNode('TZ')), $str);
	}
	
	// value as a two letter code and subtag as in RFC 1766, comma separated
	function Languages()
	{
		$node = $this->_FindNode('X-LANGUAGES');
		if ($node == -1) { return ""; }
		
		return $this->_GetValue(array('X-LANGUAGES', $node));
	}
	
	// value as a two letter code and subtag as in RFC 1766, comma separated
	function SetLanguages($value)
	{
		$this->_SetValue(array('X-LANGUAGES', $this->_GetNode('X-LANGUAGES')), $value);
	}
	
	function Note()
	{
		$node = $this->_FindNode('NOTE');
		if ($node == -1) { return ""; }
		
		return $this->_GetValue(array('NOTE', $node));
	}
	
	function SetNote($value)
	{
		$this->_SetValue(array('NOTE', $this->_GetNode('NOTE')), $value);
	}
	
	function PhotoURL()
	{
		$node = $this->_FindNode('PHOTO', array('VALUE' => 'URL'));
		if ($node == -1) { return ""; }
		
		return $this->_GetValue(array('PHOTO', $node));
	}
	
	function SetPhotoURL($value)
	{
		$this->_SetValue(array('PHOTO', $this->_GetNode('PHOTO', array('VALUE' => 'URL'))), $value);
	}
	
	function Photo()
	{
		foreach (MimeTypes::GetImageExtensions() as $type)
		{
			$node = $this->_FindNode('PHOTO', $type);
			if ($node != -1)
			{
				$data = $this->_GetValue(array('PHOTO', $node));
				$types = $this->_GetTypes(array('PHOTO', $node), 'ENCODING');
				foreach ($types as $i)
				{
					$i = strtoupper($i);
					if (strcmp($i, 'BINARY') == 0)
					{
					}
					else if (strcmp($i, 'B') == 0 || strcmp($i, 'BASE64') == 0)
					{
						$data = Utilities::DecodeBase64($data);
					}
					else if (strcmp($i, "QUOTED-PRINTABLE") == 0)
					{
						$data = Utilities::DecodeQuotedPrintable($data);
					}
               else
               {
                  WriteInfo("Unsupported vCard PHOTO encoding: $i");
               }
				}
				
				return array(MimeTypes::GetMimeTypeFromExtension($type), $data);
			}
		}
		
		return NULL;
	}
	
   // can pass both params or a two dimension array as the first param
	function SetPhoto($mimeType, $value = NULL)
	{
      if (is_array($mimeType))
      {
         $value = $mimeType[1];
         $mimeType = $mimeType[0];
      }
      
		// remove existing photos so we don't end up with duplicate entries
		foreach (MimeTypes::GetImageExtensions() as $type)
		{
			$nodes = $this->_FindNodes('PHOTO', $type);
			foreach (array_reverse($nodes) as $node)	// remove nodes in reverse order to preserve indices
			{
				$this->_RemoveNode(array('PHOTO', $node));
			}
		}
		
		if ($value != NULL)
		{
			// DRL FIXIT! We're not correctly adding "ENCODING=base64;TYPE=jpeg" below! It's instead going to be "TYPE=base64,jpeg".
			$this->_SetValue(array('PHOTO', $this->_GetNode('PHOTO', array('TYPE' => MimeTypes::GetExtensionForMimeType($mimeType), 'ENCODING' => 'base64'))), Utilities::EncodeBase64($value));
		}
	}
	
	function LastUpdated()
	{
		$node = $this->_FindNode('REV');
		if ($node == -1) { return NULL; }
		
		$date = NULL;
		$str = $this->_GetValue(array('REV', $node));
		if ($str != NULL && strcmp($str, "") != 0)
		{
			// format can be 1995-10-31T22:27:10Z or 1997-11-15
			$date = DateAndTime::FromString($str);
		}
		return $date;
	}
	
	function SetLastUpdated($value)
	{
		$str = NULL;
		if ($value != NULL && $value->HasDate())
		{
         $str = $value->ToFormat(DateAndTime::$ISO8601BasicFormat);
		}
		$this->_SetValue(array('REV', $this->_GetNode('REV')), $str);
	}
	
	function Uid()
	{
		$node = $this->_FindNode('UID');
		if ($node == -1) { return ""; }
		
		return $this->_GetValue(array('UID', $node));
	}
	
	function SetUid($value)
	{
		$this->_SetValue(array('UID', $this->_GetNode('UID')), $value);
	}
	
   // these are a hash of name->value pairs used for import only and are not serialized!
	function Variables()
	{
      return $this->variables;
	}
	
   // these are a hash of name->value pairs used for import only and are not serialized!
   // NOTE: There is a difference between an empty array (no variables, cleared variables) and NULL (not specified)!
	function SetVariables($value)
	{
      $this->variables = $value;
	}
	
	function PhoneCarriers()
	{
		$nodes = $this->_FindNodes('X-TEL-CARRIER');
		
		$values = array();
		foreach ($nodes as $node)
		{
			$carrier = $this->_GetValue(array('X-TEL-CARRIER', $node));
			$phones = $this->_GetTypes(array('X-TEL-CARRIER', $node));
			foreach ($phones as $phone)
			{
				$values[$phone] = $carrier;
			}
		}
		
		return $values;
	}
	
	function PhoneCarrier($phone)
	{
		$node = $this->_FindNode('X-TEL-CARRIER', $phone);
		if ($node == -1) { return NULL; }
		
		return $this->_GetValue(array('X-TEL-CARRIER', $node));
	}
	
	function SetPhoneCarrier($phone, $carrier)
	{
		$phones = $this->PhoneCarriers();
		
		if ($carrier != NULL)
		{
			$phones[$phone] = $carrier;
		}
		else
		{
			unset($phones[$phone]);
		}
	
		$this->SetPhoneCarriers($phones);
	}
	
	function RemovePhoneCarrier($phone)
	{
		$phones = $this->PhoneCarriers();
		
		unset($phones[$phone]);
	
		$this->SetPhoneCarriers($phones);
	}
	
	function SetPhoneCarriers($values)
	{
		$nodes = $this->_FindNodes('X-TEL-CARRIER');
		foreach (array_reverse($nodes) as $node)	// remove nodes in reverse order to preserve indices
		{
			$this->_RemoveNode(array('X-TEL-CARRIER', $node));
		}
	
		// swap the hash to be carrier keyed
		$carriers = array();
		foreach (array_keys($values) as $phone)
		{
			$carrier = $values[$phone];
			$phones = array($phone);
			
			if (Utilities::ArrayContains($carriers, $carrier, 1))
			{
				Utilities::MergeIntoArray($carriers[$carrier], $phones);
			}
			else
			{
				$carriers[$carrier] = $phones;
			}
		}
		
		$keys = array_keys($carriers);
		sort($keys);	   // Store nodes in order - EL
		foreach ($keys as $carrier)
		{
			$phones = $carriers[$carrier];
			
			$this->_CreateNode('X-TEL-CARRIER', $phones, $carrier);
		}
	}
	
	function Addresses()
	{
		$nodes = $this->_FindNodes('ADR');
		
		$values = array();
		foreach ($nodes as $node)
		{
			$values[] = new vCardAddress($this, $node);
		}
		
		return $values;
	}
	
	function AddAddress($address, $typeOrTypes = NULL)
	{
      // perform check in case tags were already set instead of passed in
      if ($typeOrTypes)
         $address->SetTags(vCard::_NormalizeTypes($typeOrTypes));
		
		$addresses = $this->Addresses();
	
		// DRL FIXIT! We should check that we're not adding an existing address (for
		// example adding a 'WORK' address when we already have a 'WORK' address)!
	
		$addresses[] = $address;
		
		$this->SetAddresses($addresses);
	}
	
	function RemoveAddress($address)
	{
		$addresses = $this->Addresses();
		
		Utilities::RemoveFromArray($addresses, $address);
	
		$this->SetAddresses($addresses);
	}
	
	function SetAddresses($values)
	{
		$nodes = $this->_FindNodes('ADR');
		foreach (array_reverse($nodes) as $node)	// remove nodes in reverse order to preserve indices
		{
			$this->_RemoveNode(array('ADR', $node));
		}
	
		// DRL FIXIT! We should check that we're not adding an existing address (for
		// example adding a 'WORK' address when we already have a 'WORK' address)!
	
      $primaryFound = false;
		foreach ($values as $address)
		{
         $types = vCard::_NormalizeTypes($address->Tags());
         if (Utilities::ArrayContains($types, vCard::$Primary))
         {
            if ($primaryFound)
            {
       		   WriteError("More than one primary ADR field in vCard!");
               Utilities::RemoveFromArray($types, vCard::$Primary);
            }
            $primaryFound = true;
         }
			$address->ToNode($this, $this->_CreateNode('ADR', $types));
		}
	}
	
	function Phones()
	{
		return $this->_GetArray('TEL');
	}
	
	function AddPhone($phone, $typeOrTypes = NULL)
	{
	   $this->_AddToArray('TEL', $phone, $typeOrTypes);
	}
	
   function RemovePhone($phone)
	{
	   $this->_RemoveFromArray('TEL', $phone);
	}
	
	function SetPhones($values)
	{
      $this->_SetArray('TEL', $values);
	}
	
	// this should never return a FAX number
	function DefaultPhone()   // returns any item if none are primary
	{
	   return $this->_GetDefault('TEL');
	}
	
	// this should never return a FAX number
	function PrimaryPhone()
	{
	   return $this->_GetPrimary('TEL');
	}
	
	// this should never be passed a FAX number
	function SetPrimaryPhone($value)
	{
      $this->_SetPrimary('TEL', $value);
	}
	
	function WebSites()
	{
	   return $this->_GetArray('URL');
	}
	
	function AddWebSite($url, $typeOrTypes = NULL)
	{
	   $this->_AddToArray('URL', $url, $typeOrTypes);
	}
	
	function RemoveWebSite($url)
	{
	   $this->_RemoveFromArray('URL', $url);
	}
	
	function SetWebSites($values)
	{
      $this->_SetArray('URL', $values);
	}
	
	function DefaultWebSite()   // returns any item if none are primary
	{
	   return $this->_GetDefault('URL');
	}
	
	function PrimaryWebSite()
	{
	   return $this->_GetPrimary('URL');
	}
	
	function SetPrimaryWebSite($value)
	{
      $this->_SetPrimary('URL', $value);
	}
	
	function Emails()
	{
	   return $this->_GetArray('EMAIL');
	}
	
	function AddEmail($email, $typeOrTypes = NULL)
	{
	   $this->_AddToArray('EMAIL', $email, $typeOrTypes);
	}
	
	function RemoveEmail($email)
	{
	   $this->_RemoveFromArray('EMAIL', $email);
	}
	
	function SetEmails($values)
	{
      $this->_SetArray('EMAIL', $values);
	}
	
	function DefaultEmail()   // returns any item if none are primary
	{
	   return $this->_GetDefault('EMAIL');
	}
	
	function PrimaryEmail()
	{
	   return $this->_GetPrimary('EMAIL');
	}
	
	function SetPrimaryEmail($value)
	{
      $this->_SetPrimary('EMAIL', $value);
	}
	
	function IMs()
	{
	   return $this->_GetArray('IMPP');
	}
	
	function AddIM($im, $typeOrTypes)   // a type is required such as "Skype"
	{
	   $this->_AddToArray('IMPP', $im, $typeOrTypes);
	}
	
	function RemoveIM($im)
	{
	   $this->_RemoveFromArray('IMPP', $im);
	}
	
	function SetIMs($values)   // a type is required for each IM, such as "Skype"
	{
      $this->_SetArray('IMPP', $values);
	}
	
	function DefaultIM()   // returns any item if none are primary
	{
	   return $this->_GetDefault('IMPP');
	}
	
	function PrimaryIM()
	{
	   return $this->_GetPrimary('IMPP');
	}
	
	function SetPrimaryIM($value)
	{
      $this->_SetPrimary('IMPP', $value);
	}

	function Groups()
	{
		$nodes = $this->_FindNodes('X-ADDRESSBOOKSERVER-GROUP');
		
		$values = array();
		foreach ($nodes as $node)
		{
			$values[] = $this->_GetValue(array('X-ADDRESSBOOKSERVER-GROUP', $node));
		}
		
		return $values;
	}
	
	function AddGroup($uid)
	{
		if ($uid == NULL) { WriteError("Undefined group!"); return; }
	
		$ref_groups = $this->Groups();
		$groups = $ref_groups;
		
		if (!in_array($uid, $groups))
		{
			$groups[] = $uid;
   		$this->SetGroups($groups);
		}
	}
	
	function RemoveGroup($uid)
	{
		$ref_groups = $this->Groups();
		$groups = $ref_groups;
		
		Utilities::RemoveFromArray($groups, $uid);
	
		$this->SetGroups($groups);
	}
	
	function SetGroups($ref_values)
	{
		$newGroups = $ref_values;
		
		$nodes = $this->_FindNodes('X-ADDRESSBOOKSERVER-GROUP');
		foreach (array_reverse($nodes) as $node)	// remove nodes in reverse order to preserve indices
		{
			$this->_RemoveNode(array('X-ADDRESSBOOKSERVER-GROUP', $node));
		}
	
		sort($newGroups);	   // Store nodes in order - EL
		foreach ($newGroups as $uid)
		{
			$this->_CreateNode('X-ADDRESSBOOKSERVER-GROUP', array(), $uid);
		}
	}
   
   private static function _ComparePhoneEmailIM($a, $b)
   {
      $val = 0;
      
      if (Utilities::ArrayContains($a, vCard::$Primary, true))
         $val -= 10;
      if (Utilities::ArrayContains($b, vCard::$Primary, true))
         $val += 10;
      
      if (Utilities::ArrayContains($a, vCard::$Mobile, true))
         $val -= 5;
      if (Utilities::ArrayContains($b, vCard::$Mobile, true))
         $val += 5;
      
      if (Utilities::ArrayContains($a, vCard::$Home, true))
         $val -= 2;
      if (Utilities::ArrayContains($b, vCard::$Home, true))
         $val += 2;
      
      if (Utilities::ArrayContains($a, vCard::$Work, true))
         $val -= 1;
      if (Utilities::ArrayContains($b, vCard::$Work, true))
         $val += 1;
      
      return $val > 0 ? 1 : ($val < 0 ? -1 : 0);
   }
   
   static function SortPhonesEmailsIMsByPriority(&$phones)
   {
      uasort($phones, 'vCard::_ComparePhoneEmailIM');
   }

   public static function CopyDisplayNameFromFirstLast($dest, $source, $replace)
   {
      $destDisplayName = $dest->DisplayName();
      $destFirstName = $dest->FirstName();
      $destLastName = $dest->LastName();
      $destFirstLastName = $destFirstName . ' ' . $destLastName;
      $srcFirstName = $source->FirstName();
      $srcLastName = $source->LastName();
      $srcFirstLastName = $srcFirstName . ' ' . $srcLastName;
   
      if (($replace || $srcFirstName) && $destDisplayName == $destFirstName)
         $dest->SetDisplayName($srcFirstName);
      else if (($replace || $srcLastName) && $destDisplayName == $destLastName)
         $dest->SetDisplayName($srcLastName);
      else if (($replace || $srcFirstLastName) && $destDisplayName == $destFirstLastName)
         $dest->SetDisplayName($srcFirstLastName);
   }

   public static function CopyDisplayNameFromFirstLastCompany($dest, $source, $replace)
   {
      $destDisplayName = $dest->DisplayName();
      $destFirstName = $dest->FirstName();
      $destLastName = $dest->LastName();
      $destFirstLastName = $destFirstName . ' ' . $destLastName;
      $destCompanyName = $dest->CompanyName();
      $srcFirstName = $source->FirstName();
      $srcLastName = $source->LastName();
      $srcFirstLastName = $srcFirstName . ' ' . $srcLastName;
      $srcCompanyName = $source->CompanyName();
   
      if (($replace || $srcFirstName) && $destDisplayName == $destFirstName)
         $dest->SetDisplayName($srcFirstName);
      else if (($replace || $srcLastName) && $destDisplayName == $destLastName)
         $dest->SetDisplayName($srcLastName);
      else if (($replace || $srcFirstLastName) && $destDisplayName == $destFirstLastName)
         $dest->SetDisplayName($srcFirstLastName);
      else if (($replace || $srcCompanyName) && $destDisplayName == $destCompanyName)
         $dest->SetDisplayName($srcCompanyName);
   }
   
   // copy from another vCard, optionally only certain fields, and
   // optionally merge (combine collections such as phones from both, 
   // don't remove a value if it is not set in the source, etc.)
   // These are never copied:
   //   IsDeleted
	//   LastUpdated
	//   Uid
   function CopyFrom($source, $fields = NULL, $replace = true)
   {
      // NOTE: sync modules should not provide special handling for DisplayName since we handle it here
      if ($fields && !isset($fields['DisplayName']))
      {
      	// ILA SA-269 we use array_key_exists instead of isset because although the key exists the null value is handled as !isset
         if (array_key_exists('FirstName', $fields) && array_key_exists('LastName', $fields))
         {
            if (array_key_exists('CompanyName', $fields))
               $fields = array_merge($fields, array('DisplayName' => 'vCard::CopyDisplayNameFromFirstLastCompany'));
            else
               $fields = array_merge($fields, array('DisplayName' => 'vCard::CopyDisplayNameFromFirstLast'));
         }
      }
      
      $fields = Utilities::ExecuteCallbacksAndGetRemainingFields($this, $source, $fields, $replace);
      if ((!$fields || Utilities::ArrayContains($fields, 'DisplayName')) && ($replace || $source->DisplayName())) $this->SetDisplayName($source->DisplayName());
	   if ((!$fields || Utilities::ArrayContains($fields, 'LastName')) && ($replace || $source->LastName())) $this->SetLastName($source->LastName());
	   if ((!$fields || Utilities::ArrayContains($fields, 'MiddleName')) && ($replace || $source->MiddleName())) $this->SetMiddleName($source->MiddleName());
  	   if ((!$fields || Utilities::ArrayContains($fields, 'FirstName')) && ($replace || $source->FirstName())) $this->SetFirstName($source->FirstName());
	   if ((!$fields || Utilities::ArrayContains($fields, 'NamePrefix')) && ($replace || $source->NamePrefix())) $this->SetNamePrefix($source->NamePrefix());
	   if ((!$fields || Utilities::ArrayContains($fields, 'NameSuffix')) && ($replace || $source->NameSuffix())) $this->SetNameSuffix($source->NameSuffix());
	   if ((!$fields || Utilities::ArrayContains($fields, 'Nickname')) && ($replace || $source->Nickname())) $this->SetNickname($source->Nickname());
	   if ((!$fields || Utilities::ArrayContains($fields, 'CompanyName')) && ($replace || $source->CompanyName())) $this->SetCompanyName($source->CompanyName());
	   if ((!$fields || Utilities::ArrayContains($fields, 'Department')) && ($replace || $source->Department())) $this->SetDepartment($source->Department());
	   if ((!$fields || Utilities::ArrayContains($fields, 'JobTitle')) && ($replace || $source->JobTitle())) $this->SetJobTitle($source->JobTitle());
	   if ((!$fields || Utilities::ArrayContains($fields, 'Birthday')) && ($replace || $source->Birthday())) $this->SetBirthday($source->Birthday());
	   if ((!$fields || Utilities::ArrayContains($fields, 'Anniversary')) && ($replace || $source->Anniversary())) $this->SetAnniversary($source->Anniversary());
	   if ((!$fields || Utilities::ArrayContains($fields, 'Gender')) && ($replace || $source->Gender())) $this->SetGender($source->Gender());
	   if ((!$fields || Utilities::ArrayContains($fields, 'Location')) && ($replace || $source->Location())) $this->SetLocation($source->Location());
	   if ((!$fields || Utilities::ArrayContains($fields, 'TimeZone')) && ($replace || $source->TimeZone())) $this->SetTimeZone($source->TimeZone());
	   if ((!$fields || Utilities::ArrayContains($fields, 'PhotoURL')) && ($replace || $source->PhotoURL())) $this->SetPhotoURL($source->PhotoURL());
	   if ((!$fields || Utilities::ArrayContains($fields, 'Photo')) && ($replace || $source->Photo())) $this->SetPhoto($source->Photo());
      // Languages is a comma seperated list (a string) and NOT an array
	   if ((!$fields || Utilities::ArrayContains($fields, 'Languages')) && ($replace || $source->Languages())) $this->SetLanguages($source->Languages());

      // these combine when not replacing, unless they are the same when ignoring whitespace
      $n1 = Utilities::NormalizeSpaces($this->Note());
      $n2 = Utilities::NormalizeSpaces($source->Note());
	   if ((!$fields || Utilities::ArrayContains($fields, 'Note')) && !Utilities::ValuesEqualOrBothUndef($n1, $n2))
      { 
         if ($replace || (empty($n1) || strpos($n2, $n1) !== false))
         {
            // replace, or the source note is a superset
            $this->SetNote($source->Note());
         }
         else if (empty($n2) || strpos($n1, $n2) !== false)
         {
            // the current note is already a superset
         }
         else
         {
            // combine the two notes
            $this->SetNote(Utilities::CombineStrings("\n\n", $this->Note(), $source->Note()));
         }
      }
      
      // these are a regular array
	   if (!$fields || Utilities::ArrayContains($fields, 'Addresses')) { if ($replace) { $this->SetAddresses($source->Addresses()); } else { $this->SetAddresses(vCard::RemoveDuplicateAddresses(Utilities::MergeArrays($this->Addresses(), $source->Addresses()))); } }
	   if (!$fields || Utilities::ArrayContains($fields, 'Groups')) { if ($replace) { $this->SetGroups($source->Groups()); } else { $this->SetGroups(Utilities::MergeArrays($this->Groups(), $source->Groups())); } }
      
      // these are maps and require slightly different handling
	   if (!$fields || Utilities::ArrayContains($fields, 'PhoneCarriers')) { if ($replace) { $this->SetPhoneCarriers($source->PhoneCarriers()); } else { $this->SetPhoneCarriers(Utilities::MergeArrayKeys($this->PhoneCarriers(), $source->PhoneCarriers())); } }
	   if (!$fields || Utilities::ArrayContains($fields, 'Phones')) { if ($replace) { $this->SetPhones($source->Phones()); } else { $this->SetPhones(Utilities::MergeArrayKeys($this->Phones(), $source->Phones())); } }
	   if (!$fields || Utilities::ArrayContains($fields, 'WebSites')) { if ($replace) { $this->SetWebSites($source->WebSites()); } else { $this->SetWebSites(Utilities::MergeArrayKeys($this->WebSites(), $source->WebSites())); } }
	   if (!$fields || Utilities::ArrayContains($fields, 'Emails')) { if ($replace) { $this->SetEmails($source->Emails()); } else { $this->SetEmails(Utilities::MergeArrayKeys($this->Emails(), $source->Emails())); } }
	   if (!$fields || Utilities::ArrayContains($fields, 'IMs')) { if ($replace) { $this->SetIMs($source->IMs()); } else { $this->SetIMs(Utilities::MergeArrayKeys($this->IMs(), $source->IMs())); } }
      
      // lets always take the most restrictive read-only state
      if ($source->IsReadOnly())
         $this->SetIsReadOnly(true);
   }

   // returns whether the passed item is fairly certain to be the 
   // same as this one and therefore they should be combined
   function IsSimilarTo($item)
   {
      $same = 0;
      $diff = 0;
      if (!empty($this->LastName())) { if ($this->LastName() == $item->LastName()) $same++; else if (!empty($item->LastName())) $diff++; }
      if (!empty($this->MiddleName())) { if ($this->MiddleName() == $item->MiddleName()) $same++; else if (!empty($item->MiddleName())) $diff++; }
      if (!empty($this->FirstName())) { if ($this->FirstName() == $item->FirstName()) $same++; else if (!empty($item->FirstName())) $diff++; }
      if (!empty($this->NamePrefix())) { if ($this->NamePrefix() == $item->NamePrefix()) $same++; else if (!empty($item->NamePrefix())) $diff++; }
      if (!empty($this->NameSuffix())) { if ($this->NameSuffix() == $item->NameSuffix()) $same++; else if (!empty($item->NameSuffix())) $diff++; }
      if (!empty($this->DisplayName())) { if ($this->DisplayName() == $item->DisplayName()) $same++; else if (!empty($item->DisplayName())) $diff++; }
      if (!empty($this->Nickname())) { if ($this->Nickname() == $item->Nickname()) $same++; else if (!empty($item->Nickname())) $diff++; }
      if (!empty($this->CompanyName())) { if ($this->CompanyName() == $item->CompanyName()) $same++; else if (!empty($item->CompanyName())) $diff++; }
      if (!empty($this->Department())) { if ($this->Department() == $item->Department()) $same++; else if (!empty($item->Department())) $diff++; }
      if (!empty($this->JobTitle())) { if ($this->JobTitle() == $item->JobTitle()) $same++; else if (!empty($item->JobTitle())) $diff++; }
      if (!empty($this->Birthday())) { if (DateAndTime::Equal($this->Birthday(), $item->Birthday())) $same++; else if (!empty($item->Birthday())) $diff++; }
      if (!empty($this->Anniversary())) { if (DateAndTime::Equal($this->Anniversary(), $item->Anniversary())) $same++; else if (!empty($item->Anniversary())) $diff++; }
      if (!empty($this->Gender())) { if ($this->Gender() == $item->Gender()) $same++; else if (!empty($item->Gender())) $diff++; }
      if (!empty($this->Location())) { if ($this->Location() == $item->Location()) $same++; else if (!empty($item->Location())) $diff++; }
      if (!empty($this->TimeZone())) { if ($this->TimeZone() == $item->TimeZone()) $same++; else if (!empty($item->TimeZone())) $diff++; }
      if (!empty($this->PhotoURL())) { if ($this->PhotoURL() == $item->PhotoURL()) $same++; else if (!empty($item->PhotoURL())) $diff++; }
      if (!empty($this->Photo())) { if ($this->Photo() == $item->Photo()) $same++; else if (!empty($item->Photo())) $diff++; }
      
      // the arrays don't have to match as they'll be merged
      
      return $diff == 0 && $same > 0;
   }

   function CalcFirstName()
   {
      $value = $this->FirstName();
      if (!empty($value)) return $value;

      $value = $this->Nickname();
      if (!empty($value)) return $value;

      $value = $this->DisplayName();
      if (empty($value)) return NULL;
      if ($value == $this->CompanyName()) return NULL;

      $values = explode(' ', $value);
      if (count($values) < 1) return NULL;
      
      return $values[0];
   }
   
   function CalcLastName()
   {
      $value = $this->LastName();
      if (!empty($value)) return $value;

      $value = $this->DisplayName();
      if (empty($value)) return NULL;
      if ($value == $this->CompanyName()) return NULL;

      $values = explode(' ', $value);
      if (count($values) < 2) return NULL;
      
      return $values[count($values)-1];
   }
   
   function CalcDisplayName()
   {
      $value = trim($this->DisplayName());
      if (!empty($value)) return $value;
      
      $value = Utilities::CombineStrings(' ', $this->CalcFirstName(), $this->CalcLastName());
      if (!empty($value)) return $value;
   
      $value = $this->Nickname();
      if (!empty($value)) return $value;
   
      $value = Utilities::CombineStrings(' ', $this->CompanyName(), $this->JobTitle());
      if (!empty($value)) return $value;
   
      $value = $this->DefaultEmail();
      if (!empty($value)) return $value;
   
      $value = $this->DefaultPhone();
      if (!empty($value)) return $value;
   
      return NULL;
   }

	// returns an array of supported string values, index is the name and value is the type
   static function GetSupportedStrings($prefix = '')
   {
   	return array(
			$prefix . 'Nickname' => 'text',
			$prefix . 'First_Name' => 'text',
      	$prefix . 'Last_Name' => 'text',
      	$prefix . 'Display_Name' => 'text',
      	$prefix . 'Default_Email' => 'email',
      	$prefix . 'Default_Phone' => 'tel',
      	$prefix . 'Home_Mobile_Phone' => 'tel',
      	$prefix . 'Gender' => 'text'
		);
   }

   // returns name=>value pairs for the common vCard properties, optionally with a prefix
   function GetAsStrings($prefix = '', $includeVariables = true)
   {
      $result = array();

//      if ($this->Uid())
//         $result[$prefix . 'ContactID'] = $this->Uid();
      
      Utilities::AddToArray($result, 
         current(array_filter(array($this->CalcDisplayName(), $this->FirstName(), $this->Nickname()))),
         $prefix . 'Formal_Name');
      Utilities::AddToArray($result, 
         current(array_filter(array($this->Nickname(), $this->CalcFirstName(), $this->CalcDisplayName()))),
         $prefix . 'Informal_Name');
      Utilities::AddToArray($result, $this->Nickname(), $prefix . 'Nickname');
      Utilities::AddToArray($result, $this->CalcFirstName(), $prefix . 'First_Name');
      Utilities::AddToArray($result, $this->CalcLastName(), $prefix . 'Last_Name');
      Utilities::AddToArray($result, $this->CalcDisplayName(), $prefix . 'Display_Name');
      Utilities::AddToArray($result, $this->DefaultEmail(), $prefix . 'Default_Email');
      Utilities::AddToArray($result, $this->DefaultPhone(), $prefix . 'Default_Phone');
      Utilities::AddToArray($result, $this->HomeMobilePhone(), $prefix . 'Home_Mobile_Phone');
      Utilities::AddToArray($result, $this->Gender(), $prefix . 'Gender');
      
      if ($includeVariables && $this->variables != NULL)
      {
         foreach ($this->variables as $name => $value)
         {
            $result[$prefix . $name] = $value;
         }
      }

      return $result;
   }
   
   // copies recognized values from an array of name=>value pairs, and if prefix is 
   // provided the names must be prefixed by that in order to be recognized
   // returns array of keys copied
   function CopyFromStrings($source, $prefix = '', $intoVariables = true)
   {
      $result = array();
      $vars = array();
      foreach ($source as $_name => $value)
      {
         if (!empty($prefix) && strpos($_name, $prefix) === false)
            continue;
            
         $name = substr($_name, strlen($prefix));
            
         if ($name == 'Nickname')
         {
            $this->SetNickname($value);
            $result[] = $_name;
         }
         else if ($name == 'First_Name')
         {
            $this->SetFirstName($value);
            $result[] = $_name;
         }
         else if ($name == 'Last_Name')
         {
            $this->SetLastName($value);
            $result[] = $_name;
         }
         else if ($name == 'Display_Name')
         {
            $this->SetDisplayName($value);
            $result[] = $_name;
         }
         else if ($name == 'Primary_Email')
         {
            $this->SetPrimaryEmail($value);
            $result[] = $_name;
         }
         else if ($name == 'Primary_Phone')
         {
            $this->SetPrimaryPhone($value);
            $result[] = $_name;
         }
         else if ($name == 'Home_Mobile_Phone')
         {
            $this->SetHomeMobilePhone($value);
            $result[] = $_name;
         }
         else if ($name == 'Gender')
         {
            $this->SetGender(strtoupper(substr($value, 0, 1)));
            $result[] = $_name;
         }
         else if (
            // DefaultXxx are not saved, use PrimaryXxx instead
            $name == 'Default_Email' || $name == 'Default_Phone' ||
            // these calculated values are not saved
            $name == 'Formal_Name' || $name == 'Informal_Name')
         {
            // nothing to do
         }
         else if ($intoVariables)
         {
            $vars[$name] = $value;
            $result[] = $_name;
         }
      }
      if ($intoVariables)
         $this->SetVariables($vars);
      return $result;
   }
   
   static function RemoveDuplicateAddresses($addresses)
   {
      for ($i = 0; $i < count($addresses); $i++)
      {
         for ($j = 0; $j < $i; $j++)
         {
            $addr1 = &$addresses[$i];
            $addr2 = $addresses[$j];
            
            if ($addr1->IsSimilarTo($addr2))
            {
               $addr1->CopyFrom($addr2, NULL, false);   // merge changes
               Utilities::RemoveFromArray($addresses, $addr2);
               $i--;
               $j--;
            }
         }
      }
      
      return $addresses;
   }
   
	// gets a node for setting, creates it if not found
	private function _GetNode($name, $typeOrTypes = NULL)
	{
		if ($typeOrTypes != NULL && !is_array($typeOrTypes))
		{
			$typeOrTypes = array($typeOrTypes);
		}
		
		$node = $this->_FindNode($name, $typeOrTypes);
	
		// if no match found add a new node
		if ($node == -1)
			$node = $this->_CreateNode($name, $typeOrTypes);
	
		return $node;
	}
	
	// looks for a matching node, returns index or -1 if not found
	private function _FindNode($name, $typeOrTypes = NULL)
	{
		$nodes = array();
		if ($typeOrTypes != NULL)
		{
 			$nodes = $this->_FindNodes($name, $typeOrTypes);
	
			if (count($nodes) == 0)
			{
				if (vCardCalBase::_HasType($typeOrTypes, 'HOME'))
				{
					// if not found, look for one that doesn't have the "work" or "other" type
					
					$nodes = array();
					$temp_types = vCard::_RemoveType($typeOrTypes, 'HOME');
					$temp = $this->_FindNodes($name, $temp_types);
					foreach ($temp as $node)
					{
						if (!$this->_NodeHasType(array($name, $node), 'WORK') && 
							!$this->_NodeHasType(array($name, $node), 'OTHER'))
						{
							$nodes[] = $node;
						}
					}
				}
			}
			
			// if we're not looking for a CELL number don't return one unless what we seek is a default phone in which case it could be a CELL
			if (!vCardCalBase::_HasType($typeOrTypes, 'CELL') && !vCardCalBase::_HasType($typeOrTypes, vCard::$Primary))
			{
				$temp = $nodes;
				$nodes = array();
				foreach ($temp as $node)
				{
					if (!$this->_NodeHasType(array($name, $node), 'CELL'))
					{
						$nodes[] = $node;
					}
				}
			}
			// if we're not looking for a FAX number don't return them
			if (!vCardCalBase::_HasType($typeOrTypes, 'FAX'))
			{
				$temp = $nodes;
				$nodes = array();
				foreach ($temp as $node)
				{
					if (!$this->_NodeHasType(array($name, $node), 'FAX'))
					{
						$nodes[] = $node;
					}
				}
			}
			
			//========================================================================================
			// DRL FIXIT? I'm not sure about the remainder of these things added by Marcin for Google?
			
			// if we're not looking for a HOMEPAGE don't return them
			if (!vCardCalBase::_HasType($typeOrTypes, 'HOMEPAGE'))
			{
				$temp = $nodes;
				$nodes = array();
				foreach ($temp as $node)
				{
					if (!$this->_NodeHasType(array($name, $node), 'HOMEPAGE'))
					{
						$nodes[] = $node;
					}
				}
			}
	
			// if we're not looking for a BLOG page don't return them
			if (!vCardCalBase::_HasType($typeOrTypes, 'BLOG'))
			{
				$temp = $nodes;
				$nodes = array();
				foreach ($temp as $node)
				{
					if (!$this->_NodeHasType(array($name, $node), 'BLOG'))
					{
						$nodes[] = $node;
					}
				}
			}
	
			// if we're not looking for a FTP page don't return them
			if (!vCardCalBase::_HasType($typeOrTypes, 'FTP'))
			{
				$temp = $nodes;
				$nodes = array();
				foreach ($temp as $node)
				{
					if (!$this->_NodeHasType(array($name, $node), 'FTP'))
					{
						$nodes[] = $node;
					}
				}
			}
	
			// if we're not looking for a PROFILE page don't return them
			if (!vCardCalBase::_HasType($typeOrTypes, 'PROFILE'))
			{
				$temp = $nodes;
				$nodes = array();
				foreach ($temp as $node)
				{
					if (!$this->_NodeHasType(array($name, $node), 'PROFILE'))
					{
						$nodes[] = $node;
					}
				}
			}
	
			// if we're not looking for a OTHER page don't return them
			if (!vCardCalBase::_HasType($typeOrTypes, 'OTHER'))
			{
				$temp = $nodes;
				$nodes = array();
				foreach ($temp as $node)
				{
					if (!$this->_NodeHasType(array($name, $node), 'OTHER'))
					{
						$nodes[] = $node;
					}
				}
			}		
			
		}
		else
		{
			$nodes = $this->_FindNodes($name);
		}
	
		// if there is more than one choice, return the preferred one
		foreach ($nodes as $node)
		{
			if ($this->_NodeHasType(array($name, $node), vCard::$Primary))
			{
				$nodes[0] = $node;
            break;
			}
		}

		if (count($nodes) == 0)
			return -1;
			
		return $nodes[0];
	}
   
	function _GetArray($name)
	{
		$nodes = $this->_FindNodes($name);
		
		$values = array();
		foreach ($nodes as $node)
		{
			$types = $this->_GetTypes(array($name, $node));
			$values[$this->_GetValue(array($name, $node))] = $types;
		}
		
		return $values;
	}
	
	function _SetArray($name, $values)
	{
		$nodes = $this->_FindNodes($name);
		foreach (array_reverse($nodes) as $node)	// remove nodes in reverse order to preserve indices
		{
			$this->_RemoveNode(array($name, $node));
		}
	
      $primaryFound = false;
		$keys = array_keys($values);
		sort($keys);	   // Store nodes in order - EL
		foreach ($keys as $key)
		{
         if (!$key)
         {
    		   WriteError("Invalid value of '$key' used in $name field in vCard, ignoring!");
            continue;
         }
         
			$types = vCard::_NormalizeTypes($values[$key]);
         
         // If we find multiple "locations" (i.e. home and work) or multiple 
         // "uses" (i.e. mobile and landline) just pick one arbitrarily to 
         // avoid weird issues elsewhere
         
         $loc = '';
         $use = '';
         $result = array();
         
         foreach ($types as $type)
         {
            if ($type == vCard::$Primary)
            {
               if ($primaryFound)
               {
          		   WriteError("More than one primary $name field in vCard!");
               }
               else
               {
                  $primaryFound = true;
                  $result[] = vCard::$Primary;
               }
            }
            else if (Utilities::ArrayContains(array_keys(vCard::$AttrLocations), $type))
            {
               if (!empty($loc))
               {
                  // either is "other" we'll drop that one
                  if ($loc == 'other')
                  {
                     // use $type;
                  }
                  else if ($type == 'other')
                  {
                     $type = $loc;
                  }
                  else
                  {
                     WriteInfo('Duplicate usage attribute in list: ' . implode(',', $types) . ' using: ' . $type);
                  }
               }
               $loc = $type;
            }
            else
            {
               if (!empty($use))
               {
                  // if the other is a "good" usage we'll keep it
                  if (Utilities::ArrayContains(array_keys(vCard::$WebSiteTypes), $use) || 
                     Utilities::ArrayContains(array_keys(vCard::$AttrPhoneTypes), $use) || 
                     Utilities::ArrayContains(array_keys(vCard::$IMTypes), $use) ||
                  // or if this one is an "obsolete" usage we'll keep the other one
                     Utilities::ArrayContains(array_keys(vCard::$ObsoleteTypes), $type))
                  {
                     WriteInfo('Duplicate usage attribute in list: ' . implode(',', $types) . ' using: ' . $use);
                     
                     $type = $use;
                  }
                  else
                  {
                     WriteInfo('Duplicate usage attribute in list: ' . implode(',', $types) . ' using: ' . $type);
                  }
               }
               $use = $type;
            }
         }
      
         if ($loc)
            $result[] = $loc;
         if ($use)
            $result[] = $use;
			
   		sort($result);	   // Store types in order
			$this->_CreateNode($name, $result, $key);
		}
	}
	
	function _AddToArray($name, $value, $typeOrTypes)
	{
		if ($value == NULL)
      {
         WriteError("Undefined $name value, ignoring!");
         return;
      }
	
		$values = $this->_GetArray($name);
      
      $typeOrTypes = vCard::_NormalizeTypes($typeOrTypes);
      
		if (in_array($value, array_keys($values)))
		{
			Utilities::MergeIntoArray($values[$value], $typeOrTypes);
		}
		else
		{
			$values[$value] = $typeOrTypes;
		}
      
      $this->_SetArray($name, $values);
	}
	
	function _RemoveFromArray($name, $value)
	{
		$values = $this->_GetArray($name);
		
		unset($values[$value]);
	
      $this->_SetArray($name, $values);
	}
   
   static function _NormalizeTypes($typeOrTypes)
   {
      if ($typeOrTypes == NULL)
         return array();
         
		if (!is_array($typeOrTypes))
			$typeOrTypes = array($typeOrTypes);
         
      foreach ($typeOrTypes as &$type)
      {
         $type = strtolower($type);
         
         if (isset(vCard::$TypeConversions[$type]))
            $type = vCard::$TypeConversions[$type];
      }
      
   	$typeOrTypes = array_unique($typeOrTypes);	   // Store types in order, remove duplicates
      
      return $typeOrTypes;
   }
	
   // check if there are multiple "primary" items, or other inconsistencies
	function _CheckArray($name)
	{
      $broken = false;
      $primaryFound = false;

		$nodes = $this->_FindNodes($name);
		foreach ($nodes as $node)
		{
         $key = $this->_GetValue(array($name, $node));
			$types = $this->_GetTypes(array($name, $node));
         
         $temp = vCard::_NormalizeTypes($types);
         
         if (!$key || !Utilities::ArrayEquals($types, $temp))   // DRL FIXIT? Should we ignore case and order, or is it the intent that those be updated?
         {
            $broken = true;
            break;
         }
         
         $loc = '';
         $use = '';
         
         foreach ($types as $type)
         {
            if ($type == vCard::$Primary)
            {
               if ($primaryFound)
               {
                  $broken = true;
                  break;
               }
               else
               {
                  $primaryFound = true;
               }
            }
            else if (Utilities::ArrayContains(array_keys(vCard::$AttrLocations), $type))
            {
               if (!empty($loc))
               {
                  $broken = true;
                  break;
               }
               $loc = $type;
            }
            else
            {
               if (!empty($use))
               {
                  $broken = true;
                  break;
               }
               $use = $type;
            }
         }
		}
      
      if ($broken)
      {
         $values = $this->_GetArray($name);
         $this->_SetArray($name, $values);
      }
	}
   
   // as above but for addresses
	function _CheckAddressArray($name)
	{
      $broken = false;
      $primaryFound = false;

		$nodes = $this->Addresses();
		foreach ($nodes as $node)
		{
			$types = $node->Tags();
         
         $temp = vCard::_NormalizeTypes($types);
         
         if (!Utilities::ArrayEquals($types, $temp))   // DRL FIXIT? Should we ignore case and order, or is it the intent that those be updated?
         {
            $broken = true;
            break;
         }
         
         $loc = '';
         $use = '';
         
         foreach ($types as $type)
         {
            if ($type == vCard::$Primary)
            {
               if ($primaryFound)
               {
                  $broken = true;
                  break;
               }
               else
               {
                  $primaryFound = true;
               }
            }
            else if (Utilities::ArrayContains(array_keys(vCard::$AttrLocations), $type))
            {
               if (!empty($loc))
               {
                  $broken = true;
                  break;
               }
               $loc = $type;
            }
            else
            {
               if (!empty($use))
               {
                  $broken = true;
                  break;
               }
               $use = $type;
            }
         }
		}
      
      if ($broken)
      {
         $this->SetAddresses($nodes);
      }
	}
	
	function _GetDefault($name)
	{
      $types = array(vCard::$Primary, 'home', 'cell');
      
      foreach ($types as $type)
      {
   		$node = $this->_FindNode($name, vCard::$Primary);
   		if ($node != -1)
      		return $this->_GetValue(array($name, $node));
      }
      
   	$nodes = $this->_FindNodes($name);
      if (count($nodes) == 0)
         return "";
         
      $node = $nodes[0];
		return $this->_GetValue(array($name, $node));
	}
	
	function _GetPrimary($name)
	{
		$node = $this->_FindNode($name, vCard::$Primary);
		if ($node == -1)
         return NULL;
		
		return $this->_GetValue(array($name, $node));
	}
	
	function _SetPrimary($name, $value)
	{
      $primary = $this->_GetPrimary($name, $value);
      if ($primary == $value)
         return;
         
		$values = $this->_GetArray($name);
      
      // remove the old primary
      if ($primary)
      {
			Utilities::RemoveFromArray($values[$primary], vCard::$Primary);
      }
      
      // set the new primary
		if (in_array($value, array_keys($values)))
		{
			Utilities::AddToArray($values[$value], vCard::$Primary);
		}
		else if (!empty($value))
		{
			$values[$value] = vCard::$Primary;
		}
	
      $this->_SetArray($name, $values);
	}
}

//spl_autoload_register(array('vCard', 'autoload'));

/*
===================================================================

	This is code I use for testing this module. Use as an example.

===================================================================
*/

if (0)
{
   $temp1 = "BEGIN:VCARD\nVERSION:3.0\nN:Shaw;Dominique;;;\nEND:VCARD";
   $card = vCard::FromString($temp1);
   $card->SetLastName('Shaw');
   $temp2 = $card->ToString();
   if ($temp1 != $temp2)
   {
      WriteDie("Error with extra semicolons");
   }
   $card->SetFirstName('Dominique');
   $temp2 = $card->ToString();
   if ($temp1 != $temp2)
   {
      WriteDie("Error with extra semicolons");
   }
   $card->SetMiddleName('');
   $temp2 = $card->ToString();
   if ($temp1 != $temp2)
   {
      WriteDie("Error with extra semicolons");
   }
   $card->SetMiddleName(NULL);
   $temp2 = $card->ToString();
   if ($temp1 != $temp2)
   {
      WriteDie("Error with extra semicolons");
   }
   $card->SetNamePrefix('');
   $temp2 = $card->ToString();
   if ($temp1 != $temp2)
   {
      WriteDie("Error with extra semicolons");
   }
   $card->SetNamePrefix(NULL);
   $temp2 = $card->ToString();
   if ($temp1 != $temp2)
   {
      WriteDie("Error with extra semicolons");
   }
   $card->SetNameSuffix('');
   $temp2 = $card->ToString();
   if ($temp1 != $temp2)
   {
      WriteDie("Error with extra semicolons");
   }
   $card->SetNameSuffix(NULL);
   $temp2 = $card->ToString();
   if ($temp1 != $temp2)
   {
      WriteDie("Error with extra semicolons");
   }
   $card->SetFirstName('');
   $card->SetLastName('');
   $temp2 = $card->ToString();
   if ($temp2 != "BEGIN:VCARD\nVERSION:3.0\nEND:VCARD")
   {
      WriteDie("Error with extra semicolons");
   }
   
	$card = new vCard();
	$card->SetFirstName(NULL);
	$emails =
	array(
		NULL => array('pref', "HOME"),
		"" => array("WORK")
	);
	$card->SetEmails($emails);
	$card->AddEmail(NULL, array("work"));
   $temp = $card->ToString();
	if ($temp != "BEGIN:VCARD\nVERSION:3.0\nEND:VCARD")
	{
		WriteDie("Error with empty vCard");
	}

	$addresses = array();
	$address = new vCardAddress($card);
	$address->SetStreetAddress("1333 Balmoral Road\n1335 Balmoral Road");
	$address->SetCity("Vancouver");
	$address->SetTags(array("HOME"));
	$addresses[0] = $address;
	$card->SetAddresses($addresses);
	$address = new vCardAddress($card);
	$address->SetStreetAddress("1711 Carnegie Crescent");
	$address->SetCity("Victoria");
	$address->SetTags(array("HOME"));
	$addresses[0] = $address;
	$card->SetAddresses($addresses);
	if ($card->HomeCity() != "Victoria")
	{
		WriteDie("Error with SetAddresses()");
	}
   
	// added this test because it was previously failing on an empty vCard
	$card = new vCard();
	$emails =
	array(
		"jqpublic\@xyz.dom1.com" => array('pref', "HOME")
	);
	$card->SetPrimaryEmail("Frank\@Test.com");
	$card->SetEmails($emails);
	$card->AddEmail("Frank_Dawson\@Lotus.com", array("work"));
	if ($card->HomeEmail() != "jqpublic\@xyz.dom1.com" ||
		$card->WorkEmail() != "Frank_Dawson\@Lotus.com" ||
		$card->PrimaryEmail() != "jqpublic\@xyz.dom1.com")
	{
		WriteDie("Error with SetEmails()");
	}
   $card->SetPrimaryEmail("Frank\@Test.com");
   $card->SetPrimaryEmail(NULL);
   if ($card->PrimaryEmail() !== NULL)
   {
      WriteDie("Error with SetPrimaryEmail(NULL)");
   }
	$card->SetPrimaryEmail("Dom\@Test.com");
	if ($card->PrimaryEmail() != "Dom\@Test.com")
	{
		WriteDie("Error with SetPrimaryEmail()");
	}
	$phones =
	array(
		"250-595-1362" => array("CELL", 'pref'),	// DRL FIXIT! This should work for MOBILE too but it doesn't!
	);
	$card->SetPhones($phones);
	$card->AddPhone("250-595-1361", array("HOME"));
	$card->AddPhone("250-595-1363", array("OTHER"));
	$card->AddPhone("250-595-1365", "FAX");
	$card->RemovePhone("250-595-1365");
	$card->AddPhone("250-595-1360", "FAX");
	if ($card->HomeMobilePhone() != "250-595-1362" || $card->PrimaryPhone() != "250-595-1362" ||
		$card->HomePhone() != "250-595-1361" || $card->HomeFax() != "250-595-1360")
	{
		WriteDie("Error with SetPhones()");
	}
	
	$str = "
BEGIN:vCard
VERSION:3.0
FN:Frank Dawson
ORG:Lotus Development Corporation
ADR;TYPE=WORK,POSTAL,PARCEL:;;6544 Battleford Drive
 ;Raleigh;NC;27613-3502;U.S.A.
ADR;TYPE=OTHER:;;1333 Balmoral Road
 ;Victoria;BC;V8R 1L6;CA
TEL;TYPE=FAX,BUSINESS:+1-919-676-9564
TEL;TYPE=VOICE,MSG,WORK,pref:+1-919-676-9515
EMAIL;TYPE=INTERNET,pref:Frank_Dawson\@Lotus.com
EMAIL;TYPE=INTERNET:fdawson\@earthlink.net
EMAIL;TYPE=internet,business:jqpublic\@xyz.dom1.com
URL:http://home.earthlink.net/~fdawson
PHOTO;VALUE=URL:file:///jqpublic.gif
PHOTO;ENCODING=BASE64;TYPE=GIF:U09NRURBVEE=
END:vCard
	";
	
	$card = vCard::FromString($str);

	$photo = $card->Photo();
	if ($card->PhotoURL() != "file:///jqpublic.gif" ||
		$photo[0] != "image/gif" || $photo[1] != "SOMEDATA")
	{
		WriteDie("Error with parsing photos");
	}
	$card->SetPhoto("image/gif", "SOMEPHOTODATA");
	$card->SetPhoto("image/jpeg", "SOMEMOREDATA");
	$card->SetPhotoURL("http://URL.jpeg");
	$photo = $card->Photo();
	if ($card->PhotoURL() != "http://URL.jpeg" ||
		$photo[0] != "image/jpeg" || $photo[1] != "SOMEMOREDATA")
	{
		WriteDie("Error with parsing photos");
	}
	if ($card->HomeEmail() != "Frank_Dawson\@Lotus.com" ||
		$card->WorkEmail() != "jqpublic\@xyz.dom1.com" ||
		$card->PrimaryEmail() != "Frank_Dawson\@Lotus.com")
	{
		WriteDie("Error with parsing emails()");
	}
	$emails = $card->Emails();
	if (!in_array("Frank_Dawson\@Lotus.com", array_keys($emails)) ||
		!in_array("fdawson\@earthlink.net", array_keys($emails)) ||
		!in_array("jqpublic\@xyz.dom1.com", array_keys($emails)))
	{
		WriteDie("Error with Emails()");
	}
	if (!Utilities::ArrayContains($emails["Frank_Dawson\@Lotus.com"], 'pref', 1) ||
		Utilities::ArrayContains($emails["fdawson\@earthlink.net"], 'pref', 1) || Utilities::ArrayContains($emails["fdawson\@earthlink.net"], "work", 1) ||
		!Utilities::ArrayContains($emails["jqpublic\@xyz.dom1.com"], 'work', 1))
	{
		WriteDie("Error with Emails()");
	}
	$emails =
	array(
		"Frank_Dawson\@Lotus.com" => array("work"),
		"jqpublic\@xyz.dom1.com" => array('pref', "HOME")
	);
	$card->SetEmails($emails);
	if ($card->HomeEmail() != "jqpublic\@xyz.dom1.com" ||
		$card->WorkEmail() != "Frank_Dawson\@Lotus.com" ||
		$card->PrimaryEmail() != "jqpublic\@xyz.dom1.com")
	{
		WriteDie("Error with SetEmails()");
	}
	$card->SetPrimaryEmail("Frank_Dawson\@Lotus.com");
	$temp = $card->HomeEmail();
	$temp = $card->WorkEmail();
	$temp = $card->PrimaryEmail();
	if ($card->HomeEmail() != "jqpublic\@xyz.dom1.com" ||
		$card->WorkEmail() != "Frank_Dawson\@Lotus.com" ||
		$card->PrimaryEmail() != "Frank_Dawson\@Lotus.com")
	{
		WriteDie("Error with SetPrimaryEmail()");
	}
	$card->SetPrimaryEmail("dlacerte\@lacerte.org");
	if ($card->HomeEmail() != "jqpublic\@xyz.dom1.com" ||
		$card->WorkEmail() != "Frank_Dawson\@Lotus.com" ||
		$card->PrimaryEmail() != "dlacerte\@lacerte.org")
	{
		WriteDie("Error with SetEmails()");
	}


	$phones = $card->Phones();
	if (!in_array("+1-919-676-9564", array_keys($phones)) || !in_array("+1-919-676-9515", array_keys($phones)) ||
		Utilities::ArrayContains($phones["+1-919-676-9564"], 'pref', 1) || !Utilities::ArrayContains($phones["+1-919-676-9515"], 'pref', 1))
	{
		WriteDie("Error with Phones()");
	}
	$phones =
	array(
		"250-595-1362" => array('MOBILE', 'pref'),
		"250-595-1361" => array("HOME"),
		"250-595-1360" => array("FAX")
	);
	$card->SetPhones($phones);
	if ($card->HomeMobilePhone() != "250-595-1362" || $card->PrimaryPhone() != "250-595-1362" ||
		$card->HomePhone() != "250-595-1361" || $card->HomeFax() != "250-595-1360")
	{
		WriteDie("Error with SetPhones()");
	}
	$card->SetPrimaryPhone("250-595-1361");
	if ($card->HomeMobilePhone() != "250-595-1362" || $card->PrimaryPhone() != "250-595-1361" ||
		$card->HomePhone() != "250-595-1361" || $card->HomeFax() != "250-595-1360")
	{
		WriteDie("Error with SetPhones()");
	}
	$card->SetPrimaryPhone("250-595-1363");
	if ($card->HomeMobilePhone() != "250-595-1362" || $card->PrimaryPhone() != "250-595-1363" ||
		$card->HomePhone() != "250-595-1361" || $card->HomeFax() != "250-595-1360")
	{
		WriteDie("Error with SetPhones()");
	}
	$card->SetPhoneCarrier("250-595-1361", "Rogers");
	$card->SetPhoneCarrier("250-595-1362", "Bell");
	$card->SetPhoneCarrier("250-595-1362", "Fido");
	$card->SetPhoneCarrier("250-595-1363", "Telus");
	$card->SetPhoneCarrier("250-595-1363", NULL);
	$card->RemovePhoneCarrier("250-595-1361");
	if ($card->PhoneCarrier("250-595-1361") != NULL ||
		$card->PhoneCarrier("250-595-1362") != "Fido" ||
		$card->PhoneCarrier("250-595-1363") != NULL)
	{
		WriteDie("Error with Add/RemovePhoneCarrier()");
		}
	$carriers = $card->PhoneCarriers();
	$carriers["250-595-1363"] = "Virgin";
	$card->SetPhoneCarriers($carriers);
	if ($card->PhoneCarrier("250-595-1361") != NULL ||
		$card->PhoneCarrier("250-595-1362") != "Fido" ||
		$card->PhoneCarrier("250-595-1363") != "Virgin")
	{
		WriteDie("Error with SetPhoneCarriers()");
	}

	if ($card->WorkStreetAddress() != "6544 Battleford Drive")
	{
		WriteDie("Error with WorkStreetAddress()");
	}
	$addresses = $card->Addresses();
	$address = new vCardAddress($card);
	$address->SetStreetAddress("1333 Balmoral Road\n1335 Balmoral Road");
	$address->SetCity("Vancouver");
	$address->SetTags(array("HOME"));
	$addresses[] = $address;
	$card->SetAddresses($addresses);
	if ($card->HomeCity() != "Vancouver")
	{
		WriteDie("Error with HomeCity()");
	}
   $temp = $card->ToString();

	$card = new vCard();

	$lastName = $card->LastName();
	if ($lastName != "")
	{
		WriteDie("Error getting last name 1");
	}
	$card->SetLastName("Lacerte");
	$lastName = $card->LastName();
	if ($lastName != "Lacerte")
	{
		WriteDie("Error setting last name 1");
	}

	$middleName = $card->MiddleName();
	if ($middleName != "")
	{
		WriteDie("Error getting middle name 1");
	}
	$card->SetMiddleName("Roger");
	$card->SetLastName(NULL);	// test for removing an item
	$middleName = $card->MiddleName();
	if ($middleName != "Roger")
	{
		WriteDie("Error setting middle name 1");
	}
	$lastName = $card->LastName();
	if (defined($lastName))
	{
		WriteDie("Error clearing last name");
	}

	$firstName = $card->FirstName();
	if ($firstName != "")
	{
		WriteDie("Error getting first name 1");
	}
	$card->SetFirstName("Dominique");
	$firstName = $card->FirstName();
	if ($firstName != "Dominique")
	{
		WriteDie("Error setting first name 1");
	}

	$suffix = $card->NameSuffix();
	if ($suffix != "")
	{
		WriteDie("Error getting name suffix 1");
	}
	$card->SetNameSuffix("Phd.");
	$suffix = $card->NameSuffix();
	if ($suffix != "Phd.")
	{
		WriteDie("Error setting name suffix 1");
	}

	$prefix = $card->NamePrefix();
	if ($prefix != "")
	{
		WriteDie("Error getting name prefix 1");
	}
	$card->SetNamePrefix("Mr.");
	$prefix = $card->NamePrefix();
	if ($prefix != "Mr.")
	{
		WriteDie("Error setting name prefix 1");
	}

	$displayName = $card->DisplayName();
	if ($displayName != "")
	{
		WriteDie("Error getting display name 1");
	}
	$card->SetDisplayName("Dominique Lacerte");
	$displayName = $card->DisplayName();
	if ($displayName != "Dominique Lacerte")
	{
		WriteDie("Error setting display name 1");
	}

	$nickname = $card->Nickname();
	if ($nickname != "")
	{
		WriteDie("Error getting nickname 1");
	}
	$card->SetNickname("Dom");
	$nickname = $card->Nickname();
	if ($nickname != "Dom")
	{
		WriteDie("Error setting nickname 1");
	}

	$companyName = $card->CompanyName();
	if ($companyName != "")
	{
		WriteDie("Error getting company name 1");
	}
	$card->SetCompanyName("NewHeights");
	$companyName = $card->CompanyName();
	if ($companyName != "NewHeights")
	{
		WriteDie("Error setting company name 1");
	}

	$department = $card->Department();
	if ($department != "")
	{
		WriteDie("Error getting department 1");
	}
	$card->SetDepartment("Development, Client");
	$department = $card->Department();
	if ($department != "Development, Client")
	{
		WriteDie("Error setting department 1");
	}

	$jobTitle = $card->JobTitle();
	if ($jobTitle != "")
	{
		WriteDie("Error getting job title 1");
	}
	$card->SetJobTitle("Engineer");
	$jobTitle = $card->JobTitle();
	if ($jobTitle != "Engineer")
	{
		WriteDie("Error setting job title 1");
	}

	$workPhone = $card->WorkPhone();
	if ($workPhone != "")
	{
		WriteDie("Error getting work phone 1");
	}
	$card->SetWorkPhone("1-250-380-0584");
	$workPhone = $card->WorkPhone();
	if ($workPhone != "1-250-380-0584")
	{
		WriteDie("Error setting work phone 1");
	}

	$workFax = $card->WorkFax();
	if ($workFax != "")
	{
		WriteDie("Error getting work fax 1");
	}
	$card->SetWorkFax("1-250-380-0FAX");
	$workFax = $card->WorkFax();
	if ($workFax != "1-250-380-0FAX")
	{
		WriteDie("Error setting work fax 1");
	}

	$card->SetWorkPhone("");
	$workPhone = $card->WorkPhone();
	if ($workPhone != "")
	{
		WriteDie("Error clearing work phone");
	}

	$workEmail = $card->WorkEmail();
	if ($workEmail != "")
	{
		WriteDie("Error getting work email 1");
	}
	$card->SetWorkEmail('dlacerte@NewHeights.com');
	$workEmail = $card->WorkEmail();
	if ($workEmail != 'dlacerte@NewHeights.com')
	{
		WriteDie("Error setting work email 1");
	}

	$workExtendedAddress = $card->WorkExtendedAddress();
	if ($workExtendedAddress != "")
	{
		WriteDie("Error getting work extended address 1");
	}
	$card->SetWorkExtendedAddress("Unit 1");
	$workExtendedAddress = $card->WorkExtendedAddress();
	if ($workExtendedAddress != "Unit 1")
	{
		WriteDie("Error setting work extended address 1");
	}

	$workStreetAddress = $card->WorkStreetAddress();
	if ($workStreetAddress != "")
	{
		WriteDie("Error getting work street address 1");
	}
	$card->SetWorkStreetAddress("1009 Government Street");
	$workStreetAddress = $card->WorkStreetAddress();
	if ($workStreetAddress != "1009 Government Street")
	{
		WriteDie("Error setting work street address 1");
	}

	$workCity = $card->WorkCity();
	if ($workCity != "")
	{
		WriteDie("Error getting work city 1");
	}
	$card->SetWorkCity("Burnaby");
	$workCity = $card->WorkCity();
	if ($workCity != "Burnaby")
	{
		WriteDie("Error setting work city 1");
	}

	$workRegion = $card->WorkRegion();
	if ($workRegion != "")
	{
		WriteDie("Error getting work region 1");
	}
	$card->SetWorkRegion("British Columbia");
	$workRegion = $card->WorkRegion();
	if ($workRegion != "British Columbia")
	{
		WriteDie("Error setting work region 1");
	}

	$workCountry = $card->WorkCountry();
	if ($workCountry != "")
	{
		WriteDie("Error getting work country 1");
	}
	$card->SetWorkCountry("Canada");
	$workCountry = $card->WorkCountry();
	if ($workCountry != "Canada")
	{
		WriteDie("Error setting work country 1");
	}

	$workPostalCode = $card->WorkPostalCode();
	if ($workPostalCode != "")
	{
		WriteDie("Error getting work postal code 1");
	}
	$card->SetWorkPostalCode("V8W 2Y3");
	$workPostalCode = $card->WorkPostalCode();
	if ($workPostalCode != "V8W 2Y3")
	{
		WriteDie("Error setting work postal code 1");
	}

	$homePhone = $card->HomePhone();
	if ($homePhone != "")
	{
		WriteDie("Error getting home phone 1");
	}
	$card->SetHomePhone("1-250-595-HOME");
	$homePhone = $card->HomePhone();
	if ($homePhone != "1-250-595-HOME")
	{
		WriteDie("Error setting home phone 1");
	}

	$homeFax = $card->HomeFax();
	if ($homeFax != "")
	{
		WriteDie("Error getting home fax 1");
	}
	$card->SetHomeFax("1-250-FAX-HOME");
	$homeFax = $card->HomeFax();
	if ($homeFax != "1-250-FAX-HOME")
	{
		WriteDie("Error setting home fax 1");
	}

	$homeEmail = $card->HomeEmail();
	if ($homeEmail != "")
	{
		WriteDie("Error getting home email 1");
	}
	$card->SetHomeEmail('SaltyFoam@hotmail.com');
	$homeEmail = $card->HomeEmail();
	if ($homeEmail != 'SaltyFoam@hotmail.com')
	{
		WriteDie("Error setting home email 1");
	}

	$homeExtendedAddress = $card->HomeExtendedAddress();
	if ($homeExtendedAddress != "")
	{
		WriteDie("Error getting home extended address 1");
	}
	$card->SetHomeExtendedAddress("Room 1");
	$homeExtendedAddress = $card->HomeExtendedAddress();
	if ($homeExtendedAddress != "Room 1")
	{
		WriteDie("Error setting home extended address 1");
	}

	$homeStreetAddress = $card->HomeStreetAddress();
	if ($homeStreetAddress != "")
	{
		WriteDie("Error getting home street address 1");
	}
	$card->SetHomeStreetAddress("1341 Balmoral Road");
	$homeStreetAddress = $card->HomeStreetAddress();
	if ($homeStreetAddress != "1341 Balmoral Road")
	{
		WriteDie("Error setting home street address 1");
	}

	$homeCity = $card->HomeCity();
	if ($homeCity != "")
	{
		WriteDie("Error getting home city 1");
	}
	$card->SetHomeCity("Victoria");
	$homeCity = $card->HomeCity();
	if ($homeCity != "Victoria")
	{
		WriteDie("Error setting home city 1");
	}

	$homeRegion = $card->HomeRegion();
	if ($homeRegion != "")
	{
		WriteDie("Error getting home region 1");
	}
	$card->SetHomeRegion("B.C.");
	$homeRegion = $card->HomeRegion();
	if ($homeRegion != "B.C.")
	{
		WriteDie("Error setting home region 1");
	}

	$homeCountry = $card->HomeCountry();
	if ($homeCountry != "")
	{
		WriteDie("Error getting home country 1");
	}
	$card->SetHomeCountry("CA");
	$homeCountry = $card->HomeCountry();
	if ($homeCountry != "CA")
	{
		WriteDie("Error setting home country 1");
	}

	$homePostalCode = $card->HomePostalCode();
	if ($homePostalCode != "")
	{
		WriteDie("Error getting home postal code 1");
	}
	$card->SetHomePostalCode("V8R 1L6");
	$homePostalCode = $card->HomePostalCode();
	if ($homePostalCode != "V8R 1L6")
	{
		WriteDie("Error setting home postal code 1");
	}

	$mobilePhone = $card->HomeMobilePhone();
	if ($mobilePhone != "")
	{
		WriteDie("Error getting mobile phone 1");
	}
	$card->SetHomeMobilePhone("1-250-595-1362");
	$mobilePhone = $card->HomeMobilePhone();
	if ($mobilePhone != "1-250-595-1362")
	{
		WriteDie("Error setting mobile phone 1");
	}

	$card->SetPhoneCarrier("1-250-380-0584", "Bell");
	$card->SetPhoneCarrier("1-250-380-0FAX", "Fido");
	$card->SetPhoneCarrier("1-250-595-1362", "Bell");
	
	$webSite = $card->HomeWebSite();
	if ($webSite != "")
	{
		WriteDie("Error getting web site 1");
	}
	$card->SetHomeWebSite("http://www.NewHeights.com");
	$webSite = $card->HomeWebSite();
	if ($webSite != "http://www.NewHeights.com")
	{
		WriteDie("Error setting web site 1");
	}

	$bday = DateAndTime::FromString('1980-10-02');
	$birthday = $card->Birthday();
	if (defined($birthday))
	{
		WriteDie("Error getting birthday 1");
	}
	$card->SetBirthday($bday);
	$birthday = $card->Birthday();
	if ($birthday != $bday)
	{
		WriteDie("Error setting birthday 1");
	}
	$card->SetAnniversary(NULL);
	$anniversary = $card->Anniversary();
	if (defined($anniversary))
	{
		WriteDie("Error setting anniversary");
	}

	$gender = $card->Gender();
	if ($gender != "")
	{
		WriteDie("Error getting gender 1");
	}
	$card->SetGender("M");
	$gender = $card->Gender();
	if ($gender != "M")
	{
		WriteDie("Error setting gender 1");
	}

	$temp = $card->Location();
	$latitude = $temp[0]; $longitude = $temp[1];
	if ($latitude != NULL || $longitude != NULL)
	{
		WriteDie("Error getting location 1");
	}
	$card->SetLocation("28.893", "-101.23");
	$temp = $card->Location();
	$latitude = $temp[0]; $longitude = $temp[1];
	if ($latitude != "28.893" || $longitude != "-101.23")
	{
		WriteDie("Error setting location 1");
	}

	$timeZone = $card->TimeZone();
	if ($timeZone != "")
	{
		WriteDie("Error getting timeZone 1");
	}
	$card->SetTimeZone(-28800);
	$timeZone = $card->TimeZone();
	if ($timeZone != -28800)
	{
		WriteDie("Error setting timeZone 1");
	}

	$note = $card->Note();
	if ($note != "")
	{
		WriteDie("Error getting note 1");
	}
	$card->SetNote("Cool dude!\r\n\r\nWhat's up?");
	$note = $card->Note();
	if ($note != "Cool dude!\r\n\r\nWhat's up?")
	{
		WriteDie("Error setting note 1");
	}

	$now = DateAndTime::Now();
	$lastUpdated = $card->LastUpdated();
	if ($lastUpdated != "")
	{
		WriteDie("Error getting lastUpdated 1");
	}
	$card->SetLastUpdated($now);
	$lastUpdated = $card->LastUpdated();
	if (DateAndTime::NotEqual($lastUpdated, $now))
	{
		WriteDie("Error setting lastUpdated 1");
	}

	$card->SetPhoto("image/jpeg", "SOMEMOREDATA");
	$card->SetPhoto("image/jpeg", NULL);
	$temp = $card->Photo();
	$mime = $temp[0]; $photo = $temp[1];
	if ($photo != NULL)
	{
		WriteDie("Error setting empty photo");
	}

	$card->SetPhoto("image/jpeg", "SOMEMOREDATA");
	$card->SetPhotoURL("http://URL.jpeg");

	$temp = $card->ToString();
	print_r($temp);
	
	$card = vCard::FromString($temp);

	$note = $card->Note();
	if ($note != "Cool dude!\n\nWhat's up?")   // NOTE: \r\n converted to \n after converting to string!
	{
		WriteDie("Error saving note to string");
	}

	$lastName = $card->LastName();
	if ($lastName != "")
	{
		WriteDie("Error getting last name 2");
	}

	$middleName = $card->MiddleName();
	if ($middleName != "Roger")
	{
		WriteDie("Error getting middle name 2");
	}

	$firstName = $card->FirstName();
	if ($firstName != "Dominique")
	{
		WriteDie("Error getting first name 2");
	}

	$suffix = $card->NameSuffix();
	if ($suffix != "Phd.")
	{
		WriteDie("Error getting name suffix 2");
	}

	$prefix = $card->NamePrefix();
	if ($prefix != "Mr.")
	{
		WriteDie("Error getting name prefix 2");
	}

	$displayName = $card->DisplayName();
	if ($displayName != "Dominique Lacerte")
	{
		WriteDie("Error getting display name 2");
	}

	$nickname = $card->Nickname();
	if ($nickname != "Dom")
	{
		WriteDie("Error getting nickname 2");
	}

	$companyName = $card->CompanyName();
	if ($companyName != "NewHeights")
	{
		WriteDie("Error getting company name 2");
	}

	$department = $card->Department();
	if ($department != "Development, Client" && 
		$department != "Development,Client")	// DRL FIXIT? space was removed
	{
		WriteDie("Error getting company name 2");
	}

	$jobTitle = $card->JobTitle();
	if ($jobTitle != "Engineer")
	{
		WriteDie("Error getting job title 2");
	}

	$workPhone = $card->WorkPhone();
	if ($workPhone != "")
	{
		WriteDie("Error getting work phone 2");
	}

	$workFax = $card->WorkFax();
	if ($workFax != "1-250-380-0FAX")
	{
		WriteDie("Error getting work fax 2");
	}

	$workEmail = $card->WorkEmail();
	if ($workEmail != 'dlacerte@NewHeights.com')
	{
		WriteDie("Error getting work email 2");
	}

	$workExtendedAddress = $card->WorkExtendedAddress();
	if ($workExtendedAddress != "Unit 1")
	{
		WriteDie("Error getting work extended address 2");
	}

	$workStreetAddress = $card->WorkStreetAddress();
	if ($workStreetAddress != "1009 Government Street")
	{
		WriteDie("Error getting work street address 2");
	}

	$workCity = $card->WorkCity();
	if ($workCity != "Burnaby")
	{
		WriteDie("Error getting work city 2");
	}

	$workRegion = $card->WorkRegion();
	if ($workRegion != "British Columbia")
	{
		WriteDie("Error getting work region 2");
	}

	$workCountry = $card->WorkCountry();
	if ($workCountry != "Canada")
	{
		WriteDie("Error getting work country 2");
	}

	$workPostalCode = $card->WorkPostalCode();
	if ($workPostalCode != "V8W 2Y3")
	{
		WriteDie("Error getting work postal code 2");
	}

	$homePhone = $card->HomePhone();
	if ($homePhone != "1-250-595-HOME")
	{
		WriteDie("Error getting home phone 2");
	}

	$homeFax = $card->HomeFax();
	if ($homeFax != "1-250-FAX-HOME")
	{
		WriteDie("Error getting home fax 2");
	}

	$homeEmail = $card->HomeEmail();
	if ($homeEmail != 'SaltyFoam@hotmail.com')
	{
		WriteDie("Error getting home email 2");
	}

	$homeExtendedAddress = $card->HomeExtendedAddress();
	if ($homeExtendedAddress != "Room 1")
	{
		WriteDie("Error getting home extended address 2");
	}

	$homeStreetAddress = $card->HomeStreetAddress();
	if ($homeStreetAddress != "1341 Balmoral Road")
	{
		WriteDie("Error getting home street address 2");
	}

	$homeCity = $card->HomeCity();
	if ($homeCity != "Victoria")
	{
		WriteDie("Error getting home city 2");
	}

	$homeRegion = $card->HomeRegion();
	if ($homeRegion != "B.C.")
	{
		WriteDie("Error getting home region 2");
	}

	$homeCountry = $card->HomeCountry();
	if ($homeCountry != "CA")
	{
		WriteDie("Error getting home country 2");
	}

	$homePostalCode = $card->HomePostalCode();
	if ($homePostalCode != "V8R 1L6")
	{
		WriteDie("Error getting home postal code 2");
	}

	$mobilePhone = $card->HomeMobilePhone();
	if ($mobilePhone != "1-250-595-1362")
	{
		WriteDie("Error getting mobile phone 2");
	}

	$webSite = $card->HomeWebSite();
	if ($webSite != "http://www.NewHeights.com")
	{
		WriteDie("Error getting web site 2");
	}

	$birthday = $card->Birthday();
	if ($birthday != $bday)
	{
		WriteDie("Error getting birthday 2");
	}

	$gender = $card->Gender();
	if ($gender != "M")
	{
		WriteDie("Error getting gender 2");
	}

	$temp = $card->Location();
	$latitude = $temp[0]; $longitude = $temp[1];
	if ($latitude != "28.893" || $longitude != "-101.23")
	{
		WriteDie("Error getting location 2");
	}

	$timeZone = $card->TimeZone();
	if ($timeZone != -28800)
	{
		WriteDie("Error getting time zone 2");
	}

	$note = $card->Note();
	if ($note != "Cool dude!\n\nWhat's up?")
	{
		WriteDie("Error getting note 2");
	}

	$lastUpdated = $card->LastUpdated();
	if (DateAndTime::NotEqual($lastUpdated, $now))
	{
		WriteDie("Error getting last updated 2");
	}
}


?>

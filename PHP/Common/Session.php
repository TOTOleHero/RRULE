<?php

$MaxSessionLifetime = 48 * 60 * 60;
$MinSessionLifetime = 1 * 60 * 60;

// the sessions can timeout on client side due to cookie timeout but also
// on server side so we set the server timeout to the maximum possible
/* I set these in php.ini instead.
ini_set("session.gc_maxlifetime", $MaxSessionLifetime);
ini_set("session.gc_divisor", "1");
ini_set("session.gc_probability", "1");
ini_set("session.cookie_lifetime", "0");
ini_set("session.cookie_path", "/");
*/

// Begin a new session if there isn't an existing one.
session_start();

class Session
{
//   protected $FilterNames;   // initialized in derived class
   
	private $url;		// the URL used to access this session
	private $db;		// MySQL link identifier

	// login...
	private $userID;
	private $isLive;  // when false, this user is not actually at the Web site, this is an offline session for processing
	
	private $view;		// heirarchy path as in  Home, Account, Settings
//	private $page;
//   private $filter;

   public function __construct($db, $userID=NULL)
	{
		global $MainUri, $MaxSessionLifetime, $MinSessionLifetime;
		
		if ($userID)
      {
         $this->isLive = false;
         $this->userID = $userID;
      }
      else
      {
         $this->isLive = true;
         
         // the base part of the URL is hard-coded, the parameters are not
         if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST')
         {
            // this is likely a "POST" so we'll get the variables and create the URI ourselves
            $this->url = $thisUri = $MainUri . '?' . http_build_query($_POST);
         }
         else
         {
            $REQUEST_URI = isset($_SERVER["REQUEST_URI"]) ? $_SERVER["REQUEST_URI"] : null;
            $this->url = $thisUri = parse_url($MainUri, PHP_URL_SCHEME) . '://' . parse_url($MainUri, PHP_URL_HOST) .
               $REQUEST_URI;
         }
         
         $this->userID = isset($_SESSION['UserID']) ? $_SESSION['UserID'] : NULL;
         
         // this extends the expiration each time the user connects to the server
         if (!$this->userID || (isset($_SESSION['RememberPassword']) && $_SESSION['RememberPassword']))
            $timeout = time() + $MaxSessionLifetime;   // not logged in yet, or wants password remembered
         else
            $timeout = time() + $MinSessionLifetime;   // logged in and doesn't want password remembered
         $currentCookieParams = session_get_cookie_params();
         setcookie(session_name(), session_id(), $timeout,
            $currentCookieParams["path"],
            $currentCookieParams["domain"],
            $currentCookieParams["secure"],
            $currentCookieParams["httponly"]);
      }

      $this->db = $db; 
		$this->view = array();
//		$this->page = 0;
//		$this->filter = array();

		$this->SetView(FormIsSet('View') ? explode(',', FormParseStr('View')) : NULL);
//		$this->SetPage(FormIsSet('Page') ? FormParseInt('Page') : 0);
//      foreach ($this->FilterNames as $name)
//      {
//         $this->filter[$name] = FormIsSet($name) ? FormParseStr($name) : NULL;   // DRL FIXIT? What about INT types?
//      }
   }

   public function url() { return $this->url; }	// the URL of the current page
   public function db() { return $this->db; }

   public function Logout()
   {
      $this->SetUserID(NULL);
   }
	
   public function SetRememberPassword($remember)
   {
      $_SESSION['RememberPassword'] = $remember ? 1 : 0;
   }

	public function SetUserID($value)
	{
		$this->userID = !empty($value) ? $value : NULL;
		$cachedValue = isset($_SESSION['UserID']) ? $_SESSION['UserID'] : NULL;
		
		if ($this->isLive && !Utilities::ValuesEqualOrBothUndef($cachedValue, $this->userID))
		{
			session_destroy();
			session_start();
			
			if (empty($this->userID))
			{
				unset($_SESSION['UserID']);
            unset($_SESSION['RememberPassword']);
			}
			else
			{
				$_SESSION['UserID'] = $this->userID;
			}
		}
	}

	public function SetView($value)	// an array
	{
		$this->view = !empty($value) ? $value : array();
//		$this->page = 0;	// reset the page when changing the view
	}
	
//	public function SetPage($value)
//	{
//		if (empty($value))
//			$value = 0;
//
//		$this->page = $value;
//		$_SESSION['Page'] = $value;
//	}

	public function IsLoggedIn() { return $this->userID != NULL; }
   
	public function userID() { return $this->userID; }
   public function isLive() { return $this->isLive; }
   public function view() { return $this->view; }
//	public function page() { return $this->page; }
//	public function filter() { return $this->filter; }
//	public function filterForSql()
//   {
//      $result = array();
//      foreach ($this->filter as $name => $value)
//      {
//         if ($value != NULL)
//            $result[$name] = SqlPrepStr($value);   // DRL FIXIT? What about INT types?
//      }
//      return $result;
//   }

//	public function GetBaseUrl()
//	{
//		global $MainUri;
//
//      return $MainUri;
//   }
   
	public function GetLinkUrl()
	{
		global $MainUri;
		
		$url = $MainUri . '?';

		if (!empty($this->view)) { $url .= 'View=' . implode(',', $this->view) . '&'; }
//		$url .= 'Page=' . $this->page . '&';
//      foreach ($this->FilterNames as $name)
//      {
//         if ($this->filter[$name] != NULL)
//         {
//            $url .= $name . '=' . $this->filter[$name] . '&';
//         }
//      }

		$url = substr($url, 0, -1);

		return $url;
	}
/*
	public function GetLinkUrlForPage($page)
	{
		global $MainUri;
		
		$url = $MainUri . '?';

		if (!empty($this->view)) { $url .= 'View=' . implode(',', $this->view) . '&'; }
		$url .= 'Page=' . $page . '&';
      foreach ($this->FilterNames as $name)
      {
         if ($this->filter[$name] != NULL)
         {
            $url .= $name . '=' . $this->filter[$name] . '&';
         }
      }

		$url = substr($url, 0, -1);

		return $url;
	}
	
	public function GetFormUrl()
	{
		global $MainUri;
		
		return $MainUri;
	}
*/	
	public function GetFieldsForForm()
	{
		$form = '';

		if (!empty($this->view)) { $form .= "<INPUT type='hidden' name='View' value='" . implode(',', $this->view) . "'>\r\n"; }
//		// the page is always set as we have Javascript code looking for it when the page is to be changed
//		$form .= "<INPUT type='hidden' name='Page' value='" . $this->page . "'>\r\n";

		return $form;
	}
}

?>
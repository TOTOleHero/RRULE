<?php

require_once(dirname(__FILE__) . '/File.php');
require_once(dirname(__FILE__) . '/Log.php');
require_once(dirname(__FILE__) . '/Url.php');
require_once(dirname(__FILE__) . '/../ThirdParty/google-api-php-client/src/Google/autoload.php');
require_once(dirname(__FILE__) . '/../ThirdParty/PHPoAuthLib/src/OAuth/bootstrap.php');

use OAuth\Common\Storage\Memory;
use OAuth\Common\Consumer\Credentials;
use OAuth\Common\Http\Exception\TokenResponseException;

class RestException extends Exception
{
   public $HttpCode;
   
   public function __construct($httpCode, $message)
   {
      parent::__construct($message, E_USER_ERROR);
      
      $this->HttpCode = $httpCode;
   }

   // custom string representation of object
   public function __toString()
   {
      return __CLASS__ . ': ' . $this->message . '(' . $this->HttpCode . ')';
   }
}

class RestLoginException extends RestException
{
   public function __construct($httpCode, $message)
   {
      parent::__construct($httpCode, $message);
   }
}

class RestNotFoundException extends RestException
{
   public function __construct($httpCode, $message)
   {
      parent::__construct($httpCode, $message);
   }
}

class RestNoAccessException extends RestException
{
   public function __construct($httpCode, $message)
   {
      parent::__construct($httpCode, $message);
   }
}

class RestSyncTokenExpiredException extends RestException
{
   public function __construct($httpCode, $message)
   {
      parent::__construct($httpCode, $message);
   }
}

class RestQuotaExceededException extends RestException
{
   public function __construct($httpCode, $message)
   {
      parent::__construct($httpCode, $message);
   }
}

class RestInvalidRecipientException extends RestException
{
   public function __construct($httpCode, $message)
   {
      parent::__construct($httpCode, $message);
   }
}

class Rest
{
   static $Services = array(
         'Google',
         'LinkedIn',
         'Meetup',
         'Microsoft',
         'Pinterest',
         'Twitter',
      );

   private $ServiceName;         // as in 'Google' or 'Twitter', etc.
   private $Client;
   private $Storage;
   private $AccessTokenIndex;
   private $RequestTokenIndex;   // for OAUTH 1.0
   private $TestUri;
   private $AccessTokenParamName;
   private $AccessTokenRevoked;
   private $AuthorizationParameters;
	
    /**
	 * SPL-compatible autoloader.
	 *
     * @param string $className Name of the class to load.
     *
     * @return boolean
   public static function autoload($className)
   {
      if ($className != 'Rest')
		{
         return false;
		}
      return include str_replace('_', '/', $className) . '.php';
   }
     */

	function __construct($configFile)
	{
      $lines = explode(PHP_EOL, File::ReadTextFile($configFile));
      
      foreach (Rest::$Services as $service)
      {
         if (strpos($configFile, $service) !== false)
         {
            $this->ServiceName = $service;
            break;
         }
      }
      if (empty($this->ServiceName))
         throw new RestException(500, "Unrecognized service ($configFile), add it to Rest.php!");
         
      // I think the only reason NOT to use Google is if you need support for OAUTH 1.0
      // Also, I think Facebook could be switched over to use the Rest class as well.
      if ($this->ServiceName != 'Twitter')
      {
   		$this->Client = new Google_Client();
         
         $this->Client->setApplicationName('Uberfine');
         
         $this->Client->setRedirectUri($lines[2]);
         $this->Client->setOAuth2Uris($lines[3], $lines[4], $lines[5]);
         
         $this->Client->setClientId($lines[7]);
         $this->Client->setClientSecret($lines[8]);
         if ($lines[9])
            $this->Client->setDeveloperKey($lines[9]);
      }
      else
      {
         $this->Storage = new Memory();
         
         $credentials = new Credentials($lines[7], $lines[8], $lines[2]);
             
         $serviceFactory = new \OAuth\ServiceFactory();
         
         $this->Client = $serviceFactory->createService($this->ServiceName, $credentials, $this->Storage);
      }
            
      // this class may be used for multiple APIs so we need to keep the
      // access tokens separate for each one
      $this->AccessTokenIndex = 'access_token_' . $this->ServiceName;
      $this->RequestTokenIndex = 'request_token_' . $this->ServiceName;
      
      $this->TestUri = $lines[6];
      
      $this->AccessTokenParamName = $lines[10];
      
      $this->AuthorizationParameters = array();
      
      $this->AccessTokenRevoked = false;
	}
	
	function __destruct()
	{
	}
   
   function SetRequestedAccess($types)
   {
      if ($this->Client instanceof Google_Client)
      {
         $this->Client->setScopes($types);
//         $this->Client->setIncludeGrantedScopes(true);
      }
      else
      {
         // DRL FIXIT!
      }
   }
   
   // a hash of name=>value pairs to add to the authorization URL
   function SetAuthorizationParameters($params)
   {
      $this->AuthorizationParameters = $params;
   }
   
   // this method will not return if the user needs to log in
   function CheckLogin($currentUri=NULL)
   {
      // check if we've just been redirected from the response handler
      if (isset($_SESSION[$this->AccessTokenIndex]))
      {
         $this->SetAccessToken($_SESSION[$this->AccessTokenIndex]);
         unset($_SESSION[$this->AccessTokenIndex]);
      }

      if (!$this->IsLoggedIn())
      {
         // this is the application redirect URI, save it for later
         if (empty($currentUri))
            $currentUri = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER["REQUEST_URI"];
         $_SESSION['APP_REDIRECT_URI'] = $currentUri;
         
         $auth_url = $this->CreateAuthorizationUrl();
         
         header('Location: ' . filter_var($auth_url, FILTER_SANITIZE_URL));
         exit;
      }
   }
   
   // only called from an authorization response
   function ProcessLogin()
   {
      global $MainUri;

      $redirectUri = NULL;
      $errorMsg = NULL;

      try
      {
         if ($this->Client instanceof Google_Client)
         {
            if (isset($_GET['code']))
            {
               $this->AccessTokenRevoked = false;

               $this->Client->authenticate($_GET['code']);

               // save the access token so we can capture it in CheckLogin()
               $access_token = $this->Client->getAccessToken();
               $_SESSION[$this->AccessTokenIndex] = $access_token;
            }
            else
            {
               $errorMsg = 'Unknown authorization error';

               if (isset($_REQUEST['error']))
               {
                  $errorMsg = $_REQUEST['error'];
                  if (isset($_REQUEST['error_description']))
                     $errorMsg .= ": " . $_REQUEST['error_description'];
                  WriteError("REST OAUTH error: $errorMsg");
               }
            }
         }
         else
         {
            // OAUTH 2.0
            if (isset($_GET['code']))
            {
               $this->AccessTokenRevoked = false;

               $state = isset($_GET['state']) ? $_GET['state'] : null;

               $this->Client->requestAccessToken($_GET['code'], $state);


               // save the access token so we can capture it in CheckLogin()
               $access_token = $this->GetAccessToken();
               $_SESSION[$this->AccessTokenIndex] = $access_token;
            }
            // OAUTH 1.0
            else if (!empty($_GET['oauth_token']) && isset($_SESSION[$this->RequestTokenIndex]))
            {
               $this->AccessTokenRevoked = false;

               // retrieve the request token secret we saved previously
               $token = Rest::_DeserializeToken($_SESSION[$this->RequestTokenIndex]);
               unset($_SESSION[$this->RequestTokenIndex]);

               $this->Storage->storeAccessToken($this->ServiceName, $token);
               $this->Client->requestAccessToken($_GET['oauth_token'], $_GET['oauth_verifier']);


               // save the access token so we can capture it in CheckLogin()
               $access_token = $this->GetAccessToken();
               $_SESSION[$this->AccessTokenIndex] = $access_token;
            }
            else
            {
               assert('0');
            }
         }
      }
      catch (Exception $e)
      {
         WriteError('Caught exception in REST authenticate call: ' .  $e->getMessage() . "\n" . $e->getTraceAsString());

         $errorMsg = $e->getMessage();
      }

      // get the application redirect URI that was saved earlier
      $redirectUri = $_SESSION['APP_REDIRECT_URI'];
      if (empty($redirectUri))
      {
         assert('0');
         $redirectUri = $MainUri;
      }

      // add the error message to the redirect URL, that code should display it
      if (!empty($errorMsg))
      {
         $redirectUri = Url::SetParam($redirectUri, 'ErrorMessage', $errorMsg);
      }

      header('Location: ' . filter_var($redirectUri, FILTER_SANITIZE_URL));
   }
   
   function CreateAuthorizationUrl()
   {
      if ($this->Client instanceof Google_Client)
      {
         $auth_url = $this->Client->createAuthUrl();
         
         foreach ($this->AuthorizationParameters as $name => $value)
         {
            $auth_url = Url::SetParam($auth_url, $name, $value);
         }
         
         return $auth_url;
      }
      else
      {
         // check for extra OAUTH 1.0 processing...
         if (method_exists($this->Client, 'requestRequestToken'))
         {
            $token = $this->Client->requestRequestToken();

            // save the request token so we can capture it in CheckLogin()
            $_SESSION[$this->RequestTokenIndex] = Rest::_SerializeAccessToken($token);
         
            return $this->Client->getAuthorizationUri(array('oauth_token' => $token->getRequestToken()))->getAbsoluteUri();
         }
         
         return $this->Client->getAuthorizationUri()->getAbsoluteUri();
      }
   }
   
   function Logout()
   {
      if ($this->AccessTokenRevoked || empty($this->GetAccessToken()))
         throw new RestLoginException(530, 'Not logged in');
         
      $this->RevokeAccessToken();
   }
   
   function IsLoggedIn()
   {
      if ($this->Client instanceof Google_Client)
      {
         return !$this->AccessTokenRevoked && !$this->Client->isAccessTokenExpired();
      }
      else
      {
         if (!$this->Storage->hasAccessToken($this->ServiceName))
            return false;
            
         $token = $this->Storage->retrieveAccessToken($this->ServiceName);
         
         return !$this->AccessTokenRevoked && !$token->isExpired(); 
      }
/*      
      $at = $this->GetAccessToken();
      if (empty($at))
         return false;
         
      $at = json_decode($at, true);
      $accessToken = $at['access_token'];
         
      // try to access the test URI to see if we have access
      $uri = $this->TestUri;
      if (strpos($uri, '?') === false)
         $uri .= '?';
      else
         $uri .= '&';
      $uri .= $this->AccessTokenParamName . '=' . Url::EncodeURIComponent($accessToken);
      
      $req = new Google_Http_Request($uri, 'GET', array());
      $response = $this->Client->getIo()->makeRequest($req);

      if ($response->getResponseHttpCode() == 401)
      {
         $this->Client->revokeToken($accessToken);
         return false;
      }
      
      return true;
*/
   }
   
   // used to save the token while online in order to use it later while online or offline
   function GetAccessToken()
   {
      if ($this->AccessTokenRevoked)
         return NULL;

      if ($this->Client instanceof Google_Client)
      {
         return $this->Client->getAccessToken();
      }
      else
      {
         if (!$this->Storage->hasAccessToken($this->ServiceName))
            return NULL;

         $token = $this->Storage->retrieveAccessToken($this->ServiceName);
         
         return Rest::_SerializeAccessToken($token);
      }
   }
   
   // used when online (saved in a cookie) or offline (saved in the database)
   function SetAccessToken($accessToken)
   {
      if ($accessToken == NULL)
      {
         $this->AccessTokenRevoked = true;
         return;
      }

      if ($this->Client instanceof Google_Client)
      {
         $this->Client->setAccessToken($accessToken);
      }
      else
      {
         $token = Rest::_DeserializeToken($accessToken);
         
         $this->Storage->storeAccessToken($this->ServiceName, $token);
      }
   }

   static function _SerializeAccessToken($token)
   {
      $data = array(
         'accessToken' => $token->getAccessToken(),
         'refreshToken' => $token->getRefreshToken(),
         'endOfLife' => $token->getEndOfLife(),
         'extraParams' => $token->getExtraParams(),
         // OAUTH 1.0:
         'accessTokenSecret' => $token->getAccessTokenSecret(),
         'requestTokenSecret' => $token->getRequestTokenSecret()
      );
      
      return json_encode($data);
   }
   
   static function _DeserializeToken($accessToken)
   {
      $data = json_decode($accessToken, true);
      
      $token = new OAuth\OAuth1\Token\StdOAuth1Token();
      $token->setAccessToken($data['accessToken']);
      $token->setRefreshToken($data['refreshToken']);
      $token->setEndOfLife($data['endOfLife']);
      $token->setExtraParams($data['extraParams']);
      // OAUTH 1.0:
      $token->setAccessTokenSecret($data['accessTokenSecret']);
      $token->setRequestTokenSecret($data['requestTokenSecret']);
      
      return $token;
   }
   
   // used when logging out or when changing scopes
   function RevokeAccessToken()
   {
      unset($_SESSION[$this->AccessTokenIndex]);
      $this->AccessTokenRevoked = true;
      
      if ($this->Client instanceof Google_Client)
      {
         $this->Client->revokeToken();
      }
      else
      {
         // DRL FIXIT!
      }
   }
   
   function Get($url, $parameters = array(), $headers = array())
   {
      return $this->_MakeRequest('GET', $url, $parameters, $headers);
   }
   
   function Post($url, $headers, $body)
   {
      return $this->_MakeRequest('POST', $url, array(), $headers, $body);
   }
   
   function Put($url, $headers, $body)
   {
      return $this->_MakeRequest('PUT', $url, array(), $headers, $body);
   }
   
   function Delete($url, $parameters = array(), $headers = array())
   {
      return $this->_MakeRequest('DELETE', $url, $parameters, $headers);
   }
   
   function _MakeRequest($method, $url, $parameters = array(), $headers = array(), $body = NULL)
   {
//WriteInfo("$method: $url");
//      if ($body) WriteInfo('Body: ' . $body);
      
      if (strtolower($method) != 'post' && strtolower($method) != 'put')
      {
         $url .= '?';
         foreach ($parameters as $name => $value)
         {
            $url .= $name . '=' . Url::EncodeURIComponent($value) . '&';
         }
         $url = substr($url, 0, strlen($url)-1);   // remove trailing ? or &
// This is not supported by LinkedIn...
//       $url .= $this->AccessTokenParamName . '=' . Url::EncodeURIComponent($accessToken);
      }
      
      $at = $this->GetAccessToken();
      if (empty($at))
         throw new RestLoginException(530, 'Not logged in');
      $at = json_decode($at, true);

      $retry = true;
      $retryCount = -1;
      while ($retry)
      {
         $retry = false;
         $retryCount++;
         $code = 0;
         $errorText = '';
         $result = NULL;
         
         if ($this->Client instanceof Google_Client)
         {         
            $accessToken = $at['access_token'];
            
            $headers['Authorization'] = "Bearer $accessToken";
            
            $response = NULL;
            try
            {
               $req = new Google_Http_Request($url, $method, $headers, $body);
               $response = $this->Client->getIo()->makeRequest($req);

               if ($response->getResponseHttpCode() == 401)
               {
                  if (isset($at['refresh_token']))
                  {
                     $refreshToken = $at['refresh_token'];
                     $this->Client->refreshToken($refreshToken);
                     $at = $this->GetAccessToken();
                     if ($at)
                     {
                        $at = json_decode($at, true);
                        if (isset($at['access_token']))
                        {
                           // if we no longer have a refresh token save the old one to re-use
                           if (!isset($at['refresh_token']))
                              $at['refresh_token'] = $refreshToken;
                              
                           $accessToken = $at['access_token'];
                           
                           // replace the old access token with the new one and try again
// This is not supported by LinkedIn...
//                           $url = Url::SetParam($url, $this->AccessTokenParamName, $accessToken);
                           $headers['Authorization'] = "Bearer $accessToken";
                           
                           $req = new Google_Http_Request($url, $method, $headers, $body);
                           $response = $this->Client->getIo()->makeRequest($req);
                        }
                     }
                  }
               }
            }
            catch (Google_Auth_Exception $e)
            {
               $this->AccessTokenRevoked = true;
               
               $str = $e->getMessage();
               $iStart = strpos($str, '"error_description" : "');
               if ($iStart === false) {
                  $iStart = strpos($str, '"error_description":"'); // ILA try another format if the first doesn't work
               }
               if ($iStart >= 0)
               {
                  $iStart += 23;
                  $iEnd = strpos($str, '"', $iStart);
                  if ($iEnd < 0)
                     $iEnd = strlen($str);
                  $str = substr($str, $iStart, $iEnd-$iStart);
               }
               throw new RestLoginException(530, $str);
            }
            catch (Google_IO_Exception $e)
            {
               if (strpos($e->getMessage(), 'Timed out') !== false ||
                  strpos($e->getMessage(), 'Could not contact DNS servers') !== false)
               {
                  // this sometimes happened for outlook.office.com so I retry after a short delay
      
                  WriteInfo("REST request got '" . $e->getMessage() . "', retrying after a delay.");
                  usleep(500000);   // half second delay
                  $retry = true;
                  
                  continue;   // go to bottom of loop!!!
               }
               else
               {
                  // not usually a fatal error?
                  WriteInfo("REST request got '" . $e->getMessage() . "'.");
                  throw new RestException(500, $e->getMessage());
               }
            }
               
            $code = $response->getResponseHttpCode();
            $result = $response->getResponseBody();
            
            $errorText = 'Error';
            
            $decodedResponse = json_decode($result, true);
            if ($decodedResponse == null)
            {
               if (!empty($result))
                  $errorText = $result;
            }
            else
            {
               if (isset($decodedResponse['error']))
               {
                  if (is_array($decodedResponse['error']))
                  {
                     if (isset($decodedResponse['error']['message']))
                     {
                        $errorText = $decodedResponse['error']['message'];
                     }
                  }
                  else
                  {
                     $errorText = $decodedResponse['error'];
                     if (isset($decodedResponse['error_description']))
                     {
                        $errorText .= ": " . $decodedResponse['error_description'];
                     }
                  }
               }
               else if (isset($decodedResponse['message']))
               {
                  $errorText = $decodedResponse['message'];
               }
            }
         }
         else
         {
            try
            {
               $result = $this->Client->request($url, $method, $body, $headers);
               
               $code = 200;
            }
            catch (TokenResponseException $e)
            {
               WriteError('Exception making REST request: ' . $e->getMessage());
               
               $errorText = $e->getMessage();
               
               $i = strpos($errorText, 'HTTP/1.1');
               if ($i !== false)
               {
                  $code = substr($errorText, $i+9, 3);
                  $errorText = substr($errorText, $i+13);
               }
            }
         }

         if ($code >= 200 && $code < 300)
         {
            return $result;
         }
         else if ($code == 401)
         {
            // need to log back in
            $this->RevokeAccessToken();
            
            throw new RestTokenExpiredException($code, $errorText);
         }
         else if ($code == 403)
         {
            if (strpos($errorText, 'Quota Exceeded') !== false)
               throw new RestQuotaExceededException($code, $errorText);
               
            throw new RestNoAccessException($code, $errorText);
         }
         else if ($code == 404 || $code == 410)
         {
            if ($code == 410 && strpos($errorText, 'Sync token is no longer valid') !== false)
               throw new RestSyncTokenExpiredException($code, $errorText);
            else
               throw new RestNotFoundException($code, $errorText);
         }
         else if (($code >= 500 && $code < 600) && $retryCount < 2)
         {
            // for Google API the recommendation for this situation is to retry after a short delay
            
            WriteInfo("REST request '$url' got a $code response with '$errorText', retrying after a delay.");            
   			usleep(500000);   // half second delay
            $retry = true;
         }
         else
         {
            WriteCallStack("Error");
            WriteVariable($response);
         
            throw new RestException($code, $errorText);
         }
      }
   }
}

?>

<?php

require_once(dirname(__FILE__) . '/DateAndTime.php');
require_once(dirname(__FILE__) . '/Html.php');
require_once(dirname(__FILE__) . '/Mime.php');
require_once(dirname(__FILE__) . '/MimeTypes.php');
require_once(dirname(__FILE__) . '/Utilities.php');

/*
===================================================================

Implementation.

===================================================================

This module implements a wrapper for a MIME email.

===================================================================
*/
	

class Message extends Mime
{
   public static $Folders = 
   array(
      'inbox'   => 'Inbox',      // received messages
      'sent'    => 'Sent Mail',  // sent messages
      'outbox'  => 'Outbox',     // messages waiting to be sent
      'drafts'  => 'Drafts',     // messages that are not yet completed
      'deleted' => 'Deleted'     // messages that have been deleted
   );
   
   // these are not persisted!
   protected $uid = NULL;
   protected $messageBoxUid = NULL;
   protected $isDeleted = false;
   protected $isRead = false;
   protected $isFlagged = false;
   protected $folder = 'inbox';  // from Folders above
   protected $lastUpdated = NULL;
   protected $url = NULL;

   protected $type = 'email';

   const TYPE_EMAIL   = 'email';
   const TYPE_FB_PAGE = 'fb_page';
   const TYPE_FB_MSG  = 'fb_msg';
   const TYPE_FB_AD   = 'fb_ad';
   const TYPE_FB_POST = 'fb_post';
   const TYPE_FB_COMMENT = 'fb_comment';
   const TYPE_FB_LIKE = 'fb_like';
   const TYPE_FB_SHARE = 'fb_share';

   private $types = [
      self::TYPE_EMAIL, self::TYPE_FB_PAGE, self::TYPE_FB_MSG, self::TYPE_FB_AD, self::TYPE_FB_POST,
      self::TYPE_FB_COMMENT, self::TYPE_FB_LIKE, self::TYPE_FB_SHARE
   ];
   
	function __construct()
	{
      parent::__construct();
	}
	
	function __destruct()
	{
      parent::__destruct();
	}

	function SetType($type)
   {
      if (!in_array($type, $this->types, true))
      {
         throw new Exception('Wrong type for email');
      }
      $this->type = $type;
   }

   // @todo ILA SA-170 rename to Type() for conformance
   function GetType()
   {
      $type = $this->type;
      if (is_null($type)) $type = self::TYPE_EMAIL;
      return $type;
   }

//	function ToBinary()
//	{
//		return $this->Mime::ToBinary();
//	}
	
//	function ToString()
//	{
//		return $this->Mime::ToString();
//	}
	
	static function FromBinary($binary, $notUsed = null)
	{
		return Mime::FromBinary($binary, new Message());
	}
	
	static function FromString($string, $notUsed = null, $notUsed2 = null)
	{
		return Mime::FromString($string, false, new Message());
	}

   public function IsDeleted()
   {
      return $this->isDeleted;
   }
   
   public function SetIsDeleted($val)
   {
      $this->isDeleted = $val;
   }
   
   public function IsRead()
   {
      return $this->isRead;
   }
   
   public function SetIsRead($val)
   {
      $this->isRead = $val;
   }
   
   public function IsFlagged()
   {
      return $this->isFlagged;
   }
   
   public function SetIsFlagged($val)
   {
      $this->isFlagged = $val;
   }
   
   public function Folder()
   {
      return $this->folder;
   }

   // this method takes a string from $Folders as in "inbox"   
   public function SetFolder($val)
   {
      $this->folder = $val;
   }
   
   public function Url()
   {
      return $this->url;
   }
   
   // this is an HTTP URL that will open a Web page directly to the message, or the message thread, it available
   public function SetUrl($val)
   {
      $this->url = $val;
   }
   
	function MessageID()
	{
      return $this->GetHeader('MessageID');
	}
	
	function SetMessageID($value)
	{
      $this->SetHeader('MessageID', $value);
	}
	
	function From()
	{
      return $this->GetHeader('From');
	}
	
	function SetFrom($value)
	{
      $this->SetHeader('From', $value);
	}
	
	function To()
	{
      $val = $this->GetHeader('To');
      if (empty($val))
         return array();
      return explode(',', $val);
	}
	
	function SetTo($value)
	{
      if (!is_array($value))
         $value = array($value);
         
      $this->SetHeader('To', implode(',', $value));
	}

   function SetReplyTo($value)
   {
      $this->SetHeader('Reply-To', $value);
   }

   function ReplyTo()
   {
      return $this->GetHeader('Reply-To');
   }

	function SetInReplyTo($value)
   {
      $this->SetHeader('In-Reply-To', $value);
   }

   function InReplyTo()
   {
      return $this->GetHeader('In-Reply-To');
   }
	
	function CC()
	{
      $val = $this->GetHeader('CC');
      if (empty($val))
         return array();
      return explode(',', $val);
	}
	
	function SetCC($value)
	{
      if (!is_array($value))
         $value = array($value);
         
      $this->SetHeader('CC', implode(',', $value));
	}
	
	function BCC()
	{
      $val = $this->GetHeader('BCC');
      if (empty($val))
         return array();
      return explode(',', $val);
	}
	
	function SetBCC($value)
	{
      if (!is_array($value))
         $value = array($value);
         
      $this->SetHeader('BCC', implode(',', $value));
	}
	
	function Date()
	{
      return DateAndTime::FromString($this->GetHeader('Date'));
	}
	
	function SetDate($value)
	{
      $this->SetHeader('Date', $value ? $value->ToFormat(DateAndTime::$LongFormat3) : NULL);
	}
	
	function Subject()
	{
      return $this->GetHeader('Subject');
	}
	
	function SetSubject($value)
	{
      $this->SetHeader('Subject', $value);
	}
   
   // returns an array
   function ListUnsubscribe()
   {
      $val = $this->GetHeader('List-Unsubscribe');
      $val = explode(',', $val);
      
      $result = array();
      foreach ($val as $item)
      {
         $result[] = Utilities::RemoveSurroundingAngleBrackets(Utilities::RemoveSurroundingSpaces($item));
      }
   }
   
   // this can take one or more items like:
   //    http://something.com?params
   //    mailto:someone@something.com
   function SetListUnsubscribe($value)
   {
      if (!is_array($value))
         $value = array($value);
      
      $val = '';
      foreach ($value as $item)
      {
         if (!empty($val))
            $val .= ', ';
         $val .= '<' . $item . '>';
      }
      $this->SetHeader('List-Unsubscribe', $val);
   }
   
   function TextBody()
	{
      $mime = $this->GetSegmentWithType('text/plain');
      return $mime ? $mime->GetSegmentContent(0) : NULL;
	}
	
	function HtmlBody()
	{
      $mime = $this->GetSegmentWithType('text/html');
      return $mime ? $mime->GetSegmentContent(0) : NULL;
	}
	
   // returns an array of MIME objects
	function Attachments()
	{
      $result = array();

      // get inline attachments
      $mime = $this->GetSegmentWithType('multipart/related');
      if ($mime)
      {
         foreach ($mime->Segments() as $segment)
         {
            if (strpos(strtolower($segment->GetHeader('Content-Type')), 'multipart/alternative') === false)
            {
               $result[] = $segment;
            }
         }
      }
      
      // get out-of-line attachments
      $mime = $this->GetSegmentWithType('multipart/mixed');
      if ($mime)
      {
         foreach ($mime->Segments() as $segment)
         {
            if (strpos(strtolower($segment->GetHeader('Content-Type')), 'multipart/related') === false)
            {
               $result[] = $segment;
            }
         }
      }
      
      return $result;
	}
	
   // - in order to save space, don't provide both body types if they weren't both provided at source
   // - attachments are an array of Mime instances
	function SetBody($htmlBody, $textBody, $attachments = NULL)
	{
      while ($this->GetSegmentCount() > 0)
      {
         $this->RemoveSegment(0);
      }
      
      $text = new Mime();
      $text->SetHeader('Content-Type', 'multipart/alternative');
      if (!empty($textBody))
         $text->AddSegment('text/plain', NULL, $textBody);
      if (!empty($htmlBody))
         $text->AddSegment('text/html', NULL, $htmlBody);
//      if (empty($textBody) && empty($htmlBody))
//         $text->AddSegment('text/plain', NULL, '');

      $body = new Mime();
      $body->SetHeader('Content-Type', 'multipart/related');
      $body->AddMimeSegment($text);
      
      // add inline attachments to the message body segment
      if ($attachments)
      {
         foreach ($attachments as $attachment)
         {
            $disp = strtolower($attachment->GetHeader('Content-Disposition'));
            if (strpos($disp, 'inline') !== false)
            {
               $body->AddMimeSegment($attachment);
            }
         }
      }

      $this->SetHeader('Content-Type', 'multipart/mixed');
      $this->AddMimeSegment($body);
      
      // add out-of-line attachments to the outer segment
      if ($attachments)
      {
         foreach ($attachments as $attachment)
         {
            $disp = strtolower($attachment->GetHeader('Content-Disposition'));
            if (strpos($disp, 'inline') === false)
            {
               $this->AddMimeSegment($attachment);
            }
         }
      }
	}
   
   // The following methods are here to support the sync code...
   
	function Uid()
	{
      return $this->uid ? $this->uid : $this->GetHeader('MessageID');
	}
	
   // if the third party source has a UID that is different from the MessageID this can be used to store it
	function SetUid($value)
	{
      $this->uid = $value;
	}
	
	function MessageBoxUid()
	{
      return $this->messageBoxUid;
	}
	
	function SetMessageBoxUid($value)
	{
      $this->messageBoxUid = $value;
	}
	
   public function LastUpdated()
   {
      return $this->lastUpdated ? $this->lastUpdated : $this->Date();
   }
   
   // if the third party source has a concept of the last modified date this can be used to store it, our 
   // internal code will update this for changes such as when the item is deleted or flagged/unflagged
   public function SetLastUpdated($val)
   {
      $this->lastUpdated = $val;
   }

   // copy from another Message, optionally only certain fields, and 
   // optionally merge (combine collections such as addresses from both, 
   // don't remove a value if it is not set in the source, etc.)
   // These are never copied:
   //   IsDeleted
	//   LastUpdated
	//   Uid
   function CopyFrom($source, $fields = NULL, $replace = true)
   {
       $fields = Utilities::ExecuteCallbacksAndGetRemainingFields($this, $source, $fields, $replace);

      $temp = $source->ToString();
   	Mime::FromString($temp, false, $this);

	   if ((!$fields || Utilities::ArrayContains($fields, 'IsRead')) && ($replace || $source->IsRead())) $this->SetIsRead($source->IsRead());
	   if ((!$fields || Utilities::ArrayContains($fields, 'IsFlagged')) && ($replace || $source->IsFlagged())) $this->SetIsFlagged($source->IsFlagged());
	   if ((!$fields || Utilities::ArrayContains($fields, 'Folder')) && ($replace || $source->Folder())) $this->SetFolder($source->Folder());
	   if ((!$fields || Utilities::ArrayContains($fields, 'Url')) && ($replace || $source->Url())) $this->SetUrl($source->Url());
   }

   // returns whether the passed item is fairly certain to be the 
   // same as this one and therefore they should be combined
   function IsSimilarTo($item)
   {
      $same = 0;
      $diff = 0;
      if (!empty($this->MessageID())) { if ($this->MessageID() == $item->MessageID()) $same++; else if (!empty($item->MessageID())) $diff++; }
      if (!empty($this->Date())) { if ($this->Date() == $item->Date()) $same++; else if (!empty($item->Date())) $diff++; }
      if (!empty($this->Subject())) { if ($this->Subject() == $item->Subject()) $same++; else if (!empty($item->Subject())) $diff++; }
      
      return $diff == 0 && $same > 0;
   }
}

/*
===================================================================

	This is code I use for testing this module. Use as an example.

===================================================================
*/

if (0)
{
   $date = DateAndTime::Now();
   // milliseconds aren't saved so ignore them...
   $date->SetMillisecond(0);

	$msg = new Message();

   $bcc = $msg->BCC();
	if (count($bcc) != 0)
	{
		WriteDie("Error with BCC");
	}

   $msg->SetUid('<ABC@test.com>');
   $msg->SetMessageID('<1234@local.machine.example>');
	$msg->SetSubject(NULL);
   $msg->SetIsDeleted(false);
   $msg->SetIsRead(true);
   $msg->SetIsFlagged(false);
   $msg->SetLastUpdated($date);
	$msg->SetDate($date);
   $msg->SetTo(array('John Doe <jdoe@machine.example>', 'Mary Smith <mary@example.net>'));
   $msg->SetCC('saltyfoam@hotmail.com');
   $msg->SetBCC(array('Sue <sue@machine.example>', '<mandy@example.net>'));
   
   $attachments = array(
      Mime::FromString(
"Content-Type: image/jpeg; name=\"sam.jpg\"
Content-Transfer-Encoding: 7bit
Content-Disposition: inline; filename=\"sam.jpg\"

Blah1Blah1Blah1", false),   // we must pass "false" so that the Content-Disposition remains in the root MIME object
      Mime::FromString(
"Content-Type: application/pdf; name=\"doc.pdf\"
Content-Transfer-Encoding: 7bit
Content-Disposition: attachment; filename=\"doc.pdf\"

Blah2Blah2Blah2", false),   // we must pass "false" so that the Content-Disposition remains in the root MIME object
   );
   
   $msg->SetBody('<P>This is the HTML version!</P>', NULL, $attachments);

	if ($msg->Uid() != '<ABC@test.com>')
	{
		WriteDie("Error with SetUid()");
	}
	if ($msg->MessageID() != '<1234@local.machine.example>')
	{
		WriteDie("Error with SetMessageID()");
	}
	if ($msg->Subject() != NULL)
	{
		WriteDie("Error with SetSubject()");
	}
	$msg->SetSubject('The Subject');
	if ($msg->Subject() != "The Subject")
	{
		WriteDie("Error with SetSubject()");
	}
   if ($msg->IsDeleted() != false)
   {
		WriteDie("Error with SetIsDeleted()");
   }
   if ($msg->IsRead() != true)
   {
		WriteDie("Error with SetIsRead()");
   }
   if ($msg->IsFlagged() != false)
   {
		WriteDie("Error with SetIsFlagged()");
   }
   if (DateAndTime::Compare($msg->LastUpdated(), $date) != 0)
   {
		WriteDie("Error with SetLastUpdated()");
   }
   
   $str = $msg->ToString();
   $msg2 = Message::FromString($str);
   
	if ($msg2->Uid() != "<1234@local.machine.example>")
	{
		WriteDie("Error with UID");
	}
	if ($msg2->MessageID() != "<1234@local.machine.example>")
	{
		WriteDie("Error with MessageID");
	}
   $date2 = $msg2->Date();
	if (DateAndTime::Compare($date2, $date) != 0)
	{
		WriteDie("Error with Date");
	}
	if ($msg2->Subject() != "The Subject")
	{
		WriteDie("Error with Subject");
	}
   $to = $msg2->To();
	if (count($to) != 2 || 
      $to[0] != "John Doe <jdoe@machine.example>" ||
      $to[1] != "Mary Smith <mary@example.net>")
	{
		WriteDie("Error with To");
	}
   $cc = $msg2->CC();
	if (count($cc) != 1 || 
      $cc[0] != "saltyfoam@hotmail.com")
	{
		WriteDie("Error with CC");
	}
   $bcc = $msg2->BCC();
	if (count($bcc) != 2 || 
      $bcc[0] != "Sue <sue@machine.example>" ||
      $bcc[1] != "<mandy@example.net>")
	{
		WriteDie("Error with BCC");
	}
	if ($msg2->TextBody() != NULL)
	{
		WriteDie("Error with TextBody");
	}
	if ($msg2->HtmlBody() != "<P>This is the HTML version!</P>")
	{
		WriteDie("Error with HtmlBody");
	}
	if (count($msg2->Attachments()) != 2)
	{
		WriteDie("Error with Attachments");
	}
}


?>

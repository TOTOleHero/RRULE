<?php


/*
===================================================================

Implementation.

===================================================================

*/
	

class ApiBase
{
   /** @var Rest */
   protected $Rest;
		
   /**
   * SPL-compatible autoloader.
   *
   * @param string $className Name of the class to load.
   *
   * @return boolean
   public static function autoload($className)
   {
      if ($className != 'ApiBase')
      {
         return false;
      }
      return include str_replace('_', '/', $className) . '.php';
   }
   */

   public $CURSOR_LIMIT = 1000;

	function __construct()
	{
	}
	
	function __destruct()
	{
	}

   function GetDataTypes()
   {
	   return [];
   }

   // Returns a list of fields that are supported by this data source.
   // The field is the name of the property on the object such as 'Nickname'
   // for the Nickname property on the vCard object.
   function SupportedFields($dataType) { return []; }

   /*
    * ILA SA-4 Implementation. The default for every dateType is that is not read only.
    * If you need to declare a datatype as read-only then override that method and return true for those datatypes.
    */
	function IsReadOnlyDataType($dataType) {
	   return false;
   }
   
   // this method will not return if the user needs to log in
   function CheckLogin($currentUri=NULL)
   {
      $this->Rest->CheckLogin($currentUri);
   }
   
   function Logout()
   {
      $this->Rest->Logout();
   }
   
   function IsLoggedIn()
   {
      return $this->Rest->IsLoggedIn();
   }
   
   // used to save the token while online in order to use it later while offline
   function GetAccessToken()
   {
      return $this->Rest->GetAccessToken();
   }
   
   // used when offline
   function SetAccessToken($accessToken)
   {
      $this->Rest->SetAccessToken($accessToken);
   }
   
   // used when logging out or when changing scopes
   function RevokeAccessToken()
   {
      $this->Rest->RevokeAccessToken();
   }
   
   // Lets provide an API where the data type is opaque to simplify some code...
   
   // calendarUid is only used for the "events", "tasks" and "messages" data types
   function GetItems($dataType, $calendarID = NULL, $sinceETag = NULL, &$etag = NULL, &$cursor = NULL)
   {
      if ($dataType == 'contact groups')
         return $this->GetContactGroups($sinceETag, $etag);
      if ($dataType == 'contacts')
         return $this->GetContacts($sinceETag, $etag);
      if ($dataType == 'calendars')
         return $this->GetCalendars($sinceETag, $etag);
      if ($dataType == 'events')
         return $this->GetEvents($calendarID, $sinceETag, $etag);
      if ($dataType == 'task lists')
         return $this->GetTaskLists($sinceETag, $etag);
      if ($dataType == 'tasks')
         return $this->GetTasks($calendarID, $sinceETag, $etag);
      if ($dataType == 'message boxes')
         return $this->GetMessageBoxes($sinceETag, $etag);
      if ($dataType == 'messages')
         return $this->GetMessages($calendarID, $sinceETag, $etag, $cursor);
         
      WriteError("Unrecognized data type: $dataType");
      exit(1);
   }
   
   // calendarUid is only used for the "events", "tasks" and "messages" data types
   function UpdateItem($dataType, $item, $calendarID = NULL)
   {
      if ($dataType == 'contact groups')
         return $this->UpdateContactGroup($item);
      if ($dataType == 'contacts')
         return $this->UpdateContact($item);
      if ($dataType == 'calendars')
         return $this->UpdateCalendar($item);
      if ($dataType == 'events')
         return $this->UpdateEvent($item, $calendarID);
      if ($dataType == 'task lists')
         return $this->UpdateTaskList($item);
      if ($dataType == 'tasks')
         return $this->UpdateTask($item, $calendarID);
      if ($dataType == 'message boxes')
         return $this->UpdateMessageBox($item);
      if ($dataType == 'messages')
         return $this->UpdateMessage($item, $calendarID);
         
      WriteError("Unrecognized data type: $dataType");
      exit(1);
   }
   
   function DeleteItem($dataType, $ID, $calendarID = NULL)
   {
      if ($dataType == 'contact groups')
         return $this->DeleteContactGroup($ID);
      if ($dataType == 'contacts')
         return $this->DeleteContact($ID);
      if ($dataType == 'calendars')
         return $this->DeleteCalendar($ID);
      if ($dataType == 'events')
         return $this->DeleteEvent($ID, $calendarID);
      if ($dataType == 'task lists')
         return $this->DeleteTaskList($ID);
      if ($dataType == 'tasks')
         return $this->DeleteTask($ID, $calendarID);
      if ($dataType == 'message boxes')
         return $this->DeleteMessageBox($ID);
      if ($dataType == 'messages')
         return $this->DeleteMessage($ID, $calendarID);
         
      WriteError("Unrecognized data type: $dataType");
      exit(1);
   }
   
   // Deleting an item can usually be performed by updating it with the deleted flag set...
   
   function DeleteContactGroup($ID)
   {
      $item = new vCardGroup();
      $item->SetUid($ID);
      $item->SetIsDeleted(true);
      return $this->UpdateContactGroup($item);
   }
   
   function DeleteContact($ID)
   {
      $item = new vCard();
      $item->SetUid($ID);
      $item->SetIsDeleted(true);
      return $this->UpdateContact($item);
   }
   
   function DeleteCalendar($ID)
   {
      $item = new iCalCalendar();
      $item->SetUid($ID);
      $item->SetIsDeleted(true);
      return $this->UpdateCalendar($item);
   }
   
   function DeleteEvent($ID, $calendarID)
   {
      $item = new iCalEvent();
      $item->SetUid($ID);
      $item->AddCalendar($calendarID);
      $item->SetIsDeleted(true);
      return $this->UpdateEvent($item, $calendarID);
   }
   
   function DeleteTaskList($ID)
   {
      $item = new iCalCalendar();
      $item->SetUid($ID);
      $item->SetIsDeleted(true);
      return $this->UpdateTaskList($item);
   }
   
   function DeleteTask($ID, $taskListID)
   {
      $item = new iCalEvent();
      $item->SetType(iCalEvent::$TaskType);
      $item->SetUid($ID);
      $item->SetTaskList($taskListID);
      $item->SetIsDeleted(true);
      return $this->UpdateTask($item, $taskListID);
   }
   
   function DeleteMessageBox($ID)
   {
      $item = new MessageBox();
      $item->SetUid($ID);
      $item->SetIsDeleted(true);
      return $this->UpdateMessageBox($item);
   }
   
   function DeleteMessage($ID, $messageBoxID)
   {
      $item = new Message();
      $item->SetUid($ID);
      $item->SetIsDeleted(true);
      return $this->UpdateMessage($item, $messageBoxID);
   }
   
   // this is used for those data sources that don't support 
   // notifying of deleted and/or changed items
   static function RemoveUnchangedAndAddDeletedItems($dataType, &$etag, &$items)
   {
      $oldDate = NULL;
      $oldItems = array();
      if ($etag)
      {
         $etag = json_decode($etag, true);
         $oldDate = DateAndTime::FromString($etag['SyncDate']);
         $oldItems = array_flip(explode(',', $etag['ItemList']));
      }
      $newDate = $oldDate;
      $newItems = array();
      
      $len = count($items);   // count will change below so save it
      for ($i = 0; $i < $len; $i++)
      {
         $id = $items[$i]->Uid();
         $updated = $items[$i]->LastUpdated();
         
         if (!$items[$i]->IsDeleted())
         {
            $newItems[$id] = true;
            unset($oldItems[$id]);
         }
         
         if ($oldDate != NULL && DateAndTime::GreaterThanOrEqual($oldDate, $updated))
         {
            // item hasn't changed since last time we checked
            unset($items[$i]);
         }
         if ($newDate == NULL || DateAndTime::GreaterThan($updated, $newDate))
         {
            $newDate = $updated;
         }
      }
      
      $items = array_values($items); // 'reindex' array

      // add deleted items
      foreach ($oldItems as $id => $dummy)
      {
         $item = new vCard();   // DRL Hopefully the data type doesn't matter!
         $item->SetUid($id);
         $item->SetIsDeleted(true);
         $items[] = $item;
      }

      // we need to return an etag even for an empty list, so do so here
      if ($newDate == NULL)
         $newDate = DateAndTime::Subtract(DateAndTime::Now(0), DateAndTime::$SecondsPerDay);
      
      $etag = array();
      $etag['SyncDate'] = $newDate->ToFormat(DateAndTime::$DefaultFormat);
      $etag['ItemList'] = implode(',', array_keys($newItems));
      $etag = json_encode($etag);
   }
};

//spl_autoload_register(array('ApiBase', 'autoload'));

?>

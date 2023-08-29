<?php

namespace ICT\Core;

use ICT\Core\Contact;

/* * ***************************************************************
 * Copyright © 2014 ICT Innovations Pakistan All Rights Reserved   *
 * Developed By: Nasir Iqbal                                       *
 * Website : http://www.ictinnovations.com/                        *
 * Mail : nasir@ictinnovations.com                                 *
 * *************************************************************** */

class Campaign
{
  /** @const */
  const STATUS_NEW = 'new';
  const STATUS_READY = 'ready';
  const STATUS_RUNNING = 'running';
  const STATUS_PAUSED = 'paused';
  const STATUS_COMPLETED = 'completed';
  const STATUS_BROKEN = 'broken';

  private static $table = 'campaign';
  private static $primary_key = 'campaign_id';
  private static $fields = array(
      'campaign_id ',
      'program_id',
      'group_id',
      'cpm',
      'try_allowed',
      'account_id',
      'status',
      'pid',
      'last_run',
      'source',
  );
  private static $read_only = array(
      'campaign_id',
      'status'
  );

  /**
   * @property-read integer $campaign_id
   * @var integer
   */
  public $campaign_id = NULL;

  /** @var integer */
  public $program_id = NULL;

  /** @var integer */
  public $group_id = NULL;

  /** @var string */
  public $cpm = NULL;

  /** @var string */
  public $try_allowed = NULL;

  /** @var integer */
  public $account_id = NULL;

  /** @var string */
  public $status = Campaign::STATUS_NEW;

  /** @var string */
  public $source = '';

  /** @var integer */
  public $created_by = NULL;
  
  public function __construct($campaign_id = NULL)
  {
    if (!empty($campaign_id) && $campaign_id > 0) {
      $this->campaign_id = $campaign_id;
      $this->load();
    } 
  }
  
  public static function search($aFilter = array())
  {
    $aCampaign = array();
    $from_str = self::$table . " c LEFT JOIN program p ON c.program_id=p.program_id";
    $aWhere = array();
    foreach ($aFilter as $search_field => $search_value) {
      switch ($search_field) {
        case 'campaign_id':
          $aWhere[] = "c.$search_field = $search_value";
          break;
        case 'program_id':
          $aWhere[] = "c.$search_field LIKE '%$search_value'";
          break;
        case 'group_id':
          $aWhere[] = "c.$search_field = '$search_value'";
          break;
        case 'status':
          $aWhere[] = "c.$search_field LIKE '%$search_value%'";
          break;

        case 'user_id':
        case 'created_by':
          $aWhere[] = "c.created_by = '$search_value'";
          break;
        case 'before':
          $aWhere[] = "c.date_created <= $search_value";
          break;
        case 'after':
          $aWhere[] = "c.date_created >= $search_value";
          break;
      }
    }
    if (!empty($aWhere)) {
      $from_str .= ' WHERE ' . implode(' AND ', $aWhere);
    }
    $query = "SELECT c.*, p.type AS program_type FROM " . $from_str;
    Corelog::log("Campaign search with $query", Corelog::DEBUG, array('aFilter' => $aFilter));
    $result = DB::query('Campaign', $query);
    while ($data = mysqli_fetch_assoc($result)) {
      $aCampaign[] = $data;
    }

    return $aCampaign;
  }

  private function load()
  {
    $query = "SELECT * FROM " . self::$table . " WHERE campaign_id='%campaign_id%' ";
    $result = DB::query(self::$table, $query, array('campaign_id' => $this->campaign_id));
    $data = mysqli_fetch_assoc($result);
    if ($data) {
      $this->campaign_id = $data['campaign_id'];
      $this->program_id  = $data['program_id'];
      $this->group_id    = $data['group_id'];
      $this->cpm         = $data['cpm'];
      $this->try_allowed = $data['try_allowed'];
      $this->account_id  = $data['account_id'];
      $this->status      = $data['status'];
      $this->user_id     = $data['created_by'];
      Corelog::log("Campaign loaded id: $this->campaign_id $this->status", Corelog::CRUD);
    } else {
      throw new CoreException('404', 'Campaign not found');
    }
  }

  public function delete()
  {
    $this->task_cancel();
    Corelog::log("Campaign delete", Corelog::CRUD);
    return DB::delete(self::$table, 'campaign_id', $this->campaign_id);
  }

  public function __isset($field)
  {
    $method_name = 'isset_' . $field;
    if (method_exists($this, $method_name)) {
      return $this->$method_name();
    } else {
      return isset($this->$field);
    }
  }

  public function __get($field)
  {
    $method_name = 'get_' . $field;
    if (method_exists($this, $method_name)) {
      return $this->$method_name();
    } else if (!empty($field) && isset($this->$field)) {
      return $this->$field;
    }
    return NULL;
  }

  public function __set($field, $value)
  {
    $method_name = 'set_' . $field;
    if (method_exists($this, $method_name)) {
      $this->$method_name($value);
    } else if (empty($field) || in_array($field, self::$read_only)) {
      return;
    } else {
      $this->$field = $value;
    }
  }

  public function set_cpm($cpm)
  {
    $this->cpm = $cpm;
  }

  public function get_cpm()
  {
    return $this->cpm;
  }

  public function get_id()
  {
    return $this->campaign_id;
  }

  public function save()
  {
    $oAccount = new Account(Account::USER_DEFAULT);
    if ($this->account_id < 1) {
      $this->account_id = $oAccount->account_id;
    }
    if ($oAccount->setting_read('crmsettings', 'disabled') == 'ictcrm') {
       $this->fetch_remote_group();
    }
    $data = array(
        'campaign_id' => $this->campaign_id,
        'program_id' => $this->program_id,
        'group_id' => $this->group_id,
        'cpm' => $this->cpm,
        'try_allowed' => $this->try_allowed,
        'account_id' => $this->account_id,
        'status' => $this->status,
        'source' => $this->source
    );

    if (isset($data['campaign_id']) && !empty($data['campaign_id'])) {
      // update existing record
      $result = DB::update(self::$table, $data, 'campaign_id');
      Corelog::log("Campaign updated: $this->campaign_id", Corelog::CRUD);
    } else {
      // add new
      $result = DB::update(self::$table, $data, false);
      $this->campaign_id = $data['campaign_id'];
      Corelog::log("New Campaign created: $this->campaign_id", Corelog::CRUD);
    }
    return $result;
  }

  public function start()
  {
    return $this->daemon('start');
  }

  public function stop()
  {
    return $this->daemon('stop');
  }

  public function reload()
  {
    return $this->daemon('reload');
  }

  public function daemon($action = 'start')
  {
    global $path_root;
    $daemon = $path_root . DIRECTORY_SEPARATOR . 'bin' . DIRECTORY_SEPARATOR . 'campaign';
    /* Excutable file start / stop deamon */
    $output = array();
    $result = false;
    exec("$daemon $this->campaign_id $action", $output, $result);
    if ($result != 0) {
      return false;
    } else {
      return $this->campaign_id;
    }
  }

  public function task_cancel()
  {
    $aSchedule = Schedule::search(array('type'=>'campaign', 'data'=>$this->campaign_id));
    foreach ($aSchedule as $schedule) {
      $oSchedule = new Schedule($schedule['task_id']);
      $oSchedule->delete();
    }
  }
   
  public function fetch_remote_group() {
    $url = Conf::get('crm:url', '');
    if (!empty($url)) {
      $username = Conf::get('crm:username', '');
      $password = Conf::get('crm:password', '');

      $login_parameters = array(
         "user_auth" => array(
              "user_name" => $username,
              "password" => md5($password),
              "version" => "1"
         ),
         "application_name" => "RestTest",
         "name_value_list" => array(),
     );

     $login_result =  \ICT\Core\Group::call("login", $login_parameters, $url);

     $leads = $this->get_relationship_contacts($login_result->id, $this->group_id, 'leads', $url);

     $contacts = $this->get_relationship_contacts($login_result->id, $this->group_id, 'contacts', $url);

     $prospects = $this->get_relationship_contacts($login_result->id, $this->group_id, 'prospects', $url);

     $group_data = array (
        "name" => "Group" . time(),
        "description" => "remote"
     );

     $result = DB::update('contact_group', $group_data, false);
     $this->group_id = $group_data['contact_group_id']; // NOTE: DB::update using table name suffixed with _id as primary key
     Corelog::log("New group created: $this->group_id", Corelog::CRUD);

     $aContact = array();

     $aContact = $this->process_each_list($leads, $aContact);

     $aContact = $this->process_each_list($contacts, $aContact);

     $aContact = $this->process_each_list($prospects, $aContact);

     foreach($aContact as $contact_entry) {
       $oContact = new Contact();
       $oContact->first_name = $contact_entry['first_name'];
       $oContact->last_name = $contact_entry['last_name'];
       $oContact->phone = $contact_entry['phone'];
       $oContact->email = $contact_entry['email'];
       $oContact->save();
       $oContact->link($this->group_id);
     }
   }
   else {
     throw new CoreException(411, "CRM Not configured");   
   }

  }

  public function get_relationship_contacts($session_id, $module_id, $relationship_name, $url) {

    //get session id
    $session_id = $session_id;

    //retrieve related prospect list ------------------------------ 

    $get_relationships_parameters = array(

         'session'=>$session_id,

         //The name of the module from which to retrieve records.
         'module_name' => 'ProspectLists',

         //The ID of the specified module bean.
         'module_id' => $module_id,

         //The relationship name of the linked field from which to return records.
         'link_field_name' => $relationship_name,

         //The portion of the WHERE clause from the SQL statement used to find the related items.
         'related_module_query' => '',

         //The related fields to be returned.
         'related_fields' => array(
            'id',
            'first_name',
            'last_name',
            'phone_mobile',
            'email',
            'email1'
         ),

         //For every related bean returned, specify link field names to field information.
         'related_module_link_name_to_fields_array' => array(
         ),

         //To exclude deleted records
         'deleted'=> '0',

         //order by
         'order_by' => '',

         //offset
         'offset' => 0
    );

    $get_relationships_result =  \ICT\Core\Group::call("get_relationships", $get_relationships_parameters, $url);
    return $get_relationships_result;

    }

    public function process_each_list($module_name, $aContact) {

      foreach($module_name->entry_list as $entry) {

        $data = array(
          "first_name" => $entry->name_value_list->first_name->value,
          "last_name" => $entry->name_value_list->last_name->value,
          "phone" => $entry->name_value_list->phone_mobile->value,
          "email" => $entry->name_value_list->email1->value
        );
Corelog::log(print_r($data, true), Corelog::ERROR);    
//  Corelog::log("line number 195".print_r($_instance, true), Corelog::ERROR);


        array_push($aContact , $data);
      }
      return $aContact;

    }
 
}

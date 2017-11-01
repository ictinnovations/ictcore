<?php
namespace ICT\Core;
/* * ***************************************************************
 * Copyright © 2014 ICT Innovations Pakistan All Rights Reserved   *
 * Developed By: Nasir Iqbal                                       *
 * Website : http://www.ictinnovations.com/                        *
 * Mail : nasir@ictinnovations.com                                 *
 * *************************************************************** */
use Exception;
use ICT\Core\Contact;
class Campaign
{
  /** @const */
  const COMPANY = -2;
  private static $table = 'campaign';
  private static  $trans_table = 'transmission';
  private static $primary_key = 'campaign_id';
  private static $fields = array(
      'campaign_id ',
      'program_id',
      'group_id',
      'delay',
      'try_allowed',
      'account_id',
      'created_by',
      'status',
      'pid',
      'last_run'
  );
  private static $read_only = array(
      'campaign_id',
      'status'
  );
  /**
   * @property-read integer $campaign_id
   * @var integer
   */
  private $campaign_id = NULL;
  /** @var integer */
  public $program_id = NULL;
  /** @var integer */
  public $group_id = NULL;
  /** @var string */
  public $delay = NULL;
  /** @var string */
  public $try_allowed = NULL;
  /** @var integer */
  public $account_id = NULL;
  /** @var string */
  public $status = NULL;
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
    $from_str = self::$table;
    $aWhere = array();
    foreach ($aFilter as $search_field => $search_value) {
      switch ($search_field) {
        case 'campaign_id':
          $aWhere[] = "$search_field = $search_value";
          break;
        case 'program_id':
          $aWhere[] = "$search_field LIKE '%$search_value'";
          break;
        case 'group_id':
          $aWhere[] = "$search_field = '$search_value'";
          break;
        case 'status':
          $aWhere[] = "$search_field LIKE '%$search_value%'";
          break;
      }
    }
    if (!empty($aWhere)) {
      $from_str .= ' WHERE ' . implode(' AND ', $aWhere);
    }
    $query = "SELECT * FROM " . $from_str;
    Corelog::log("Campaign search with $query", Corelog::DEBUG, array('aFilter' => $aFilter));
    $result = DB::query('Campaign', $query);
    while ($data = mysql_fetch_assoc($result)) {
      $aCampaign[] = $data;
    }
    // if no Campaign found, check for special Campaigns
    /*if (empty($aCampaign) && isset($aFilter['campaign_id']) && $aFilter['campaign_id'] == Campaign::COMPANY) {
      $oCampaign = new Campaign($aFilter['campaign_id']);
      $aCampaign[$oCampaign->campaign_id] = array(
          'campaign_id' => $oCampaign->campaign_id,
          'program_id' => $oCampaign->program_id,
          'group_id' => $oCampaign->group_id,
          'status' => $oCampaign->status
      );
    }*/
    return $aCampaign;
  }
  private function load()
  {
    $query = "SELECT * FROM " . self::$table . " WHERE campaign_id='%campaign_id%' ";
    $result = DB::query(self::$table, $query, array('campaign_id' => $this->campaign_id), true);
    $data = mysql_fetch_assoc($result);
    if ($data) {
      $this->campaign_id = $data['campaign_id'];
      $this->program_id  = $data['program_id'];
      $this->group_id    = $data['group_id'];
      $this->delay       = $data['delay'];
      $this->try_allowed = $data['try_allowed'];
      $this->account_id  = $data['account_id'];
      $this->status      = $data['status'];
      $this->created_by      = $data['created_by'];
      Corelog::log("Campaign loaded id: $this->campaign_id $this->status", Corelog::CRUD);
    } else {
      throw new CoreException('404', 'Campaign not found');
    }
  }
  public function delete()
  {
     $this->task_cancel();
    Corelog::log("Campaign delete", Corelog::CRUD);
    return DB::delete(self::$table, 'campaign_id', $this->campaign_id, true);
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
  public function get_id()
  {
    return $this->campaign_id;
  }
  /*public function email_to_phone()
  {
    $aEmail = imap_rfc822_parse_adrlist($this->email, Conf::get('sendmail:domain', 'localhost'));
    $strPhone = $aEmail[0]->mailbox; // we are only interested in 1st (0) part of aEmail list
    $this->phone = preg_replace("/[^0-9]/", "", $strPhone); // keep only digits
    return $this->phone;
  }
  public function phone_to_email()
  {
    $strEmail = $this->phone . '@' . Conf::get('sendmail:domain', 'localhost');
    $this->email = $strEmail;
    return $this->email;
  }*/
  public function save()
  {
    $data = array(
        'campaign_id' => $this->campaign_id,
        'program_id' => $this->program_id,
        'group_id' => $this->group_id,
        'delay' => $this->delay,
        'try_allowed' => $this->try_allowed,
        'account_id' => $this->account_id,
        'status' => $this->status
    );
    if (isset($data['campaign_id']) && !empty($data['campaign_id'])) {
      // update existing record
      $result = DB::update(self::$table, $data, 'campaign_id', true);
      Corelog::log("Campaign updated: $this->campaign_id", Corelog::CRUD);
    } else {
      // add new
     $act_id = $this->account_id;
     $result = DB::update(self::$table, $data, false, true);
     $this->campaign_id = $data['campaign_id'];
     Corelog::log("New Campaign created: $this->campaign_id", Corelog::CRUD);
    }
    return $result;
  }
  public function start()
  {
        /* Excutable file  Start deamon */
          exec(dirname(__DIR__)."/bin/campaign.php $this->campaign_id start", $output);
        /* Update campaign process id and last run */
         $query_campiagn = "UPDATE campaign set pid=".posix_getpid() .",last_run=" .time(). " where campaign_id =".$this->campaign_id;
         $res = mysql_query($query_campaign);
         if (mysql_errno()) {

            Corelog::log("Campaign update failed", Corelog::CRUD);
          } 
    return $output[0];
  }

  public function stop()
  {
    /* Excutable file  stop deamon */
     exec(dirname(__DIR__)."/bin/campaign.php $this->campaign_id  stop", $output);
     return $output[0];
  }

  public function task_cancel()
  {
      $aSchedule = Schedule::search(array('type'=>'campaign', 'data'=>$this->campaign_id));
      foreach ($aSchedule as $schedule) 
      {
        $oSchedule = new Schedule($schedule['task_id']);
        $oSchedule->delete();
      }
  }
 
}
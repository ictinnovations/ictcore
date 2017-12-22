<?php

namespace ICT\Core;

/* * ***************************************************************
 * Copyright © 2014 ICT Innovations Pakistan All Rights Reserved   *
 * Developed By: Nasir Iqbal                                       *
 * Website : http://www.ictinnovations.com/                        *
 * Mail : nasir@ictinnovations.com                                 *
 * *************************************************************** */

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
  public $campaign_id = NULL;

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
      }
    }
    if (!empty($aWhere)) {
      $from_str .= ' WHERE ' . implode(' AND ', $aWhere);
    }
    $query = "SELECT c.*, p.type AS program_type FROM " . $from_str;
    Corelog::log("Campaign search with $query", Corelog::DEBUG, array('aFilter' => $aFilter));
    $result = DB::query('Campaign', $query);
    while ($data = mysql_fetch_assoc($result)) {
      $aCampaign[] = $data;
    }

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
      $result = DB::update(self::$table, $data, false, true);
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

  public function daemon($action = 'start')
  {
    global $path_root;
    $daemon = $path_root . DIRECTORY_SEPARATOR . 'bin' . DIRECTORY_SEPARATOR . 'campaign';
    /* Excutable file start / stop deamon */
    $output = null;
    exec("$daemon $this->campaign_id  $action", $output);
    return $output[0];
  }

  public function task_cancel()
  {
    $aSchedule = Schedule::search(array('type'=>'campaign', 'data'=>$this->campaign_id));
    foreach ($aSchedule as $schedule) {
      $oSchedule = new Schedule($schedule['task_id']);
      $oSchedule->delete();
    }
  }
 
}

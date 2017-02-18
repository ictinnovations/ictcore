<?php

namespace ICT\Core;

/* * ***************************************************************
 * Copyright © 2014 ICT Innovations Pakistan All Rights Reserved   *
 * Developed By: Nasir Iqbal                                       *
 * Website : http://www.ictinnovations.com/                        *
 * Mail : nasir@ictinnovations.com                                 *
 * *************************************************************** */

class Spool
{

  /** @const */
  const STATUS_QUEUED = 'queued';
  const STATUS_CONNECTED = 'connected';
  const STATUS_DONE = 'done'; // when we are not sure if failed or completed
  const STATUS_COMPLETED = 'completed';
  const STATUS_FAILED = 'failed';
  const STATUS_INVALID = 'invalid';

  private static $doneStatus = array(
      Spool::STATUS_COMPLETED,
      Spool::STATUS_FAILED,
      Spool::STATUS_INVALID
  );
  // **************************************************** Spool related data */
  private static $table = 'spool';
  private static $primary_key = 'spool_id';
  private static $fields = array(
      'spool_id',
      'time_spool',
      'time_start',
      'time_connect',
      'time_end',
      'call_id',
      'status',
      'response',
      'amount',
      'service_flag',
      'transmission_id',
      'provider_id',
      'node_id',
      'account_id'
  );
  private static $read_only = array(
      'spool_id',
      'time_spool',
      'time_start',
      'time_connect',
      'time_end'
  );

  /**
   * @property-read integer $spool_id
   * @var integer
   */
  private $spool_id = NULL;

  /**
   * @property-read integer $time_spool
   * @var integer
   */
  private $time_spool = NULL;

  /**
   * @property-read integer $time_start
   * @var integer
   */
  private $time_start = NULL;

  /**
   * @property-read integer $time_connect
   * @var integer
   */
  private $time_connect = NULL;

  /**
   * @property-read integer $time_end
   * @var integer
   */
  private $time_end = NULL;

  /** @var string */
  public $call_id = NULL;

  /**
   * @property string $status
   * @see Spool::set_status()
   * @var string */
  private $status = NULL;

  /** @var string */
  public $response = NULL;

  /** @var float */
  public $amount = NULL;

  /** @var integer */
  public $service_flag = NULL;

  /** @var integer */
  public $transmission_id = NULL;

  /** @var integer */
  public $provider_id = 0;

  /** @var integer */
  public $node_id = NULL;

  /** @var integer */
  public $account_id = NULL;

  public function __construct($spool_id = null)
  {
    if (!empty($spool_id) && ctype_digit(trim($spool_id))) {
      $this->spool_id = $spool_id;
      $this->load();
    } else {
      // create spool entry for this attempt
      $this->node_id = 1; // TODO
      $this->status = Spool::STATUS_QUEUED;
      $this->time_spool = time();
    }
  }

  public static function search($aFilter = array())
  {
    $aSpool = array();
    $from_str = self::$table;
    $aWhere = array();
    foreach ($aFilter as $search_field => $search_value) {
      switch ($search_field) {
        case 'spool_id':
        case 'account_id':
        case 'transmission_id':
          $aWhere[] = "$search_field = $search_value";
          break;
        case 'service_flag':
          $aWhere[] = "($search_field | $search_value) = $search_value";
          break;
        case 'status':
        case 'response':
          $aWhere[] = "$search_field = '$search_value'";
          break;
      }
    }
    if (!empty($aWhere)) {
      $from_str .= ' WHERE ' . implode(' AND ', $aWhere);
    }

    $query = "SELECT spool_id, account_id, transmission_id, status, response FROM " . $from_str;
    Corelog::log("spool search with $query", Corelog::DEBUG, array('aFilter' => $aFilter));
    $result = DB::query('spool', $query);
    while ($data = mysql_fetch_assoc($result)) {
      $aSpool[$data['spool_id']] = $data;
    }

    return $aSpool;
  }

  private function load()
  {
    $query = "SELECT * FROM " . self::$table . " WHERE spool_id='%spool_id%' ";
    $result = DB::query(self::$table, $query, array('spool_id' => $this->spool_id));
    $data = mysql_fetch_assoc($result);
    if ($data) {
      $this->spool_id = $data['spool_id'];
      $this->time_spool = $data['time_spool'];
      $this->time_start = $data['time_start'];
      $this->time_connect = $data['time_connect'];
      $this->time_end = $data['time_end'];
      $this->call_id = $data['call_id'];
      $this->status = $data['status'];
      $this->response = $data['response'];
      $this->amount = $data['amount'];
      $this->service_flag = $data['service_flag'];
      $this->transmission_id = $data['transmission_id'];
      $this->provider_id = $data['provider_id'];
      $this->node_id = $data['node_id'];
      $this->account_id = $data['account_id'];

      Corelog::log("Spool loaded spool_id: $this->spool_id", Corelog::CRUD);
    } else {
      throw new CoreException('404', 'Spool not found');
    }
  }

  public function delete()
  {
    Corelog::log("Spool delete", Corelog::CRUD);
    return DB::delete(self::$table, 'spool_id', $this->spool_id, true);
  }

  public function is_done()
  {
    if (in_array($this->status, self::$doneStatus)) {
      return TRUE;
    } else {
      return FALSE;
    }
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
    return $this->spool_id;
  }

  private function set_status($status)
  {
    // prevent updating already done spool
    if ($this->is_done()) {
      return;
    }
    switch ($status) {
      case Spool::STATUS_CONNECTED:
        $this->time_connect = time();
        break;
      case Spool::STATUS_COMPLETED:
      case Spool::STATUS_FAILED:
      case Spool::STATUS_INVALID:
        $this->time_end = time();
        break;
      case Spool::STATUS_DONE:
        // decide if spool failed or completed ?
        if (Spool::STATUS_CONNECTED == $this->status) {
          $this->__set('status', Spool::STATUS_COMPLETED);
        } else {
          $this->__set('status', Spool::STATUS_FAILED);
        }
        return; // no further processing needed, so exist
    }
    $this->status = $status;
  }

  public function save()
  {
    $data = array(
        'spool_id' => $this->spool_id,
        'time_spool' => $this->time_spool,
        'time_start' => $this->time_start,
        'time_connect' => $this->time_connect,
        'time_end' => $this->time_end,
        'call_id' => $this->call_id,
        'status' => $this->status,
        'response' => $this->response,
        'amount' => $this->amount,
        'transmission_id' => $this->transmission_id,
        'service_flag' => $this->service_flag,
        'provider_id' => $this->provider_id,
        'node_id' => $this->node_id,
        'account_id' => $this->account_id
    );

    if (isset($data['spool_id']) && !empty($data['spool_id'])) {
      // update existing record, no authentication needed
      $result = DB::update(self::$table, $data, 'spool_id');
      Corelog::log("Spool updated: $this->spool_id", Corelog::CRUD);
    } else {
      // add new, no authentication needed
      $result = DB::update(self::$table, $data, false);
      $this->spool_id = $data['spool_id'];
      Corelog::log("New Spool created: $this->spool_id", Corelog::CRUD);
    }

    return $result;
  }

}
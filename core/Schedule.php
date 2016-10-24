<?php
/* * ***************************************************************
 * Copyright © 2014 ICT Innovations Pakistan All Rights Reserved   *
 * Developed By: Nasir Iqbal                                       *
 * Website : http://www.ictinnovations.com/                        *
 * Mail : nasir@ictinnovations.com                                 *
 * *************************************************************** */

class Schedule
{

  /** @const */
  private static $table = 'schedule';
  private static $primary_key = 'schedule_id';
  private static $fields = array(
      'schedule_id',
      'type',
      'action',
      'data',
      'month',
      'day',
      'weekday',
      'hour',
      'minute',
      'weight',
      'is_recurring',
      'last_run',
      'expiry',
      'account_id'
  );
  private static $read_only = array(
      'schedule_id',
      'last_run'
  );

  /**
   * @property-read integer $schedule_id 
   * @var integer 
   */
  private $schedule_id = NULL;

  /** @var string */
  public $type = NULL;

  /** @var string */
  public $action = NULL;

  /** @var string */
  public $data = NULL;

  /** @var integer */
  public $month = '*';

  /** @var integer */
  public $day = '*';

  /**
   * @property-write integer $delay
   * @see Schedule::set_delay()
   */
  /**
   * @property-write integer $timestamp
   * @see Schedule::set_timestamp()
   */
  /**
   * @property-write string $datetime
   * @see Schedule::set_datetime()
   */

  /**
   * @property string $weekday
   * @see  Schedule::set_weekday()
   * @var integer 
   */
  private $weekday = '*';

  /** @var integer */
  public $hour = '*';

  /** @var integer */
  public $minute = '*';

  /** @var integer */
  public $weight = 0;

  /** @var integer */
  public $is_recurring = 0;

  /**
   * @property-read integer $last_run 
   * @var integer
   */
  private $last_run = NULL;

  /** @var integer */
  public $expiry = NULL;

  /** @var integer */
  public $account_id = NULL;

  public function __construct($schedule_id = NULL)
  {
    if (!empty($schedule_id)) {
      $this->schedule_id = $schedule_id;
      $this->load();
    }
  }

  public static function search($aFilter = array())
  {
    $aSchedule = array();
    $from_str = self::$table;
    $aWhere = array();
    foreach ($aFilter as $search_field => $search_value) {
      switch ($search_field) {
        case 'schedule_id':
        case 'account_id':
          $aWhere[] = "$search_field = $search_value";
          break;
        case 'data':
          $aWhere[] = "data = '" . json_encode($search_value, JSON_NUMERIC_CHECK) . "'";
          break;
        case 'type':
        case 'action':
          $aWhere[] = "$search_field = '$search_value'";
          break;
      }
    }
    if (!empty($aWhere)) {
      $from_str .= ' WHERE ' . implode(' AND ', $aWhere);
    }

    $query = "SELECT schedule_id, account_id, type, action, data FROM " . $from_str;
    Corelog::log("schedule search with $query", Corelog::DEBUG, array('aFilter' => $aFilter));
    $result = DB::query('schedule', $query);
    while ($data = mysql_fetch_assoc($result)) {
      $aSchedule[$data['schedule_id']] = $data;
    }

    return $aSchedule;
  }

  public static function search_pending()
  {
    $aSchedule = array();
    // Get all matching schedules and run!
    $query = "SELECT schedule_id FROM " . self::$table . " s
              WHERE (s.weekday = '*'  OR s.weekday = DAYOFWEEK(CURDATE())) 
                AND (s.day = '*'      OR s.day = DAYOFMONTH(CURDATE())) 
                AND (s.hour = '*'     OR s.hour = HOUR(CURTIME())) 
                AND (s.minute = '*'   OR s.minute = MINUTE(CURTIME()))
                AND (s.expiry IS NULL OR s.expiry > UNIX_TIMESTAMP())
                AND (s.last_run IS NULL OR (s.last_run + 59) < UNIX_TIMESTAMP())"; // don't run a task twice
    $rsSchedule = DB::query(self::$table, $query, array());
    while ($data = mysql_fetch_assoc($rsSchedule)) {
      $aSchedule[$data['schedule_id']] = $data['schedule_id'];
    }
    Corelog::log("Schedule search", Corelog::CRUD, $aSchedule);
    return $aSchedule;
  }

  private function load()
  {
    $query = "SELECT * FROM " . self::$table . " WHERE schedule_id='%schedule_id%'";
    $schedule = DB::query(self::$table, $query, array('schedule_id' => $this->schedule_id));
    $data = mysql_fetch_assoc($schedule);
    if ($data) {
      $this->schedule_id = $data['schedule_id'];
      $this->type = $data['type'];
      $this->action = $data['action'];
      $this->data = json_decode($data['data'], true);
      $this->month = $data['month'];
      $this->day = $data['day'];
      $this->weekday = $data['weekday'];
      $this->hour = $data['hour'];
      $this->minute = $data['minute'];
      $this->weight = $data['weight'];
      $this->is_recurring = $data['is_recurring'];
      $this->last_run = $data['last_run'];
      $this->expiry = $data['expiry'];
      $this->account_id = $data['account_id'];
      Corelog::log("Schedule loaded type: $this->type action: $this->action", Corelog::CRUD);
    } else {
      throw new CoreException('404', 'Schedule not found');
    }
  }

  public function delete()
  {
    Corelog::log("Schedule delete", Corelog::CRUD);
    return DB::delete(self::$table, 'schedule_id', $this->schedule_id);
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
    } else if (!empty($field) && in_array($field, self::$fields)) {
      return $this->$field;
    }
    return NULL;
  }

  public function __set($field, $value)
  {
    $method_name = 'set_' . $field;
    if (method_exists($this, $method_name)) {
      $this->$method_name($value);
    } else if (empty($field) || !in_array($field, self::$fields) || in_array($field, self::$read_only)) {
      return;
    } else {
      $this->$field = $value;
    }
  }

  public function set_datetime($date)
  {
    $aDate = date_parse($date);
    if (!empty($aDate['month']) || $aDate['month'] === 0) {
      $this->month = $aDate['month'];
    }
    if (!empty($aDate['day']) || $aDate['day'] === 0) {
      $this->day = $aDate['day'];
    }
    if (!empty($aDate['hour']) || $aDate['hour'] === 0) {
      $this->hour = $aDate['hour'];
    }
    if (!empty($aDate['minute']) || $aDate['minute'] === 0) {
      $this->month = $aDate['minute'];
    }
  }

  public function set_weekday($weekday)
  {
    $this->day = '*'; // disable day based scheduling
    $this->weekday = $weekday; // TODO weekday is an array
    throw new CoreException('501', 'Weekday based schedule not implemented');
  }

  public function set_timestamp($timestamp)
  {
    $aDateTime = getdate($timestamp);

    $this->month = $aDateTime['mon'];
    $this->day = $aDateTime['mday'];
    $this->hour = $aDateTime['hours'];
    $this->minute = $aDateTime['minutes'];
  }

  public function set_delay($seconds = 60)
  {
    $cur_time = time();
    return $this->set_timestamp($cur_time + $seconds);
  }

  public function save()
  {
    $data = array(
        'schedule_id' => $this->schedule_id,
        'type' => $this->type,
        'action' => $this->action,
        'data' => json_encode($this->data, JSON_NUMERIC_CHECK),
        'month' => $this->month,
        'day' => $this->day,
        'weekday' => $this->weekday,
        'hour' => $this->hour,
        'minute' => $this->minute,
        'weight' => $this->weight,
        'is_recurring' => $this->is_recurring,
        'last_run' => $this->last_run,
        'expiry' => $this->expiry,
        'account_id' => $this->account_id
    );

    if (isset($data['schedule_id']) && !empty($data['schedule_id'])) {
      // update existing record, no authentication needed
      $schedule = DB::update(self::$table, $data, 'schedule_id');
      Corelog::log("Schedule updated: $this->schedule_id", Corelog::CRUD);
    } else {
      // add new, no authentication needed
      $schedule = DB::update(self::$table, $data, false);
      $this->schedule_id = $data['schedule_id'];
      Corelog::log("New Schedule created: $this->schedule_id", Corelog::CRUD);
    }

    return $schedule;
  }

  public static function process_all()
  {
    $aSchedule = self::search_pending();
    // now process each aSchedule in a separate thread
    include 'lib/CoreThread.php';
    foreach ($aSchedule as $schedule_id) {
      $scheduleThread = new ScheduleProcess();
      $scheduleThread->wait()->run($schedule_id);
    }
  }

  public function process()
  {
    Corelog::log("proccessing schedule:" . $this->schedule_id, Corelog::CRUD);

    $result = false;
    $classType = ucfirst(strtolower(trim($this->type)));
    if (class_exists($classType)) {
      if (method_exists($classType, 'schedule_process')) {
        $result = call_user_func_array(array($classType, 'schedule_process'), array($this));
      }
    }

    if ($this->is_recurring) {
      $sql = "UPDATE schedule SET last_run = %cur_time% WHERE schedule_id=%schedule_id%";
    } else {
      $sql = "UPDATE schedule SET last_run = %cur_time%, expiry = %cur_time% WHERE schedule_id=%schedule_id%";
    }
    DB::query(self::$table, $sql, array('cur_time' => time(), 'schedule_id' => $this->schedule_id));

    return $result;
  }

}
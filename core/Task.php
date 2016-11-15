<?php
/* * ***************************************************************
 * Copyright © 2014 ICT Innovations Pakistan All Rights Reserved   *
 * Developed By: Nasir Iqbal                                       *
 * Website : http://www.ictinnovations.com/                        *
 * Mail : nasir@ictinnovations.com                                 *
 * *************************************************************** */

class Task
{

  /** @const */
  const ONHOLD = 0;
  const PENDING = 1;
  const PROCESSED = 2;
  const EXPIRED = 3;

  const RUN_ONCE = 0;
  const RECURRING = 1;

  protected static $table = 'task';
  protected static $primary_key = 'task_id';
  protected static $fields = array(
      'task_id',
      'type',
      'action',
      'data',
      'weight',
      'status',
      'is_recurring',
      'due_at',
      'expiry',
      'last_run',
      'account_id'
  );
  protected static $read_only = array(
      'task_id',
      'last_run'
  );

  /**
   * @property-read integer $task_id 
   * @var integer 
   */
  protected $task_id = NULL;

  /** @var string */
  public $type = NULL;

  /** @var string */
  public $action = NULL;

  /** @var string */
  public $data = NULL;

  /** @var integer */
  public $weight = 0;

  /** @var integer */
  public $status = Task::PENDING;

  /** @var integer */
  public $is_recurring = Task::RUN_ONCE;

  /** @var integer */
  public $due_at = NULL;

  /** @var integer */
  public $expiry = NULL;  

  /**
   * @property-read integer $last_run 
   * @var integer
   */
  protected $last_run = NULL;

  /** @var integer */
  public $account_id = NULL;

  private $server_time = NULL;

  public function __construct($task_id = NULL, $server_time = NULL)
  {
    if (empty($server_time)) {
      $this->server_time = time();
    } else {
      $this->server_time = $server_time;
    }

    if (!empty($task_id)) {
      $this->task_id = $task_id;
      $this->load();
    }
  }

  public static function search($aFilter = array())
  {
    $aTask = array();
    $from_str = self::$table;
    $aWhere = array();
    foreach ($aFilter as $search_field => $search_value) {
      switch ($search_field) {
        case 'task_id':
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

    $query = "SELECT task_id, account_id, type, action, data FROM " . $from_str;
    Corelog::log("task search with $query", Corelog::DEBUG, array('aFilter' => $aFilter));
    $result = DB::query('task', $query);
    while ($data = mysql_fetch_assoc($result)) {
      $aTask[$data['task_id']] = $data;
    }

    return $aTask;
  }

  public static function search_pending()
  {
    $aTask = array();
    // Get all matching tasks and run!
    $query = "SELECT task_id, UNIX_TIMESTAMP() AS server_time 
              FROM " . self::$table . " t
              WHERE status = " . Task::PENDING . "
                AND (t.last_run IS NULL OR (t.last_run + 59) < UNIX_TIMESTAMP())"; // don't run a task twice
    $rsTask = DB::query(self::$table, $query, array());
    while ($data = mysql_fetch_assoc($rsTask)) {
      $aTask[$data['task_id']] = $data;
    }
    Corelog::log("Task search", Corelog::CRUD, $aTask);
    return $aTask;
  }

  protected function load()
  {
    $query = "SELECT * FROM " . self::$table . " WHERE task_id='%task_id%'";
    $task = DB::query(self::$table, $query, array('task_id' => $this->task_id));
    $data = mysql_fetch_assoc($task);
    if ($data) {
      $this->task_id = $data['task_id'];
      $this->type = $data['type'];
      $this->action = $data['action'];
      $this->data = json_decode($data['data'], true);
      $this->weight = $data['weight'];
      $this->status = $data['status'];
      $this->due_at = $data['due_at'];
      $this->is_recurring = $data['is_recurring'];
      $this->expiry = $data['expiry'];
      $this->last_run = $data['last_run'];
      $this->account_id = $data['account_id'];
      Corelog::log("Task loaded type: $this->type action: $this->action", Corelog::CRUD);
    } else {
      throw new CoreException('404', 'Task not found');
    }
  }

  public function delete()
  {
    Corelog::log("Task delete", Corelog::CRUD);
    return DB::delete(self::$table, 'task_id', $this->task_id);
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

  public function save()
  {
    $data = array(
        'task_id' => $this->task_id,
        'type' => $this->type,
        'action' => $this->action,
        'data' => json_encode($this->data, JSON_NUMERIC_CHECK),
        'weight' => $this->weight,
        'status' => $this->status,
        'due_at' => $this->due_at,
        'is_recurring' => $this->is_recurring,
        'expiry' => $this->expiry,
        'last_run' => $this->last_run,
        'account_id' => $this->account_id
    );

    if (isset($data['task_id']) && !empty($data['task_id'])) {
      // update existing record, no authentication needed
      $task = DB::update(self::$table, $data, 'task_id');
      Corelog::log("Task updated: $this->task_id", Corelog::CRUD);
    } else {
      // add new, no authentication needed
      $task = DB::update(self::$table, $data, false);
      $this->task_id = $data['task_id'];
      Corelog::log("New Task created: $this->task_id", Corelog::CRUD);
    }

    return $task;
  }

  public static function process_all()
  {
    $listTask = self::search_pending();
    // now process each aTask in a separate thread
    include 'lib/CoreThread.php';
    foreach ($listTask as $task_id => $aTask) {
      $taskThread = new TaskThread();
      $taskThread->wait()->run($task_id, $aTask['server_time']);
    }
  }

  public function process()
  {
    $task_status = Task::PENDING;
    if ($this->is_recurring) {
      if (($this->due_at + $this->expiry) <= $this->server_time) {
        $task_status = Task::EXPIRED;
      }
    } else if ($this->expiry <= $this->server_time) {
      $task_status = Task::EXPIRED;
    }

    if ($task_status == Task::EXPIRED) {
      Corelog::log("task has been expired:" . $this->task_id, Corelog::WARNING);

      $sql = "UPDATE task SET status = %status% WHERE task_id=%task_id%";
      DB::query(self::$table, $sql, array('status' => Task::EXPIRED, 'task_id' => $this->task_id));

    } else {
      Corelog::log("proccessing task:" . $this->task_id, Corelog::FLOW);

      $sql = "UPDATE task SET last_run = %cur_time%, expiry = %cur_time%, status = %status% WHERE task_id=%task_id%";
      DB::query(self::$table, $sql, array('cur_time' => time(), 'status' => Task::PROCESSED, 'task_id' => $this->task_id));

      $result = false;
      $classType = ucfirst(strtolower(trim($this->type)));
      if (class_exists($classType)) {
        if (method_exists($classType, 'task_process')) {
          $result = call_user_func_array(array($classType, 'task_process'), array($this));
        }
      }
    
      return $result;
    }
  }

}
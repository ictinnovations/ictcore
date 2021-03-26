<?php

namespace ICT\Core;

/* * ***************************************************************
 * Copyright © 2015 ICT Innovations Pakistan All Rights Reserved   *
 * Developed By: Nasir Iqbal                                       *
 * Website : http://www.ictinnovations.com/                        *
 * Mail : nasir@ictinnovations.com                                 *
 * *************************************************************** */

class Action
{

  private static $table = 'action';
  private static $fields = array(
      'action_id',
      'type',
      'action',
      'data',
      'weight',
      'is_default',
      'application_id'
  );
  private static $read_only = array(
      'action_id',
      'type'
  );

  /**
   * @property-read integer $action_id
   * @var integer
   */
  public $action_id = NULL;

  /**
   * @property-read string $type
   * @var string
   */
  private $type = 'match';

  /**
   * @var string
   * @see Action::nextApplication for alternative
   */
  public $action = NULL;

  /** @var array */
  public $data = array();

  /** @var integer */
  public $weight = 0;

  /** @var integer */
  public $is_default = false;

  /** @var integer */
  public $application_id = NULL;

  /**
   * @var Application
   * temporary alternative to action
   * @see Action::action
   */
  public $nextApplication = NULL;

  public function __construct($action_id = NULL)
  {
    if (!empty($action_id)) {
      $this->action_id = $action_id;
      $this->_load();
    }
  }

  public static function load($action_id)
  {
    // TODO: copy from application when needed
    return new self($action_id);
  }

  public static function search($application_id)
  {
    $aAction = array();
    $query = "SELECT action_id FROM " . self::$table . " WHERE application_id='%application_id%' ";
    $result = DB::query(self::$table, $query, array('application_id' => $application_id));
    forEach ($result as $data ) {
      $aAction[] = $data;
    }
    Corelog::log("Search actions for application: $application_id", Corelog::DEBUG, $aAction);
    return $aAction;
  }

  protected function _load()
  {
    Corelog::log("Loading action: $this->action_id", Corelog::DEBUG);
    $query = "SELECT * FROM " . self::$table . " WHERE action_id='%action_id%' ";
    $result = DB::query(self::$table, $query, array('action_id' => $this->action_id));
    $data = $result[0];
    $this->action_id = $data['action_id'];
    $this->type = $data['type'];
    $this->action = $data['action'];
    $this->data = json_decode($data['data'], true);
    $this->weight = $data['weight'];
    $this->is_default = $data['is_default'];
    $this->application_id = $data['application_id'];
  }

  public function delete()
  {
    Corelog::log("Deleting action: $this->action_id", Corelog::CRUD);
    return DB::delete(self::$table, 'action_id', $this->action_id);
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

  public function get_id()
  {
    return $this->action_id;
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

  public function save()
  {
    if ($this->nextApplication instanceof Application && $this->nextApplication->application_id) {
      $this->action = $this->nextApplication->application_id;
    }
    $data = array(
        'action_id' => $this->action_id,
        'type' => $this->type,
        'action' => $this->action,
        'data' => json_encode($this->data, JSON_NUMERIC_CHECK),
        'weight' => $this->weight,
        'is_default' => $this->is_default,
        'application_id' => $this->application_id
    );

    if (isset($data['action_id']) && !empty($data['action_id'])) {
      // update existing record
      $result = DB::update(self::$table, $data, 'action_id');
      Corelog::log("Action updated: $this->action_id", Corelog::CRUD);
    } else {
      // add new
      $result = DB::update(self::$table, $data, false);
      $this->action_id = $data['action_id'];
      Corelog::log("New action created: $this->action_id", Corelog::CRUD);
    }
    return $result;
  }

  public function test($aResult)
  {
    foreach ($this->data as $test_variable => $test_value) {
      if (isset($aResult[$test_variable]) && $aResult[$test_variable] == $test_value) {
        Corelog::log("Action selected: $this->action_id", Corelog::DEBUG);
        return true; // action matched
      }
    }
    Corelog::log("Action ignored: $this->action_id", Corelog::DEBUG);
    return false;  // no action match found
  }

  public function is_default()
  {
    return $this->is_default;
  }

}

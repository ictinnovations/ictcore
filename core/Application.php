<?php

namespace ICT\Core;

/* * ***************************************************************
 * Copyright © 2015 ICT Innovations Pakistan All Rights Reserved   *
 * Developed By: Nasir Iqbal                                       *
 * Website : http://www.ictinnovations.com/                        *
 * Mail : nasir@ictinnovations.com                                 *
 * *************************************************************** */

class Application
{
  /* const */

  const ORDER_PRE = 1;
  const ORDER_PERI = 2;   // Durring, middle
  const ORDER_POST = 4;
  const ORDER_INIT = 8;   // Dial / Ring
  const ORDER_CONNECT = 16;  // Answer
  const ORDER_START = 32;  // Greetings
  const ORDER_ACTIVE = 64;  // Call
  const ORDER_END = 128; // Bye
  const ORDER_CLOSE = 256; // Hangup

  /**
   * ********************************************** Application related data **
   */

  protected static $table = 'application';
  protected static $fields = array(
      'application_id',
      'name',
      'type',
      'data',
      'weight',
      'program_id'
  );
  protected static $read_only = array(
      'application_id',
      'type'
  );

  /**
   * @property-read integer $application_id
   * @var integer
   */
  protected $application_id = NULL;

  /** @var string */
  public $name = 'unknown';

  /**
   * @property-read string $type
   * @var string
   */
  protected $type = 'unknown';

  /**
   * @property array $data
   * @see function Application::get_data() and Application::set_data()
   * @var array
   */
  protected $data = array();

  /** @var integer */
  public $weight = Application::ORDER_ACTIVE;

  /** @var integer */
  public $program_id = NULL;

  /**
   * ******************************************** Default Application Values **
   */

  /**
   * default condition
   * @var array 
   */
  public static $defaultCondition = array('result' => 'success');

  /**
   * All possible results to use 
   * @var array 
   */
  public static $supportedResult = array(
      'result' => array('success', 'error')
  );

  /**
   * Parameters required by this application along with default values
   * @var array 
   */
  public static $requiredParameter = array();

  /**
   * ***************************************************** Runtime Variables **
   */

  /** @var array */
  protected $result = null;

  /** @var Action[] */
  protected $aAction = array(); // a cache variable

  /** @var Transmission */
  protected $oTransmission;

  public function __construct($application_id = null, $aParameter = null)
  {
    if (!empty($aParameter) && is_array($aParameter)) {
      $this->set_data($aParameter);
    } else {
      $this->data = $this::$requiredParameter;
    }
    if (!empty($application_id)) {
      $this->application_id = $application_id;
      $this->_load();
    }
  }

  public function token_resolve()
  {
    foreach ($this->data as $name => $value) {
      $this->{$name} = $value;
    }
  }

  public static function search($program_id, $weight = NULL)
  {
    $aApplication = array();
    $where = "program_id='%program_id%'";
    if ($weight !== NULL) { // remember weight can be 0, so ===
      $where .= " AND (weight & %weight%) = %weight%";
    }
    $query = "SELECT application_id FROM " . self::$table . " WHERE $where";
    $result = DB::query(self::$table, $query, array('program_id' => $program_id, 'weight' => $weight));
    while ($data = mysql_fetch_assoc($result)) {
      $aApplication[$data['application_id']] = $data['application_id'];
    }
    Corelog::log("Application search for program: $program_id", Corelog::CRUD, $aApplication);
    return $aApplication;
  }

  public function deploy(Program &$oProgram)
  {
    Corelog::log("Deploying application : $this->type($this->application_id) for program : $oProgram->program_id", Corelog::LOGIC);
    // further code will go here
  }

  public function remove()
  {
    // nothing to remove
  }

  public static function getClass($application_id, $namespace = 'ICT\\Core\\Application')
  {
    if (ctype_digit(trim($application_id))) {
      $query = "SELECT type FROM " . self::$table . " WHERE application_id='%application_id%' ";
      $result = DB::query(self::$table, $query, array('application_id' => $application_id));
      if (is_resource($result)) {
        $application_type = mysql_result($result, 0);
      }
    } else {
      $application_type = $application_id;
    }
    $class_name = ucfirst(strtolower(trim($application_type)));
    if (!empty($namespace)) {
      $class_name = $namespace . '\\' . $class_name;
    }
    if (class_exists($class_name)) {
      return $class_name;
    } else {
      return false;
    }
  }

  public static function load($application_id)
  {
    $class_name = self::getClass($application_id);
    if ($class_name) {
      Corelog::log("Creating instance of : $class_name for application: $application_id", Corelog::CRUD);
      return new $class_name($application_id);
    } else {
      Corelog::log("$class_name class not found, Creating instance of : Application", Corelog::CRUD);
      return new self($application_id);
    }
  }

  protected function _load()
  {
    $query = "SELECT * FROM " . self::$table . " WHERE application_id='%application_id%' ";
    $result = DB::query(self::$table, $query, array('application_id' => $this->application_id));
    $data = mysql_fetch_assoc($result);
    if ($data) {
      $this->application_id = $data['application_id'];
      $this->name = $data['name'];
      $this->type = $data['type'];
      $this->data = json_decode($data['data'], true);
      $this->weight = $data['weight'];
      $this->program_id = $data['program_id'];

      $this->aAction = array();
      $listAction = Action::search($this->application_id);
      foreach ($listAction as $action_id) {
        $this->aAction[$action_id] = new Action($action_id);
      }
      Corelog::log("Application loaded: $this->name", Corelog::CRUD);
    } else {
      throw new CoreException('404', 'Transmission not found');
    }
  }

  public function delete()
  {
    Corelog::log("Deleting application : $this->application_id", Corelog::CRUD);
    // first of all delete all actions linked to this application
    foreach ($this->aAction as $oAction) {
      if (is_object($oAction)) {
        $oAction->delete();
      }
    }
    return DB::delete(self::$table, 'application_id', $this->application_id);
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
    return $this->application_id;
  }

  public function get_data($field = '_all_')
  {
    if ('_all_' == $field) {
      return $this->data;
    } else if (isset($this->data[$field])) {
      return $this->data[$field];
    }
    return array(); // empty array
  }

  public function set_data($field, $value = '_reset_')
  {
    if ('_reset_' == $value) {
      $newData = (array) $field; // use field as data array
      $this->data = array_merge($this::$requiredParameter, $newData);
    } else {
      $this->data = array_merge($this->data, array($field => $value));
    }
  }

  public function save()
  {
    $data = array(
        'application_id' => $this->application_id,
        'name' => $this->name,
        'type' => $this->type,
        'data' => json_encode($this->data, JSON_NUMERIC_CHECK),
        'weight' => $this->weight,
        'program_id' => $this->program_id
    );

    if (isset($data['application_id']) && !empty($data['application_id'])) {
      // update existing record
      $result = DB::update(self::$table, $data, 'application_id');
      Corelog::log("Application updated: $this->application_id", Corelog::CRUD);
    } else {
      // add new
      $result = DB::update(self::$table, $data, false);
      $this->application_id = $data['application_id'];
      Corelog::log("New application created: $this->application_id", Corelog::CRUD);
    }
    return $result;
  }

  public function result_create($data, $name = null, $type = Result::TYPE_APPLICATION)
  {
    if (empty($name)) {
      $name = $this->name;
    }
    $oResult = $this->oTransmission->result_create($data, $name, $type, $this->application_id);
    return $oResult;
  }

  public function execute()
  {
    $oService = new Service();
    return $oService->application_template($this->name);
  }

  public function _execute(Transmission &$oTransmission)
  {
    Corelog::log("Executing application : $this->type($this->application_id)", Corelog::FLOW);

    $this->oTransmission = &$oTransmission;

    // before processing update data with available tokens
    $oToken = new Token();
    $oToken->add('application', $this);
    $this->data = $oToken->render_variable($this->data);

    $spool_id = $oTransmission->oSpool->spool_id;
    $app_id = 'app_' . $this->application_id;
    $app_data = $this->execute($oTransmission);

    if (empty($app_data)) {
      return NULL; // no output
    } else {
      $oResponse = new Response();
      $oResponse->spool_id = $spool_id;
      $oResponse->application_id = $app_id;
      $oResponse->application_data = $oToken->render_template($app_data);
      return $oResponse;
    }
  }

  /**
   * Processs function
   * To process application results after its execution
   */
  public function process()
  {
    return Spool::STATUS_CONNECTED;
  }

  /**
   * Wrapper function for process 
   */
  public function _process(Transmission &$oTransmission)
  {
    Corelog::log("Processing application : $this->type($this->application_id)", Corelog::FLOW);

    $this->oTransmission = &$oTransmission;

    // adopt any orphan result, matching application type
    $oTransmission->result_associate($this->type, $this->application_id);
    // processing application results
    $oSession = Session::get_instance();
    $this->result = &$oSession->request->application_data;
    $spool_status = $this->process();

    Corelog::log('Application processing completed with result: ' . $this->result['result'], Corelog::FLOW);
    Corelog::log('Application complete results', Corelog::DEBUG, $this->result);

    // First of all save application result which is common to all applications
    if (!empty($this->result['result'])) {
      $this->result_create($this->result['result'], $this->name, Result::TYPE_APPLICATION);
    }
    // then update spool status
    $oTransmission->oSpool->status = $spool_status;

    // quite further processing if spool is no longer active
    if ($oTransmission->oSpool->is_done()) {
      return $spool_status;
    }

    // According to result determine next application and execute it
    $action_executed = false;
    // we have created it an such a way that multiple action can be matched and executed
    foreach ($this->aAction as $action_id => $oAction) {
      // we may test against multiple input if application need that
      if ($oAction->test($this->result)) {
        Corelog::log("Action matched: $oAction->action_id", Corelog::CRUD);
        $nextApplication = Application::load($oAction->action);
        $nextApplication->_execute($oTransmission);
        $action_executed = $action_id;
      }
    }
    // in case no action matched then we will proceed with default
    if ($action_executed === false) {
      Corelog::log("No action matched", Corelog::CRUD);
      foreach ($this->aAction as $oAction) {
        if ($oAction->is_default) {
          Corelog::log("Selecting default action: $oAction->action_id", Corelog::CRUD);
          $nextApplication = Application::load($oAction->action);
          $nextApplication->_execute($oTransmission);
          $action_executed = true; // default
          break; // only one action can be default so we should break here
        }
      }
    }

    // if no further application has been exectuted then mark this spool as done
    if ($action_executed == false) {
      $oTransmission->oSpool->status = Spool::STATUS_DONE;
      return Spool::STATUS_DONE;
    } else {
      return $spool_status;
    }
  }

}
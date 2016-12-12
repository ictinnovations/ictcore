<?php

namespace ICT\Core;

/* * ***************************************************************
 * Copyright © 2015 ICT Innovations Pakistan All Rights Reserved   *
 * Developed By: Nasir Iqbal                                       *
 * Website : http://www.ictinnovations.com/                        *
 * Mail : nasir@ictinnovations.com                                 *
 * *************************************************************** */

class Program
{

  /**
   * ************************************************** Program related data **
   */
  protected static $table = 'program';
  protected static $fields = array(
      'program_id',
      'name',
      'type',
      'data',
      'parent_id'
  );
  protected static $read_only = array(
      'program_id',
      'type'
  );

  /**
   * @property-read integer $program_id
   * @var integer 
   */
  protected $program_id = null;

  /** @var string */
  public $name = 'unknown';

  /**
   * @property-read string $type
   * @var string 
   */
  protected $type = 'program';

  /**
   * @property array $data
   * @see function Program::get_data() and Program::set_data()
   * @var array 
   */
  protected $data = array();

  /** @var integer */
  public $parent_id = null;

  /**
   * ************************************************ Default Program Values **
   */

  /**
   * Parameters required by this program along with default values
   * @var array 
   */
  public static $requiredParameter = array();

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
      'result' => array('success', 'error'),
      'error' => ''
  );

  /**
   * ***************************************************** Runtime Variables **
   */

  /** @var array */
  protected $aCache = array();

  /** @var array */
  protected $result = null;

  /** @var Sequence */
  protected $oSequence = null;

  /** @var Transmission */
  protected $oTransmission = null;

  public function __construct($program_id = null, $aParameter = null)
  {

    if (!empty($aParameter) && is_array($aParameter)) {
      $this->set_data($aParameter);
    }
    if (!empty($program_id)) {
      $this->program_id = $program_id;
      $this->_load();
    }
  }

  public static function search($aFilter = array())
  {
    $aProgram = array();
    $aWhere = array();
    foreach ($aFilter as $search_field => $search_value) {
      switch ($search_field) {
        case 'program_id':
          $aWhere[] = "program_id = $search_value";
          break;
        case 'name':
        case 'type':
          $aWhere[] = "$search_field LIKE '%$search_value%'";
          break;
      }
    }
    $where_str = implode(' AND ', $aWhere);

    $query = "SELECT program_id, name, type, parent_id FROM " . self::$table . " WHERE $where_str";
    Corelog::log("program search with $query", Corelog::DEBUG, array('aFilter' => $aFilter));
    $result = DB::query('program', $query);
    while ($data = mysql_fetch_assoc($result)) {
      $aProgram[$data['program_id']] = $data;
    }

    return $aProgram;
  }

  public static function search_child($program_id)
  {
    $aProgram = array();
    $where = "parent_id='%program_id%'";
    $query = "SELECT program_id FROM " . self::$table . " WHERE $where";
    $result = DB::query(self::$table, $query, array('program_id' => $program_id));
    while ($data = mysql_fetch_assoc($result)) {
      $aProgram[$data['program_id']] = $data['program_id'];
    }
    Corelog::log("Child program search for program: $program_id", Corelog::CRUD, $aProgram);
    return $aProgram;
  }

  public static function search_resource($resource_type, $resource_id)
  {
    $aProgram = array();

    $query = "SELECT program_id FROM " . self::$table . "_resource WHERE resource_type='%resource_type%' AND resource_id=%resource_id%";
    $result = DB::query(self::$table . "_resource", $query, array('resource_type' => $resource_type, 'resource_id' => $resource_id));
    while ($resource = mysql_fetch_assoc($result)) {
      $aProgram[$resource['program_id']] = $resource;
    }

    if (empty($aProgram)) {
      return false;
    } else {
      return $aProgram;
    }
  }

  public function token_get()
  {
    $aToken = array();
    foreach (self::$fields as $field) {
      $aToken[$field] = $this->$field;
    }
    return $aToken;
  }

  public function load_token()
  {
    // prepare token cache for program related things
    $oToken = new Token();
    $oToken->add('program', $this->token_get());
    foreach ($this->aCache as $name => $object) {
      if (is_object($object) && method_exists($object, 'token_get')) {
        $oToken->add($name, $object->token_get());
      }
    }
    return $oToken;
  }

  /* Function: scheme
    Program scheme for primary transmission, application execution order
    and conditions
   */

  public function scheme()
  {
    Corelog::log("Creating program scheme", Corelog::LOGIC);

    $app1st = new Application();
    $app1st->data = array('message' => 'test application one');

    $app2nd = new Application();
    $app2nd->data = array('message' => 'test application two');

    $app3rd = new Application();
    $app3rd->data = array('message' => 'test application three');

    $oScheme = new Scheme();
    $oScheme->add($app1st);
    $oScheme->condition('result', 'success')->add($app2nd);
    $oScheme->condition('result', 'error')->add($app3rd);

    return $oScheme;
  }

  public function deploy()
  {
    Corelog::log("Constructing porgram", Corelog::LOGIC);

    $this->load_cache();

    $oScheme = $this->scheme();

    $oSequence = new Sequence();
    $oSequence->token_create($this);

    return $oScheme->compile($this, $oSequence);
  }

  public function remove()
  {
    // nothing to remove
  }

  public static function getClass($program_id, $namespace = 'ICT\\Core\\Program')
  {
    if (ctype_digit(trim($program_id))) {
      $query = "SELECT type FROM " . self::$table . " WHERE program_id='%program_id%' ";
      $result = DB::query(self::$table, $query, array('program_id' => $program_id));
      if (is_resource($result)) {
        $program_type = mysql_result($result, 0);
      }
    } else {
      $program_type = $program_id;
    }
    $class_name = ucfirst(strtolower(trim($program_type)));
    if (!empty($namespace)) {
      $class_name = $namespace . '\\' . $class_name;
    }
    if (class_exists($class_name)) {
      return $class_name;
    } else {
      return false;
    }
  }

  public static function load($program_id, $aParameter = null)
  {
    $class_name = self::getClass($program_id);
    if ($class_name) {
      Corelog::log("Creating instance of : $class_name for program: $program_id", Corelog::CRUD);
      return new $class_name($program_id, $aParameter);
    } else {
      Corelog::log("$class_name class not found, Creating instance of : Program", Corelog::CRUD);
      return new self($program_id, $aParameter);
    }
  }

  protected function _load()
  {
    $query = "SELECT * FROM " . self::$table . " WHERE program_id='%program_id%' ";
    $result = DB::query(self::$table, $query, array('program_id' => $this->program_id));
    $data = mysql_fetch_assoc($result);
    $this->program_id = $data['program_id'];
    $this->name = $data['name'];
    $this->type = $data['type'];
    $this->set_data(json_decode($data['data'], true));
    $this->parent_id = $data['parent_id'];

    Corelog::log("Program loaded: $this->name", Corelog::CRUD);
  }

  /* Wrapper function for data_map */

  protected function load_cache()
  {
    foreach ($this->data as $parameter_name => $parameter_value) {
      $this->aCache[$parameter_name] = $parameter_value;
      $dataMap = $this->data_map($parameter_name, $parameter_value);
      foreach ($dataMap as $new_key => $new_value) {
        $this->aCache[$new_key] = $new_value;
      }
    }
  }

  /**
   * Function: data map
   * Needed to load objects based data using their corresponding IDs from given program data
   */
  protected function data_map($parameter_name, $parameter_value)
  {
    $dataMap = array();
    switch ($parameter_name) {
      default:
        $dataMap[$parameter_name] = $parameter_value;
        break;
    }
    return $dataMap;
  }

  public function delete()
  {
    Corelog::log("Deleting program: $this->program_id", Corelog::CRUD);

    // first of all uninstall this program from dialplan
    $this->remove();

    // then delete all application linked to this program
    $listApplication = Application::search($this->program_id);
    foreach ($listApplication as $application_id) {
      $oApplication = Application::load($application_id);
      $oApplication->remove(); // before delete, remove that application from dialplan
      $oApplication->delete();
    }

    // then delete all child programs
    $listChild = $this->search_child($this->program_id);
    foreach ($listChild as $program_id) {
      $oProgram = Program::load($program_id);
      $oProgram->delete();
    }

    // also delete all resources
    DB::delete(self::$table . '_resource', 'program_id', $this->program_id, true);

    return DB::delete(self::$table, 'program_id', $this->program_id, true);
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
      $this->data = (array) $field; // use field as data array
    } else {
      $this->data = array_merge($this->data, array($field => $value));
    }
  }

  public function save()
  {
    /* Filter out all unnecessary parameters
      this is required to avoid repetitive data mapping
     */
    $this->load_cache();
    $oToken = $this->load_token();
    $finalData = $oToken->render_variable($this::$requiredParameter);

    $data = array(
        'program_id' => $this->program_id,
        'name' => $this->name,
        'type' => $this->type,
        'data' => json_encode($finalData, JSON_NUMERIC_CHECK),
        'parent_id' => $this->parent_id
    );

    if (isset($data['program_id']) && !empty($data['program_id'])) {
      // update existing record
      $result = DB::update(self::$table, $data, 'program_id');
      Corelog::log("Program updated: $this->program_id", Corelog::CRUD);
    } else {
      // add new
      $result = DB::update(self::$table, $data, false);
      $this->program_id = $data['program_id'];
      Corelog::log("New Program created: $this->program_id", Corelog::CRUD);
    }
    // before leaving update program resources
    $this->save_resource($this->data);
    return $result;
  }

  public function save_resource($data)
  {
    foreach ($data as $name => $value) {
      // in case of $value is array call same function recursively
      if (is_array($value)) {
        $this->save_resource($value);
        continue;
      }
      // first of all clear old resource and then save new resource
      DB::delete(self::$table . '_resource', 'program_id', $this->program_id, true);
      $fields = array(
          'program_id' => $this->program_id,
          'resource_type' => $name,
          'resource_id' => $value
      );
      DB::update(self::$table . '_resource', $fields);
    }
  }

  /**
   * Function: transmission_instant
   * Wrapper function to transmission_create, in addition it will also prepare Program from scratch
   */
  public static function transmission_instant($aProgram, $aTransmission)
  {
    // Prepare program
    $oProgram = new static();
    foreach ($aProgram as $key => $value) {
      $oProgram->$key = $value;
    }
    $oProgram->save();
    $oProgram->deploy();

    // Prepare transmission
    $contact_id = empty($aTransmission['contact_id']) ? null : $aTransmission['contact_id'];
    unset($aTransmission['contact_id']);
    if (empty($aTransmission['account_id'])) {
      $oAccount = new Account(Account::USER_DEFAULT);
      $account_id = $oAccount->account_id;
    } else {
      $account_id = $aTransmission['account_id'];
    }
    unset($aTransmission['account_id']);
    $direction = empty($aTransmission['direction']) ? Transmission::OUTBOUND : $aTransmission['direction'];
    unset($aTransmission['direction']);

    $newTransmission = $oProgram->transmission_create($contact_id, $account_id, $direction);
    foreach ($aTransmission as $key => $value) {
      $newTransmission->$key = $value;
    }
    $newTransmission->save();

    return $newTransmission;
  }

  /** Function: transmission_create
   * Creating transmission while using current program
   */
  public function transmission_create($contact_id, $account_id, $direction = Transmission::OUTBOUND)
  {
    $oTransmission = new Transmission();
    $oTransmission->title = $this->name;
    $oTransmission->program_id = $this->program_id;
    $oTransmission->origin = $this->type;
    $oTransmission->service_flag = Service::SERVICE_FLAG;
    $oTransmission->status = Transmission::STATUS_PENDING;
    $oTransmission->direction = $direction;
    $oTransmission->contact_id = $contact_id;
    $oTransmission->account_id = $account_id;
    return $oTransmission;
  }

  public function execute()
  {
    $result = false;
    // fetch program's initial applications (identified by ORDER_INIT as weight)
    $listApplication = Application::search($this->program_id, Application::ORDER_INIT);
    foreach ($listApplication as $application_id) {
      $oApplication = Application::load($application_id);
      $oApplication->_execute($this->oTransmission, $this->oSequence);
      $result = true;
    }
    return $result;
  }

  public function _execute(Transmission &$oTransmission, Sequence &$oSequence)
  {
    Corelog::log("Executing program : $this->type($this->program_id)", Corelog::FLOW);

    $this->oTransmission = &$oTransmission;
    $this->oSequence = &$oSequence;

    // before processing update data with available tokens
    $data = array_merge($this::$requiredParameter, $this->data);
    $this->set_data($oSequence->oToken->render_variable($data));
    $this->load_cache();
    // update token cache
    $oSequence->token_create($this);

    return $this->execute();
  }

  /**
   * Event: Transmission completed
   * Will be fired when first / initial transmission is done
   * here we will decide either its was completed or failed
   */
  protected function transmission_done()
  {
    if ($this->result['result'] == 'error') {
      return Transmission::STATUS_FAILED;
    }

    // after processing further, we can confirm if current transmission was completed
    return Transmission::STATUS_COMPLETED;
  }

  /**
   * Event: Program completed
   * Will be fired when all is done, nothing else left to do for this program
   */
  public function program_completed($program_type, Program &$oProgram)
  {
    Corelog::log("Program complated : $oProgram->type($oProgram->program_id)", Corelog::LOGIC);
    // final action
    if ($program_type == 'primary') {
      // further code will goes here
    }
  }

  /**
   * Application completed
   * will be fired after completion of each step mentioned in scheme
   */
  protected function application_completed(Application $oApplication)
  {
    Corelog::log(ucfirst($oApplication->type) . " application completed with id " . $oApplication->application_id, Corelog::LOGIC);
    // if you have plan to do something at very specific point during active transmission
  }

  /**
   * background function: process
   * Process results after completion of each associated application
   * To determine current status of program
   */
  public function process()
  {
    // check if current attempt has been ended
    if ($this->oTransmission->oSpool->is_done()) {
      // first of all set initial / default results
      if (Spool::STATUS_COMPLETED == $this->oTransmission->oSpool->status) {
        $this->result = array('result' => 'success', 'error' => '');
      } else {
        $this->result = array('result' => 'error', 'error' => $this->oTransmission->oSpool->response);
      }

      // further confirm result by triggerring transmission done event
      $transmission_status = $this->transmission_done();
      $this->oTransmission->status = $transmission_status;
      // update result cache, maybe result are changed
      $this->oSequence->oToken->add('result', $this->result);

      // check for transmission status, and trigger program completed if required
      if (Transmission::STATUS_COMPLETED == $this->oTransmission->status) {
        $this->program_completed('primary', $this);
      }

      // if their is parent also trigger its program_completed event,
      // in either case if current transmission is complated or failed
      if ($this->oTransmission->is_done() && !empty($this->parent_id)) {
        $parentProgram = Program::load($this->parent_id);
        $parentProgram->oTransmission = &$this->oTransmission;
        $parentProgram->oSequence = &$this->oSequence;
        $parentProgram->program_completed('associated', $this);
      }
    }
  }

  /**
   * Wrapper function for above process function 
   */
  public function _process(Transmission &$oTransmission, Sequence &$oSequence)
  {
    Corelog::log("Processing with program : $this->type($this->program_id)", Corelog::FLOW);

    // make input variable available at class level
    $this->oTransmission = &$oTransmission;
    $this->oSequence = &$oSequence;

    // before processing update data with available tokens
    $data = array_merge($this::$requiredParameter, $this->data);
    $this->set_data($oSequence->oToken->render_variable($data));
    $this->load_cache();
    // update token cache
    $oSequence->token_create($this);

    /**
     * ***************************************** Process application results **
     */
    // load request application
    if ($oTransmission->status == Transmission::STATUS_INITIALIZING) { // For new transmission
      // first of all change transmission status
      $oTransmission->status = Transmission::STATUS_PROCESSING;
      $listApplication = Application::search($this->program_id, Application::ORDER_INIT);
      foreach ($listApplication as $application_id) {
        $oApplication = Application::load($application_id);
        break; // only one application is needed
      }
    } else { // in case of existing in-process transmission
      // use application id from request if not present then
      // check if dialplan provide any reference to application
      if (!empty($oSequence->oRequest->application_id)) {
        $oApplication = Application::load($oSequence->oRequest->application_id);
      } else if (!empty($oSequence->oDialplan) && ctype_digit(($oSequence->oDialplan->application_id))) {
        $oApplication = Application::load($oSequence->oDialplan->application_id);
      }
    }

    // process application
    $oApplication->_process($oTransmission, $oSequence);
    $this->application_completed($oApplication);

    /**
     * ********************************************* Process program Results **
     */
    $this->process();
  }

}
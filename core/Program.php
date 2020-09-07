<?php

namespace ICT\Core;

/* * ***************************************************************
 * Copyright © 2015 ICT Innovations Pakistan All Rights Reserved   *
 * Developed By: Nasir Iqbal                                       *
 * Website : http://www.ictinnovations.com/                        *
 * Mail : nasir@ictinnovations.com                                 *
 * *************************************************************** */

use ICT\Core\Exchange\Dialplan;

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
      'type',
      'data'
  );

  /**
   * @property-read integer $program_id
   * @var integer 
   */
  public $program_id = null;

  /** @var string */
  public $name = 'unknown';

  /**
   * @property-read string $type
   * @var string 
   */
  protected $type = 'program';

  /**
   * @property-read array $data
   * @var array
   */
  protected $data = array();

  /** @var integer */
  public $parent_id = null;

  /**
   * @property-read integer $user_id
   * owner id of current record
   * @var integer
   */
  public $user_id = NULL;

  /**
   * **************************************************** Program Parameters **
   */

  /**
   * account_id of account associated with this program
   * @var int $account_id
   */
  public $account_id = null;

  /**
   * ************************************************ Default Program Values **
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
      'result' => array('success', 'error'),
      'error' => ''
  );

  /**
   * ***************************************************** Runtime Variables **
   */

  /** @var Data */
  protected $aResource = array();

  /** @var array */
  public $result = null;

  /** @var Transmission */
  protected $oTransmission = null;

  public function __construct($program_id = null, $aParameter = null)
  {
    if (!empty($aParameter) && is_array($aParameter)) {
      $this->parameter_load($aParameter);
    }
    if (!empty($program_id)) {
      $this->program_id = $program_id;
      $this->_load();
    }
  }

  public function token_load()
  {
    foreach ($this->aResource as $name => $value) {
      if (is_object($value) && method_exists($value, 'token_load')) {
        $value->token_load();
      }
      $this->{$name} = $value;
    }
  }

  public function token_resolve()
  {
    // first resolve all token variables
    $oToken = new Token(Token::SOURCE_ALL);
    $oToken->add('program', $this);

    $parameterList = $this->parameter_save();
    foreach ($parameterList as $name => $value) {
      $this->{$name} = $oToken->render_variable($value);
    }

    // and then load all available resources
    $this->resource_load();
  }

  /**
   * set all aditional program properties according to given aParameter array
   * @param array $aParameter
   */
  public function parameter_load($aParameter)
  {
    foreach($aParameter as $name => $value) {
      $this->{$name} = $value;
    }
  }

  /**
   * return a name value pair of all aditional program parameters which we need to save
   * @return array
   */
  public function parameter_save()
  {
    $aParameters = array(
        'account_id' => $this->account_id
    );
    return $aParameters;
  }

  public static function search($aFilter = array())
  {
    $aProgram = array();
    $from_str = self::$table;
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

        case 'user_id':
        case 'created_by':
          $aWhere[] = "created_by = '$search_value'";
          break;
        case 'before':
          $aWhere[] = "date_created <= $search_value";
          break;
        case 'after':
          $aWhere[] = "date_created >= $search_value";
          break;
      }
    }
    if (!empty($aWhere)) {
      $from_str .= ' WHERE ' . implode(' AND ', $aWhere);
    }

    $query = "SELECT program_id, name, type, parent_id FROM " . $from_str;
    Corelog::log("program search with $query", Corelog::DEBUG, array('aFilter' => $aFilter));
    $result = DB::query('program', $query);
    while ($data = mysql_fetch_assoc($result)) {
      $aProgram[] = $data;
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
      $aProgram[] = $data;
    }
    Corelog::log("Child program search for program: $program_id", Corelog::CRUD, $aProgram);
    return $aProgram;
  }

  /**
   * Function: scheme
   * Program scheme for primary transmission, application execution order
   * and conditions
   */
  public function scheme()
  {
    Corelog::log("Creating program scheme", Corelog::LOGIC);

    $app1st = new Application();
    $app1st->name = 'application1';

    $app2nd = new Application();
    $app2nd->name = 'application2';

    $app3rd = new Application();
    $app3rd->name = 'application3';

    $oScheme = new Scheme($app1st);
    $app2Node = $oScheme->node_create(array('result' => 'success'));
    $app2Node->link($app2nd);
    $app3Node = $oScheme->node_create(array('result' => 'error'));
    $app3Node->link($app3rd);

    return $oScheme;
  }

  public function deploy()
  {
    Corelog::log("Constructing porgram", Corelog::LOGIC);

    if (!empty($this->program_id)) {
      $this->remove();
    }

    $this->resource_load();

    $oScheme = $this->scheme();
    return $oScheme->compile($this);
  }

  public function remove()
  {
    // first of all uninstall this program from dialplan
    $this->remove_dialplan();

    // then delete all application linked to this program
    $listApplication = Application::search($this->program_id);
    foreach ($listApplication as $aApplication) {
      $oApplication = Application::load($aApplication['application_id']);
      $oApplication->delete();
    }

    // then delete all child programs
    $listChild = $this->search_child($this->program_id);
    foreach ($listChild as $childProgram) {
      $oProgram = Program::load($childProgram['program_id']);
      $oProgram->delete();
    }

    // also delete all resources
    DB::delete(self::$table . '_resource', 'program_id', $this->program_id);
  }

  public function remove_dialplan()
  {
    $filter = array('program_id' => $this->program_id);
    $listDialplan = Dialplan::search($filter);
    foreach ($listDialplan as $aDialplan) {
      $oDialplan = new Dialplan($aDialplan['dialplan_id']);
      $oDialplan->delete();
    }
  }

  public static function getClass(&$program_id, $namespace = 'ICT\\Core\\Program')
  {
    if (ctype_digit(trim($program_id))) {
      $query = "SELECT type FROM " . self::$table . " WHERE program_id='%program_id%' ";
      $result = DB::query(self::$table, $query, array('program_id' => $program_id));
      if (is_resource($result)) {
        $program_type = mysql_result($result, 0);
      }
    } else {
      $program_type = $program_id;
      $program_id   = null;
    }
    $class_name = ucfirst(strtolower(trim($program_type)));
    if (!empty($namespace)) {
      $class_name = $namespace . '\\' . $class_name;
    }
    if (class_exists($class_name, true)) {
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
    if ($data) {
      $this->program_id = $data['program_id'];
      $this->name = $data['name'];
      $this->type = $data['type'];
      $this->data = json_decode($data['data'], true);
      $this->parent_id = $data['parent_id'];
      $this->user_id = $data['created_by'];

      // expand data field and load all additional program parameters
      $this->parameter_load($this->data, true);

      Corelog::log("Program loaded: $this->name", Corelog::CRUD);
    } else {
      throw new CoreException('404', 'Program not found');
    }
  }

  /**
   * Locate and load account
   * Use account_id from program parameters as reference
   * @return Account null or a valid account object
   */
  protected function resource_load_account()
  {
    if (!Token::is_token($this->account_id) && !empty($this->account_id)) {
      $oAccount = new Account($this->account_id);
      return $oAccount;
    }
  }

  /**
   * Locate and load objects / resources required by program
   * Note: Wrapper function for all resource hook starting with resource_load_
   * Note: target function can use program parameters to locate ids and references
   */
  protected function resource_load()
  {
    $methodList = get_class_methods($this);
    foreach ($methodList as $resourceLoader) {
      if (0 === strpos($resourceLoader, 'resource_load_')) {
        $aResource = $this->{$resourceLoader}();
        if (is_object($aResource)) { // only objects are allowed in resources
          $resource_name = str_replace('resource_load_', '', $resourceLoader);
          $this->aResource[$resource_name] = $aResource;
        }
      }
    }
  }

  /**
   * Save references to all required objects and resources
   * to maintain their available and audit
   */
  public function resource_save()
  {
    // first make them available
    $this->resource_load();

    foreach ($this->aResource as $name => $value) {
      $id_field = isset($value->id) ? 'id' : $name.'_id';
      // in case of $value is not object we don't need to save its id
      if (is_object($value) && $value->{$id_field}) {
        $fields = array(
            'program_id' => $this->program_id,
            'resource_type' => $name,
            'resource_id' => $value->{$id_field}
        );
        DB::update(self::$table . '_resource', $fields);
      }
    }
  }

  /**
   * Search for programs which are dependents on given resource / object
   * @param string $resource_type
   * @param int $resource_id
   * @return array return program list which depends on provided resource / object
   */
  public static function resource_search($resource_type, $resource_id)
  {
    $aProgram = array();

    $query = "SELECT program_id FROM " . self::$table . "_resource WHERE resource_type='%resource_type%' AND resource_id=%resource_id%";
    $result = DB::query(self::$table . "_resource", $query, array('resource_type' => $resource_type, 'resource_id' => $resource_id));
    while ($resource = mysql_fetch_assoc($result)) {
      $aProgram[] = $resource;
    }

    if (empty($aProgram)) {
      return false;
    } else {
      return $aProgram;
    }
  }

  public function delete()
  {
    Corelog::log("Deleting program: $this->program_id", Corelog::CRUD);

    // first of all uninstall this program from dialplan
    $this->remove();

    return DB::delete(self::$table, 'program_id', $this->program_id);
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
    return $this->program_id;
  }

  public function get_data()
  {
    return $this->parameter_save();
  }

  public function save()
  {
    // collect all aditional parameter to save in database
    $this->data = $this->parameter_save();
    $data = array(
        'program_id' => $this->program_id,
        'name' => $this->name,
        'type' => $this->type,
        'data' => json_encode($this->data, JSON_NUMERIC_CHECK),
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
    // but first clear all old resource and then save new resource
    DB::delete(self::$table . '_resource', 'program_id', $this->program_id);
    $this->resource_save();

    return $result;
  }

  /**
   * Function: transmission_instant
   * Wrapper function to transmission_create, in addition it will also prepare Program from scratch
   * @return Transmission return instance of newly created transmission
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
    foreach ($listApplication as $aApplication) {
      $oApplication = Application::load($aApplication['application_id']);
      $oApplication->_execute($this->oTransmission);
      $result = true;
    }
    return $result;
  }

  public function _execute(Transmission &$oTransmission)
  {
    Corelog::log("Executing program : $this->type($this->program_id)", Corelog::FLOW);

    $this->oTransmission = &$oTransmission;

    // before processing update parameters with available tokens
    $this->token_resolve();

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
   * Locate account_id and contact_id for given request
   * @param Request $oRequest
   * @param Dialplan $oDialplan
   * @return int account_id and false in case of failure
   */
  public function authorize(Request $oRequest, Dialplan $oDialplan)
  {
    if ($oDialplan->context == 'internal') {
      $account = $oRequest->source;
      $contact = $oRequest->destination;
    } else {
      $account = $oRequest->destination;
      $contact = $oRequest->source;
    }

    // search fo existing account and contact
    $oGateway = Gateway::load($oDialplan->gateway_flag);
    $contactField = $oGateway::CONTACT_FIELD;
    if (empty($contactField)) {
      $contactField = $oGateway::CONTACT_ANONYMOUS;
    }
    $oAccount = Core::locate_account($account, $contactField);
    if ($oAccount) {
      $oContact = Core::locate_contact($contact, $contactField);
      return array('account' => $oAccount, 'contact' => $oContact);
    } else {
      return false; // no account found
    }
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

      // check for transmission status, and trigger program completed if required
      if (Transmission::STATUS_COMPLETED == $this->oTransmission->status) {
        $this->program_completed('primary', $this);
      }

      // if their is parent also trigger its program_completed event,
      // in either case if current transmission is complated or failed
      if ($this->oTransmission->is_done() && !empty($this->parent_id)) {
        $parentProgram = Program::load($this->parent_id);
        $parentProgram->oTransmission = &$this->oTransmission;
        $parentProgram->program_completed('associated', $this);
      }
    }
  }

  /**
   * Wrapper function for above process function 
   */
  public function _process(Transmission &$oTransmission)
  {
    Corelog::log("Processing with program : $this->type($this->program_id)", Corelog::FLOW);

    // make input variable available at class level
    $this->oTransmission = &$oTransmission;

    // before processing update program parameters with available tokens
    $this->token_resolve();

    /**
     * ***************************************** Process application results **
     */
    // load request application
    if ($oTransmission->status == Transmission::STATUS_INITIALIZING) { // For new transmission
      // first of all change transmission status
      $oTransmission->status = Transmission::STATUS_PROCESSING;
      $listApplication = Application::search($this->program_id, Application::ORDER_INIT);
      foreach ($listApplication as $aApplication) {
        $oApplication = Application::load($aApplication['application_id']);
        break; // only one application is needed
      }
    } else { // in case of existing in-process transmission
      // use application id from request if not present then
      // check if dialplan provide any reference to application
      $oSession = Session::get_instance();
      if (!empty($oSession->request->application_id)) {
        $oApplication = Application::load($oSession->request->application_id);
      } else if (!empty($oSession->dialplan) && ctype_digit(($oSession->dialplan->application_id))) {
        $oApplication = Application::load($oSession->dialplan->application_id);
      }
    }

    // process application
    $oApplication->_process($oTransmission);
    $this->application_completed($oApplication);

    /**
     * ********************************************* Process program Results **
     */
    $this->process();
  }

}

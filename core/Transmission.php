<?php

namespace ICT\Core;

/* * ***************************************************************
 * Copyright © 2014 ICT Innovations Pakistan All Rights Reserved   *
 * Developed By: Nasir Iqbal                                       *
 * Website : http://www.ictinnovations.com/                        *
 * Mail : nasir@ictinnovations.com                                 *
 * *************************************************************** */

use Exception;

class Transmission
{

  /** @const */
  const STATUS_PENDING = 'pending';
  const STATUS_PENDING_RETRY = 'pending_retry';
  const STATUS_INITIALIZING = 'initializing';
  const STATUS_PROCESSING = 'processing';
  const STATUS_DONE = 'done'; // when transmission is done, and we don't know if it was completed or failed
  const STATUS_COMPLETED = 'completed';
  const STATUS_FAILED = 'failed';
  const STATUS_INVALID = 'invalid';
  const INTERNAL = 'internal'; // currently not in use
  const INBOUND = 'inbound';
  const OUTBOUND = 'outbound';

  private static $pendingStatus = array(
      Transmission::STATUS_PENDING,
      Transmission::STATUS_PENDING_RETRY
  );
  private static $doneStatus = array(
      Transmission::STATUS_COMPLETED,
      Transmission::STATUS_FAILED,
      Transmission::STATUS_INVALID
  );

  /**
   * ********************************************* Transmission related data **
   */
  private static $table = 'transmission';
  private static $fields = array(
      'transmission_id',
      'title',
      'service_flag',
      'account_id',
      'contact_id',
      'program_id',
      'origin',
      'direction',
      'status',
      'response',
      'try_allowed',
      'try_done',
      'last_run',
      'is_deleted',
      'campaign_id'
  );
  private static $read_only = array(
      'transmission_id',
      'try_done',
      'last_run',
      'is_deleted'
  );

  /**
   * @property-read integer $transmission_id
   * @var integer
   */
  public $transmission_id = NULL;

  /** @var string */
  public $title = 'Unknown';

  /** @var string */
  public $service_flag = NULL;

  /**
   * @property integer $account_id
   * @see Transmission::set_account_id()
   * @var integer
   */
  private $account_id = NULL;

  /**
   * @property-write integer $phone
   * Create new contact from given phone number
   * @see Transmission::set_phone()
   */
  /**
   * @property-write integer $email
   * Create new contact from given email address
   * @see Transmission::set_email()
   */

  /**
   * @property integer $contact_id
   * @see Transmission::set_contact_id()
   * @var integer
   */
  private $contact_id = NULL;

  /** @var string */
  public $program_id = NULL;

  /** @var string */
  public $origin = NULL;

  /** @var string */
  public $direction = NULL;

  /**
   * @property string $status
   * @see Transmission::set_status()
   * @var string 
   */
  private $status = Transmission::STATUS_PENDING;

  /** @var string */
  public $response = NULL;

  /** @var integer */
  public $try_allowed = 1;

  /**
   * @property-read integer $try_done
   * @var integer 
   */
  private $try_done = 0;

  /**
   * @property integer $last_run
   * @var integer 
   */
  public $last_run = NULL;

  /**
   * @property-read integer $is_deleted
   * 0 = no deleted, 1 = deleted
   * @var integer
   */
  private $is_deleted = 0;

  /**
   * $property-read integer $campaign_id
   * @var integer
   */
  public $campaign_id = NULL;

  /**
   * @property-read integer $user_id
   * owner id of current record
   * @var integer
   */
  public $user_id = NULL;

  /**
   * ***************************************************** Runtime Variables **
   * all runtime variable for transmission are public
   */

  /** @var Account $oAccount  */
  public $oAccount = null;

  /** @var Contact $oContact  */
  public $oContact = null;

  /** @var Spool $oSpool  */
  public $oSpool = null;

  /** @var Result[] $aResult  */
  public $aResult = array();

  public function __construct($transmission_id = NULL)
  {
    if (!empty($transmission_id)) {
      $this->transmission_id = $transmission_id;
      $this->load();
    }
  }

  public function token_load()
  {
    $this->account = $this->oAccount;
    $this->contact = $this->oContact;
    if ($this->direction == Transmission::INBOUND) {
      $this->destination = $this->oAccount;
      $this->source = $this->oContact;
    } else if ($this->direction == Transmission::OUTBOUND) {
      $this->destination = $this->oContact;
      $this->source = $this->oAccount;
    }
    $this->spool = $this->oSpool;
    $this->result = $this->aResult;
  }

  public static function search($aFilter = array())
  {
    $aTransmission = array();
    $from_str = self::$table;
    $aWhere = array();
    foreach ($aFilter as $search_field => $search_value) {
      switch ($search_field) {
        case 'transmission_id':
        case 'program_id':
        case 'account_id':
        case 'contact_id':
        case 'campaign_id':
          $aWhere[] = "$search_field = $search_value";
          break;
        case 'service_flag':
          $aWhere[] = "($search_field & $search_value) = $search_value";
          break;
        case 'title':
        case 'origin':
        case 'direction':
        case 'status':
        case 'response':
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

    $query = "SELECT transmission_id, account_id, contact_id, status, response, direction, last_run FROM " . $from_str;
    Corelog::log("transmission search with $query", Corelog::DEBUG, array('aFilter' => $aFilter));
    $result = DB::query('transmission', $query);
    while ($data = mysql_fetch_assoc($result)) {
      $aTransmission[] = $data;
    }

    return $aTransmission;
  }

  private function load()
  {
    $query = "SELECT * FROM " . self::$table . " WHERE transmission_id='%transmission_id%' ";
    $result = DB::query(self::$table, $query, array('transmission_id' => $this->transmission_id));
    $data = mysql_fetch_assoc($result);
    if ($data) {
      $this->transmission_id = $data['transmission_id'];
      $this->title = $data['title'];
      $this->service_flag = $data['service_flag'];
      $this->account_id = $data['account_id'];
      $this->contact_id = $data['contact_id'];
      $this->program_id = $data['program_id'];
      $this->origin = $data['origin'];
      $this->direction = $data['direction'];
      $this->status = $data['status'];
      $this->response = $data['response'];
      $this->try_allowed = $data['try_allowed'];
      $this->try_done = $data['try_done'];
      $this->last_run = $data['last_run'];
      $this->is_deleted = $data['is_deleted'];
      $this->campaign_id = $data['campaign_id'];
      $this->user_id = $data['created_by'];

      $this->oAccount = new Account($this->account_id);
      $this->oContact = new Contact($this->contact_id);

      //$this->load_session();
      Corelog::log("Transmission loaded transmission_id: $this->transmission_id", Corelog::CRUD);
    } else {
      throw new CoreException('404', 'Transmission not found');
    }
  }

  /* yet we don't need any sessions
    private function load_session() {
    return true; // session disabled
    ini_set('session.use_cookies', '0'); // we don't dealing with broswers so no cookies
    session_write_close(); // first kill existing one
    session_id($this->transmission_id);
    session_start();
    } */

  public function delete()
  {
    Corelog::log("Transmission delete", Corelog::CRUD);
    // first delete all associated schedules
    $this->task_cancel();
    // TODO: Don't delete instead mark it as deleted or also delete related spool and result records
    return DB::delete(self::$table, 'transmission_id', $this->transmission_id);
  }

  public function is_pending()
  {
    if (in_array($this->status, Transmission::$pendingStatus)) {
      return TRUE;
    }
    return FALSE;
  }

  public function is_done()
  {
    if (in_array($this->status, Transmission::$doneStatus)) {
      return TRUE;
    }
    return FALSE;
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
    return $this->transmission_id;
  }

  private function set_account_id($account_id)
  {
    $this->oAccount = new Account($account_id);
    $this->account_id = $this->oAccount->account_id;
  }

  private function set_contact_id($contact_id)
  {
    $this->oContact = new Contact($contact_id);
    $this->contact_id = $this->oContact->contact_id;
  }

  private function set_status($status)
  {
    // rollout all un nessary updates
    if ($this->status == $status) {
      return; // save value so no updated needed
    }
    if (($this->is_done() && Transmission::STATUS_PENDING_RETRY != $status)) {
      return; // only retry are allowed if transmission is already completed
    }

    switch ($status) {
      case Transmission::STATUS_FAILED:
        if ($this->try_allowed > $this->try_done) {
          $this->status = Transmission::STATUS_PENDING_RETRY;
          $this->schedule_create(array('delay' => 60)); // retry after 60 seconds
        }
        break;
    }
    $this->status = $status;
  }

  private function set_phone($phone)
  {
    $oContact = Contact::construct_from_array(array('phone' => $phone));
    $oContact->save();
    $this->set_contact_id($oContact->contact_id);
  }

  private function set_email($email)
  {
    $oContact = Contact::construct_from_array(array('email' => $email));
    $oContact->save();
    $this->set_contact_id($oContact->contact_id);
  }

  public function save()
  {
    $this->account_id = $this->oAccount->account_id;
    $this->contact_id = $this->oContact->contact_id;

    $data = array(
        'transmission_id' => $this->transmission_id,
        'title' => $this->title,
        'service_flag' => $this->service_flag,
        'account_id' => $this->account_id,
        'contact_id' => $this->contact_id,
        'program_id' => $this->program_id,
        'origin' => $this->origin,
        'direction' => $this->direction,
        'status' => $this->status,
        'response' => $this->response,
        'try_allowed' => $this->try_allowed,
        'try_done' => $this->try_done,
        'last_run' => $this->last_run,
        'is_deleted' => $this->is_deleted,
        'campaign_id' => $this->campaign_id
    );

    if (isset($data['transmission_id']) && !empty($data['transmission_id'])) {
      // update existing record
      $result = DB::update(self::$table, $data, 'transmission_id');
      Corelog::log("Transmission updated: $this->transmission_id", Corelog::CRUD);
    } else {
      // add new
      $result = DB::update(self::$table, $data, false);
      $this->transmission_id = $data['transmission_id'];
      Corelog::log("New Transmission created: $this->transmission_id", Corelog::CRUD);
    }

    //$this->load_session();

    return $result;
  }

  public function __clone()
  {
    $this->transmission_id = NULL;
    $this->status = Transmission::STATUS_PENDING;
    $this->response = NULL;
    $this->try_done = 0;
    $this->last_run = NULL;
    $this->is_deleted = 0;   // not deleteds
  }

  public function schedule_create($schedule_data = array())
  {
    if (empty($schedule_data)) {
      $schedule_data = array(
          'status' => Task::ONHOLD,
          'delay' => 60
      );
    }
    return $this->task_create($schedule_data);
  }

  public function task_create($task_data = array())
  {
    if (empty($task_data)) {
      $task_data = array('status' => Task::PENDING);
    }
    $oSchedule = new Schedule();
    $oSchedule->type = 'transmission';
    $oSchedule->action = 'send';
    $oSchedule->data = $this->transmission_id;
    $oSchedule->account_id = $this->account_id;
    foreach ($task_data as $schedule_field => $schedule_value) {
      $oSchedule->$schedule_field = $schedule_value;
    }
    $oSchedule->save();
    return $oSchedule->schedule_id;
  }

  public function task_cancel()
  {
    $aSchedule = Schedule::search(array('type' => 'transmission', 'data' => $this->transmission_id));
    foreach ($aSchedule as $schedule) {
      $oSchedule = new Schedule($schedule['schedule_id']);
      $oSchedule->delete();
    }
  }

  public static function task_process(Task $oTask)
  {
    try {
      $oTransmission = new self($oTask->data); // data is transmission_id
      switch ($oTask->action) {
        case 'send':
          // before sending transmission from schedule 
          // remember to login its owner
          $oTransmission->activate_owner();
          $oTransmission->send();
          break;
        default:
          throw new CoreException("500", "Unknown task action, Unable to continue!");
      }
    } catch (Exception $ex) {
      Corelog::log($ex->getMessage(), Corelog::ERROR);
      Corelog::log("Unable to process transmission task", Corelog::ERROR);
    }

    // in either case remember to remove the task
    $oTask->delete();
  }

  public function send()
  {
    if (Transmission::STATUS_INVALID == $this->status) {
      throw new CoreException('423', "Invalid transmission, unable to process");
    } else if (Transmission::STATUS_PROCESSING == $this->status) {
      throw new CoreException('423', 'Transmission already in process');
    } else if (Transmission::STATUS_COMPLETED == $this->status) {
      throw new CoreException('423', "Transmission completed, request denied");
    }

    Corelog::log("transmission send, transmission_id=" . $this->transmission_id, Corelog::CRUD);

    $this->status = Transmission::STATUS_PROCESSING;
    $this->try_done++;
    $this->last_run = time();
    $this->save();

    // send current transmission
    return Core::send($this);
  }

  public function activate_owner()
  {
    // Load concerned user credentials to permissions
    if (empty($this->account_id) || empty($this->oAccount->user_id)) {
      throw new CoreException("500", "Can't activate transmission user! invalid account_id");
    }

    try {
      $oUser = do_login($this->oAccount->user_id);
      if (empty($oUser)) {
        throw new CoreException("500", "Unknown error while loading transmission owner");
      }
    } catch (Exception $ex) {
      throw new CoreException("500", "Unable to activate transmission owner user", $ex);
    }

    return $oUser;
  }

  public function &spool_create($spool_id = null)
  {
    if (empty($spool_id)) {
      // create new spool
      $this->oSpool = new Spool();
      // and copy from settings transmission
      $this->oSpool->transmission_id = $this->transmission_id;
      $this->oSpool->account_id = $this->account_id;
      $this->oSpool->service_flag = $this->service_flag;
      $this->oSpool->save(); // save spool to generate a spool id
    } else {
      // load existing spool record
      $this->oSpool = new Spool($spool_id);
      $this->result_load();
    }

    return $this->oSpool;
  }

  public function &result_create($data, $name, $type = Result::TYPE_APPLICATION, $application_id = '')
  {
    Corelog::log("New result, type: " . $type . ", name: " . $name . ", data: " . print_r($data, true), Corelog::LOGIC);
    $this->aResult[$name] = new Result();
    $this->aResult[$name]->spool_id = isset($this->oSpool) ? $this->oSpool->spool_id : null;
    $this->aResult[$name]->name = $name;
    $this->aResult[$name]->data = $data;
    $this->aResult[$name]->type = $type;
    $this->aResult[$name]->application_id = $application_id;
    return $this->aResult[$name];
  }

  public function result_associate($application_type, $application_id)
  {
    foreach ($this->aResult as $oResult) {
      if ($oResult->application_id == $application_type) {
        $this->application_id = $application_id;
      }
    }
  }

  public function &result_load()
  {
    $listResult = Result::search(array('spool_id' => $this->oSpool->spool_id));
    foreach ($listResult as $aResult) {
      $oResult = new Result($aResult['spool_result_id']);
      $this->aResult[$oResult->name] = $oResult;
    }
    return $this->aResult;
  }

}

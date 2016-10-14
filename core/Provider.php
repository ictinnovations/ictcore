<?php
/* * ***************************************************************
 * Copyright © 2014 ICT Innovations Pakistan All Rights Reserved   *
 * Developed By: Nasir Iqbal                                       *
 * Website : http://www.ictinnovations.com/                        *
 * Mail : nasir@ictinnovations.com                                 *
 * *************************************************************** */

define('PROVIDER_DEFAULT', -1);

class Provider
{

  /** @const */
  private static $table = 'provider';
  private static $primary_key = 'provider_id';
  private static $fields = array(
      'provider_id',
      'name',
      'gateway_flag',
      'service_flag',
      'technology_id',
      'node_id',
      'host',
      'port',
      'username',
      'password',
      'dialstring',
      'prefix',
      'settings',
      'register',
      'weight',
      'type',
      'active'
  );
  private static $read_only = array(
      'provider_id'
  );

  /**
   * @property-read integer $provider_id
   * @var integer
   */
  private $provider_id = NULL;

  /** @var string */
  public $name = NULL;

  /**
   * @property-read integer $gateway_flag 
   * @see Provider::set_service_flag()
   * @var integer 
   */
  private $gateway_flag = NULL;

  /**
   * @property integer $service_flag 
   * @see Provider::set_service_flag()
   * @var integer 
   */
  private $service_flag = NULL;

  /** @var integer */
  public $technology_id = NULL;

  /** @var integer */
  public $node_id = NULL;

  /** @var string */
  public $host = NULL;

  /** @var string */
  public $port = NULL;

  /** @var string */
  public $username = NULL;

  /** @var string */
  public $password = NULL;

  /** @var string */
  public $dialstring = NULL;

  /** @var string */
  public $prefix = NULL;

  /** @var string */
  public $settings = NULL;

  /** @var integer */
  public $register = 0;

  /** @var integer */
  public $weight = NULL;

  /** @var string */
  public $type = NULL;

  /** @var integer */
  public $active = NULL;

  public function __construct($provider_id = NULL, $service_flag = NULL)
  {
    if (!empty($provider_id) && $provider_id != PROVIDER_DEFAULT) {
      $this->provider_id = $provider_id;
      $this->load();
    } else if (!empty($service_flag)) {
      $query = "SELECT provider_id FROM " . self::$table . " 
                 WHERE active=1 AND (service_flag & $service_flag = $service_flag)
                 ORDER BY provider_id DESC LIMIT 1";
      $result = DB::query(self::$table, $query, array('provider_id' => $this->provider_id));
      $data = mysql_fetch_assoc($result);
      $this->provider_id = $data['provider_id'];
      $this->load();
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

  public static function search($aFilter = array())
  {
    $aProvider = array();
    $from_str = self::$table;
    $aWhere = array();
    foreach ($aFilter as $search_field => $search_value) {
      switch ($search_field) {
        case 'provider_id':
        case 'name':
        case 'node_id':
        case 'host':
          $aWhere[] = "$search_field = '$search_value'";
          break;
        case 'gateway_flag':
        case 'service_flag':
          $aWhere[] = "($search_field | $search_value) = $search_value";
          break;
      }
    }
    if (!empty($aWhere)) {
      $from_str .= ' WHERE ' . implode(' AND ', $aWhere);
    }
    $query = "SELECT provider_id, name, host, gateway_flag, service_flag, node_id FROM " . $from_str;
    Corelog::log("provider search with $query", Corelog::DEBUG, array('aFilter' => $aFilter));
    $result = DB::query(self::$table, $query);
    while ($data = mysql_fetch_assoc($result)) {
      $aProvider[$data['provider_id']] = $data;
    }

    return $aProvider;
  }

  private function load()
  {
    $query = "SELECT * FROM " . self::$table . " WHERE provider_id='%provider_id%' ";
    $result = DB::query(self::$table, $query, array('provider_id' => $this->provider_id));
    $data = mysql_fetch_assoc($result);
    if ($data) {
      $this->provider_id = $data['provider_id'];
      $this->name = $data['name'];
      $this->gateway_flag = $data['gateway_flag'];
      $this->service_flag = $data['service_flag'];
      $this->technology_id = $data['technology_id'];
      $this->node_id = $data['node_id'];
      $this->host = $data['host'];
      $this->port = $data['port'];
      $this->username = $data['username'];
      $this->password = $data['password'];
      $this->dialstring = $data['dialstring'];
      $this->prefix = $data['prefix'];
      $this->settings = $data['settings'];
      $this->register = $data['register'];
      $this->weight = $data['weight'];
      $this->type = $data['type'];
      $this->active = $data['active'];
      Corelog::log("Provider loaded name: $this->name", Corelog::CRUD);
    } else {
      throw new CoreException('404', 'Provider not found');
    }
  }

  public function delete()
  {
    Corelog::log("Provider delete", Corelog::CRUD);
    return DB::delete(self::$table, 'provider_id', $this->provider_id, true);
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

  private function set_service_flag($service_flag)
  {
    $this->service_flag = $service_flag;
    $service_class = service_flag_to_class($this->service_flag);
    $gateway_class = $service_class::GATEWAY_CLASS;
    $this->gateway_flag = $gateway_class::GATEWAY_FLAG;
  }

  public function save()
  {
    $data = array(
        'provider_id' => $this->provider_id,
        'name' => $this->name,
        'gateway_flag' => $this->gateway_flag,
        'service_flag' => $this->service_flag,
        'technology_id' => $this->technology_id,
        'node_id' => $this->node_id,
        'host' => $this->host,
        'port' => $this->port,
        'username' => $this->username,
        'password' => $this->password,
        'dialstring' => $this->dialstring,
        'prefix' => $this->prefix,
        'settings' => $this->settings,
        'register' => $this->register,
        'weight' => $this->weight,
        'type' => $this->type,
        'active' => $this->active
    );

    if (isset($data['provider_id']) && !empty($data['provider_id'])) {
      // update existing record
      $result = DB::update(self::$table, $data, 'provider_id', true);
      Corelog::log("Provider updated: $this->provider_id", Corelog::CRUD);
    } else {
      // add new
      $result = DB::update(self::$table, $data, false, true);
      $this->provider_id = $data['provider_id'];
      Corelog::log("New Provider created: $this->provider_id", Corelog::CRUD);
    }

    if ($result) {
      $oToken = new Token();
      $oToken->add('provider', $this);

      // get gateway class name
      $gateway_class = gateway_flag_to_class($this->gateway_flag);
      // Create new instance of gateway class to save provider info
      $oGateway = new $gateway_class();
      $aSetting = $oToken->token_replace($oGateway->template_provider());
      $oGateway->save_provider($this->name, $aSetting);

      return TRUE;
    }
  }

  function update_config()
  {
    $query = DB::query('provider', "SELECT provider_id FROM provider WHERE active=1");

    while ($aProvider = mysql_fetch_array($query)) {
      
    }

    return true;
  }

}
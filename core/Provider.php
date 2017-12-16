<?php

namespace ICT\Core;

/* * ***************************************************************
 * Copyright © 2014 ICT Innovations Pakistan All Rights Reserved   *
 * Developed By: Nasir Iqbal                                       *
 * Website : http://www.ictinnovations.com/                        *
 * Mail : nasir@ictinnovations.com                                 *
 * *************************************************************** */

class Provider
{

  /** @const */
  private static $table = 'provider';
  private static $primary_key = 'provider_id';
  private static $fields = array(
      'provider_id',
      'name',
      'service_flag',
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
      'provider_id',
      'type'
  );

  /**
   * @property-read integer $provider_id
   * @var integer
   */
  protected $provider_id = NULL;

  /** @var string */
  public $name = NULL;

  /**
   * @property-read string $type
   * @var string
   */
  protected $type = 'provider';

  /**
   * @property integer $service_flag 
   * @var integer 
   */
  public $service_flag = NULL;

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

  /** @var integer */
  public $active = NULL;

  public function __construct($provider_id = NULL)
  {
    if (!empty($provider_id)) {
      $this->provider_id = $provider_id;
      $this->_load();
    }
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
        case 'type':
          $aWhere[] = "$search_field = '$search_value'";
          break;
        case 'service_flag':
          $aWhere[] = "($search_field | $search_value) = $search_value";
          break;
      }
    }
    if (!empty($aWhere)) {
      $from_str .= ' WHERE ' . implode(' AND ', $aWhere);
    }
    $query = "SELECT provider_id, name, host, service_flag, node_id, type FROM " . $from_str;
    Corelog::log("provider search with $query", Corelog::DEBUG, array('aFilter' => $aFilter));
    $result = DB::query(self::$table, $query);
    while ($data = mysql_fetch_assoc($result)) {
      $aProvider[] = $data;
    }

    return $aProvider;
  }

  public static function getClass(&$provider_id, $namespace = 'ICT\\Core\\Provider')
  {
    if (ctype_digit(trim($provider_id))) {
      $query = "SELECT type FROM " . self::$table . " WHERE provider_id='%provider_id%' ";
      $result = DB::query(self::$table, $query, array('provider_id' => $provider_id));
      if (is_resource($result)) {
        $provider_type = mysql_result($result, 0);
      }
    } else {
      $provider_type = $provider_id;
      $provider_id   = null;
    }
    $class_name = ucfirst(strtolower(trim($provider_type)));
    if (!empty($namespace)) {
      $class_name = $namespace . '\\' . $class_name;
    }
    if (class_exists($class_name, true)) {
      return $class_name;
    } else {
      return false;
    }
  }

  public static function load($provider_id)
  {
    $class_name = self::getClass($provider_id);
    if ($class_name) {
      Corelog::log("Creating instance of : $class_name for provider: $provider_id", Corelog::CRUD);
      return new $class_name($provider_id);
    } else {
      Corelog::log("$class_name class not found, Creating instance of : Provider", Corelog::CRUD);
      return new self($provider_id);
    }
  }

  private function _load()
  {
    Corelog::log("Loading provider: $this->provider_id", Corelog::CRUD);
    $query = "SELECT * FROM " . self::$table . " WHERE provider_id='%provider_id%' ";
    $result = DB::query(self::$table, $query, array('provider_id' => $this->provider_id));
    $data = mysql_fetch_assoc($result);
    if ($data) {
      $this->provider_id = $data['provider_id'];
      $this->name = $data['name'];
      $this->service_flag = $data['service_flag'];
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
    return $this->provider_id;
  }

  public function save()
  {
    $data = array(
        'provider_id' => $this->provider_id,
        'name' => $this->name,
        'service_flag' => $this->service_flag,
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

    return $result;
  }

}

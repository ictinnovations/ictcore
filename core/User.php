<?php

namespace ICT\Core;

/* * ***************************************************************
 * Copyright © 2014 ICT Innovations Pakistan All Rights Reserved   *
 * Developed By: Nasir Iqbal                                       *
 * Website : http://www.ictinnovations.com/                        *
 * Mail : nasir@ictinnovations.com                                 *
 * *************************************************************** */

use ICT\Core\User\Role;
use ICT\Core\User\Permission;

class User
{

  const GUEST = -1;

  private static $table = 'usr';
  private static $link_role = 'user_role';
  private static $link_permission = 'user_permission';
  private static $primary_key = 'usr_id';
  private static $fields = array(
      'user_id', // will be mapped to usr_id in database table
      'role_id',
      'role_list', // dummy field to hold comma separated list of roles
      'username',
      'passwd',
      'password_hash', // will be mapped to passwd in database table
      'password', // dummy field to hold plain password, will not be saved in database
      'first_name',
      'last_name',
      'phone',
      'email',
      'address',
      'company',
      'country_id',
      'timezone_id',
      'active'
  );
  private static $read_only = array(
      'user_id',
      'role_id',
      'role_list',
      'password_hash'
  );

  /**
   * @property-read integer $user_id
   * @var integer
   */
  private $user_id = NULL;

  /**
   * @property-read integer $role_id
   * not in use
   * @var integer
   */
  private $role_id = NULL;

  /**
   * @property-read string $role_list
   * comma separated list of role_id, to set role call role_assign
   * @see User::role_assign() and User::role_unassign()
   * @var string
   */
  private $role_list = NULL;

  /**
   * @property-read string $username
   * @see User::set_username
   * @var string
   */
  private $username = NULL;

  /** @var string */
  private $passwd = NULL;

  /**
   * @property-write string $password
   * Accept plain password, which will be imediately converted into md5 hash
   * @see User::set_password
   */
  /**
   * @property-read string $password_hash
   * represent password hash value from database
   * @see User::get_password_hash
   */

  /** @var string */
  public $first_name = NULL;

  /** @var string */
  public $last_name = NULL;

  /** @var string */
  public $phone = NULL;

  /** @var string */
  public $email = NULL;

  /** @var string */
  public $address = NULL;

  /** @var string */
  public $company = NULL;

  /** @var integer */
  public $country_id = NULL;

  /** @var string */
  public $language_id = NULL;

  /** @var integer */
  public $timezone_id = NULL;

  /** @var integer */
  public $active = 0;

  /**
   * ***************************************************** Runtime Variables **
   */

  /** @var Role[] $aRole  */
  private $aRole = array();

  /** @var array $aPermission  */
  private $aPermission = array();

  public function __construct($user_id = NULL)
  {
    if (!empty($user_id)) {
      if (!is_numeric($user_id)) {
        $this->username = $user_id;
      } else {
        $this->user_id = $user_id;
        if (User::GUEST == $user_id) {
          Corelog::log("Guest user: creating instance", Corelog::CRUD);
          $this->user_id = User::GUEST;
          $this->username = 'guest';
          $this->first_name = 'Anonymous';
          $this->last_name = 'Guest';
          $this->email = 'no-reply@example.com';
          $this->phone = '1111111111';
          $this->address = Conf::get('company:address', 'PK');
          return $this->user_id; // don't proceed further
        }
      }
      $this->load();
    }
  }

  public static function search($aFilter = array())
  {
    $aUser = array();
    $from_str = self::$table;
    $aWhere = array();
    foreach ($aFilter as $search_field => $search_value) {
      switch ($search_field) {
        case 'user_id':
          $aWhere[] = "usr_id = $search_value";
          break;
        case 'created_by':
          $aWhere[] = "created_by = $search_value";
          break;
        case 'username':
        case 'phone':
        case 'email':
        case 'first_name':
        case 'last_name':
          $aWhere[] = "$search_field = '$search_value'";
          break;
      }
    }
    if (!empty($aWhere)) {
      $from_str .= ' WHERE ' . implode(' AND ', $aWhere);
    }

    $query = "SELECT usr_id AS user_id, username, first_name, last_name, phone, email FROM " . $from_str;
    Corelog::log("user search with $query", Corelog::DEBUG, array('aFilter' => $aFilter));
    $result = DB::query('user', $query);
    while ($data = mysql_fetch_assoc($result)) {
      $aUser[$data['user_id']] = $data;
    }

    // if no user found, check for guest user
    if (empty($aUser) && isset($aFilter['user_id']) && $aFilter['user_id'] == User::GUEST) {
      $oUser = new User($aFilter['user_id']);
      $aUser[$oUser->user_id] = array(
          'user_id' => $oUser->user_id,
          'username' => $oUser->username,
          'first_name' => $oUser->first_name,
          'last_name' => $oUser->last_name,
          'phone' => $oUser->phone,
          'email' => $oUser->email
      );
    }

    return $aUser;
  }

  private function load()
  {
    Corelog::log("Loading user with id:" . $this->user_id . ' name:' . $this->username, Corelog::CRUD);
    if (!empty($this->username)) {
      $search_field = 'u.username';
      $search_value = $this->username;
    } else {
      $search_field = 'u.usr_id';
      $search_value = $this->user_id;
    }
    $query = "SELECT u.*, GROUP_CONCAT(DISTINCT ur.role_id SEPARATOR ',') AS role_list
              FROM " . self::$table . " u LEFT JOIN " . self::$link_role . " ur ON u.usr_id = ur.usr_id
              WHERE %search_field%='%search_value%'
              GROUP BY u.usr_id";
    $result = DB::query(self::$table, $query, array('search_field' => $search_field, 'search_value' => $search_value));
    $data = mysql_fetch_assoc($result);
    if ($data) {
      $this->user_id = $data['usr_id'];
      $this->role_id = $data['role_id'];
      $this->role_list = $data['role_list'];
      $this->username = $data['username'];
      $this->passwd = $data['passwd'];
      $this->first_name = $data['first_name'];
      $this->last_name = $data['last_name'];
      $this->phone = $data['phone'];
      $this->email = $data['email'];
      $this->address = $data['address'];
      $this->company = $data['company'];
      $this->country_id = $data['country_id'];
      $this->language_id = $data['language_id'];
      $this->timezone_id = $data['timezone_id'];
      $this->active = $data['active'];

      $this->load_role();
      $this->load_permission();
    } else {
      throw new CoreException('404', 'User not found');
    }
  }

  private function load_role()
  {
    $this->aRole = array();
    $listRole = explode(',', $this->role_list);
    foreach ($listRole as $role_id) {
      if (empty($role_id)) {
        continue;
      }
      $this->aRole[$role_id] = new Role($role_id);
    }
  }

  private function load_permission()
  {
    $this->aPermission = array();

    $query = "SELECT up.permission_id FROM " . self::$link_permission . " up WHERE up.usr_id=" . $this->user_id;
    $filter = array('query' => $query);
    $listPermission = Permission::search($filter);
    foreach($listPermission as $permission_id => $aPermission) {
      $this->aPermission[$permission_id] = $aPermission['name'];
    }
  }

  public function delete()
  {
    Corelog::log("Deleting user: $this->user_id", Corelog::CRUD);
    // first remove roles assignements for current user
    $query = 'DELETE FROM ' . self::$link_role . ' WHERE usr_id=%user_id%';
    DB::query(self::$link_role, $query, array('user_id' => $this->user_id), true);
    // then remove permissions for current user
    $query = 'DELETE FROM ' . self::$link_permission . ' WHERE usr_id=%user_id%';
    DB::query(self::$link_permission, $query, array('user_id' => $this->user_id), true);
    // now delete user
    return DB::delete(self::$table, 'user_id', $this->user_id, true);
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
    return $this->user_id;
  }

  private function set_username($username)
  {
    if (empty($this->username)) {
      $this->username = $username;
    }
  }

  private function set_password($password)
  {
    $this->passwd = md5($password);
  }

  private function get_password_hash()
  {
    return $this->passwd;
  }

  public function role_assign($oRole)
  {
    $this->aRole[$oRole->role_id] = $oRole;
    $this->role_list = implode(',', array_keys($this->aRole));
  }

  public function role_unassign($oRole)
  {
    unset($this->aRole[$oRole->role_id]);
  }

  public function permission_assign($oPermission)
  {
    $this->aPermission[$oPermission->permission_id] = $oPermission->name;
  }

  public function permission_unassign($oPermission)
  {
    unset($this->aPermission[$oPermission->permission_id]);
  }

  public function save()
  {
    $data = array(
        'usr_id' => $this->user_id,
        'user_id' => $this->user_id,
        'role_id' => $this->role_id,
        'username' => $this->username,
        'passwd' => $this->password_hash,
        'first_name' => $this->first_name,
        'last_name' => $this->last_name,
        'phone' => $this->phone,
        'email' => $this->email,
        'address' => $this->address,
        'company' => $this->company,
        'country_id' => $this->country_id,
        'language_id' => $this->language_id,
        'timezone_id' => $this->timezone_id,
        'active' => $this->active
            // Note: user_id or created_by field can't be updated here, instead use associate method
    );
    if (!empty($this->password)) {
      $data['passwd'] = md5($this->password);
    }
    if (isset($data['user_id']) && !empty($data['user_id'])) {
      // first remove existing roles assignements
      $query = 'DELETE FROM ' . self::$link_role . ' WHERE usr_id=%user_id%';
      DB::query(self::$link_role, $query, array('user_id' => $this->user_id), true);
      // then remove permissions for current user
      $query = 'DELETE FROM ' . self::$link_permission . ' WHERE usr_id=%user_id%';
      DB::query(self::$link_permission, $query, array('user_id' => $this->user_id), true);
      // update existing record
      DB::update(self::$table, $data, 'user_id', true);
      Corelog::log("User updated: $this->user_id", Corelog::CRUD);
    } else {
      // add new
      DB::update(self::$table, $data, false, true);
      $data['user_id'] = $data['usr_id']; // mapping
      $this->user_id = $data['user_id'];
      Corelog::log("New user created: $this->user_id", Corelog::CRUD);
    }

    // save roles for current user
    foreach ($this->aRole as $oRole) {
      $query = "INSERT INTO " . self::$link_role . " (usr_id, role_id) VALUES (%user_id%, %role_id%)";
      DB::query(self::$link_role, $query, array('user_id' => $this->user_id, 'role_id' => $oRole->role_id), true);
    }

    // save permissions for current user
    foreach ($this->aPermission as $permission_id) {
      $query = "INSERT INTO " . self::$link_permission . " (usr_id, permission_id) VALUES (%user_id%, %permission_id%)";
      $result = DB::query(self::$link_permission, $query, array('user_id' => $this->user_id, 'permission_id' => $permission_id), true);
    }

    return $result;
  }

  public function authenticate($access_key, $key_type = 'password')
  {
    // treat guest user as authenticated
    if ($this->user_id == User::GUEST) {
      return true;
    }
    switch ($key_type) {
      case 'password': // plain password
        if (md5($access_key) == $this->password_hash) {
          return true;
        }
        break;
      case 'password_hash':
        if ($access_key == $this->password_hash) {
          return true;
        }
        break;
      case 'certificate':
        // TODO
        break;
      case 'host':
        // TODO
        break;
    }
    return false;
  }

  public function authorize($permission)
  {
    $aPart = explode('_', $permission);
    $level = count($aPart);
    $perm = '';

    // first check if parent permission exist and then try for sub permissions
    for ($i = 0; $i < $level; $i++) {
      $perm .= $aPart[$i];
      if (in_array($perm, $this->aPermission)) {
        return true;
      } else {
        $perm .= '_';
      }
    }

    // now try with role permissions
    foreach ($this->aRole as $oRole) {
      if ($oRole->authorize($permission)) {
        return true;
      }
    }

    // authorization fialed
    return false;
  }

}

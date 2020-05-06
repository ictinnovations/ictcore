<?php

namespace ICT\Core;

/* * ***************************************************************
 * Copyright © 2014 ICT Innovations Pakistan All Rights Reserved   *
 * Developed By: Nasir Iqbal                                       *
 * Website : http://www.ictinnovations.com/                        *
 * Mail : nasir@ictinnovations.com                                 *
 * *************************************************************** */

use Exception;
use Firebase\JWT\JWT;
use ICT\Core\User\Permission;
use ICT\Core\User\Role;

class User
{

  const GUEST = -1;

  const AUTH_TYPE_BASIC = 'basic';
  const AUTH_TYPE_DIGEST = 'digest';
  const AUTH_TYPE_BEARER = 'bearer';
  const AUTH_TYPE_NETWORK = 'network';

  private static $table = 'usr';
  private static $link_role = 'user_role';
  private static $link_permission = 'user_permission';
  private static $primary_key = 'usr_id';
  private static $fields = array(
      'user_id', // will be mapped to usr_id in database table
      'role_id',
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
      'password_hash'
  );

  /**
   * @property-read integer $user_id
   * @var integer
   */
  public $user_id = NULL;

  /**
   * @property-read integer $role_id
   * not in use
   * @var integer
   */
  private $role_id = NULL;

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

  /** @var integer */
  public $owner_id = null;

  /**
   * ***************************************************** Runtime Variables **
   */

  /**
   * @property-read string $aRole
   * list of Roles, to set role call role_assign
   * @see User::role_assign() and User::role_unassign()
   * @var Role[] $aRole
   */
  private $aRole = array();

  /** @var array $aPermission  */
  private $aPermission = array();

  public function __construct($user_id = NULL)
  {
    if (!empty($user_id)) {
      if (!is_numeric($user_id)) {
        if (filter_var($user_id, FILTER_VALIDATE_EMAIL)) {
          $this->email = $user_id;
        } else {
          $this->username = $user_id;
        }
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

    $query = "SELECT usr_id AS user_id, username, first_name, last_name, phone, email FROM " . $from_str;
    Corelog::log("user search with $query", Corelog::DEBUG, array('aFilter' => $aFilter));
    $result = DB::query('user', $query);
    while ($data = mysql_fetch_assoc($result)) {
      $aUser[] = $data;
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

  public function search_role($aFilter = array()) {
    $aFilter['query'] = "SELECT ur.role_id FROM " . self::$link_role . " ur WHERE ur.usr_id=" . $this->user_id;
    return Role::search($aFilter);
  }

  public function search_permission($aFilter = array()) {
    $aFilter['query'] = "SELECT up.permission_id FROM " . self::$link_permission . " up WHERE up.usr_id=" . $this->user_id;
    return Permission::search($aFilter);
  }

  private function load()
  {
    Corelog::log("Loading user with id:" . $this->user_id . ' name:' . $this->username, Corelog::CRUD);
    if (!empty($this->email)) {
      $search_field = 'u.email';
      $search_value = $this->email;
    } else if (!empty($this->username)) {
      $search_field = 'u.username';
      $search_value = $this->username;
    } else {
      $search_field = 'u.usr_id';
      $search_value = $this->user_id;
    }
    $query = "SELECT u.* FROM " . self::$table . " u WHERE %search_field%='%search_value%'";
    $result = DB::query(self::$table, $query, array('search_field' => $search_field, 'search_value' => $search_value));
    $data = mysql_fetch_assoc($result);
    if ($data) {
      $this->user_id = $data['usr_id'];
      $this->role_id = $data['role_id'];
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
    $listRole = $this->search_role();
    foreach ($listRole as $aRole) {
      $role_id = $aRole['role_id'];
      $this->aRole[$role_id] = new Role($role_id);
    }
  }

  private function load_permission()
  {
    $this->aPermission = array();
    $listPermission = $this->search_permission();
    foreach($listPermission as $aPermission) {
      $permission_id = $aPermission['permission_id'];
      $this->aPermission[$permission_id] = $aPermission['name'];
    }
  }

  public function delete()
  {
    Corelog::log("Deleting user: $this->user_id", Corelog::CRUD);
    // first remove roles assignements for current user
    $query = 'DELETE FROM ' . self::$link_role . ' WHERE usr_id=%user_id%';
    DB::query(self::$link_role, $query, array('user_id' => $this->user_id));
    // then remove permissions for current user
    $query = 'DELETE FROM ' . self::$link_permission . ' WHERE usr_id=%user_id%';
    DB::query(self::$link_permission, $query, array('user_id' => $this->user_id));
    // now delete user
    return DB::delete(self::$table, 'usr_id', $this->user_id);
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

  public function role_assign($role_id)
  {
    $oRole = new Role($role_id);
    $this->aRole[$oRole->role_id] = $oRole;
  }

  public function role_unassign($role_id)
  {
    unset($this->aRole[$role_id]);
  }

  public function permission_assign($permission_id)
  {
    $oPermission = new Permission($permission_id);
    $this->aPermission[$oPermission->permission_id] = $oPermission->name;
  }

  public function permission_unassign($permission_id)
  {
    unset($this->aPermission[$permission_id]);
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
      DB::query(self::$link_role, $query, array('user_id' => $this->user_id));
      // then remove permissions for current user
      $query = 'DELETE FROM ' . self::$link_permission . ' WHERE usr_id=%user_id%';
      DB::query(self::$link_permission, $query, array('user_id' => $this->user_id));
      // update existing record
      $result = DB::update(self::$table, $data, 'usr_id');
      Corelog::log("User updated: $this->user_id", Corelog::CRUD);
    } else {
      // add new
      $result = DB::update(self::$table, $data, false);
      $data['user_id'] = $data['usr_id']; // mapping
      $this->user_id = $data['user_id'];
      Corelog::log("New user created: $this->user_id", Corelog::CRUD);
    }

    // save roles for current user
    foreach ($this->aRole as $oRole) {
      $query = "INSERT INTO " . self::$link_role . " (usr_id, role_id) VALUES (%user_id%, %role_id%)";
      $result = DB::query(self::$link_role, $query, array('user_id' => $this->user_id, 'role_id' => $oRole->role_id));
    }

    // save permissions for current user
    foreach ($this->aPermission as $permission_id) {
      $query = "INSERT INTO " . self::$link_permission . " (usr_id, permission_id) VALUES (%user_id%, %permission_id%)";
      $result = DB::query(self::$link_permission, $query, array('user_id' => $this->user_id, 'permission_id' => $permission_id));
    }

    return $result;
  }

  public function generate_token()
  {
    $key_file = Conf::get('security:private_key', '/usr/ictcore/etc/ssh/ib_node');
    $private_key = file_get_contents($key_file);

    $token = array(
        "iss" => Conf::get('website:url'),
        "iat" => time(),
        "nbf" => time(),
        "exp" => time() + Conf::get('security:token_expiry', (60 * 60 * 24 * 30 * 12 * 1)), // valid for one year
        "user_id" => $this->user_id,
        "username" => $this->username,
        "is_admin" => can_access('user_create', $this->user_id) ? "1" : "0",
        "api-version" => "1.0"
    );

    return JWT::encode($token, $private_key, Conf::get('security:hash_type', 'RS256'));
  }

  public static function authenticate($access_key, $key_type = User::AUTH_TYPE_BASIC)
  {
    $oUser = null;
    switch ($key_type) {
      case User::AUTH_TYPE_BEARER:
        try {
          $key_file = Conf::get('security:public_key', '/usr/ictcore/etc/ssh/ib_node.pub');
          $hash_type = Conf::get('security:hash_type', 'RS256');
          $public_key = file_get_contents($key_file);
          $token = JWT::decode($access_key, $public_key, array($hash_type));
          if ($token) {
            // TODO check api-version
            if (!empty($token->user_id)) {
              $oUser = new self($token->user_id);
              return $oUser;
            }
          }
        } catch (Exception $e) {
          Corelog::log('Unable to parse bearer token. error: ' . $e->getMessage(), Corelog::ERROR);
        }
        Corelog::log('Bearer authentication failed', Corelog::ERROR);
        return false;

      case User::AUTH_TYPE_NETWORK:
        return false; // TODO
      case User::AUTH_TYPE_DIGEST:
        if (!empty($access_key['username'])) {
          $oUser = new self($access_key['username']);
          if ($oUser->get_password_hash() == $access_key['password']) {
            return $oUser;
          }
        }
        Corelog::log('Basic authentication failed', Corelog::ERROR);
        return false;

      case User::AUTH_TYPE_BASIC:
      default:
        if (!empty($access_key['username'])) {
          $oUser = new self($access_key['username']);
          if ($oUser->get_password_hash() == md5($access_key['password'])) {
            return $oUser;
          }
        }
        Corelog::log('Network authentication has been failed', Corelog::ERROR);
        return false;
    }
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

<?php

namespace ICT\Core\User;

/* * ***************************************************************
 * Copyright © 2014 ICT Innovations Pakistan All Rights Reserved   *
 * Developed By: Nasir Iqbal                                       *
 * Website : http://www.ictinnovations.com/                        *
 * Mail : nasir@ictinnovations.com                                 *
 * *************************************************************** */

use ICT\Core\CoreException;
use ICT\Core\Corelog;
use ICT\Core\DB;

class Role
{

  /** @const */
  private static $table = 'role';
  private static $link_permission = 'role_permission';
  private static $primary_key = 'role_id';
  private static $fields = array(
      'role_id',
      'name',
      'description'
  );
  private static $read_only = array(
      'role_id'
  );

  /**
   * @property-read integer $role_id
   * @var integer
   */
  public $role_id = NULL;

  /** @var string */
  public $name = NULL;

  /** @var string */
  public $description = NULL;

  /** @var array $aPermission */
  private $aPermission = array();

  public function __construct($role_id = NULL)
  {
    if (!empty($role_id)) {
      $this->role_id = $role_id;
      $this->load();
    }
  }

  public static function search($aFilter = array())
  {
    $aRole = array();
    $from_str = self::$table;
    $aWhere = array();
    foreach ($aFilter as $search_field => $search_value) {
      switch ($search_field) {
        case 'role_id':
          $aWhere[] = "role_id = $search_value";
          break;
        case 'name':
          $aWhere[] = "name LIKE '$search_value%'";
          break;
        case 'query':
          $aWhere[] = "role_id IN ($search_value)";
      }
    }
    if (!empty($aWhere)) {
      $from_str .= ' WHERE ' . implode(' AND ', $aWhere);
    }

    $query = "SELECT role_id, name FROM " . $from_str;
    Corelog::log("role search with $query", Corelog::DEBUG, array('aFilter' => $aFilter));
    $result = DB::query('role', $query);
    while ($data = mysqli_fetch_assoc($result)) {
      $aRole[] = $data;
    }

    return $aRole;
  }

  private function load()
  {
    Corelog::log("Loading role: $this->role_id", Corelog::CRUD);
    $query = "SELECT * FROM " . self::$table . " WHERE role_id='%role_id%'";
    $result = DB::query(self::$table, $query, array('role_id' => $this->role_id));
    $data = mysqli_fetch_assoc($result);
    if ($data) {
      $this->role_id = $data['role_id'];
      $this->name = $data['name'];
      $this->description = $data['description'];

      $this->load_permission();
    } else {
      throw new CoreException('404', 'Role not found');
    }
  }

  private function load_permission()
  {
    $this->aPermission = array();

    $query = "SELECT rp.permission_id FROM " . self::$link_permission . " rp WHERE rp.role_id=" . $this->role_id;
    $filter = array('query' => $query);
    $listPermission = Permission::search($filter);
    foreach($listPermission as $aPermission) {
      $permission_id = $aPermission['permission_id'];
      $this->aPermission[$permission_id] = $aPermission['name'];
    }
  }

  public function delete()
  {
    Corelog::log("Deleting role: $this->role_id", Corelog::CRUD);
    // first remove permissions for current role
    $query = 'DELETE FROM ' . self::$link_permission . ' WHERE role_id=%role_id%';
    DB::query(self::$link_permission, $query, array('role_id' => $this->role_id));
    // now delete role
    return DB::delete(self::$table, 'role_id', $this->role_id);
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
    return $this->role_id;
  }

  public function permission_assign($permission_id)
  {
    $this->aPermission[$permission_id] = $permission_id;
  }

  public function permission_unassign($permission_id)
  {
    unset($this->aPermission[$permission_id]);
  }

  public function save()
  {
    $data = array(
        'role_id' => $this->role_id,
        'name' => $this->rolename,
        'description' => $this->description
            // Note: role_id or created_by field can't be updated here, instead use associate method
    );
    if (isset($data['role_id']) && !empty($data['role_id'])) {
      // first remove permissions for current role
      $query = 'DELETE FROM ' . self::$link_permission . ' WHERE role_id=%role_id%';
      DB::query(self::$link_permission, $query, array('role_id' => $this->role_id));
      // update existing record
      $result = DB::update(self::$table, $data, 'role_id');
      Corelog::log("Role updated: $this->role_id", Corelog::CRUD);
    } else {
      // add new
      $result = DB::update(self::$table, $data, false);
      $this->role_id = $data['role_id'];
      Corelog::log("New role created: $this->role_id", Corelog::CRUD);
    }

    // save permissions for current role
    foreach ($this->aPermission as $permission_id) {
      $query = "INSERT INTO " . self::$link_permission . " (role_id, permission_id) VALUES (%role_id%, %permission_id%)";
      DB::query(self::$link_permission, $query, array('role_id' => $this->role_id, 'permission_id' => $permission_id));
    }
    return $result;
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
    return false;
  }
  
}

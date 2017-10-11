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

class Permission
{

  private static $table = 'permission';
  private static $primary_key = 'permission_id';
  private static $fields = array(
      'permission_id',
      'name'
  );
  private static $read_only = array(
      'permission_id'
  );

  /**
   * @property-read integer $permission_id
   * @var integer
   */
  private $permission_id = NULL;

  /** @var string */
  private $name = NULL;

  public function __construct($permission_id = NULL)
  {
    if (!empty($permission_id)) {
      $this->permission_id = $permission_id;
      $this->load();
    }
  }

  public static function search($aFilter = array())
  {
    $aPermission = array();

    $aWhere = array();
    foreach ($aFilter as $search_field => $search_value) {
      switch ($search_field) {
        case 'permission_id':
          $aWhere[] = "permission_id = $search_value";
          break;
        case 'name':
          $aWhere[] = "name LIKE '$search_value%'";
          break;
        case 'query':
          $aWhere[] = "permission_id IN ($search_value)";
      }
    }
    $where_str = implode(' AND ', $aWhere);

    $query = "SELECT permission_id, name FROM " . self::$table . " WHERE $where_str";
    Corelog::log("permission search with $query", Corelog::DEBUG, array('aFilter' => $aFilter));
    $result = DB::query('permission', $query);
    while ($data = mysql_fetch_assoc($result)) {
      $aPermission[] = $data;
    }

    return $aPermission;
  }

  private function load()
  {
    Corelog::log("Loading permission: $this->permission_id", Corelog::CRUD);
    $query = "SELECT * FROM " . self::$table . " WHERE permission_id='%permission_id%'";
    $result = DB::query(self::$table, $query, array('permission_id' => $this->permission_id));
    $data = mysql_fetch_assoc($result);
    if ($data) {
      $this->permission_id = $data['permission_id'];
      $this->name = $data['name'];
    } else {
      throw new CoreException('404', 'Permission not found');
    }
  }

  public function delete()
  {
    Corelog::log("Deleting permission: $this->permission_id", Corelog::CRUD);
    return DB::delete(self::$table, 'permission_id', $this->permission_id);
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
    return $this->permission_id;
  }

  public function save()
  {
    $data = array(
        'permission_id' => $this->permission_id,
        'name' => $this->permissionname
    );
    if (isset($data['permission_id']) && !empty($data['permission_id'])) {
      // update existing record
      $result = DB::update(self::$table, $data, 'permission_id');
      Corelog::log("Permission updated: $this->permission_id", Corelog::CRUD);
    } else {
      // add new
      $result = DB::update(self::$table, $data, false);
      $this->permission_id = $data['permission_id'];
      Corelog::log("New permission created: $this->permission_id", Corelog::CRUD);
    }

    return $result;
  }

}

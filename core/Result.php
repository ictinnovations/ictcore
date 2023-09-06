<?php

namespace ICT\Core;

/* * ***************************************************************
 * Copyright © 2014 ICT Innovations Pakistan All Rights Reserved   *
 * Developed By: Nasir Iqbal                                       *
 * Website : http://www.ictinnovations.com/                        *
 * Mail : nasir@ictinnovations.com                                 *
 * *************************************************************** */

class Result
{

  /** @const */
  const TYPE_APPLICATION = 'application';
  const TYPE_CONTACT = 'contact';
  const TYPE_MESSAGE = 'message';
  const TYPE_INFO = 'info';
  const TYPE_ERROR = 'error';

  private static $table = 'spool_result';
  private static $primary_key = 'spool_result_id';
  private static $fields = array(
      'spool_result_id',
      'application_id',
      'type',
      'name',
      'data',
      'date_created',
      'spool_id'
  );
  private static $read_only = array(
      'spool_result_id'
  );

  /**
   * @property-read integer $spool_result_id
   * @var integer 
   */
  public $spool_result_id = NULL;

  /** @var integer */
  public $application_id = NULL;

  /** @var string */
  public $type = NULL;

  /** @var string */
  public $name = NULL;

  /** @var string */
  public $data = NULL;

  /** @var integer */
  public $date_created = NULL;

  /** @var integer */
  public $spool_id = NULL;

  public function __construct($spool_result_id = NULL)
  {
    if (!empty($spool_result_id)) {
      $this->spool_result_id = $spool_result_id;
      $this->load();
    }
  }

  public static function search($aFilter = array())
  {
    $aResult = array();
    $from_str = self::$table;
    $aWhere = array();
    foreach ($aFilter as $search_field => $search_value) {
      switch ($search_field) {
        case 'spool_result_id':
        case 'spool_id':
        case 'application_id':
          $aWhere[] = "$search_field = $search_value";
          break;
        case 'type':
        case 'name':
          $aWhere[] = "$search_field = '$search_value'";
          break;
        case 'data':
          $aWhere[] = "$search_field LIKE '%$search_value%'";
          break;
      }
    }
    if (!empty($aWhere)) {
      $from_str .= ' WHERE ' . implode(' AND ', $aWhere);
    }

    $query = "SELECT * FROM " . $from_str;
    $result = DB::query(self::$table, $query);
    while ($data = mysqli_fetch_assoc($result)) {
      $aResult[] = $data;
    }
    Corelog::log("Result search for spool", Corelog::CRUD, $aResult);
    return $aResult;
  }

  private function load()
  {
    $query = "SELECT * FROM " . self::$table . " WHERE spool_result_id='%spool_result_id%'";
    $result = DB::query(self::$table, $query, array('spool_result_id' => $this->spool_result_id));
    $data = mysqli_fetch_assoc($result);
    if ($data) {
      $this->spool_result_id = $data['spool_result_id'];
      $this->application_id = $data['application_id'];
      $this->type = $data['type'];
      $this->name = $data['name'];
      $this->data = $data['data'];
      $this->date_created = $data['date_created'];
      $this->spool_id = $data['spool_id'];
      Corelog::log("Result loaded name: $this->name type: $this->type", Corelog::CRUD);
    } else {
      throw new CoreException('404', 'Transmission not found');
    }
  }

  public function delete()
  {
    Corelog::log("Deleting result: $this->spool_result_id", Corelog::CRUD);
    return DB::delete(self::$table, 'spool_result_id', $this->spool_result_id);
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
    return $this->spool_result_id;
  }

  public function save()
  {
    $data = array(
        'spool_result_id' => $this->spool_result_id,
        'application_id' => $this->application_id,
        'type' => $this->type,
        'name' => $this->name,
        'data' => $this->data,
        'date_created' => $this->date_created,
        'spool_id' => $this->spool_id
    );

    if (isset($data['spool_result_id']) && !empty($data['spool_result_id'])) {
      // update existing record, no authentication needed
      $result = DB::update(self::$table, $data, 'spool_result_id');
      Corelog::log("Result updated: $this->spool_result_id", Corelog::CRUD);
    } else {
      // add new, no authentication needed
      $result = DB::update(self::$table, $data, false);
      $this->spool_result_id = $data['spool_result_id'];
      Corelog::log("New Result created: $this->spool_result_id", Corelog::CRUD);
    }

    return $result;
  }

}

<?php

namespace ICT\Core;

/* * ***************************************************************
 * Copyright © 2012 ICT Innovations Pakistan All Rights Reserved   *
 * Developed By: Nasir Iqbal                                       *
 *             : Tahir Almas                                       *
 * Website : http://www.ictinnovations.com/                        *
 * Mail : info@ictinnovations.com                                  *
 * *************************************************************** */

// Database Session Handling Functions
class Session extends Data
{

  /**
   * @var Session
   */
  protected static $_instance = null;

  private $_db_link = null;

  /**
   * currently active user
   * @var User $user
   */
  private $user = null;

  /**
   * Current request
   * @var Request $request
   */
  private $request = null;

  /**
   * current response
   * @var Response $response
   */
  private $response = null;

  /**
   * current transmission
   * @var Transmission $transmission
   */
  private $transmission;

  public function __construct(&$data = array())
  {
    parent::__construct($data);
    $this->_db_link = DB::connect();
  }

  /**
   * @staticvar boolean $initialized
   * @return Session
   */
  public static function get_instance()
  {
    static $initialized = FALSE;
    if (!$initialized) {
      self::$_instance = new self;
      $initialized = TRUE;
    }
    return self::$_instance;
  }

  public function set($name, $value)
  {
    $this->__set($name, $value);
  }

  public function &get($name, $default = NULL)
  {
    $value = &$this->__get($name);
    if (NULL === $value) {
      return $default;
    }
    return $value;
  }

  public function open($path, $name)
  {
    Corelog::log("Session open requested with path: $path, and name: $name", Corelog::DEBUG);
    return TRUE;
  }

  public function close()
  {
    Corelog::log("Session close requested", Corelog::DEBUG);
    return TRUE;
  }

  public function read($id)
  {
    Corelog::log("Session read requested with id: $id", Corelog::DEBUG);
    session_write_close();
    $query = "SELECT data FROM session WHERE session_id='".$id."'";
    if (!$result = DB::rawSelect($query)) {
      Corelog::log("Session read failed with error: ", Corelog::WARNING);
      return FALSE;
    }

    if (count($result)) {
      $row = $result;
      $data = unserialize($row["data"]);
      if ($data instanceof Data) {
        $this->merge($data);
      }
      return $data;
    } else {
      Corelog::log("Session read found zero rows", Corelog::WARNING);
      return "";
    }
  }

  public function write($id, $data)
  {
    Corelog::log("Session write requested with id: $id", Corelog::DEBUG, $data);
    if (!($data instanceof Data)) {
      $data = $this;
    }
    $values = array(
      '%id%'   => $id,
      '%data%' => serialize($data)
    );
    $query = "INSERT INTO session (session_id, time_start, data)
                     VALUES ('%id%', UNIX_TIMESTAMP(), '%data%')
              ON DUPLICATE KEY UPDATE time_start=UNIX_TIMESTAMP(), data='%data%'";
    $final_query = str_replace(array_keys($values), array_values($values), $query);

    if (DB::raw_insert_delete_update($final_query)>0) {
      return TRUE;
    }
    Corelog::log("Session write failed", Corelog::WARNING);
  }

  public function destroy($id)
  {
    Corelog::log("Session delete requested with id: $id", Corelog::DEBUG);
    $query = "DELETE FROM session where session_id='$id'";
    $result = DB::raw_insert_delete_update($query);
    if ($result) {
      return TRUE;
    } else {
      return FALSE;
    }
  }

  public function gc($life)
  {
    Corelog::log("Session gc requested with lif: $life", Corelog::DEBUG);
    $query = "DELETE FROM session WHERE time_start < " . (time() - $life);
    $result = DB::raw_insert_delete_update($query);
    if ($result) {
      return TRUE;
    } else {
      return FALSE;
    }
  }

  public static function newId() {}

  public static function start() {
    $session_name = Conf::get('website:cookie', 'ictcore');
    session_name($session_name);
    session_start();   //calls: open()->read()
  }

  /**
   * Defines custom session handler.
   */
  public static function setHandler() {
    // commit automatic session
    //if (ini_get('session.auto_start') == 1) {
       session_write_close();
    //}
    $_instance = static::get_instance();
    session_set_save_handler(
        array($_instance, 'open'),
        array($_instance, 'close'),
        array($_instance, 'read'),
        array($_instance, 'write'),
        array($_instance, 'destroy'),
        array($_instance, 'gc')
    );
    // session_set_save_handler($_instance, true);
  }
}
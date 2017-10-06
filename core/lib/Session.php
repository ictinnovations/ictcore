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
  protected static $_instance;

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

  public static function set($name, $value)
  {
    $_instance = self::get_instance();
    $_instance->__set($name, $value);
  }

  public static function &get($name, $default = NULL)
  {
    $_instance = self::get_instance();
    $value = &$_instance->__get($name);
    if (NULL === $value) {
      return $default;
    }
    return $value;
  }

  public static function open($path, $name)
  {
    Corelog::log("Session open requested with path: $path, and name: $name", Corelog::DEBUG);
    return TRUE;
  }

  public static function close()
  {
    Corelog::log("Session close requested", Corelog::DEBUG);
    return TRUE;
  }

  public static function read($id)
  {
    Corelog::log("Session read requested with id: $id", Corelog::DEBUG);
    session_write_close();
    $query = "SELECT data FROM session WHERE session_id='$id'";
    if (!$result = DB::query('session', $query)) {
      Corelog::log("Session read failed with error: " . mysql_error(DB::$link), Corelog::WARNING);
      return FALSE;
    }
    if (mysql_num_rows($result)) {
      $row = mysql_fetch_assoc($result);
      self::$_instance = unserialize($row["data"]);
      return self::$_instance;
    } else {
      Corelog::log("Session read found zero rows", Corelog::WARNING);
      return "";
    }
  }

  public static function write($id, $data)
  {
    Corelog::log("Session write requested with id: $id", Corelog::DEBUG, $data);
    $sessionRs = DB::query('session', "SELECT session_id FROM session s WHERE s.session_id = '$id'");
    if(mysql_fetch_array($sessionRs) !== false) {
      $query = "UPDATE session SET data='%data%', time_start=UNIX_TIMESTAMP() WHERE session_id ='$id'";
    } else {
      $query = "INSERT INTO session (session_id, time_start, data) 
                  VALUES ('$id', UNIX_TIMESTAMP(), '%data%')";
    }
    DB::query('session', $query, array('data' => serialize(self::$_instance)));
    if (mysql_affected_rows(DB::$link)) {
      return TRUE;
    }
    Corelog::log("Session write failed", Corelog::WARNING);
  }

  public static function destroy($id)
  {
    Corelog::log("Session delete requested with id: $id", Corelog::DEBUG);
    $query = "DELETE FROM session where session_id='$id'";
    $result = DB::query('session', $query);
    if ($result) {
      $oSession = Session::get_instance();
      unset($oSession);
      return TRUE;
    } else {
      return FALSE;
    }
  }

  public static function gc($life)
  {
    Corelog::log("Session gc requested with lif: $life", Corelog::DEBUG);
    $query = "DELETE FROM session WHERE time_start < " . (time() - $life);
    $result = DB::query('session', $query);
    if ($result) {
      return TRUE;
    } else {
      return FALSE;
    }
  }

}

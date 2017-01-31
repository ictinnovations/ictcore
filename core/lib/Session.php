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
    $query = "SELECT data FROM transmission_session WHERE transmission_id='$id'";
    if (!$result = DB::query('transmission_session', $query)) {
      Corelog::log("Session read failed with error: " . mysql_error(DB::$link), Corelog::WARNING);
      return FALSE;
    }
    if (mysql_num_rows($result)) {
      $row = mysql_fetch_assoc($result);
      return unserialize($row["data"]);
    } else {
      Corelog::log("Session read found zero rows", Corelog::WARNING);
      return "";
    }
  }

  public static function write($id, $data)
  {
    Corelog::log("Session write requested with id: $id", Corelog::DEBUG, $data);
    $sessionRs = DB::query('transmission_session', "SELECT count(*) FROM transmission_session s WHERE s.transmission_id = '$id'");
    $row = mysql_fetch_row($sessionRs);
    if ($row[0] > 0) {
      $query = "UPDATE transmission_session SET data='%data%', time_start=UNIX_TIMESTAMP() WHERE transmission_id ='$id'";
    } else {
      $query = "INSERT INTO transmission_session (transmission_id, time_start, data) 
                  VALUES ('$id', UNIX_TIMESTAMP(), '%data%')";
    }
    DB::query('transmission_session', $query, array('data' => serialize($data)));
    if (mysql_affected_rows(DB::$link)) {
      return TRUE;
    }
    Corelog::log("Session write failed", Corelog::WARNING);
  }

  public static function destroy($id)
  {
    Corelog::log("Session delete requested with id: $id", Corelog::DEBUG);
    $query = "DELETE FROM transmission_session where transmission_id='$id'";
    $result = DB::query('transmission_session', $query);
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
    $query = "DELETE FROM transmission_session WHERE time_start < " . (time() - $life);
    $result = DB::query('transmission_session', $query);
    if ($result) {
      return TRUE;
    } else {
      return FALSE;
    }
  }

}
<?php

namespace ICT\Core;

// Database Session Handling Functions
class Session extends Data
{
  protected static $_instance = null;
  private $_db_link = null;
  private $user = null;
  private $request = null;
  private $response = null;
  private $transmission;

  public function __construct(&$data = array())
  {
    parent::__construct($data);
    $this->_db_link = DB::connect();
  }

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
    // Return TRUE to indicate that the session initialization was successful.
    return true;
  }

  public function close()
  {
    Corelog::log("Session close requested", Corelog::DEBUG);
    return true;
  }

  public function read($id)
  {
    Corelog::log("Session read requested with id: $id", Corelog::DEBUG);
    $query = "SELECT data FROM session WHERE session_id='$id'";
    if (!$result = mysqli_query($this->_db_link, $query)) {
      Corelog::log("Session read failed with error: " . mysqli_error($this->_db_link), Corelog::WARNING);
      return FALSE;
    }
    if (mysqli_num_rows($result)) {
      $row = mysqli_fetch_assoc($result);
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
      '%id%'   => mysqli_real_escape_string($this->_db_link, $id),
      '%data%' => mysqli_real_escape_string($this->_db_link, serialize($data))
    );
    $query = "INSERT INTO session (session_id, time_start, data)
                     VALUES ('%id%', UNIX_TIMESTAMP(), '%data%')
              ON DUPLICATE KEY UPDATE time_start=UNIX_TIMESTAMP(), data='%data%'";
    $final_query = str_replace(array_keys($values), array_values($values), $query);
    mysqli_query($this->_db_link, $final_query);
    if (mysqli_affected_rows($this->_db_link)) {
      return TRUE;
    }
    Corelog::log("Session write failed", Corelog::WARNING);
  }

  public function destroy($id)
  {
    Corelog::log("Session delete requested with id: $id", Corelog::DEBUG);
    $query = "DELETE FROM session where session_id='$id'";
    $result = mysqli_query($this->_db_link, $query);
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
    $result = mysqli_query($this->_db_link, $query);
    if ($result) {
      return TRUE;
    } else {
      return FALSE;
    }
  }

  public static function newId()
  {
    // Add implementation for newId() if needed
  }

  public static function start()
  {
    $session_name = Conf::get('website:cookie', 'ictfax');
    session_name($session_name);
    // session_start(); // Calls: open()->read()
  }

  public static function setHandler()
  {
    if (ini_get('session.auto_start') == 1) {
      session_write_close();
    }
    $_instance = static::get_instance();
    session_set_save_handler(
      fn ($path, $name) => $_instance->open($path, $name),
      fn () => $_instance->close(),
      fn ($id) => $_instance->read($id),
      fn ($id, $data) => $_instance->write($id, $data),
      fn ($id) => $_instance->destroy($id),
      fn ($life) => $_instance->gc($life)
    );
  }
}

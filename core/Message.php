<?php

namespace ICT\Core;

/* * ***************************************************************
 * Copyright © 2014 ICT Innovations Pakistan All Rights Reserved   *
 * Developed By: Nasir Iqbal                                       *
 * Website : http://www.ictinnovations.com/                        *
 * Mail : nasir@ictinnovations.com                                 *
 * *************************************************************** */

class Message
{

  protected static $table = 'message';
  protected static $fields = array(
      'message_id'
  );

  /**
   * @property-read integer $message_id 
   * @var integer
   */
  public $message_id = NULL;

  /** @var string */
  public $name = NULL;

  /** @var string */
  public $data = NULL;

  /**
   * Default mime type for this message type, when no type is available
   * @var string
   */
  public static $media_default = 'application/octet-stream';

  /**
   * Array of all supported file extensions along with mime types as keys
   * @var array $media_supported
   */
  public static $media_supported = array(
      'dat' => 'application/octet-stream'
  );

  public function __construct($message_id = NULL)
  {
    if (!empty($message_id)) {
      $this->message_id = $message_id;
      $this->load();
    }
  }

  public static function construct_from_array($aData)
  {
    $oMessage = new static();
    foreach ($aData as $field => $value) {
      $oMessage->$field = $value;
    }
    return $oMessage;
  }

  public static function search($aFilter = array())
  {
    Corelog::log("message search filter: " . print_r($aFilter, true), Corelog::CRUD);
  }

  protected function load()
  {
    // load, delete and save are table spacific so leaving them empty in parent class
  }

  public function delete()
  {
    // load, delete and save are table spacific so leaving them empty in parent class
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
    } else if (empty($field) || in_array($field, static::$read_only)) {
      return;
    } else {
      $this->$field = $value;
    }
  }

  public function get_id()
  {
    return $this->message_id;
  }

  public function save()
  {
    // load, delete and save are table spacific so leaving them empty in parent class
  }

  public function token_apply(Token $oToken, $default_value = Token::KEEP_ORIGNAL)
  {
    // replace tokens with given values, if current message type support this
    $oToken->render_variable($this->data, $default_value);
  }

}
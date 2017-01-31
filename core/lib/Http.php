<?php

namespace ICT\Core;

/* * ***************************************************************
 * Copyright © 2015 ICT Innovations Pakistan All Rights Reserved   *
 * Developed By: Nasir Iqbal                                       *
 * Website : http://www.ictinnovations.com/                        *
 * Mail : nasir@ictinnovations.com                                 *
 * *************************************************************** */

// initializing global variables
$oHttp = Http::get_instance();
$oHttp->input = array_merge($_GET, $_FILES, $_POST);

class Http extends Data
{

  /**
   * @var Http
   */
  protected static $_instance;

  /**
   * @staticvar boolean $initialized
   * @return Http
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

  public static function input_get($name, $default = null)
  {
    return self::get("input:$name", $default);
  }

  public static function input_set($name, $value)
  {
    self::set("input:$name", $value);
  }

  public static function output_get($name, $default = null)
  {
    return self::get("output:$name", $default);
  }

  public static function output_set($name, $value)
  {
    self::set("output:$name", $value);
  }

}
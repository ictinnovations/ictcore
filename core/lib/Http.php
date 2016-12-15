<?php

namespace ICT\Core;

/* * ***************************************************************
 * Copyright © 2015 ICT Innovations Pakistan All Rights Reserved   *
 * Developed By: Nasir Iqbal                                       *
 * Website : http://www.ictinnovations.com/                        *
 * Mail : nasir@ictinnovations.com                                 *
 * *************************************************************** */

// initializing global variables
Http::$input = array_merge($_GET, $_FILES, $_POST);

class Http extends Data
{
  public static $input = array();
  public static $output = array();

  public static function input_get($name, $default = null)
  {
    if (isset(self::$input[$name])) {
      return self::$input[$name];
    }
    // check for : colon separated name
    return self::_get(self::$input, $name, $default);
  }

  public static function input_set($name, $value)
  {
    if (strpos($name, ':') === false) {
      self::$input[$name] = $value;
    } else {
      self::_set(self::$input, $name, $value);
    }
  }

  public static function output_get($name, $default = null)
  {
    if (isset(self::$output[$name])) {
      return self::$output[$name];
    }
    // check for : colon separated name
    return self::_get(self::$output, $name, $default);
  }

  public static function output_set($name, $value)
  {
    if (strpos($name, ':') === false) {
      self::$output[$name] = $value;
    } else {
      self::_set(self::$output, $name, $value);
    }
  }

}
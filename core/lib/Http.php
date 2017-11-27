<?php

namespace ICT\Core;

/* * ***************************************************************
 * Copyright © 2015 ICT Innovations Pakistan All Rights Reserved   *
 * Developed By: Nasir Iqbal                                       *
 * Website : http://www.ictinnovations.com/                        *
 * Mail : nasir@ictinnovations.com                                 *
 * *************************************************************** */

use Jacwright\RestServer\AuthServer;
use Jacwright\RestServer\RestException;

class Http extends Data implements AuthServer
{

  /**
   * @var Http
   */
  protected static $_instance = null;

  /**
   *
   * @param string $realm
   */
  protected $realm = '';

  public function __construct(&$data = array())
  {
    parent::__construct($data);
    $this->realm = Conf::get('website:title', 'ICTCore') . ' :: REST API Server';
    $this->server = $_SERVER;
    $this->request = $_REQUEST;
  }

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

  public static function request_get($name, $default = null)
  {
    return self::get("request:$name", $default);
  }

  public static function server_get($name, $default = null)
  {
    return self::get("server:$name", $default);
  }

  public function isAuthorized($classObj) {
    if (method_exists($classObj, 'authorize')) {
      return $classObj->authorize();
		}

    // select authentication method
    if (!empty($_SERVER['PHP_AUTH_USER'])) {
      $username = $_SERVER['PHP_AUTH_USER'];
      $password = $_SERVER['PHP_AUTH_PW'];
    } else {
      return false;
    }

    // authenticate using username and password method
    try {
      $oUser = new User($username);
      if (empty($oUser->user_id) || $oUser->authenticate($password) == false) {
        return false;
      }
      do_login($oUser);
    } catch (CoreException $e) {
      return false;
    }

    return true;
  }

  public function unauthorized($classObj) {
    header("WWW-Authenticate: Basic realm=\"$this->realm\"");
    throw new RestException(401, "You are not authorized to access this resource.");
  }
}

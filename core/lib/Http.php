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

  public function isAuthenticated($classObj)
  {
    $auth_headers = $this->getAuthHeaders();

    // Try to use bearer token as default
    $auth_method = User::AUTH_TYPE_BEARER;
    $credentials = $this->getBearer($auth_headers);

    // In case bearer token is not present switch back to Basic autentication
    if (empty($credentials)) {
      $auth_method = User::AUTH_TYPE_BASIC;
      $credentials = $this->getBasic($auth_headers);
    }

    if (method_exists($classObj, 'authenticate')) {
      return $classObj->authenticate($credentials, $auth_method);
    }

    return true;
  }

  public function unauthenticated($path) {
    header("WWW-Authenticate: Basic realm=\"$this->realm\"");
    throw new RestException(401, "Invalid credentials, access is denied to $path.");
  }

  public function isAuthorized($classObj, $method) {
    if (method_exists($classObj, 'authorize')) {
      return $classObj->authorize($method);
    }

    return true;
  }

  public function unauthorized($path) {
    throw new RestException(403, "You are not authorized to access $path.");
  }

  /**
   * Get username and password from header
   */
  protected function getBasic($headers) {
    // mod_php
    if ($this->server_get('PHP_AUTH_USER', null)) {
        return array($this->server_get('PHP_AUTH_USER'), $this->server_get('PHP_AUTH_USER'));
    } else { // most other servers
      if (!empty($headers)) {
        list ($username, $password) = explode(':',base64_decode(substr($headers, 6)));
        return array('username' => $username, 'password' => $password);
      }
    }
    return array('username' => null, 'password' => null);
  }

  /**
   * Get access token from header
   */
  protected function getBearer($headers) {
    $matches = array();
    if (!empty($headers)) {
      if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
        return $matches[1];
      }
    }
    return null;
  }

  /**
   * Get authorization header
   */
  protected function getAuthHeaders() {
    $headers = null;
    if ($this->server_get('Authorization', null)) {
      $headers = trim($this->server_get('Authorization'));
    } else if ($this->server_get('HTTP_AUTHORIZATION', null)) { //Nginx or fast CGI
      $headers = trim($this->server_get('HTTP_AUTHORIZATION'));
    } else if (function_exists('apache_request_headers')) {
      $requestheaders = apache_request_headers();
      $RequestHeaders = array_combine(array_map('ucwords', array_keys($requestheaders)), array_values($requestheaders));
      if (isset($RequestHeaders['Authorization'])) {
        $headers = trim($RequestHeaders['Authorization']);
      }
    }
    return $headers;
  }

}

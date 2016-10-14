<?php
/* * ***************************************************************
 * Copyright © 2014 ICT Innovations Pakistan All Rights Reserved   *
 * Developed By: Nasir Iqbal                                       *
 * Website : http://www.ictinnovations.com/                        *
 * Mail : nasir@ictinnovations.com                                 *
 * *************************************************************** */

function service_flag_to_class($service_flag)
{
  static $serviceMap = null;

  if (empty($serviceMap)) {
    $listService = list_available_classes('Service');
    foreach ($listService as $serviceClass) {
      $flag = $serviceClass::SERVICE_FLAG;
      $serviceMap[$flag] = $serviceClass;
    }
  }

  if (!empty($service_flag) && isset($serviceMap[$service_flag])) {
    $className = $serviceMap[$service_flag];
    return $className;
  } else {
    return false;
  }
}

function gateway_flag_to_class($gateway_flag)
{
  static $gatewayMap = null;

  if (empty($gatewayMap)) {
    $listGateway = list_available_classes('Gateway');
    foreach ($listGateway as $gatewayClass) {
      $flag = $gatewayClass::GATEWAY_FLAG;
      $gatewayMap[$flag] = $gatewayClass;
    }
  }

  if (!empty($gateway_flag) && isset($gatewayMap[$gateway_flag])) {
    $className = $gatewayMap[$gateway_flag];
    return $className;
  } else {
    return false;
  }
}

function list_available_classes($type = null)
{
  $aClass = array();
  $listClass = get_declared_classes();

  if (empty($type)) {
    return $listClass;
  } else {
    foreach ($listClass as $class) {
      if (is_subclass_of($class, $type)) {
        $aClass[] = $class;
      }
    }
    return $aClass;
  }
}

/**
 * Load all files from a given folder
 * TODO: replace this function with auto load
 * @param     string     $folder    Path of folder from where we have to include files
 * @param     string     $suffix    File suffix / extension which we need to load, default is .php
 * @return    array
 */
function include_once_directory($folder, $suffix = '.php')
{
  $aFile = array();
  $current_dir = getcwd();
  chdir(dirname(dirname(__FILE__))); // default include directory is parent (core) directory
  foreach (glob($folder . DIRECTORY_SEPARATOR . "*" . $suffix) as $filename) {
    $aFile[] = $filename;
    include_once $filename;
  }
  chdir($current_dir);
  return $aFile;
}

/**
 * Return an array from given bitmask
 * @param    integer    $mask Integer of the bit
 * @return    array
 */
function bitmask2array($mask = 0)
{
  if (is_array($mask)) {
    return $mask;
  }

  $return = array();
  while ($mask > 0) {
    for ($i = 0, $n = 0; $i <= $mask; $i = 1 * pow(2, $n), $n++) {
      $end = $i;
    }
    $return[] = $end;
    $mask = $mask - $end;
  }
  sort($return);
  return $return;
}

function do_login($user)
{
  global $ict_user;
  $oUser = null;

  if (!is_object($user)) {
    Corelog::log("do_login requested, with user_id $user", Corelog::COMMON);
    if (empty($user) || $user == USER_GUEST) {
      // load dummy user same as dummy account
      $oUser = new User(USER_GUEST);
      return $ict_user;
    }
    $oUser = new User($user);
  } else {
    Corelog::log("do_login requested, with object $user->user_id", Corelog::COMMON);
    $oUser = $user;
  }

  // if no user found or user is not active
  if (empty($oUser)) {
    throw new CoreException(401, "No such user found, can't loging");
  }
  if (!$oUser->active) {
    throw new CoreException(401, "User account disabled, can't loging");
  }

  $ict_user = $oUser;
  Corelog::log("do_login, results", Corelog::DEBUG, $ict_user);

  return $ict_user;
}

function user_get($field, $default = null)
{
  global $ict_user;
  $value = $default;
  if (is_object($ict_user)) {
    $value = $ict_user->$field;
  }
  return $value;
}

function user_set($field, $value)
{
  global $ict_user;
  if (is_object($ict_user)) {
    $ict_user->$field = $value;
  }
}

function json_check($string)
{
  json_decode($string);
  return (json_last_error() == JSON_ERROR_NONE);
}

function can_access($access_name, $user_id = null)
{
  global $ict_user;

  // load user if user_id exist otherwise use crrently logged-in user
  if (!empty($user_id)) {
    $oUser = new User($user_id);
  } else if (!empty($ict_user)) {
    $oUser = $ict_user;
  } else {
    return false;
  }

  return $oUser->authorize($access_name);
}

function sys_which($cmd, $search_path = NULL, $self_call = false)
{
  global $_ENV, $path_lib;
  $scpath = is_null($search_path) ? $_ENV['PATH'] : $search_path;

  foreach (explode(':', $scpath) as $path) {
    if (is_executable("$path/$cmd")) {
      return "$path/$cmd";
    }
  }

  if (!$self_call) {
    $possible_path = '/bin:/sbin:/usr/bin:/usr/sbin:/usr/local/bin:/usr/local/sbin:/usr/X11R6/bin:/usr/local/apache/bin:/usr/local/mysql/bin';
    $possible_path .= ':' . realpath($path_lib);
    return sys_which($cmd, $possible_path, true);
  }

  return false;
}
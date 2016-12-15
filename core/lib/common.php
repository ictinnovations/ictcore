<?php

namespace ICT\Core;

/* * ***************************************************************
 * Copyright © 2014 ICT Innovations Pakistan All Rights Reserved   *
 * Developed By: Nasir Iqbal                                       *
 * Website : http://www.ictinnovations.com/                        *
 * Mail : nasir@ictinnovations.com                                 *
 * *************************************************************** */

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
  global $path_core;
  $filter = $path_core . DIRECTORY_SEPARATOR . $folder . DIRECTORY_SEPARATOR . '*' . $suffix;
  foreach (glob($filter) as $filename) {
    $aFile[] = $filename;
    include_once $filename;
  }
  return $aFile;
}

function path_to_namespace($path)
{
  global $path_core;
  if (substr($path, 0, strlen($path_core)) == $path_core) {
    $path = substr($path, strlen($path_core));
  }
  $clean_path = trim($path, DIRECTORY_SEPARATOR);
  $namespace = 'ICT\\Core\\' . str_replace(DIRECTORY_SEPARATOR, '\\', $clean_path);
  return $namespace;
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
  $oUser = null;

  if (!is_object($user)) {
    Corelog::log("do_login requested, with user_id $user", Corelog::COMMON);
    if (empty($user) || $user == User::GUEST) {
      // load dummy user same as dummy account
      $oUser = new User(User::GUEST);
      User::$activeUser = $oUser;
      return $oUser;
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

  User::$activeUser = $oUser;
  Corelog::log("do_login, results", Corelog::DEBUG, User::$activeUser);

  return User::$activeUser;
}

function json_check($string)
{
  json_decode($string);
  return (json_last_error() == JSON_ERROR_NONE);
}

function can_access($access_name, $user_id = null)
{
  // load user if user_id exist otherwise use crrently logged-in user
  if (!empty($user_id)) {
    $oUser = new User($user_id);
  } else if (!empty(User::$activeUser)) {
    $oUser = User::$activeUser;
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
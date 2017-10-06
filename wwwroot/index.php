<?php
/* * ***************************************************************
 * Copyright © 2014 ICT Innovations Pakistan All Rights Reserved   *
 * Developed By: Nasir Iqbal                                       *
 * Website : http://www.ictinnovations.com/                        *
 * Mail : nasir@ictinnovations.com                                 *
 * *************************************************************** */

use ICT\Core\Api;
use ICT\Core\Conf;
use ICT\Core\CoreException;
use ICT\Core\User;

// default include is /usr/ictcore/core
chdir(dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'core');

// Include the framework
include_once "Core.php";

// *************************************** PREPARE SESSION AND COOKIES
$session_name = Conf::get('website:cookie', 'ictcore');
session_name($session_name);
session_start(); // session start

// **************************************************** PREPARE SYSTEM
$oApi = new Api();
$oApi->create_interface('rest', Conf::get('website:path', null)); // create rest server interface

// ****************************************** AUTHENTICATE AND EXECUTE
try {
  if (ICT\Core\can_access('api_access') === true || http_authenticate() === true) {
    $oApi->process_request();  // serve rest request
  } else {
    throw new CoreException('401', 'Unknown authentication error');
  }
} catch (CoreException $e) {
  // send error
  $oApi->send_error($e->getCode(), $e->getMessage());
}

exit();

function http_authenticate()
{
  $realm = Conf::get('website:title', 'ICTCore') . ' :: REST API Server';
  // select authentication method
  if (!empty($_SERVER['PHP_AUTH_USER'])) {
    $username = $_SERVER['PHP_AUTH_USER'];
    $password = $_SERVER['PHP_AUTH_PW'];
  } else {
    header("WWW-Authenticate: Basic realm=\"$realm\"");
    throw new CoreException('401', "You are not authorized to access this resource");
  }
  // authenticate using username and password method
  try {
    $oUser = new User($username);
  } catch (CoreException $e) {
    // hide actuall error, i.e User not found
    header("WWW-Authenticate: Basic realm=\"$realm\"");
    throw new CoreException('401', "Invalid username or password", $e);
  }
  if (empty($oUser->user_id) || $oUser->authenticate($password) == false) {
    header("WWW-Authenticate: Basic realm=\"$realm\"");
    throw new CoreException('401', "Invalid username or password");
  }

  try {
    ICT\Core\do_login($oUser);
  } catch (CoreException $ex) {
    throw new CoreException('401', "User account disabled / banned, please contact admin", $ex);
  }
  return true;
}

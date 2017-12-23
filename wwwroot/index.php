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
use ICT\Core\Session;

// default include is /usr/ictcore/core
chdir(dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'core');

// Include the framework
include_once "Core.php";

// *************************************** PREPARE SESSION AND COOKIES
Session::setHandler();
Session::start();

// **************************************************** PREPARE SYSTEM
$oApi = new Api();
$oApi->create_interface('rest', Conf::get('website:path', null)); // create rest server interface

// ****************************************** AUTHENTICATE AND EXECUTE
try {
  $oApi->process_request();  // serve rest request
} catch (CoreException $e) {
  // send error
  $oApi->send_error($e->getCode(), $e->getMessage());
} catch (Exception $e) {
  // send error
  $oApi->send_error($e->getCode(), $e->getMessage());
}

exit();

<?php
/* * ***************************************************************
 * Copyright © 2014 ICT Innovations Pakistan All Rights Reserved   *
 * Developed By: Nasir Iqbal                                       *
 * Website : http://www.ictinnovations.com/                        *
 * Mail : nasir@ictinnovations.com                                 *
 * *************************************************************** */

global $ict_conf, $ict_user, $ict_db_conn;
global $path_lib, $path_core, $path_root, $path_www, $path_etc, $path_log, $path_data, $website_log;

// following lines will allow to include files from both core and lib directories
$path_lib = realpath(dirname(__FILE__));   // /usr/ictcore/core
$path_core = dirname($path_lib);
$path_root = dirname($path_core); // /usr/ictcore
$path_www = $path_root . DIRECTORY_SEPARATOR . 'wwwroot';
$path_etc = $path_root . DIRECTORY_SEPARATOR . 'etc';
$path_log = $path_root . DIRECTORY_SEPARATOR . 'log';
$path_data = $path_root . DIRECTORY_SEPARATOR . 'data';
$path_cache = $path_root . DIRECTORY_SEPARATOR . 'cache';

// Default include path and Autoload
// For Classes
$loader = require dirname($path_core) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

/* Include all required libraries 
  These library will responsible to provide commonly requrired functions
 */
include_once 'define.php';    // common constants
include_once "common.php";    // common functions
include_once "Corelog.php";   // library for logging (order is important)
include_once "conf.php";      // library for gui / framework configuration
include_once "config.php";    // library for gateway / backend configuration
include_once "db.php";        // library for database management
include_once "session.php";   // library for session management
include_once "CoreException.php"; // library for error
// include_once "CoreThread.php"; // Multi Threading support for selected functions

/* Connecting, selecting database */
$db_port = conf_get('db:port', '3306');
$db_host = conf_get('db:host', 'localhost') . ':' . $db_port;
$db_user = conf_get('db:user', 'myuser');
$db_pass = conf_get('db:pass', '');
$db_name = conf_get('db:name', 'ictcore');

$website_host = conf_get('website:host', '127.0.0.1');
$website_port = conf_get('website:port', '80');
$website_url = conf_get('website:url', 'http://127.0.0.1/');
$log_string = conf_get('website:log', 'error warning notice info');
$website_log = Corelog::parse_config($log_string);

// first of all set php default timezone to UTC
date_default_timezone_set('UTC');       // required to bypass server timezone settings

$ict_db_link = mysql_connect($db_host, $db_user, $db_pass);
if (!$ict_db_link) {
  throw new CoreException('500', 'Unable to connect database server error:' . mysql_error());
}
$ict_db_conn = mysql_select_db($db_name);
if (!$ict_db_conn) {
  throw new CoreException('500', 'Unable to select database');
}
mysql_query("SET time_zone = '+00:00'"); // required to bypass server timezone settings

/* load default system configuration from database */
conf_system_load();

<?php

namespace ICT\Core;

/* * ***************************************************************
 * Copyright © 2014 ICT Innovations Pakistan All Rights Reserved   *
 * Developed By: Nasir Iqbal                                       *
 * Website : http://www.ictinnovations.com/                        *
 * Mail : nasir@ictinnovations.com                                 *
 * *************************************************************** */

use ICT\Core\Conf\File as ConfFile;
use ICT\Core\Conf\System as SystemConfiguration;

global $path_lib, $path_core, $path_root, $path_www, $path_etc, $path_log, $path_data, $path_cache, $path_template, $website_log;

// following lines will allow to include files from both core and lib directories
$path_lib = realpath(dirname(__FILE__));   // /usr/ictcore/core
$path_core = dirname($path_lib);
$path_root = dirname($path_core); // /usr/ictcore
$path_www = $path_root . DIRECTORY_SEPARATOR . 'wwwroot';
$path_etc = $path_root . DIRECTORY_SEPARATOR . 'etc';
$path_log = $path_root . DIRECTORY_SEPARATOR . 'log';
$path_data = $path_root . DIRECTORY_SEPARATOR . 'data';
$path_cache = $path_root . DIRECTORY_SEPARATOR . 'cache';
$path_template = $path_core . DIRECTORY_SEPARATOR . 'templates';

// Default include path and Autoload
// For Classes
$loader = require dirname($path_core) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

/* Include required libraries 
  These library will be responsible to provide commonly requrired functions
 */
include_once "common.php";    // common functions

// Read database and other basic configuration from configuration file
ConfFile::load('/etc/ictcore.conf');

// Corelog will be our default error handler
set_error_handler(array('ICT\\Core\\Corelog', 'error_handler'), E_ALL);
$log_string = Conf::get('website:log', 'error warning notice info');
$website_log = Corelog::parse_config($log_string);

// first of all set php default timezone to UTC
date_default_timezone_set('UTC');       // required to bypass server timezone settings

/* Connecting, selecting database */
DB::connect();

/* load default system configuration from database */
SystemConfiguration::load();
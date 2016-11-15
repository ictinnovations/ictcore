<?php
/* * ***************************************************************
 * Copyright © 2016 ICT Innovations Pakistan All Rights Reserved   *
 * Developed By: Nasir Iqbal                                       *
 * Website : http://www.ictinnovations.com/                        *
 * Mail : nasir@ictinnovations.com                                 *
 * *************************************************************** */

/**
 * For application logging
 *
 * @author nasir
 */
class Corelog
{
  /* Constants */

  const ERROR = 1;
  const WARNING = 2;
  const NOTICE = 4;
  const INFO = 8;
  const CRUD = 16;
  const LOGIC = 32;
  const FLOW = 64;
  const COMMON = 128;
  const DEBUG = 256;
  const EXTRA = 512; // not a valid log mode, but just for more detail
  const TRACE = 1024; // again not valid log mode, but for extended information
  const AUTH = 2048;

  public static $process_id = NULL;

  public static function error_handler($error_no, $error_string, $error_file, $error_line, $error_context) {
    $message = "$error_string in $error_file on line $error_line";
    $class   = Corelog::NOTICE;
    switch ($error_no) {
      case E_ERROR:
        $class = Corelog::ERROR;
        break;
      case E_WARNING:
        $class = Corelog::WARNING;
        break;
      default:
        $class = Corelog::NOTICE;
    }
    Corelog::log($message, $class, $error_context);
  }

  public static function log($message, $class = Corelog::INFO, $extra = null)
  {
    global $path_log, $website_log;

    if (($website_log & $class) == $class) {

      if (empty(Corelog::$process_id)) {
        Corelog::$process_id = getmypid();
      }
      $log_type = self::code_to_name($class);
      $dateTime = gmdate('Y-m-d H:i:s');
      $message = "[$dateTime] [$process_id] [$log_type] $message\n";
      if (!empty($extra) && ($website_log & Corelog::EXTRA) == Corelog::EXTRA) { // print extra log only if log level is set to EXTRA
        $message .= "--- extra data start ---\n" . print_r($extra, true) . "\n--- extra data end ---\n";
      }
      if (($website_log & Corelog::TRACE) == Corelog::TRACE) {
        // $aBacktrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 4);
        $aBacktrace = array_slice(debug_backtrace(), 0, 4); // truncated to three steps
        $message .= "=== backtrace start ===\n";
        foreach ($aBacktrace as $backtrace) {
          unset($backtrace['args']);
          unset($backtrace['object']);
          $message .= print_r($backtrace, true);
        }
        $message .= "\n=== backtrace end ===\n";
      }
      return error_log($message, 3, $path_log . '/ictcore.log');
    }
    return false;
  }

  public static function code_to_name($code)
  {
    static $aLog = NULL;
    if (empty($aLog)) {
      $aLog = self::getConstants(TRUE);
    }
    if (isset($aLog[$code])) {
      return $aLog[$code];
    } else {
      return 'UNKNOWN';
    }
  }

  public static function parse_config($config_line)
  {
    $levelList = self::getConstants();
    $config_string = strtoupper($config_line);
    $log_flag = 0;
    foreach ($levelList as $level_name => $level_code) {
      if (strpos($config_string, $level_name) !== FALSE) {
        $log_flag = ($log_flag | $level_code);
      }
    }
    return $log_flag;
  }

  public static function getConstants($swap_keys = false)
  {
    $oClass = new ReflectionClass(__CLASS__);
    if ($swap_keys) {
      return array_flip($oClass->getConstants());
    } else {
      return $oClass->getConstants();
    }
  }

}
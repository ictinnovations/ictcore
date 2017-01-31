<?php

namespace ICT\Core\Conf;

/* * ***************************************************************
 * Copyright © 2014 ICT Innovations Pakistan All Rights Reserved   *
 * Developed By: Nasir Iqbal                                       *
 * Website : http://www.ictinnovations.com/                        *
 * Mail : nasir@ictinnovations.com                                 *
 * *************************************************************** */

use Exception;
use ICT\Core\Conf;

/*
 * NOTE: no log in following file, cos this file will be executed before log setup
 */

class File extends Conf
{

  public static $config_file = '/etc/ictcore.conf';

  public static function load($file_path = NULL)
  {
    $configSource = is_file($file_path) ? $file_path : self::$config_file;

    //reading configuration file.
    if (is_file($configSource)) {
      $configuration = parse_ini_file($configSource, TRUE);
      if (!is_array($configuration)) {
        throw new Exception("Bad configuration file: $configSource", '500');
      }
    } else {
      throw new Exception("No configuration file found: $configSource", '500');
    }

    self::$config_file = $configSource;
    parent::merge_array($configuration);
  }

}
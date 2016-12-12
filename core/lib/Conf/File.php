<?php

namespace ICT\Core\Conf;

/* * ***************************************************************
 * Copyright © 2014 ICT Innovations Pakistan All Rights Reserved   *
 * Developed By: Nasir Iqbal                                       *
 * Website : http://www.ictinnovations.com/                        *
 * Mail : nasir@ictinnovations.com                                 *
 * *************************************************************** */

use ICT\Core\Conf;
use ICT\Core\CoreException;
use ICT\Core\Corelog;

class Conffile extends Conf
{

  public static function load($file_path)
  {
    global $path_etc;

    //reading configuration file.
    //in success result is $ict_conf array.
    $configSource = is_file($file_path) ? $file_path : $path_etc . '/ictcore.conf';

    if (is_file($configSource)) {
      $configuration = parse_ini_file($configSource, TRUE);
      Corelog::log("configuration file loaded", Corelog::DEBUG, $configuration);
      if (!is_array($configuration)) {
        throw new CoreException('500', "Bad configuration file: $configSource");
      }
    } else {
      throw new CoreException('500', "No configuration file found: $configSource");
    }

    parent::load($configuration);
  }

}
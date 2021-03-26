<?php

namespace ICT\Core\Conf;

/* * ***************************************************************
 * Copyright © 2014 ICT Innovations Pakistan All Rights Reserved   *
 * Developed By: Nasir Iqbal                                       *
 * Website : http://www.ictinnovations.com/                        *
 * Mail : nasir@ictinnovations.com                                 *
 * *************************************************************** */

use ICT\Core\Conf;
use ICT\Core\Corelog;
use ICT\Core\Session;

class User extends Conf
{

  public static $user_id = NULL;

  public static function load($user_id = NULL)
  {
    $filter = array();
    if (empty($user_id)) {
      $oSession = Session::get_instance();
      $user_id = $oSession->get('user:user_id', null);
    }
    Corelog::log("configuration requested for user: $user_id", Corelog::DEBUG);

    $filter[] = '(c.permission_flag & ' . Conf::PERMISSION_USER_WRITE . ')=' . Conf::PERMISSION_USER_WRITE;
    $filter[] = 'cd.class=' . Conf::USER;
    if (!empty($user_id)) {
      $filter[] = "cd.created_by=$user_id";
    } else {
      return false; // can do nothing
    }

    $configuration = self::database_conf_load($filter);
    self::$user_id = $user_id;
    parent::merge_array($configuration);
  }

  public static function set($name, $value, $permanent = FALSE, $reference = array(), $permission = Conf::PERMISSION_USER_WRITE)
  {
    $oSession = Session::get_instance();
    $reference['class'] = Conf::USER;
    $reference += array('created_by' => $oSession->get('user:user_id', null)); // set created_by, if not already set
    parent::set($name, $value, $permanent, $reference, $permission);
  }
}

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

  public static function load($user_id = null, $type = null)
  {
    $filter = array();
    if (empty($user_id)) {
      $user_id = Session::get('user:user_id', null);
    }
    Corelog::log("configuration requested for user: $user_id, type: $type", Corelog::DEBUG);

    $filter[] = '(c.permission_flag & ' . Conf::PERMISSION_USER_WRITE . ')=' . Conf::PERMISSION_USER_WRITE;
    $filter[] = 'cd.class=' . Conf::USER;
    if (!empty($user_id)) {
      $filter[] = "cd.created_by=$user_id";
    } else {
      return false; // can do nothing
    }

    $configuration = self::database_conf_get($filter, $type);
    parent::load($configuration);
  }

}
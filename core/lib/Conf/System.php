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

class System extends Conf
{

  public static function load($node_id = null, $type = null)
  {
    $filter = array();
    if (empty($node_id)) {
      $node_id = self::get('node:node_id', null);
    }
    Corelog::log("configuration requested for node: $node_id, type: $type", Corelog::DEBUG);

    $filter[] = '(c.permission_flag & ' . Conf::PERMISSION_GLOBAL_READ . ')=' . Conf::PERMISSION_GLOBAL_READ;
    $filter[] = 'cd.class=' . Conf::SYSTEM;
    if (!empty($node_id)) {
      $filter[] = "(cd.node_id=$node_id OR cd.node_id=" . Conf::NODE_ALL . ")";
    } else {
      $filter[] = "cd.node_id=" . Conf::NODE_ALL;
    }

    $configuration = self::database_conf_get($filter, $type);
    parent::load($configuration);
  }

}
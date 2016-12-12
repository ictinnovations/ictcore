<?php

namespace ICT\Core;

/* * ***************************************************************
 * Copyright © 2015 ICT Innovations Pakistan All Rights Reserved   *
 * Developed By: Nasir Iqbal                                       *
 * Website : http://www.ictinnovations.com/                        *
 * Mail : nasir@ictinnovations.com                                 *
 * *************************************************************** */

class Data
{

  protected static function _get(&$data, $name, $default)
  {
    // when need to alter pay atention to reference usage
    $aName = explode(':', $name);

    foreach ($aName as $name) {
      if (isset($data[$name])) {
        $data = &$data[$name];
      } else {
        return $default;
      }
    }

    return $data;
  }

  protected static function _set(&$data, $name, $value)
  {
    // when need to alter pay atention to reference usage
    // and also remember that it create parents
    $aName = explode(':', $name);

    foreach ($aName as $name) {
      if (!isset($data[$name]) || !is_array($data[$name])) {
        $data[$name] = array();
      }
      $data = &$data[$name];
    }
    $data = $value;
  }

}
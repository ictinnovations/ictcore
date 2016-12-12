<?php

namespace ICT\Core;

/* * ***************************************************************
 * Copyright © 2014 ICT Innovations Pakistan All Rights Reserved   *
 * Developed By: Nasir Iqbal                                       *
 * Website : http://www.ictinnovations.com/                        *
 * Mail : nasir@ictinnovations.com                                 *
 * *************************************************************** */

class Conf extends Data
{
  /* constant to represent all nodes simultaneously */
  const NODE_ALL = 0;

  const ALL = 0;
  const SYSTEM = 1;
  const USER = 2;
  const CAMPAIGN = 3;
  const CONTACT = 4;  // not in use, only for concept

  const PERMISSION_GLOBAL_WRITE = 1;
  const PERMISSION_GLOBAL_READ = 2;
  const PERMISSION_NODE_WRITE = 4;
  const PERMISSION_NODE_READ = 8;
  const PERMISSION_ADMIN_WRITE = 16;
  const PERMISSION_ADMIN_READ = 32;
  const PERMISSION_USER_WRITE = 64;
  const PERMISSION_USER_READ = 128;

  // system = 170 = GLOBAL_READ | NODE_READ | ADMIN_READ | USER_READ
  // admin  = 186 = system | ADMIN_WRITE
  // user   = 250 = admin | USER_WRITE

  public static $conf = array();

  function get($name, $default = null)
  {
    if (isset(self::$conf[$name])) {
      return self::$conf[$name];
    }
    // check for : colon separated name
    return static::_get(self::$conf, $name, $default);
  }

  function set($name, $value)
  {
    if (strpos($name, ':') === false) {
      self::$conf[$name] = $value;
    } else {
      static::_set(self::$conf, $name, $value);
    }
  }

  protected static function load($configuration = array())
  {
    foreach ($configuration as $class => $config) {
      foreach ($config as $name => $data) {
        self::$conf[$class][$name] = $data;
      }
    }
    return true;
  }

  protected static function database_conf_get($filter = array(), $type = null)
  {
    $configuration = array();

    if (!empty($type)) {
      $filter[] = "c.type='$type'";
    }

    $filter_string = implode(' AND ', $filter);
    // in following query ORDER BY class make sure that top level values should be overwritten specific configuration
    $query = "SELECT c.type, c.name, cd.data FROM configuration c LEFT JOIN configuration_data cd 
                 ON c.configuration_id = cd.configuration_id
               WHERE $filter_string 
               ORDER BY cd.class ASC, cd.node_id ASC, cd.created_by ASC";
    $result = DB::query('configuration', $query);
    if (!$result) {
      throw new CoreException('500', 'Unable to get configuration, query failed');
    }

    while ($config = mysql_fetch_assoc($result)) {
      $type = $config['type'];
      $name = $config['name'];
      $data = $config['data'];
      $configuration[$type][$name] = $data; // setting a global array
    }

    Corelog::log("configuration loaded", Corelog::DEBUG, $configuration);
    return $configuration;
  }

  public static function database_conf_save($aConf = null, $field = array())
  {
    Corelog::log("configuration save request", Corelog::COMMON, array($aConf, $field));

    $permission = can_access('configuration_admin') ? Conf::PERMISSION_ADMIN_WRITE : Conf::PERMISSION_USER_WRITE;
    $update_field = implode(', ', $field);
    $search_field = implode(' AND ', $field);

    foreach ($aConf as $type => $conf) {
      foreach ($conf as $name => $data) {
        $query = "SELECT configuration_id FROM configuration 
                   WHERE (permission_flag & $permission)=$permission AND type='$type' AND name='$name'";
        $result = DB::query('configuration', $query);
        if (mysql_num_rows($result)) {
          $configuration_id = mysql_result($result, 0, 0);
          if ($data == '[default]') {
            DB::query('configuration_data', "DELETE FROM configuration_data WHERE configuration_id=$configuration_id AND $search_field");
          } else {
            $query = "INSERT INTO configuration_data SET configuration_id='$configuration_id', $update_field, 
                                                          data='$data', date_created=UNIX_TIMESTAMP() 
                       ON DUPLICATE KEY UPDATE data='$data', last_updated=UNIX_TIMESTAMP()";
            $result = DB::query('configuration', $query);
          }
        }
      }
    }

    return $result;
  }

}
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
  // node   = 254 = user | NODE_WRITE

  /**
   * @var Conf
   */
  public static $oConf = array();

  /**
   * @staticvar boolean $initialized
   * @return Conf
   */
  public static function get_instance()
  {
    static $initialized = FALSE;
    if (!$initialized) {
      self::$oConf = new self;
      $initialized = TRUE;
    }
    return self::$oConf;
  }

  public static function set($name, $value, $permanent = FALSE, $reference = array(), $permission = Conf::PERMISSION_NODE_WRITE)
  {
    $oConf = self::get_instance();
    $oConf->__set($name, $value);
    if ($permanent) {
      list($type, $conf_name) = explode(':', $name, 2);
      static::database_conf_set($type, $conf_name, $value, $reference, $permission);
    }
  }

  public static function &get($name, $default = NULL)
  {
    $oConf = self::get_instance();
    $value = &$oConf->__get($name);
    if (NULL === $value) {
      return $default;
    }
    return $value;
  }

  public static function load($config_id)
  {
    Corelog::log("Demo, loading configuration for $config_id");
  }

  protected static function merge_array($configuration = array())
  {
    $newConf = new Data($configuration);
    $oConf = self::get_instance();
    $oConf->merge($newConf);
    return true;
  }

  protected static function database_conf_load($filter = array(), $type = null)
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

    while ($config = mysqli_fetch_assoc($result)) {
      $type = $config['type'];
      $name = $config['name'];
      $data = $config['data'];
      $configuration[$type][$name] = $data; // setting a global array
    }

    Corelog::log("configuration loaded", Corelog::DEBUG, $configuration);
    return $configuration;
  }

  public static function database_conf_save($aConf = null, $reference = array(), $permission = Conf::PERMISSION_NODE_WRITE)
  {
    Corelog::log("configuration save request", Corelog::COMMON, array($aConf, $reference));
    foreach ($aConf as $type => $conf) {
      foreach ($conf as $name => $data) {
        static::database_conf_set($type, $name, $data, $reference, $permission);
      }
    }
  }

  public static function database_conf_set($type, $name, $data, $reference = array(), $permission = Conf::PERMISSION_NODE_WRITE)
  {
    $query = "SELECT configuration_id FROM configuration
               WHERE (permission_flag & $permission)=$permission AND type='$type' AND name='$name'";
    $result = DB::query('configuration', $query);
    if (mysqli_num_rows($result)) {
      $configuration_id = static::sql_result($result, 0, 0);
    } else {
      Corelog::log("Unable to save configuration. type:$type, name:$name", Corelog::ERROR);
      return;
    }

    $update_query = '';
    $search_query = '';
    if (!empty($reference)) {
      $reference_pair = array();
      foreach ($reference as $key => $value) {
        $reference_pair[] = "$key=$value";
      }
      $update_query = ', ' . implode(', ', $reference_pair);
      $search_query = ' AND ' . implode(' AND ', $reference_pair);
    }

    if ($data == '[default]') {
      DB::query('configuration_data', "DELETE FROM configuration_data WHERE configuration_id=$configuration_id $search_query");
    } else {
      $query = "INSERT INTO configuration_data SET configuration_id='$configuration_id', data='$data',
                                                   date_created=UNIX_TIMESTAMP() $update_query
                 ON DUPLICATE KEY UPDATE data='$data', last_updated=UNIX_TIMESTAMP()";
                 Corelog::log($query, Corelog::INFO);
      DB::query('configuration', $query);
    }
  }

  public static function sql_result($result, $number, $field=0) {
        mysqli_data_seek($result, $number);
        $row = mysqli_fetch_array($result);
        return $row[$field];
  }

  
}

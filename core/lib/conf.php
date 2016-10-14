<?php
/* * ***************************************************************
 * Copyright © 2014 ICT Innovations Pakistan All Rights Reserved   *
 * Developed By: Nasir Iqbal                                       *
 * Website : http://www.ictinnovations.com/                        *
 * Mail : nasir@ictinnovations.com                                 *
 * *************************************************************** */

$ict_conf = array();

//reading configuration file.
//in success result is $ict_conf array.
$configSource = is_file('/etc/ictcore.conf') ? '/etc/ictcore.conf' : $path_etc . '/ictcore.conf';

if (is_file($configSource)) {
  $ict_conf = parse_ini_file($configSource, TRUE);
  Corelog::log("configuration file loaded", Corelog::DEBUG, $ict_conf);
  if (!is_array($ict_conf)) {
    throw new CoreException('500', "Bad configuration file: $configSource");
  }
} else {
  throw new CoreException('500', "No configuration file found: $configSource");
}

function conf_get($name, $default = null)
{
  global $ict_conf;
  if (isset($ict_conf[$name])) {
    return $ict_conf[$name];
  }
  // check for : colon separated name
  return _get($ict_conf, $name, $default);
}

function conf_set($name, $value)
{
  global $ict_conf;
  if (strpos($name, ':') === false) {
    $ict_conf[$name] = $value;
  } else {
    _set($ict_conf, $name, $value);
  }
}

function _get(&$data, $name, $default)
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

function _set(&$data, $name, $value)
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

function conf_system_load($node_id = null, $type = null)
{
  $filter = array();
  if (empty($node_id)) {
    $node_id = conf_get('node:node_id', null);
  }
  Corelog::log("configuration requested for node: $node_id, type: $type", Corelog::DEBUG);

  $filter[] = '(c.permission_flag & ' . CONF_PERMISSION_GLOBAL_READ . ')=' . CONF_PERMISSION_GLOBAL_READ;
  $filter[] = 'cd.class=' . CONF_SYSTEM;
  if (!empty($node_id)) {
    $filter[] = "(cd.node_id=$node_id OR cd.node_id=" . NODE_ALL . ")";
  } else {
    $filter[] = "cd.node_id=" . NODE_ALL;
  }

  return _conf_load($filter, $type);
}

function conf_user_load($user_id = null, $type = null)
{
  $filter = array();
  if (empty($user_id)) {
    $user_id = session_get('user:user_id', null);
  }
  Corelog::log("configuration requested for user: $user_id, type: $type", Corelog::DEBUG);

  $filter[] = '(c.permission_flag & ' . CONF_PERMISSION_USER_WRITE . ')=' . CONF_PERMISSION_USER_WRITE;
  $filter[] = 'cd.class=' . CONF_USER;
  if (!empty($user_id)) {
    $filter[] = "cd.created_by=$user_id";
  } else {
    return false; // can do nothing
  }

  return _conf_load($filter, $type);
}

function _conf_load($filter = array(), $type = null)
{
  global $ict_conf;
  $configuration = _conf_get($filter, $type);
  foreach ($configuration as $class => $config) {
    foreach ($config as $name => $data) {
      $ict_conf[$class][$name] = $data;
    }
  }
  return true;
}

function _conf_get($filter = array(), $type = null)
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

function conf_save($aConf = null, $field = array())
{
  Corelog::log("configuration save request", Corelog::COMMON, array($aConf, $field));

  $permission = can_access('configuration_admin') ? CONF_PERMISSION_ADMIN_WRITE : CONF_PERMISSION_USER_WRITE;
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

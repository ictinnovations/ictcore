<?php
/* * ***************************************************************
 * Copyright © 2016 ICT Innovations Pakistan All Rights Reserved   *
 * Developed By: Nasir Iqbal                                       *
 * Website : http://www.ictinnovations.com/                        *
 * Mail : nasir@ictinnovations.com                                 *
 * *************************************************************** */

use Jacwright\RestServer\RestServer;

class Api
{

  /** @var string #interface_type */
  private $interface_type = 'local';

  /** @var RestServer $oInterface */
  private $oInterface = null;

  protected function _authorize($permission)
  {
    if (empty($permission) || can_access($permission) == false) {
      throw new CoreException(403, 'User not permitted to perform required action');
    }
    return true;
  }

  protected function set($oEntity, $data)
  {
    foreach ($data as $key => $value) {
      try {
        $oEntity->$key = $value;
      } catch (CoreException $ex) {
        throw new CoreException(412, 'Data validation failed, for ' . $key, $ex);
      }
    }
  }

  public function create_interface($interface_type = null)
  {
    global $path_cache;
    if (!empty($interface_type) && $interface_type = 'rest') {
      // Initialize the server
      $this->interface_type = 'rest';
      $realm = conf_get('company:name', 'ICTCore') . ' :: REST API Server';
      $this->oInterface = new RestServer('production', $realm); // debug / production
      $this->oInterface->cacheDir = $path_cache; // set folder for rest server url mapping
      self::rest_load($this->oInterface, 'Api');
    }
  }

  public function send_error($code, $message)
  {
    if ($this->interface_type == 'rest') {
      $error = array('error' => array('code' => $code, 'message' => $message));
      $this->oInterface->sendData($error);
    }
  }

  public static function rest_load($restInterface, $dir = null)
  {
    if (empty($restInterface)) {
      return false;
    }

    $apiFiles = include_once_directory($dir);
    foreach ($apiFiles as $api_file) {
      $class_name = basename($api_file, '.php');
      if (class_exists($class_name)) {
        $restInterface->addClass($class_name);
        if (method_exists($class_name, 'rest_load')) {
          $child_dir = "$dir/" . str_replace('Api', '', $class_name);
          $class_name::rest_load($restInterface, $child_dir);
        }
      }
    }
  }

  public function process_request()
  {
    return $this->oInterface->handle();
  }

}
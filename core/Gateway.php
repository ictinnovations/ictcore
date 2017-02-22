<?php

namespace ICT\Core;

/* * ***************************************************************
 * Copyright © 2014 ICT Innovations Pakistan All Rights Reserved   *
 * Developed By: Nasir Iqbal                                       *
 * Website : http://www.ictinnovations.com/                        *
 * Mail : nasir@ictinnovations.com                                 *
 * *************************************************************** */

class Gateway
{

  /** @const */
  const GATEWAY_FLAG = 0;
  const GATEWAY_TYPE = 'gateway';
  const CONTACT_FIELD = 'phone';

  /** @var boolean $conn */
  protected $conn = FALSE;

  public static function load($gateway_flag) {
    static $gatewayMap = null;

    if (empty($gatewayMap)) {
      // manually load all available service classes
      include_once_directory('Gateway');
      $listGateway = list_available_classes('ICT\\Core\\Gateway');
      foreach ($listGateway as $gatewayClass) {
        $flag = $gatewayClass::GATEWAY_FLAG;
        $gatewayMap[$flag] = $gatewayClass;
      }
    }

    if (!empty($gateway_flag) && isset($gatewayMap[$gateway_flag])) {
      $className = $gatewayMap[$gateway_flag];
      $oGateway = new $className;
      return $oGateway;
    } else {
      return false;
    }
  }

  protected function connect()
  {
    $this->conn = TRUE; /* handle to connection */
  }

  protected function dissconnect()
  {
    return fclose($this->conn);
  }

  public function send($command, Provider $oProvider = NULL)
  {
    if ($this->connect()) {
      // process
      Corelog::log('Gateway->Send demo: ' . $command . ' via: ' . $oProvider->name, Corelog::WARNING);
      $this->dissconnect();
      return TRUE;
    } else {
      return FALSE;
    }
  }

  public function get()
  {
    if ($this->connect()) {
      // process
      $this->dissconnect();
      return TRUE;
    } else {
      return FALSE;
    }
  }

  public static function template_dir()
  {
    global $path_core;
    return $path_core;
  }

  public function config_save($type, $name, $data = '')
  {
    Corelog::log("Gateway->config_save demo. type: $type, name: $name", Corelog::WARNING, $data);
  }

  public function config_delete($type, $name)
  {
    Corelog::log("Gateway->config_delete demo. type: $type, name: $name", Corelog::WARNING);
  }

  public function config_reload()
  {
    Corelog::log('Gateway->config_reload', Corelog::WARNING);
  }

}
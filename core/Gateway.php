n<?php
/* * ***************************************************************
 * Copyright © 2014 ICT Innovations Pakistan All Rights Reserved   *
 * Developed By: Nasir Iqbal                                       *
 * Website : http://www.ictinnovations.com/                        *
 * Mail : nasir@ictinnovations.com                                 *
 * *************************************************************** */

abstract class Gateway
{

  /** @const */
  const GATEWAY_FLAG = 0;
  const GATEWAY_TYPE = 'gateway';
  const CONTACT_FIELD = 'phone';

  /** @var boolean $conn */
  protected $conn = FALSE;

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

  public static function config_template($config_type)
  {
    Corelog::log('Gateway->config_template demo. type: ' . $config_type, Corelog::WARNING);
    return 'invalid.twig';
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
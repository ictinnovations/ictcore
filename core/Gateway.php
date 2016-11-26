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
  const CONTACT_FIELD = 'phone';
  const GATEWAY_FLAG = 0;

  /** @var boolean $conn */
  protected $conn = FALSE;

  public static function capabilities()
  {
    $capabilities = array();
    $capabilities['service_flag'] = (
            Voice::SERVICE_FLAG |
            Fax::SERVICE_FLAG |
            Sms::SERVICE_FLAG |
            Email::SERVICE_FLAG
            //| Video::SERVICE_FLAG
            );
    $capabilities['application'] = array(
        'dial',
        'message'
    );
    $capabilities['account'] = array();
    $capabilities['provider'] = array();
    return $capabilities;
  }

  public function is_supported($feature, $type = 'service_flag')
  {
    $capabilities = $this->capabilities();
    switch ($type) {
      case 'service_flag':
        if (($capabilities['service_flag'] & $feature) == $feature) {
          return TRUE;
        } else {
          return FALSE;
        }
        break;
      case 'application':
      case 'account':
      case 'provider':
      default:
        if ($capabilities[$type] && in_array($feature, $capabilities)) {
          return TRUE;
        } else {
          return FALSE;
        }
        break;
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

  public function send($command)
  {
    if ($this->connect()) {
      // process
      Corelog::log('Gateway->Send demo: ' . $command, Corelog::WARNING);
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

  public static function template_application($application_name, $service_type = 'voice')
  {
    Corelog::log("Gateway->application_template demo. name: $application_name type: $service_type", Corelog::WARNING);
    return '';
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
<?php
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
    return (
            Voice::SERVICE_FLAG |
            Fax::SERVICE_FLAG |
            Sms::SERVICE_FLAG |
            Email::SERVICE_FLAG
            //| Video::SERVICE_FLAG
            );
  }

  public function is_supported($service)
  {
    if ($this->capabilities() | $service == $service) {
      return TRUE;
    } else {
      return FALSE;
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

  public static function template_application($application_name, $service_flag = NULL)
  {
    switch ($application_name) {
      case 'dial':
        $template = '';
        break;
      case 'message':
        $template = '';
        break;
    }
    return $template;
  }

  public function template_provider()
  {
    
  }

  public function template_account()
  {
    
  }

  public function save_provider($name, $aSetting = array())
  {
    Corelog::log('Gateway->save_provider demo. name: ' . $name, Corelog::WARNING);
    Corelog::log('Gateway->save_provider demo. setting: ' . print_r($aSetting, TRUE), Corelog::WARNING);
  }

  public function save_account()
  {
    
  }

  public function config_save()
  {
    
  }

  public function reload()
  {
    
  }

}
<?php
/* * ***************************************************************
 * Copyright © 2015 ICT Innovations Pakistan All Rights Reserved   *
 * Developed By: Nasir Iqbal                                       *
 * Website : http://www.ictinnovations.com/                        *
 * Mail : nasir@ictinnovations.com                                 *
 * *************************************************************** */

class Service
{

  /** @const */
  const SERVICE_FLAG = 0;
  const SERVICE_TYPE = 'service';
  const CONTACT_FIELD = 'phone';
  const MESSAGE_CLASS = 'Message';
  const GATEWAY_CLASS = 'Gateway';

  public function __construct()
  {
    // nothing to do
  }

  public static function capabilities()
  {
    $capabilities = array();
    $capabilities['application'] = array(
        'dial',
        'message'
    );
    $capabilities['account'] = array();
    $capabilities['provider'] = array();
    return $capabilities;
  }

  public function is_supported($feature, $type = 'application')
  {
    $capabilities = $this->capabilities();
    switch ($type) {
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

  public static function route_get() {
    $aFilter = array(
        'active' => 1,
        'service_flag' => static::SERVICE_FLAG
    );
    $listRoute = Provider::search($aFilter);
    if (count($listRoute)) {
      $aProvider = array_shift($listRoute);
      $oProvider = new Provider($aProvider['provider_id']);
      return $oProvider;
    }
    throw new CoreException('No provider available');
  }

  public static function application_template($application_name)
  {
    Corelog::log("Service->application_template demo. name: $application_name", Corelog::WARNING);
    return 'invalid.twig';
  }

  public function application_execute($application_name, $command, Provider $oProvider = NULL)
  {
    switch ($application_name) {
      default:
        // nothing to do
    }
    $cGateway = static::GATEWAY_CLASS;
    $oGateway = new $cGateway;
    $oGateway->send($command, $oProvider);
  }

  public static function config_template($config_type)
  {
    Corelog::log('Service->config_template demo. type: ' . $config_type, Corelog::WARNING);
    return 'invalid.twig';
  }

  public function config_save($config_type, $config_name = 'default', $aSetting = array())
  {
    $cGateway = static::GATEWAY_CLASS;
    $oGateway = new $cGateway;
    $oGateway->config_save($config_type, $config_name, $aSetting);
  }

  public function config_delete($config_type, $config_name = 'default')
  {
    $cGateway = static::GATEWAY_CLASS;
    $oGateway = new $cGateway;
    $oGateway->config_delete($config_type, $config_name);
  }

  public function config_reload()
  {
    $cGateway = static::GATEWAY_CLASS;
    $oGateway = new $cGateway;
    $oGateway->config_reload();
  }

}
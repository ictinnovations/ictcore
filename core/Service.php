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

  public function capabilities()
  {
    $cGateway = static::GATEWAY_CLASS;
    $capabilities = $cGateway::capabilities();
    // drop service_flag from original gateway capabilities
    unset($capabilities['service_flag']);
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

  public function template_application($application_name)
  {
    $cGateway = static::GATEWAY_CLASS;
    $template = $cGateway::template_application($application_name, static::SERVICE_TYPE);
    return $template;
  }

  public function execute_application($command, $load_provider = true)
  {
    if ($load_provider) {
      $oToken = new Token();
      try {
        $oProvider = new Provider(PROVIDER_DEFAULT, static::SERVICE_FLAG);
        $oToken->add('provider', $oProvider->token_get());
      } catch (CoreException $e) {
        Corelog::log($e->getMessage(), Corelog::NOTICE);
        Corelog::log("No gateway provider found", Corelog::NOTICE);
      }
      $command = $oToken->render_variable($command); // TODO: add REPLACE_EMPTY
    }
    // this function require gateway access to execute given command
    $cGateway = static::GATEWAY_CLASS;
    $oGateway = new $cGateway;
    return $oGateway->send($command);
  }

  public function config_template($config_type, $config_name = 'default')
  {
    $cGateway = static::GATEWAY_CLASS;
    $template = $cGateway::config_template($config_type, $config_name);
    return $template;
  }

  public function config_save($config_type, $config_name = 'default', $aSetting = array())
  {
    $cGateway = static::GATEWAY_CLASS;
    $oGateway = new $cGateway;
    return $oGateway->config_save($config_type, $config_name, $aSetting);
  }

  public function config_delete($config_type, $config_name = 'default')
  {
    $cGateway = static::GATEWAY_CLASS;
    $oGateway = new $cGateway;
    return $oGateway->config_delete($config_type, $config_name);
  }

}
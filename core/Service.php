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
  const CONTACT_FIELD = 'phone';
  const MESSAGE_CLASS = 'Message';
  const GATEWAY_CLASS = 'Gateway';

  public function __construct()
  {
    // nothing to do
  }

  public function capabilities()
  {
    return array(
        'originate'
    );
  }

  public function is_supported($application_name)
  {
    if (in_array($application_name, $this->capabilities())) {
      return TRUE;
    } else {
      return FALSE;
    }
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
      $command = $oToken->token_replace($command); // TODO: add REPLACE_EMPTY
    }
    // this function require gateway access to execute given command
    $cGateway = static::GATEWAY_CLASS;
    $oGateway = new $cGateway;
    return $oGateway->send($command);
  }

  public function template_application($application_name)
  {
    $cGateway = static::GATEWAY_CLASS;
    switch ($application_name) {
      case 'originate':
        $template = $cGateway::template_application($application_name, Voice::SERVICE_FLAG);
        break;
      default:
        $template = $cGateway::template_application($application_name);
        break;
    }
    return $template;
  }

}
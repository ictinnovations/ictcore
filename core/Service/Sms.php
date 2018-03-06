<?php

namespace ICT\Core\Service;

/* * ***************************************************************
 * Copyright © 2015 ICT Innovations Pakistan All Rights Reserved   *
 * Developed By: Nasir Iqbal                                       *
 * Website : http://www.ictinnovations.com/                        *
 * Mail : nasir@ictinnovations.com                                 *
 * *************************************************************** */

use ICT\Core\Application;
use ICT\Core\Gateway\Kannel;
use ICT\Core\Message\Text;
use ICT\Core\Provider;
use ICT\Core\Service;
use ICT\Core\Token;

class Sms extends Service
{

  /** @const */
  const SERVICE_FLAG = 4;
  const SERVICE_TYPE = 'sms';
  const CONTACT_FIELD = 'phone';
  const MESSAGE_CLASS = 'Text';
  const GATEWAY_CLASS = 'Kannel';

  public static function capabilities()
  {
    $capabilities = array();
    $capabilities['application'] = array(
        'sms_receive',
        'sms_send',
        'log'
    );
    $capabilities['account'] = array(
        'longcode'
    );
    $capabilities['provider'] = array(
        'smpp'
    );
    return $capabilities;
  }

  /**
   * ******************************************* Default Gateway for service **
   */

  public static function get_gateway()
  {
    static $oGateway = NULL;
    if (empty($oGateway)) {
      $oGateway = new Kannel();
    }
    return $oGateway;
  }

  /**
   * ******************************************* Default message for service **
   */

  public static function get_message()
  {
    static $oMessage = NULL;
    if (empty($oMessage)) {
      $oMessage = new Text();
    }
    return $oMessage;
  }

  /**
   * ***************************************** Application related functions **
   */

  public static function template_path($template_name = '')
  {
    $template_dir = Kannel::template_dir();
    $template_path = '';

    switch ($template_name) {
      case 'account':
      case 'did':
        $template_path = 'account/did.twig';
        break;
      case 'provider':
      case 'smpp':
        $template_path = 'provider/smpp.twig';
        break;
      // applications
      case 'sms_send':
      case 'sms_receive':
      case 'log':
        $template_path = "application/$template_name.json";
        break;
    }

    return "$template_dir/$template_path";
  }

  public function application_execute(Application $oApplication, $command = '', $command_type = 'string')
  {
    switch ($oApplication->type) {
      case 'sms_send': // execute sms_send directly from gateway
        // initilize token cache
        $oToken = new Token(Token::SOURCE_ALL);
        $oToken->add('application', $oApplication);

        // load provider
        $oProvider = static::get_route();
        $oToken->add('provider', $oProvider);

        // send it via gateway
        $oGateway = $this->get_gateway();
        $command = $oToken->render($command, $command_type); // render tokens
        $oGateway->send($command, $oProvider);
        break;

      default: // all other applications
        parent::application_execute($oApplication, $command, $command_type);
        break;
    }
  }

  /**
   * *************************************** Configuration related functions **
   */

  // no exention or DIDs are supported

  // no private accounts for user

  public function config_update_provider(Provider $oProvider)
  {
    if ($oProvider->active) {
      $oToken = new Token();
      $oToken->add('provider', $oProvider);
      $this->config_save($oProvider->type, $oToken, 'provider_' . $oProvider->provider_id);
    } else {
      $this->config_delete($oProvider->type, 'provider_' . $oProvider->provider_id);
    }
    Sms::config_status(Sms::STATUS_NEED_RELOAD);
  }

}

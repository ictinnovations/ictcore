<?php

namespace ICT\Core\Service;

/* * ***************************************************************
 * Copyright © 2015 ICT Innovations Pakistan All Rights Reserved   *
 * Developed By: Nasir Iqbal                                       *
 * Website : http://www.ictinnovations.com/                        *
 * Mail : nasir@ictinnovations.com                                 *
 * *************************************************************** */

use ICT\Core\Gateway\Freeswitch;
use ICT\Core\Message\Recording;
use ICT\Core\Service;

class Voice extends Service
{

  /** @const */
  const SERVICE_FLAG = 1;
  const SERVICE_TYPE = 'voice';
  const CONTACT_FIELD = 'phone';
  const MESSAGE_CLASS = 'Recording';
  const GATEWAY_CLASS = 'Freeswitch';
  
  public static function capabilities()
  {
    $capabilities = array();
    $capabilities['application'] = array(
        'inbound',
        'originate',
        'connect',
        'disconnect',
        'voice_play',
        'log'
    );
    $capabilities['account'] = array(
        'extension',
        'did'
    );
    $capabilities['provider'] = array(
        'sip'
    );
    return $capabilities;
  }

  public static function get_gateway() {
    static $oGateway = NULL;
    if (empty($oGateway)) {
      $oGateway = new Freeswitch();
    }
    return $oGateway;
  }

  public static function get_message() {
    static $oMessage = NULL;
    if (empty($oMessage)) {
      $oMessage = new Recording();
    }
    return $oMessage;
  }

  public static function config_template($type, $name = '')
  {
    switch ($type) {
      case 'did':
        return 'account/did/freeswitch/default.twig';
      case 'extension':
        return 'account/extension/freeswitch/default.twig';
      case 'sip':
        return 'provider/sip/freeswitch/default.twig';
      default:
        return 'invalid.twig';
    }
  }

  public static function application_template($application_name)
  {
    $gateway_type = Freeswitch::GATEWAY_TYPE;
    switch ($application_name) {
      case 'originate':
        return "application/originate/$gateway_type/voice/default.json";
      case 'inbound':
      case 'connect':
      case 'disconnect':
      case 'play_voice':
      case 'log':
        return "application/$application_name/$gateway_type/default.json";
    }
  }

}
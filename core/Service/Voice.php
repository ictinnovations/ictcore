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

  public static function template_path($template_name = '')
  {
    $template_dir = Freeswitch::template_dir();
    $template_path = '';

    switch ($template_name) {
      case 'user':
        $template_path = 'user.twig';
        break;
      case 'did':
        $template_path = 'account/did.twig';
        break;
      case 'account':
      case 'extension':
        $template_path = 'account/extension.twig';
        break;
      case 'provider':
      case 'sip':
        $template_path = 'provider/sip.twig';
        break;
      // applications
      case 'originate':
        $template_path = "application/voice/originate.json";
        break;
      case 'inbound':
      case 'connect':
      case 'disconnect':
      case 'play_voice':
      case 'log':
        $template_path = "application/$template_name.json";
        break;
    }

    return "$template_dir/$template_path";
  }

}
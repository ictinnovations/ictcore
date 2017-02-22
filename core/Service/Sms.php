<?php

namespace ICT\Core\Service;

/* * ***************************************************************
 * Copyright © 2015 ICT Innovations Pakistan All Rights Reserved   *
 * Developed By: Nasir Iqbal                                       *
 * Website : http://www.ictinnovations.com/                        *
 * Mail : nasir@ictinnovations.com                                 *
 * *************************************************************** */

use ICT\Core\Gateway\Kannel;
use ICT\Core\Message\Text;
use ICT\Core\Service;

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

  public static function get_gateway()
  {
    static $oGateway = NULL;
    if (empty($oGateway)) {
      $oGateway = new Kannel();
    }
    return $oGateway;
  }

  public static function get_message()
  {
    static $oMessage = NULL;
    if (empty($oMessage)) {
      $oMessage = new Text();
    }
    return $oMessage;
  }

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

}
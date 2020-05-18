<?php

namespace ICT\Core\Service;

/* * ***************************************************************
 * Copyright © 2015 ICT Innovations Pakistan All Rights Reserved   *
 * Developed By: Nasir Iqbal                                       *
 * Website : http://www.ictinnovations.com/                        *
 * Mail : nasir@ictinnovations.com                                 *
 * *************************************************************** */

use ICT\Core\Application;
use ICT\Core\CoreException;
use ICT\Core\Corelog;
use ICT\Core\Gateway\Sendmail;
use ICT\Core\Message\Template;
use ICT\Core\Provider\Smtp;
use ICT\Core\Service;
use ICT\Core\Token;

class Email extends Service
{

  /** @const */
  const SERVICE_FLAG = 8;
  const SERVICE_TYPE = 'email';
  const CONTACT_FIELD = 'email';
  const MESSAGE_CLASS = 'Template';
  const GATEWAY_CLASS = 'Sendmail';

  public static function capabilities()
  {
    $capabilities = array();
    $capabilities['application'] = array(
        'email_receive',
        'email_send',
        'log'
    );
    $capabilities['account'] = array(
        'mailbox'
    );
    $capabilities['provider'] = array(
        'smtp',
        'sendmail'
    );
    return $capabilities;
  }

  /**
   * ******************************************* Default Gateway for service **
   */

  public static function get_gateway() {
    static $oGateway = NULL;
    if (empty($oGateway)) {
      $oGateway = new Sendmail();
    }
    return $oGateway;
  }

  /**
   * ******************************************* Default message for service **
   */

  public static function get_message() {
    static $oMessage = NULL;
    if (empty($oMessage)) {
      $oMessage = new Template();
    }
    return $oMessage;
  }

  /**
   * ***************************************** Application related functions **
   */

  public static function template_path($template_name = '')
  {
    $template_dir = Sendmail::template_dir();
    $template_path = '';

    switch ($template_name) {
      // applications
      case 'email_send':
      case 'email_receive':
      case 'log':
        $template_path = "application/$template_name.json";
        break;
    }

    return "$template_dir/$template_path";
  }

  /**
   * *************************************** Configuration related functions **
   */

  // no configuration file needed for accounts

  // no private accounts for user

  // no configuration file needed for providers

}

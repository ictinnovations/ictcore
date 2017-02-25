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

  public static function get_gateway() {
    static $oGateway = NULL;
    if (empty($oGateway)) {
      $oGateway = new Sendmail();
    }
    return $oGateway;
  }

  public static function get_message() {
    static $oMessage = NULL;
    if (empty($oMessage)) {
      $oMessage = new Template();
    }
    return $oMessage;
  }

  public static function get_route()
  {
    try{
      return parent::get_route();
    } catch (CoreException $ex) {
      Corelog::log($ex->getMessage(), Corelog::NOTICE);
      Corelog::log('Using localhost as email gateway', Corelog::NOTICE);
      $oProvider = new Smtp();
      $oProvider->name = 'localhost';
      return $oProvider;
    }
  }

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

  public function application_execute(Application $oApplication, $command = '', $command_type = 'string')
  {
    switch ($oApplication->type) {
      case 'send_email': // execute send_email directly from gateway
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
}
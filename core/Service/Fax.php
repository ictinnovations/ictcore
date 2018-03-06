<?php

namespace ICT\Core\Service;

/* * ***************************************************************
 * Copyright © 2015 ICT Innovations Pakistan All Rights Reserved   *
 * Developed By: Nasir Iqbal                                       *
 * Website : http://www.ictinnovations.com/                        *
 * Mail : nasir@ictinnovations.com                                 *
 * *************************************************************** */

use ICT\Core\Account;
use ICT\Core\Application;
use ICT\Core\Gateway\Freeswitch;
use ICT\Core\Message\Document;
use ICT\Core\Provider;
use ICT\Core\Service;
use ICT\Core\Token;
use ICT\Core\User;

class Fax extends Service
{

  /** @const */
  const SERVICE_FLAG = 2;
  const SERVICE_TYPE = 'fax';
  const CONTACT_FIELD = 'phone';
  const MESSAGE_CLASS = 'Document';
  const GATEWAY_CLASS = 'Freeswitch';
  
  public static function capabilities()
  {
    $capabilities = array();
    $capabilities['application'] = array(
        'inbound',
        'originate',
        'connect',
        'disconnect',
        'fax_receive',
        'fax_send',
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

  /**
   * ******************************************* Default Gateway for service **
   */

  public static function get_gateway() {
    static $oGateway = NULL;
    if (empty($oGateway)) {
      $oGateway = new Freeswitch();
    }
    return $oGateway;
  }

  /**
   * ******************************************* Default message for service **
   */

  public static function get_message() {
    static $oMessage = NULL;
    if (empty($oMessage)) {
      $oMessage = new Document();
    }
    return $oMessage;
  }

  /**
   * ***************************************** Application related functions **
   */

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
        $template_path = "application/originate/fax.json";
        break;
      case 'inbound':
      case 'connect':
      case 'disconnect':
      case 'fax_send':
      case 'fax_receive':
      case 'log':
        $template_path = "application/$template_name.json";
        break;
    }

    return "$template_dir/$template_path";
  }

  public function application_execute(Application $oApplication, $command = '', $command_type = 'string')
  {
    // originate and connect application require to provide last / disconnect application id
    // to collect call status
    if ($oApplication->type == 'originate' || $oApplication->type == 'connect') {
      $appList = $oApplication->search($oApplication->program_id, Application::ORDER_END);
      foreach ($appList as $disconnectApp) {
        $oApplication->disconnect_application_id = $disconnectApp['application_id'];
        break; // only first
      }
    }

    switch ($oApplication->type) {
      case 'originate': // execute originate directly from gateway
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

  public function config_update_account(Account $oAccount)
  {
    if ($oAccount->active) {
      $oToken = new Token();
      $oToken->add('account', $oAccount);
      $this->config_save($oAccount->type, $oToken, 'account_' . $oAccount->account_id);
    } else {
      $this->config_delete($oAccount->type, 'account_' . $oAccount->account_id);
    }
    $oUser = new User($oAccount->user_id);
    $this->config_update_user($oUser);
  }

  public function config_update_user(User $oUser)
  {
    if ($oUser->active) {
      $account_filter = array('created_by' => $oUser->user_id, 'active' => 1);
      $listAccount = Account::search($account_filter);
      $oToken = new Token();
      $oToken->add('user', $oUser);
      $oToken->add('user_accounts', $listAccount);
      $this->config_save('user', $oToken, 'user_' . $oUser->user_id);
    } else {
      $this->config_delete('user', 'user_' . $oUser->user_id);
    }
    Fax::config_status(Fax::STATUS_NEED_RELOAD);
  }

  public function config_update_provider(Provider $oProvider)
  {
    if ($oProvider->active) {
      $oToken = new Token();
      $oToken->add('provider', $oProvider);
      $this->config_save($oProvider->type, $oToken, 'provider_' . $oProvider->provider_id);
    } else {
      $this->config_delete($oProvider->type, 'provider_' . $oProvider->provider_id);
    }
    Fax::config_status(Fax::STATUS_NEED_RELOAD);
  }

}

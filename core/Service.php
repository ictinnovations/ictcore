<?php

namespace ICT\Core;

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

  const STATUS_READY = 0;
  const STATUS_NEED_RELOAD = 1;

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
        if (isset($capabilities[$type]) && in_array($feature, $capabilities[$type])) {
          return TRUE;
        } else {
          return FALSE;
        }
        break;
    }
  }

  /**
   * ********************************************* Services related function **
   */

  public static function _load()
  {
    static $serviceMap = null;

    if (empty($serviceMap)) {
      // manually load all available service classes
      include_once_directory('Service');
      $listService = list_available_classes('ICT\\Core\\Service');
      foreach ($listService as $serviceClass) {
        $flag = $serviceClass::SERVICE_FLAG;
        $serviceMap[$flag] = $serviceClass;
      }
    }

    return $serviceMap;
  }

  /**
   * Get all available services
   * 
   * @return Service[]
   */
  public static function load_all()
  {
    $serviceMap = self::_load();
    $serviceList = array();
    foreach ($serviceMap as $className) {
      $serviceList[] = new $className;
    }
    return $serviceList;
  }
  
  /**
   * Get Service object by service_flag
   * @param int $service_flag
   * 
   * @return null|Service
   */
  public static function load($service_flag)
  {
    $serviceMap = self::_load();
    if (isset($serviceMap[$service_flag])) {
      $className = $serviceMap[$service_flag];
      $oService = new $className;
      return $oService;
    }
    return null;
  }

  /**
   * ******************************************* Default Gateway for service **
   */

  public static function get_gateway() {
    static $oGateway = NULL;
    if (empty($oGateway)) {
      $oGateway = new Gateway();
    }
    return $oGateway;
  }

  public static function get_route() {
    $aFilter = array(
        'active' => 1,
        'service_flag' => static::SERVICE_FLAG
    );
    $listRoute = Provider::search($aFilter);
    if (count($listRoute)) {
      $aProvider = array_shift($listRoute);
      $oProvider = Provider::load($aProvider['provider_id']);
      return $oProvider;
    }
    throw new CoreException('404', 'No provider available');
  }

  /**
   * ******************************************* Default message for service **
   */

  public static function get_message() {
    static $oMessage = NULL;
    if (empty($oMessage)) {
      $oMessage = new Message();
    }
    return $oMessage;
  }

  /**
   * ***************************************** Application related functions **
   */

  public static function template_path($template_name)
  {
    Corelog::log("Service->template_path demo. name: $template_name", Corelog::WARNING);
  }

  public function application_execute(Application $oApplication, $command = '', $command_type = 'string')
  {
    if (empty($command)) {
      return;
    }

    // some applications require to provide last / disconnect application id
    // to collect call status
    if (($oApplication->defaultSetting & Application::REQUIRE_END_APPLICATION) == Application::REQUIRE_END_APPLICATION) {
      // locate and assign end application id
      $appList = $oApplication->search($oApplication->program_id, Application::ORDER_END);
      foreach ($appList as $disconnectApp) {
        $oApplication->disconnect_application_id = $disconnectApp['application_id'];
        break; // only first
      }
    }

    // initilize token cache
    $oToken = new Token(Token::SOURCE_ALL);
    $oToken->add('application', $oApplication);

    // Some application require active provider to access / dial external extensions
    if (($oApplication->defaultSetting & Application::REQUIRE_PROVIDER) == Application::REQUIRE_PROVIDER) {
      // load provider
      $oProvider = static::get_route();
      $oToken->add('provider', $oProvider);
    }

    // Some application require immediate execution via gateway, instead of http response
    if (($oApplication->defaultSetting & Application::REQUIRE_GATEWAY) == Application::REQUIRE_GATEWAY) {
      // send it via gateway
      $oGateway = $this->get_gateway();
      $command = $oToken->render($command, $command_type); // render tokens
      $oGateway->send($command, $oProvider);
    } else { // default method is http response
      // put it in response cache
      $oSession = Session::get_instance();
      $oSession->response->application_id = 'app_' . $oApplication->application_id;
      $command = $oToken->render($command, $command_type); // render tokens
      $oSession->response->application_data = $command;
    }
  }

  /**
   * *************************************** Configuration related functions **
   */

  public static function config_status($new_status = null)
  {
    $status_variable = 'service:' . static::SERVICE_TYPE . '_status';
    $current_status = Conf::get($status_variable, static::STATUS_READY);
    if ($new_status === null) {
      return $current_status;
    }

    $reference = array(
      'class' => Conf::SYSTEM,
      'node_id' => Conf::get('node:node_id', null)
    );
    Conf::set($status_variable, $new_status, true, $reference, Conf::PERMISSION_NODE_WRITE);
    return Conf::get($status_variable, static::STATUS_READY);
  }
  

  public function config_update()
  {
    $status = $this->config_status();
    if (($status & static::STATUS_NEED_RELOAD) == static::STATUS_NEED_RELOAD) {
      $this->config_update_reload();
      $this->config_status(static::STATUS_READY);
    }
  }

  public function config_update_account(Account $oAccount)
  {
    Corelog::log("Service->config_update_account demo. account_id: " . $oAccount->account_id, Corelog::WARNING);
  }

  public function config_update_user(User $oUser)
  {
    Corelog::log("Service->config_update_user demo. user_id: " . $oUser->user_id, Corelog::WARNING);
  }

  public function config_update_provider(Provider $oProvider)
  {
    Corelog::log("Service->config_update_provider demo. provider_id: " . $oProvider->provider_id, Corelog::WARNING);
  }

  public function config_update_reload()
  {
    $oGateway = $this->get_gateway();
    $oGateway->config_reload();
  }

  protected function config_save($config_type, Token $oToken, $config_name = 'default')
  {
    $template = $this->template_path($config_type);
    $aSetting = $oToken->render_template($template);
    $oGateway = $this->get_gateway();
    $oGateway->config_save($config_type, $config_name, $aSetting);
  }

  protected function config_delete($config_type, $config_name = 'default')
  {
    $oGateway = $this->get_gateway();
    $oGateway->config_delete($config_type, $config_name);
  }
}

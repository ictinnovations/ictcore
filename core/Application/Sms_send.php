<?php

namespace ICT\Core\Application;

/* * ***************************************************************
 * Copyright © 2015 ICT Innovations Pakistan All Rights Reserved   *
 * Developed By: Nasir Iqbal                                       *
 * Website : http://www.ictinnovations.com/                        *
 * Mail : nasir@ictinnovations.com                                 *
 * *************************************************************** */

use ICT\Core\Application;
use ICT\Core\Service\Sms;
use ICT\Core\Spool;

class Sms_send extends Application
{

  /** @var string */
  public $name = 'sms_send';

  /**
   * @property-read string $type
   * @var string
   */
  protected $type = 'sms_send';

  /**
   * This application, is initial application will be executed at start of transmission
   * @var int weight
   */
  public $weight = Application::ORDER_INIT;

  /**
   * ************************************************ Application Parameters **
   */

  /**
   * callerid / phone number of sending party
   * @var string $source
   */
  public $source = '[transmission:source:phone]';

  /**
   * mobile / phone number of remote party
   * @var int $destination
   */
  public $destination = '[transmission:destination:phone]';

  /**
   * message body / data for sms
   * @var string $data
   */
  public $message = '[text:data]';

  /**
   * text encoding being used by sms
   * @var string $encoding
   */
  public $encoding = '[text:encoding]';

  /**
   * sms class
   * @var string $class
   */
  public $class = '[text:class]';

  /**
   * charset type
   * @var string $charset
   */
  public $charset = '[text:type]';

  /**
   * message length in bytes
   * @var int $length
   */
  public $length = '[text:length]';

  /**
   * ******************************************** Default Application Values **
   */

  /**
   * All possible results to use 
   * @var array 
   */
  public static $supportedResult = array(
      'result' => array('success', 'error'),
      'error' => array('') // empty message expected on success
  );

  /**
   * If this application require any special dependency
   * @var integer
   */
  public static $defaultSetting = (Application::REQUIRE_GATEWAY | Application::REQUIRE_PROVIDER);

  /**
   * return a name value pair of all aditional application parameters which we need to save
   * @return array
   */
  public function parameter_save()
  {
    $aParameters = array(
        'source' => $this->source,
        'destination' => $this->destination,
        'message' => $this->message,
        'encoding' => $this->encoding,
        'class' => $this->class,
        'charset' => $this->charset,
        'length' => $this->length
    );
    return $aParameters;
  }

  public function execute()
  {
    $oService = new Sms();
    $template_path = $oService->template_path('sms_send');
    $oService->application_execute($this, $template_path, 'template');
  }

  public function process()
  {
    return Spool::STATUS_COMPLETED;
  }

}

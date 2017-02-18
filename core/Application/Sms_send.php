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
   * Parameters required by this application along with default values
   * @var array 
   */
  public static $requiredParameter = array(
      'data' => '[text:data]',
      'encoding' => '[text:encoding]',
      'class' => '[text:class]',
      'type' => '[text:type]',
      'length' => '[text:length]'
  );

  public function execute()
  {
    $oService = new Sms();
    $command = $oService->application_template('sms_send');
    // this application require gateway access to send a sms
    $oService->application_execute($command, true);
    return ''; // no response, nothing to return
  }

  public function process()
  {
    return Spool::STATUS_COMPLETED;
  }

}
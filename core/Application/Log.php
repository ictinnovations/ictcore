<?php

namespace ICT\Core\Application;

/* * ***************************************************************
 * Copyright © 2015 ICT Innovations Pakistan All Rights Reserved   *
 * Developed By: Nasir Iqbal                                       *
 * Website : http://www.ictinnovations.com/                        *
 * Mail : nasir@ictinnovations.com                                 *
 * *************************************************************** */

use ICT\Core\Application;
use ICT\Core\Service;

class Log extends Application
{

  /** @var string */
  public $name = 'log';

  /**
   * @property-read string $type
   * @var string
   */
  protected $type = 'log';

  /**
   * ******************************************** Default Application Values **
   */

  /**
   * All possible results to use 
   * @var array 
   */
  public static $supportedResult = array(
      'result' => array('success')
  );

  /**
   * Parameters required by this application along with default values
   * @var array 
   */
  public static $requiredParameter = array(
      'message' => 'running program [application:program_id] and application [application:application_id]'
  );

  public function execute()
  {
    // All services should support log appliction
    $oService = Service::load($this->oTransmission->service_flag);
    $template_path = $oService->template_path('log');
    $oService->application_execute($this, $template_path, 'template');
  }

}
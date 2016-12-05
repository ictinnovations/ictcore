<?php
/* * ***************************************************************
 * Copyright © 2015 ICT Innovations Pakistan All Rights Reserved   *
 * Developed By: Nasir Iqbal                                       *
 * Website : http://www.ictinnovations.com/                        *
 * Mail : nasir@ictinnovations.com                                 *
 * *************************************************************** */

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
    $cService = service_flag_to_class($this->oTransmission->service_flag);
    $oService = new $cService();
    return $oService->application_template('log');
  }

}
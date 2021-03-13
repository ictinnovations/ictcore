<?php

namespace ICT\Core\Application;

/* * ***************************************************************
 * Copyright © 2015 ICT Innovations Pakistan All Rights Reserved   *
 * Developed By: Nasir Iqbal                                       *
 * Website : http://www.ictinnovations.com/                        *
 * Mail : nasir@ictinnovations.com                                 *
 * *************************************************************** */

use ICT\Core\Application;
use ICT\Core\Service\Voice;

class Say_number extends Application
{

  /** @var string */
  public $name = 'say_number';

  /**
   * @property-read string $type
   * @var string
   */
  protected $type = 'say_number';

  /**
   * ************************************************ Application Parameters **
   */

  /**
   * number to pronounce
   * @var string $number
   */
  public $number = '[data:number]';

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
   * return a name value pair of all aditional application parameters which we need to save
   * @return array
   */
  public function parameter_save()
  {
    $aParameters = array(
        'number' => $this->number
    );
    return $aParameters;
  }

  public function execute()
  {
    $oService = new Voice();
    $template_path = $oService->template_path('say_number');
    $oService->application_execute($this, $template_path, 'template');
  }

}

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

class Voice_play extends Application
{

  /** @var string */
  public $name = 'voice_play';

  /**
   * @property-read string $type
   * @var string
   */
  protected $type = 'voice_play';

  /**
   * ************************************************ Application Parameters **
   */

  /**
   * file name to recording for play
   * @var string $message
   */
  public $message = '[recording:file_name]';

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
        'message' => $this->message
    );
    return $aParameters;
  }

  public function execute()
  {
    $oService = new Voice();
    $template_path = $oService->template_path('voice_play');
    $oService->application_execute($this, $template_path, 'template');
  }

}
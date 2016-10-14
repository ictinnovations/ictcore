<?php
/* * ***************************************************************
 * Copyright © 2015 ICT Innovations Pakistan All Rights Reserved   *
 * Developed By: Nasir Iqbal                                       *
 * Website : http://www.ictinnovations.com/                        *
 * Mail : nasir@ictinnovations.com                                 *
 * *************************************************************** */

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
      'message' => '[recording:file_name]'
  );

  public function execute()
  {
    $oService = new Voice();
    return $oService->template_application('voice_play');
  }

}
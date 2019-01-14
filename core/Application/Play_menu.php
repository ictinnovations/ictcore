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

class Play_menu extends Application
{

  /** @var string */
  public $name = 'play_menu';

  /**
   * @property-read string $type
   * @var string
   */
  protected $type = 'play_menu';

  /**
   * ************************************************ Application Parameters **
   */

  /**
   * file name to recording for play
   * @var string $message
   */
  public $message = '[recording:file_name]';

  /**
   * number of seconds, application will wait for the user input
   * @var integer $key_timeout
   */
  public $key_timeout = 10;

  /**
   * ******************************************** Default Application Values **
   */

  /**
   * default condition
   * @var array 
   */
  public static $defaultCondition = array('result' => 'timeout');

  /**
   * All possible results to use 
   * @var array 
   */
  public static $supportedResult = array(
      'result' => array('timeout', '1', '2', '3', '4', '5', '6', '7', '8', '9', '0')
  );

  /**
   * return a name value pair of all aditional application parameters which we need to save
   * @return array
   */
  public function parameter_save()
  {
    $aParameters = array(
        'message' => $this->message,
        'key_timeout' => $this->key_timeout
    );
    return $aParameters;
  }

  public function execute()
  {
    $oService = new Voice();
    $template_path = $oService->template_path('play_menu');
    $oService->application_execute($this, $template_path, 'template');
  }

  public function process()
  {
    $this->result['result'] = 'timeout'; // default result

    // if we really have received an input from user
    if (!empty($this->result['key']) && $this->result['error'] != 'timeout') {
      // Save result
      $this->result_create($this->result['key'], 'key', Result::TYPE_INFO);
      $this->result['result'] = $this->result['key'];
    }

    return Spool::STATUS_CONNECTED;
  }
}

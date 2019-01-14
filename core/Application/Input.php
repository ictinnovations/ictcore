<?php

namespace ICT\Core\Application;

/* * ***************************************************************
 * Copyright © 2015 ICT Innovations Pakistan All Rights Reserved   *
 * Developed By: Nasir Iqbal                                       *
 * Website : http://www.ictinnovations.com/                        *
 * Mail : nasir@ictinnovations.com                                 *
 * *************************************************************** */

use ICT\Core\Application;
use ICT\Core\Result;
use ICT\Core\Service\Voice;
use ICT\Core\Spool;

class Input extends Application
{

  /** @var string */
  public $name = 'input';

  /**
   * @property-read string $type
   * @var string
   */
  protected $type = 'input';

  /**
   * ******************************************** Default Application Values **
   */

  /**
   * default condition
   * @var array 
   */
  public static $defaultCondition = array('result' => 'success');

  /**
   * All possible results to use 
   * @var array 
   */
  public static $supportedResult = array(
      'result' => array('success'),
      'key' => '',
  );

  /**
   * return a name value pair of all aditional application parameters which we need to save
   * @return array
   */
  public function parameter_save()
  {
    $aParameters = array(
        'data' => $this->data
    );
    return $aParameters;
  }

  public function execute()
  {
    $oService = new Voice();
    $template_path = $oService->template_path('input');
    $oService->application_execute($this, $template_path, 'template');
  }

  public function process()
  {
    // if we really have received an input from user
    if (!empty($this->result['key'])) {
      // Save result
      $this->result_create($this->result['key'], 'key', Result::TYPE_INFO);
    }
    $this->result['result'] = 'success';

    return Spool::STATUS_CONNECTED;
  }

}

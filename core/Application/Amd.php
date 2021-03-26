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

class Amd extends Application
{

  /** @var string */
  public $name = 'amd';

  /**
   * @property-read string $type
   * @var string
   */
  protected $type = 'amd';

  /**
   * ******************************************** Default Application Values **
   */

  /**
   * default condition
   * @var array 
   */
  public static $defaultCondition = array('result' => 'human');

  /**
   * All possible results to use 
   * @var array 
   */
  public static $supportedResult = array(
      'result' => array('human', 'machine')
  );

  public function execute()
  {
    $oService = new Voice();
    $template_path = $oService->template_path('amd');
    $oService->application_execute($this, $template_path, 'template');
  }

  public function process()
  {
    $amd_result = 'human'; // default result

    // if we really have received a valid result
    if (!empty($this->result['result']) && $this->result['result'] == 'machine') {
      $amd_result = 'machine';
    }
    // Save result
    $this->result_create($amd_result, 'amd', Result::TYPE_INFO);
    $this->result['result'] = $amd_result;

    return Spool::STATUS_CONNECTED;
  }

}

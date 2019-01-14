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

class Dnc extends Application
{

  /** @var string */
  public $name = 'dnc';

  /**
   * @property-read string $type
   * @var string
   */
  protected $type = 'dnc';

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

  public function execute()
  {
    // TODO: add current contact ($this->oTransmission->add_to_dnc()) into DNC list

    // Save result
    $this->result_create($oTransmission->oContact->phone, 'dnc', Result::TYPE_INFO);

    $oService = new Voice();
    $template_path = $oService->template_path('dnc');
    $oService->application_execute($this, $template_path, 'template');
  }

}

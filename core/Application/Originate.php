<?php

namespace ICT\Core\Application;

/* * ***************************************************************
 * Copyright © 2015 ICT Innovations Pakistan All Rights Reserved   *
 * Developed By: Nasir Iqbal                                       *
 * Website : http://www.ictinnovations.com/                        *
 * Mail : nasir@ictinnovations.com                                 *
 * *************************************************************** */

use ICT\Core\Application;
use ICT\Core\Service\Fax;
use ICT\Core\Service\Voice;

class Originate extends Application
{

  /** @var string */
  public $name = 'originate';

  /**
   * @property-read string $type
   * @var string
   */
  protected $type = 'originate';

  public function execute()
  {
    if ($this->oTransmission->service_flag == Voice::SERVICE_FLAG) {
      $oService = new Voice();
    } else if ($this->oTransmission->service_flag == Fax::SERVICE_FLAG) {
      $oService = new Fax();
    }
    $command = $oService->application_template('originate');
    // this application require gateway access to dial
    $oService->application_execute($command, true);
    return ''; // no response, nothing to return
  }

}
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
use ICT\Core\Token;

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
    $oProvider = $oService->get_route();
    $this->oSequence->oToken->add('provider', $oProvider);
    $output = $oService->application_template('originate');
    $command = $this->oSequence->oToken->render_template($output, Token::KEEP_ORIGNAL); // keep provider related token intact
    // this application require gateway access to dial
    $oService->application_execute('originate', $command, $oProvider);
    return ''; // nothing to return
  }

}
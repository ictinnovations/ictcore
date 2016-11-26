<?php
/* * ***************************************************************
 * Copyright © 2015 ICT Innovations Pakistan All Rights Reserved   *
 * Developed By: Nasir Iqbal                                       *
 * Website : http://www.ictinnovations.com/                        *
 * Mail : nasir@ictinnovations.com                                 *
 * *************************************************************** */

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
    $output = $oService->template_application('originate');
    $command = $this->oSequence->oToken->render_variable($output, Token::KEEP_ORIGNAL); // keep provider related token intact
    // this application require gateway access to send an email
    return $oService->execute_application($command, true);
  }

}
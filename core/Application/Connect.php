<?php
/* * ***************************************************************
 * Copyright © 2015 ICT Innovations Pakistan All Rights Reserved   *
 * Developed By: Nasir Iqbal                                       *
 * Website : http://www.ictinnovations.com/                        *
 * Mail : nasir@ictinnovations.com                                 *
 * *************************************************************** */

class Connect extends Application
{

  /** @var string */
  public $name = 'connect';

  /**
   * @property-read string $type
   * @var string
   */
  protected $type = 'connect';

  public function execute()
  {
    if ($this->oTransmission->service_flag == Voice::SERVICE_FLAG) {
      $oService = new Voice();
    } else if ($this->oTransmission->service_flag == Fax::SERVICE_FLAG) {
      $oService = new Fax();
    }

    return $oService->template_application('connect');
  }

  public function process()
  {
    if (!empty($this->result['result']) && $this->result['result'] == 'success') {
      return Spool::STATUS_CONNECTED;
    } else {
      return Spool::STATUS_FAILED;
    }
  }

}
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
use ICT\Core\Spool;

class Connect extends Application
{

  /** @var string */
  public $name = 'connect';

  /**
   * @property-read string $type
   * @var string
   */
  protected $type = 'connect';

  /**
   * ******************************************** Default Application Values **
   */

  /**
   * If this application require any special dependency
   * @var integer
   */
  public static $defaultSetting = Application::REQUIRE_END_APPLICATION;

  public function execute()
  {
    if ($this->oTransmission->service_flag == Voice::SERVICE_FLAG) {
      $oService = new Voice();
    } else if ($this->oTransmission->service_flag == Fax::SERVICE_FLAG) {
      $oService = new Fax();
    }

    $template_path = $oService->template_path('connect');
    $oService->application_execute($this, $template_path, 'template');
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

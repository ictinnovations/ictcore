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

class Originate extends Application
{

  /** @var string */
  public $name = 'originate';

  /**
   * @property-read string $type
   * @var string
   */
  protected $type = 'originate';

  /**
   * This application, is initial application will be executed at start of transmission
   * @var int weight
   */
  public $weight = Application::ORDER_INIT;

  /**
   * ************************************************ Application Parameters **
   */

  /**
   * callerid / phone number of dialing party
   * @var string $source
   */
  public $source = '[transmission:source:phone]';

  /**
   * phone number of remote party
   * @var int $destination
   */
  public $destination = '[transmission:destination:phone]';

  /**
   * ******************************************** Default Application Values **
   */

  /**
   * If this application require any special dependency
   * @var integer
   */
  public $defaultSetting = Application::REQUIRE_GATEWAY;

  public function __construct($application_id = null, $aParameter = null)
  {
    $this->defaultSetting = (Application::REQUIRE_END_APPLICATION | Application::REQUIRE_GATEWAY | Application::REQUIRE_PROVIDER);
    parent::__construct($application_id, $aParameter);
  }

  /**
   * return a name value pair of all aditional application parameters which we need to save
   * @return array
   */
  public function parameter_save()
  {
    $aParameters = array(
        'source' => $this->source,
        'destination' => $this->destination
    );
    return $aParameters;
  }

  public function execute()
  {
    if ($this->oTransmission->service_flag == Voice::SERVICE_FLAG) {
      $oService = new Voice();
    } else if ($this->oTransmission->service_flag == Fax::SERVICE_FLAG) {
      $oService = new Fax();
    }
    $template_path = $oService->template_path('originate');
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

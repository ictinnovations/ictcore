<?php

namespace ICT\Core\Program;

/* * ***************************************************************
 * Copyright © 2015 ICT Innovations Pakistan All Rights Reserved   *
 * Developed By: Nasir Iqbal                                       *
 * Website : http://www.ictinnovations.com/                        *
 * Mail : nasir@ictinnovations.com                                 *
 * *************************************************************** */

use ICT\Core\Account\Extension;
use ICT\Core\Application\Disconnect;
use ICT\Core\Application\Originate;
use ICT\Core\Application\Transfer;
use ICT\Core\Program;
use ICT\Core\Scheme;
use ICT\Core\Service\Voice;
use ICT\Core\Transmission;

class Agent extends Program
{

  /** @var string */
  public $name = 'agent';

  /**
   * @property-read string $type
   * @var string 
   */
  protected $type = 'agent';

  /**
   * **************************************************** Program Parameters **
   */

  /**
   * extension_id of targeted extension being used with this program
   * @var int $extension_id
   */
  public $extension_id = '[account:account_id]';

  /**
   * return a name value pair of all additional program parameters which we need to save
   * @return array
   */
  public function parameter_save()
  {
    $aParameters = array(
        'extension_id' => $this->extension_id
    );
    return $aParameters;
  }

  /**
   * Locate and load extension
   * Use extension_id from program parameters as reference
   * @return Extension null or a valid extension object
   */
  protected function resource_load_extension()
  {
    if (isset($this->extension_id) && !empty($this->extension_id)) {
      $oExtension = new Extension($this->extension_id);
      return $oExtension;
    }
  }

  /**
   * Function: scheme
   * Program scheme for primary transmission, application execution order and conditions
   */
  public function scheme()
  {
    $outboundCall = new Originate();
    $outboundCall->source = '[transmission:source:phone]';
    $outboundCall->destination = '[transmission:destination:phone]';

    $transfer = new Transfer();
    $transfer->extension = $this->aResource['extension']->phone;

    $hangupCall = new Disconnect();

    $oScheme = new Scheme($outboundCall);
    $oScheme->link($transfer)->link($hangupCall);

    return $oScheme;
  }

  /**
   * Function: transmission_create
   * Creating transmission while using current program
   */
  public function transmission_create($contact_id, $account_id, $direction = Transmission::OUTBOUND)
  {
    $oTransmission = parent::transmission_create($contact_id, $account_id, $direction);
    $oTransmission->service_flag = Voice::SERVICE_FLAG;
    return $oTransmission;
  }

}
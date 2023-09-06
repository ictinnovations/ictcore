<?php

namespace ICT\Core\Program;

/* * ***************************************************************
 * Copyright © 2015 ICT Innovations Pakistan All Rights Reserved   *
 * Developed By: Nasir Iqbal                                       *
 * Website : http://www.ictinnovations.com/                        *
 * Mail : nasir@ictinnovations.com                                 *
 * *************************************************************** */

use ICT\Core\Account\Did;
use ICT\Core\Account\Extension;
use ICT\Core\Application\Connect;
use ICT\Core\Application\Disconnect;
use ICT\Core\Application\Inbound;
use ICT\Core\Application\Transfer;
use ICT\Core\Exchange\Dialplan;
use ICT\Core\Program;
use ICT\Core\Scheme;
use ICT\Core\Service\Voice;
use ICT\Core\Transmission;

class Forward extends Program
{

  /** @var string */
  public $name = 'forward';

  /**
   * @property-read string $type
   * @var string 
   */
  protected $type = 'forward';

  /**
   * **************************************************** Program Parameters **
   */

  /**
   * did_id of selected DID, which we to forward
   * @var int $did_id
   */
  public $did_id = '[did:did_id]';

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
        'did_id' => $this->did_id,
        'extension_id' => $this->extension_id
    );
    return $aParameters;
  }

  /**
   * Locate and load did
   * Use did_id from program parameters as reference
   * @return Did null or a valid did object
   */
  protected function resource_load_did()
  {
    if (isset($this->did_id) && !empty($this->did_id)) {
      $oDid = new Did($this->did_id);
      return $oDid;
    }
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
    $inboundCall = new Inbound();
    $inboundCall->context = 'external';
    $inboundCall->filter_flag = Dialplan::FILTER_COMMON;
    $inboundCall->source = null; // allow any source
    if (isset($this->aResource['did'])) {
      $inboundCall->destination = $this->aResource['did']->phone;
    }

    $answerCall = new Connect();

    $transferCall = new Transfer();
    $transferCall->extension = $this->aResource['extension']->phone;
    $transferCall->user_id = $this->aResource['extension']->user_id;

    $hangupCall = new Disconnect();

    $oScheme = new Scheme($inboundCall);
    $oScheme->link($answerCall)->link($transferCall)->link($hangupCall);

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

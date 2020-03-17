<?php

namespace ICT\Core\Program;

/* * ***************************************************************
 * Copyright © 2015 ICT Innovations Pakistan All Rights Reserved   *
 * Developed By: Nasir Iqbal                                       *
 * Website : http://www.ictinnovations.com/                        *
 * Mail : nasir@ictinnovations.com                                 *
 * *************************************************************** */

use ICT\Core\Application\Disconnect;
use ICT\Core\Application\Originate;
use ICT\Core\Application\Tts;
use ICT\Core\Application\Wait;
use ICT\Core\Message\Text;
use ICT\Core\Program;
use ICT\Core\Scheme;
use ICT\Core\Service\Voice;
use ICT\Core\Transmission;
use ICT\Core\Corelog;

class Voicetts extends Program
{

  /** @var string */
  public $name = 'voicetts';

  /**
   * @property-read string $type
   * @var string
   */
  protected $type = 'voicetts';

  /**
   * **************************************************** Program Parameters **
   */

  /**
   * text to read
   * @var string $text
   */
  public $text = '[data:text]';

  public $delay = '[data:delay]';

 /**
   * return a name value pair of all aditional application parameters which we need to save
   * @return array
   */
  public function parameter_save()
  {
    $aParameters = array(
        'text' => $this->text
    );
    return $aParameters;
  }


  /**
   * Locate and load recording
   * Use recording_id or content or data from program parameters as reference
   * @return Recording null or a valid recording object
   */
  protected function resource_load_text()
  {
    if (isset($this->text_id) && !empty($this->text_id)) {
      $oText = new Text($this->text_id);
      return $oText;
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

    $ttsMessage = new Tts();
    $ttsMessage->text = $this->text;

    $ttsRepeat = new Tts();
    $ttsRepeat->text = $this->text;

    $wait = new Wait();
    $wait->delay = $this->delay;

    $hangupCall = new Disconnect();

    $oScheme = new Scheme($outboundCall);
    $oScheme->link($ttsMessage)->link($wait)->link($ttsRepeat)->link($hangupCall);

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

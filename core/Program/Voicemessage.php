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
use ICT\Core\Application\Voice_play;
use ICT\Core\Message\Recording;
use ICT\Core\Program;
use ICT\Core\Scheme;
use ICT\Core\Service\Voice;
use ICT\Core\Transmission;

class Voicemessage extends Program
{

  /** @var string */
  public $name = 'voicemessage';

  /**
   * @property-read string $type
   * @var string 
   */
  protected $type = 'voicemessage';

  /**
   * **************************************************** Program Parameters **
   */

  /**
   * recording_id of recording being used as message in this program
   * @var int $recording_id
   */
  public $recording_id = '[recording:recording_id]';

  /**
   * return a name value pair of all aditional program parameters which we need to save
   * @return array
   */
  public function parameter_save()
  {
    $aParameters = array(
        'recording_id' => $this->recording_id
    );
    return $aParameters;
  }

  /**
   * Locate and load recording
   * Use recording_id or content or data from program parameters as reference
   * @return Recording null or a valid recording object
   */
  protected function resource_load_recording()
  {
    if (isset($this->recording_id) && !empty($this->recording_id)) {
      $oRecording = new Recording($this->recording_id);
      return $oRecording;
    } else if (isset($this->file_name) || isset($this->recording_file)) {
      $file_name = !empty($this->file_name) ? $this->file_name : $this->recording_file;
      if (!empty($file_name)) {
        $oRecording = Recording::construct_from_array(array('file_name' => $file_name));
        $oRecording->save();
         // update recording_id with new value, and remove all temporary parameters
        $this->recording_id = $oRecording->recording_id;
        unset($this->file_name);
        unset($this->recording_file);
        return $oRecording;
      }
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

    $voicePlay = new Voice_play();
    $voicePlay->message = $this->aResource['recording']->file_name;

    $hangupCall = new Disconnect();

    $oScheme = new Scheme();
    $oScheme->add($outboundCall);
    $oScheme->add($voicePlay);
    $oScheme->add($hangupCall);

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
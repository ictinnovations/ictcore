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
   * ************************************************ Default Program Values **
   */

  /**
   * Parameters required by this program along with default values
   * @var array 
   */
  public static $requiredParameter = array(
      'recording_id' => '[recording_id]'
  );

  /**
   * Locate and load recording
   * Use recording_id or content or data from program data as reference
   * @return Recording null or a valid recording object
   */
  protected function resource_load_recording()
  {
    if (isset($this->data['recording_id']) && !empty($this->data['recording_id'])) {
      $oRecording = new Recording($this->data['recording_id']);
      return $oRecording;
    } else if (isset($this->data['file_name']) || isset($this->data['recording_file'])) {
      $file_name = !empty($this->data['file_name']) ? $this->data['file_name'] : $this->data['recording_file'];
      if (!empty($file_name)) {
        $oRecording = Recording::construct_from_array(array('file_name' => $file_name));
        $oRecording->save();
         // update recording_id with new value, and remove all temporary parameters
        $this->data['recording_id'] = $oRecording->recording_id;
        unset($this->data['file_name']);
        unset($this->data['recording_file']);
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

    $voicePlay = new Voice_play();
    $voicePlay->data = array(
        'message' => $this->aResource['recording']->file_name
    );

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
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
      'recording_id' => '[recording:recording_id]'
  );

  /**
   * Function: data map
   * Needed to load objects based data using their corresponding IDs from given program data
   */
  protected function data_map($parameter_name, $parameter_value)
  {
    $dataMap = array();
    switch ($parameter_name) {
      case 'recording_id':
        $dataMap['recording'] = new Recording($parameter_value);
        break;
      case 'recording_file':
      case 'file_name':
        $oRecording = Recording::construct_from_array(array('file_name' => $parameter_value));
        $oRecording->save();
        $dataMap['recording'] = $oRecording;
        break;
    }
    return $dataMap;
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
        'message' => $this->aCache['recording']->file_name
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
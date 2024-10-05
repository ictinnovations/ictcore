<?php

namespace ICT\Core\Application;

/* * ***************************************************************
 * Copyright © 2015 ICT Innovations Pakistan All Rights Reserved   *
 * Developed By: Nasir Iqbal                                       *
 * Website : http://www.ictinnovations.com/                        *
 * Mail : nasir@ictinnovations.com                                 *
 * *************************************************************** */

use ICT\Core\Application;
use ICT\Core\Message\Recording;
use ICT\Core\Result;
use ICT\Core\Service\Voice;
use ICT\Core\Spool;

class Record extends Application
{

  /** @var string */
  public $name = 'record';

  /**
   * @property-read string $type
   * @var string
   */
  protected $type = 'record';

  /**
   * ************************************************ Application Parameters **
   */

  /**
   * a none existant file name to save new voice recording
   * @var string $record_file
   */
  public $recording_file = '/tmp/new_recording.wav';

  /**
   * number of seconds, application can record user input
   * @var integer $max_duration
   */
  public $max_duration = 120;

  /**
   * ******************************************** Default Application Values **
   */

  /**
   * default condition
   * @var array 
   */
  public static $defaultCondition = array('result' => 'success');

  /**
   * All possible results to use 
   * @var array 
   */
  public static $supportedResult = array(
      'result' => array('success', 'error'),
      'recording_file' => '/path/to/file',
      'duration' => 0,
      'error' => '' // empty message expected on success
  );

  public function __construct($application_id = null, $aParameter = null)
  {
    global $path_cache;
    parent::__construct($application_id, $aParameter);
    $this->recording_file = tempnam($path_cache, 'voice_') . '.wav';
  }

  /**
   * return a name value pair of all aditional application parameters which we need to save
   * @return array
   */
  public function parameter_save()
  {
    $aParameters = array(
        'max_duration' => $this->max_duration
    );
    return $aParameters;
  }

  public function execute()
  {
    $oService = new Voice();
    $template_path = $oService->template_path('record');
    $oService->application_execute($this, $template_path, 'template');
  }

  public function process()
  {
    // if we really have received a Recording
    if (isset($this->result['recording_file']) && file_exists($this->result['recording_file'])) {
      // we received a recording file, we need to save it
      $file_name = 'recording_' . $this->application_id . '_' . $this->oTransmission->oSpool->spool_id;
      $oRecording = new Recording();
      $oRecording->name = $file_name;
      $oRecording->description = 'file received while processing transmission: ' . $this->oTransmission->transmission_id;
      $oRecording->file_name = $this->result['recording_file'];
      $oRecording->save();

      // Save result
      $this->result_create($oRecording->recording_id, 'recording', Result::TYPE_MESSAGE);
      $this->result_create($oRecording->duration, 'duration', Result::TYPE_INFO);
      $this->result['result'] = 'success';
    } else {
      // if no valid file found then change result to with error
      $this->result['result'] = 'error';
      $this->result_create('invalid recording', 'error', Result::TYPE_ERROR);
    }

    return Spool::STATUS_CONNECTED;
    // return Spool::STATUS_COMPLETED;
  }

}

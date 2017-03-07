<?php

namespace ICT\Core\Application;

/* * ***************************************************************
 * Copyright © 2015 ICT Innovations Pakistan All Rights Reserved   *
 * Developed By: Nasir Iqbal                                       *
 * Website : http://www.ictinnovations.com/                        *
 * Mail : nasir@ictinnovations.com                                 *
 * *************************************************************** */

use ICT\Core\Application;
use ICT\Core\Exchange\Dialplan;
use ICT\Core\Gateway\Kannel;
use ICT\Core\Message\Text;
use ICT\Core\Program;
use ICT\Core\Result;
use ICT\Core\Spool;

class Sms_receive extends Application
{

  /** @var string */
  public $name = 'sms_receive';

  /**
   * @property-read string $type
   * @var string
   */
  protected $type = 'sms_receive';

  /**
   * This application, is initial application will be executed at start of transmission
   * @var int weight
   */
  public $weight = Application::ORDER_INIT;

  /**
   * ************************************************ Application Parameters **
   */

  /**
   * communication context, internal or external
   * @var string $context
   */
  public $context = 'external';

  /**
   * caller id of incomming sms
   * by default it should be empty for dialplan to allow any number as source
   * @var string $source
   */
  public $source = null;

  /**
   * destination / long number for sms destination
   * by default it should be empty for dialplan to allow any number as destination
   * @var string $detination
   */
  public $destination = null;

  /**
   * to trigger this application from dialplan, which filter must be met
   * @var int $filter_flag
   */
  public $filter_flag = Dialplan::FILTER_COMMON;

  /**
   * ******************************************** Default Application Values **
   */

  /**
   * All possible results to use 
   * @var array 
   */
  public static $supportedResult = array(
      'result' => array('success', 'error'),
      'context' => array('internal', 'external'),
      'source' => '[contact:phone]',
      'destination' => '[account:phone]'
  );

  /**
   * return a name value pair of all aditional application parameters which we need to save
   * @return array
   */
  public function parameter_save()
  {
    $aParameters = array(
        'context' => $this->context,
        'source' => $this->source,
        'destination' => $this->destination,
        'filter_flag' => $this->filter_flag
    );
    return $aParameters;
  }

  public function deploy(Program &$oProgram)
  {
    $oDialplan = new Dialplan();
    $oDialplan->application_id = $this->type;
    $oDialplan->program_id = $oProgram->program_id;
    $oDialplan->context = $this->context;
    $oDialplan->gateway_flag = Kannel::GATEWAY_FLAG;
    if (!empty($this->source)) {
      $oDialplan->source = $this->source;
    }
    if (!empty($this->destination)) {
      $oDialplan->destination = $this->destination;
    }

    return $oDialplan->save();
  }

  public function remove()
  {
    $filter = array('application_id' => $this->application_id);
    $listDialplan = Dialplan::search($filter);
    foreach ($listDialplan as $aDialplan) {
      $oDialplan = new Dialplan($aDialplan['dialplan_id']);
      $oDialplan->delete();
    }
  }

  public function execute()
  {
    // this application can't be executed in that way, so nothing to execute here
  }

  public function process()
  {
    // if we really have received an email
    if (!empty($this->result['data'])) {
      // we received an email, we need to save it
      $file_name = 'sms_' . $this->application_id . '_' . $this->oTransmission->oSpool->spool_id;
      $oText = new Text();
      $oText->name = $file_name;
      $oText->description = 'sms received while processing transmission: ' . $this->oTransmission->transmission_id;
      $oText->data = $this->result['message'];
      $oText->save();

      // Save result
      $this->result_create($oText->text_id, 'text', Result::TYPE_MESSAGE);
      $this->result['result'] = 'success';
    } else {
      $this->result['result'] = 'error';
    }

    return Spool::STATUS_COMPLETED;
  }

}
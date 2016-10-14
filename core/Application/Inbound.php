<?php
/* * ***************************************************************
 * Copyright © 2015 ICT Innovations Pakistan All Rights Reserved   *
 * Developed By: Nasir Iqbal                                       *
 * Website : http://www.ictinnovations.com/                        *
 * Mail : nasir@ictinnovations.com                                 *
 * *************************************************************** */

class Inbound extends Application
{

  /** @var string */
  public $name = 'inbound';

  /**
   * @property-read string $type
   * @var string
   */
  protected $type = 'inbound';

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
      'source' => array('[source:phone]'),
      'destination' => array('[destination:phone]')
  );

  /**
   * Parameters required by this application along with default values
   * @var array 
   */
  public static $requiredParameter = array(
      'context' => 'external',
      'source' => '[source:phone]',
      'destination' => '[destination:phone]',
      'filter_flag' => Dialplan::FILTER_COMMON
  );

  public function deploy(Program &$oProgram)
  {
    $oDialplan = new Dialplan();
    $oDialplan->application_id = $this->type;
    $oDialplan->program_id = $oProgram->program_id;
    $oDialplan->context = $this->data['context'];
    $oDialplan->filter_flag = $this->data['filter_flag'];
    $oDialplan->gateway_flag = Freeswitch::GATEWAY_FLAG;
    if (!empty($this->data['source'])) {
      $oDialplan->source = $this->data['source'];
    }
    if (!empty($this->data['destination'])) {
      $oDialplan->destination = $this->data['destination'];
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
    return Spool::STATUS_CONNECTED;
  }

}
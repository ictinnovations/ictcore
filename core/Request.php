<?php
/* * ****************************************************************
 * Copyright © 2016 ICT Innovations Pakistan All Rights Reserved   *
 * Developed By: Nasir Iqbal                                       *
 * Website : http://www.ictinnovations.com/                        *
 * Mail : nasir@ictinnovations.com                                 *
 * **************************************************************** */

class Request
{

  /** @var integer $gateway_flag */
  public $gateway_flag = Freeswitch::GATEWAY_FLAG;

  /** @var integer $spool_id */
  public $spool_id = null;

  /** @var integer $application-id */
  public $application_id = null;

  /** @var array $application_data */
  public $application_data = array();

  /** @var string $source */
  public $source = null;

  /** @var string $destination */
  public $destination = null;

  public function __get($field)
  {
    switch ($field) {
      default:
        return $this->$field;
    }
  }

  public function __set($field, $value)
  {
    switch ($field) {
      default:
        $this->$field = trim($value);
    }
  }

  public function __sleep()
  {
    return array('gateway_flag', 'spool_id', 'application_id', 'application_data', 'source', 'destination');
  }

  public function schedule($schedule_data = array())
  {
    if (empty($schedule_data)) {
      $schedule_data = array('delay' => 60);
    }
    $oSchedule = new Schedule();
    $oSchedule->type = 'request';
    $oSchedule->action = 'process';
    $oSchedule->data = serialize($this);
    foreach ($schedule_data as $schedule_field => $schedule_value) {
      $oSchedule->$schedule_field = $schedule_value;
    }
    $oSchedule->save();
    return $oSchedule->schedule_id;
  }

  public function schedule_cancel()
  {
    // TODO: schedule_cancel in not supported
  }

  public function schedule_process($oSchedule)
  {
    try {
      $oRequest = unserialize($oSchedule->data); // data is serilized request
      switch ($oSchedule->action) {
        case 'process':
          Core::process($oRequest);
          break;
        default:
          throw new CoreException("500", "Unknown schedule action, Unable to continue!");
      }
    } catch (Exception $ex) {
      Corelog::log($ex->getMessage(), Corelog::ERROR);
      Corelog::log("Unable to process request", Corelog::ERROR);
    }
  }

}
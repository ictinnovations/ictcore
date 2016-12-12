<?php

namespace ICT\Core;

/* * ****************************************************************
 * Copyright © 2016 ICT Innovations Pakistan All Rights Reserved   *
 * Developed By: Nasir Iqbal                                       *
 * Website : http://www.ictinnovations.com/                        *
 * Mail : nasir@ictinnovations.com                                 *
 * **************************************************************** */

use Exception;

class Request
{

  /** @var integer $gateway_flag */
  public $gateway_flag = Gateway::GATEWAY_FLAG;

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

  public function task_create($task_data = array())
  {
    if (empty($task_data)) {
      $task_data = array('status' => Task::PENDING); // start now
    }
    $oTask = new Task();
    $oTask->type = 'request';
    $oTask->action = 'process';
    $oTask->data = serialize($this);
    foreach ($task_data as $task_field => $task_value) {
      $oTask->$task_field = $task_value;
    }
    $oTask->save();
    return $oTask->task_id;
  }

  public function task_cancel()
  {
    // TODO: task_cancel in not supported
  }

  public function task_process($oTask)
  {
    try {
      $oRequest = unserialize($oTask->data); // data is serilized request
      switch ($oTask->action) {
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
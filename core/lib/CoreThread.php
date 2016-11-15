<?php
/* * ***************************************************************
 * Copyright © 2014 ICT Innovations Pakistan All Rights Reserved   *
 * Developed By: Nasir Iqbal                                       *
 * Website : http://www.ictinnovations.com/                        *
 * Mail : nasir@ictinnovations.com                                 *
 * *************************************************************** */

use Aza\Components\Thread\Thread;

class CoreThread extends Thread
{
  function __construct($pName = null, $pool = null, $debug = false, array $options = null)
  {
    global $ict_db_link;
    parent::__construct($pName, $pool, $debug, $options);
    $ict_db_link = DB::connect(TRUE);
    Corelog::$process_id = getmypid();
    Corelog::log("New thread started for: " . get_class($this), Corelog::FLOW);
  }
}

class SendThread extends CoreThread
{

  function process()
  {
    // First parameter will be transmission
    $oTransmission = $this->getParam(0);
    sleep(1); // wait 1 second before processing
    Core::send($oTransmission);
  }

}

class ProcessThread extends CoreThread
{

  function process()
  {
    // First parameter will be oRequest
    $oRequest = $this->getParam(0);
    sleep(1); // wait 1 second before processing
    Core::process($oRequest);
  }

}

class TaskThread extends CoreThread
{

  function process()
  {
    // First parameter will be task_id
    $task_id = $this->getParam(0);
    // Second parameter will be server_time
    $server_time = $this->getParam(1);
    $oTask = new Task($task_id);
    $oTask->process($server_time);
  }

}
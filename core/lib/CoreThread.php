<?php
/* * ***************************************************************
 * Copyright © 2014 ICT Innovations Pakistan All Rights Reserved   *
 * Developed By: Nasir Iqbal                                       *
 * Website : http://www.ictinnovations.com/                        *
 * Mail : nasir@ictinnovations.com                                 *
 * *************************************************************** */

use Aza\Components\Thread\Thread;

class CoreSend extends Thread
{

  function process()
  {
    // First parameter will be transmission
    $oTransmission = $this->getParam(0);
    sleep(1); // wait 1 second before processing
    Core::send($oTransmission);
  }

}

class CoreProcess extends Thread
{

  function process()
  {
    // First parameter will be oRequest
    $oRequest = $this->getParam(0);
    sleep(1); // wait 1 second before processing
    Core::process($oRequest);
  }

}

class ScheduleProcess extends Thread
{

  function process()
  {
    // First parameter will be schedule_id
    $schedule_id = $this->getParam(0);
    $oSchedule = new Schedule($schedule_id);
    $oSchedule->process();
  }

}
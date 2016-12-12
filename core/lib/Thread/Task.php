<?php

namespace ICT\Core\Thread;

/* * ***************************************************************
 * Copyright © 2014 ICT Innovations Pakistan All Rights Reserved   *
 * Developed By: Nasir Iqbal                                       *
 * Website : http://www.ictinnovations.com/                        *
 * Mail : nasir@ictinnovations.com                                 *
 * *************************************************************** */

use ICT\Core\CoreThread;
use ICT\Core\Task as UserTask;

class Task extends CoreThread
{

  function process()
  {
    // First parameter will be task_id
    $task_id = $this->getParam(0);
    // Second parameter will be server_time
    $server_time = $this->getParam(1);
    $oTask = new UserTask($task_id);
    $oTask->process($server_time);
  }

}
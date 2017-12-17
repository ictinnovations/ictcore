<?php
/* * ***************************************************************
 * Copyright © 2014 ICT Innovations Pakistan All Rights Reserved   *
 * Developed By: Nasir Iqbal                                       *
 * Website : http://www.ictinnovations.com/                        *
 * Mail : nasir@ictinnovations.com                                 *
 * *************************************************************** */

use ICT\Core\Service;
use ICT\Core\Task;

// default include is /usr/ictcore/core
chdir(dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'core');

include_once "Core.php";

function cron_process()
{
  // process all pending retries
  Task::process_all();

  // execute reload method for all available services
  // it will restart gateway in case there are new configurations
  $listService = Service::load_all();
  foreach ($listService as $oService) {
    $oService->config_update();
  }

  // execute email fetch script
  // nothing special we just need to include it for execution
  include_once('../bin/sendmail/gateway.php');
}

cron_process();
exit();

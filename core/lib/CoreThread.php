<?php

namespace ICT\Core;

/* * ***************************************************************
 * Copyright © 2014 ICT Innovations Pakistan All Rights Reserved   *
 * Developed By: Nasir Iqbal                                       *
 * Website : http://www.ictinnovations.com/                        *
 * Mail : nasir@ictinnovations.com                                 *
 * *************************************************************** */

use Aza\Components\Thread\Thread;

class CoreThread extends Thread
{
  protected function onFork()
  {
    DB::$link = DB::connect(TRUE);
    Corelog::$process_id = getmypid();
    Corelog::log("New thread started for: " . get_class($this), Corelog::FLOW);
  }

  protected function onShutdown()
  {
    // nothing to do
  }

}
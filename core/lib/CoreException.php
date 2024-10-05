<?php

namespace ICT\Core;

/* * ***************************************************************
 * Copyright © 2015 ICT Innovations Pakistan All Rights Reserved   *
 * Developed By: Nasir Iqbal                                       *
 * Website : http://www.ictinnovations.com/                        *
 * Mail : nasir@ictinnovations.com                                 *
 * *************************************************************** */

/*
  TODO drop \Jacwright\RestServer\RestException
  Note: we are using this to match RestServer requirement for error
 */

include_once dirname(__FILE__) . '/../../vendor/jacwright/restserver/source/Jacwright/RestServer/RestServer.php';

use Jacwright\RestServer\RestException;

class CoreException extends RestException
{

  public function __construct($code, $message = null, $sourceException = null)
  {
    parent::__construct($code, $message);
    // save sourceException in logs, only if its not CoreException cos in that 
    // case, log is already saved
    if ($sourceException && !($sourceException instanceof CoreException)) {
      $extra = array(
          'code' => $this->getCode(),
          'file' => $this->getFile(),
          'line' => $this->getLine()
      );
      Corelog::log($sourceException->getMessage(), Corelog::ERROR, $extra);
    }
    $extra = array(
        'code' => $this->getCode(),
        'file' => $this->getFile(),
        'line' => $this->getLine()
    );
    Corelog::log($message, Corelog::ERROR, $extra);
  }

}
<?php

namespace ICT\Core;

/* * ***************************************************************
 * Copyright © 2016 ICT Innovations Pakistan All Rights Reserved   *
 * Developed By: Nasir Iqbal                                       *
 * Website : http://www.ictinnovations.com/                        *
 * Mail : nasir@ictinnovations.com                                 *
 * *************************************************************** */

class Response
{

  /** @var integer $spool_id */
  public $spool_id = null;

  /** @var integer $application_id */
  public $application_id = null;

  /** @var array $application_data */
  public $application_data = array();

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

}
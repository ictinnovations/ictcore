<?php

namespace ICT\Core\Account;

/* * ***************************************************************
 * Copyright © 2014 ICT Innovations Pakistan All Rights Reserved   *
 * Developed By: Nasir Iqbal                                       *
 * Website : http://www.ictinnovations.com/                        *
 * Mail : nasir@ictinnovations.com                                 *
 * *************************************************************** */

use ICT\Core\Account;
use ICT\Core\Service\Voice;

class Did extends Account
{

  /**
   * @property-read string $type
   * @var string
   */
  protected $type = 'did';

  public static function search($aFilter = array())
  {
    $aFilter['type'] = 'did';
    return parent::search($aFilter);
  }

  // Note: No configuration needed for DID, cos creating an incoming trunk is a separate thing
}

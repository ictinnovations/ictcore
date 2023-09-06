<?php

namespace ICT\Core\Account;

/* * ***************************************************************
 * Copyright © 2014 ICT Innovations Pakistan All Rights Reserved   *
 * Developed By: Nasir Iqbal                                       *
 * Website : http://www.ictinnovations.com/                        *
 * Mail : nasir@ictinnovations.com                                 *
 * *************************************************************** */

use ICT\Core\Account;

class EAddress extends Account
{

  /**
   * @property-read string $type
   * @var string
   */
  protected $type = 'eaddress';

  public static function search($aFilter = array())
  {
    $aFilter['type'] = 'eaddress';
    return parent::search($aFilter);
  }

  // Note: No configuration needed for Email type account, cos creating an incoming trunk is a separate thing
}

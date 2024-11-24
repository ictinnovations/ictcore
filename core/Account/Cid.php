<?php

namespace ICT\Core\Account;

/* * ***************************************************************
 * Copyright © 2024 ICT Innovations Pakistan All Rights Reserved   *
 * Developed By: Nasir Iqbal                                       *
 * Website : http://www.ictinnovations.com/                        *
 * Mail : nasir@ictinnovations.com                                 *
 * *************************************************************** */

use ICT\Core\Account;

class Cid extends Account
{

  /**
   * @property-read string $type
   * @var string
   */
  protected $type = 'cid';


  public static function search($aFilter = array())
  {
    $aFilter['type'] = 'cid';
    return parent::search($aFilter);
  }
  
  // Note: No configuration needed for DID, cos creating an incoming trunk is a separate thing
}
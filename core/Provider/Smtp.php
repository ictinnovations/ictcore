<?php

namespace ICT\Core\Provider;

/* * ***************************************************************
 * Copyright © 2014 ICT Innovations Pakistan All Rights Reserved   *
 * Developed By: Nasir Iqbal                                       *
 * Website : http://www.ictinnovations.com/                        *
 * Mail : nasir@ictinnovations.com                                 *
 * *************************************************************** */

use ICT\Core\Provider;

class Smtp extends Provider
{

  /**
   * @property-read string $type
   * @var string 
   */
  protected $type = 'smtp';

  public function search($aFilter = array())
  {
    $aFilter['type'] = 'smtp';
    return parent::search($aFilter);
  }

}
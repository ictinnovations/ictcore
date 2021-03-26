<?php

namespace ICT\Core\Provider;

/* * ***************************************************************
 * Copyright © 2014 ICT Innovations Pakistan All Rights Reserved   *
 * Developed By: Nasir Iqbal                                       *
 * Website : http://www.ictinnovations.com/                        *
 * Mail : nasir@ictinnovations.com                                 *
 * *************************************************************** */

use ICT\Core\Provider;
use ICT\Core\Service\Email;

class Smtp extends Provider
{

  /**
   * @property-read string $type
   * @var string 
   */
  public $type = 'smtp';

  /** @var string */
  public $port = '25';

  /**
   * @property integer $service_flag
   * @var integer
   */
  public $service_flag = Email::SERVICE_FLAG;

  public static function search($aFilter = array())
  {
    $aFilter['type'] = 'smtp';
    return parent::search($aFilter);
  }

}

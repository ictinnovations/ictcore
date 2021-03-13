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

class Emailcmd extends Provider
{

  /**
   * @property-read string $type
   * @var string 
   */
  public $type = 'emailcmd';

  /** @var string */
  public $cli = '/usr/sbin/sendmail';

  /**
   * @property integer $service_flag
   * @var integer
   */
  public $service_flag = Email::SERVICE_FLAG;

  public static function search($aFilter = array())
  {
    $aFilter['type'] = 'emailcmd';
    return parent::search($aFilter);
  }

}

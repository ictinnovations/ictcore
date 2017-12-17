<?php

namespace ICT\Core\Provider;

/* * ***************************************************************
 * Copyright © 2014 ICT Innovations Pakistan All Rights Reserved   *
 * Developed By: Nasir Iqbal                                       *
 * Website : http://www.ictinnovations.com/                        *
 * Mail : nasir@ictinnovations.com                                 *
 * *************************************************************** */

use ICT\Core\Provider;
use ICT\Core\Service\Sms;

class Smpp extends Provider
{

  /**
   * @property-read string $type
   * @var string 
   */
  protected $type = 'smpp';

  public static function search($aFilter = array())
  {
    $aFilter['type'] = 'smpp';
    return parent::search($aFilter);
  }

  public function save()
  {
    $result = parent::save();

    // configuration update is required for providers
    $oSms = new Sms();
    $oSms->config_update_provider($this);

    return $result;
  }

  public function delete()
  {
    // configuration update is required for providers
    $this->active = 0; // disable to delete, no save needed
    $oSms = new Sms();
    $oSms->config_update_provider($this);

    // now it is safe to delete
    parent::delete();
  }

}

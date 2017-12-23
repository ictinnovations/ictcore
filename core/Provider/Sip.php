<?php

namespace ICT\Core\Provider;

/* * ***************************************************************
 * Copyright © 2014 ICT Innovations Pakistan All Rights Reserved   *
 * Developed By: Nasir Iqbal                                       *
 * Website : http://www.ictinnovations.com/                        *
 * Mail : nasir@ictinnovations.com                                 *
 * *************************************************************** */

use ICT\Core\Provider;
use ICT\Core\Service\Fax;
use ICT\Core\Service\Voice;

class Sip extends Provider
{

  /**
   * @property-read string $type
   * @var string 
   */
  protected $type = 'sip';

  /**
   * @property integer $service_flag
   * @var integer
   */
  public $service_flag = 3; // i.e (Voice::SERVICE_FLAG | Fax::SERVICE_FLAG);

  public static function search($aFilter = array())
  {
    $aFilter['type'] = 'sip';
    return parent::search($aFilter);
  }

  public function save()
  {
    $result = parent::save();

    // configuration update is required for providers
    $oVoice = new Voice();
    $oVoice->config_update_provider($this);

    return $result;
  }

  public function delete()
  {
    // configuration update is required for providers
    $this->active = 0; // disable to delete, no save needed
    $oVoice = new Voice();
    $oVoice->config_update_provider($this);

    // now it is safe to delete
    return parent::delete();
  }

}

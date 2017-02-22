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
use ICT\Core\Token;

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
    parent::save();

    $oToken = new Token();
    $oToken->add('provider', $this);

    $oVoice = new Sms();
    $template = $oVoice->template_path('smpp');
    $aSetting = $oToken->render_template($template);
    $oVoice->config_delete('smpp', $this->name);
    $oVoice->config_save('smpp', $this->name, $aSetting);
    $oVoice->config_reload();
  }

  public function delete()
  {
    $oVoice = new Sms();
    $oVoice->config_delete('smpp', $this->username);
    parent::delete();
    $oVoice->config_reload();
  }

}
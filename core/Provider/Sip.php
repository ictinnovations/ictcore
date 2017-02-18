<?php

namespace ICT\Core\Provider;

/* * ***************************************************************
 * Copyright © 2014 ICT Innovations Pakistan All Rights Reserved   *
 * Developed By: Nasir Iqbal                                       *
 * Website : http://www.ictinnovations.com/                        *
 * Mail : nasir@ictinnovations.com                                 *
 * *************************************************************** */

use ICT\Core\Provider;
use ICT\Core\Service\Voice;
use ICT\Core\Token;

class Sip extends Provider
{

  /**
   * @property-read string $type
   * @var string 
   */
  protected $type = 'sip';

  public static function search($aFilter = array())
  {
    $aFilter['type'] = 'sip';
    return parent::search($aFilter);
  }

  public function save()
  {
    parent::save();

    $oToken = new Token();
    $oToken->add('provider', $this);

    $oVoice = new Voice();
    $template = $oVoice->config_template('sip', $this->username);
    $aSetting = $oToken->render_template($template);
    $oVoice->config_delete('sip', $this->name);
    $oVoice->config_save('sip', $this->name, $aSetting);
    $oVoice->config_reload();
  }

  public function delete()
  {
    $oVoice = new Voice();
    $oVoice->config_delete('sip', $this->username);
    parent::delete();
    $oVoice->config_reload();
  }

}
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
use ICT\Core\Token;

class Extension extends Account
{

  /**
   * @property-read string $type
   * @var string 
   */
  protected $type = 'extension';

  public static function search($aFilter = array())
  {
    $aFilter['type'] = 'extension';
    return parent::search($aFilter);
  }

  public function save()
  {
    parent::save();

    $oToken = new Token();
    $oToken->add('account', $this->token_get());
    $oToken->add('extension', $this->token_get());

    $oVoice = new Voice();
    $template = $oVoice->config_template($this->type, $this->username);
    $extension = $oToken->render_template($template);
    $oVoice->config_save('extension', $this->username, $extension);
  }

  public function delete()
  {
    $oVoice = new Voice();
    $oVoice->config_delete('extension', $this->username);
    parent::delete();
  }

}
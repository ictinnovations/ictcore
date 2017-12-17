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
    $result = parent::save();

    // configuration update is required for accounts
    $oVoice = new Voice();
    $oVoice->config_update_account($this);

    return $result;
  }

  public function delete()
  {
    // configuration update is required for accounts
    $this->active = 0; // disable to delete, no save needed
    $oVoice = new Voice();
    $oVoice->config_update_account($this);

    // now it is safe to delete
    parent::delete();
  }

  public function associate($user_id, $aUser = array())
  {
    parent::associate($user_id, $aUser);
    // configuration update is required for accounts
    $oVoice = new Voice();
    $oVoice->config_update_account($this);
  }

  public function dissociate()
  {
    parent::dissociate();
    // configuration update is required for accounts
    $oVoice = new Voice();
    $oVoice->config_update_account($this);
  }
}
<?php

namespace ICT\Core\Application;

/* * ***************************************************************
 * Copyright © 2015 ICT Innovations Pakistan All Rights Reserved   *
 * Developed By: Nasir Iqbal                                       *
 * Website : http://www.ictinnovations.com/                        *
 * Mail : nasir@ictinnovations.com                                 *
 * *************************************************************** */

use ICT\Core\Application;
use ICT\Core\Service\Voice;
use ICT\Core\Session;

class Transfer extends Application
{

  /** @var string */
  public $name = 'transfer';

  /**
   * @property-read string $type
   * @var string
   */
  protected $type = 'transfer';

  /**
   * ************************************************ Application Parameters **
   */

  /**
   * target extension to transfer
   * @var string $extension
   */
  public $extension = '[extension:phone]';

  /**
   * user_id for the owner of extension
   * @var int $user_id
   */
  public $user_id = '[extension:user_id]';

  public function token_load()
  {
    parent::token_load();
    if (empty($this->user_id) || $this->user_id == '[extension:user_id]') {
      $oSession = Session::get_instance();
      if (isset($oSession->user)) {
        $this->user_id = $oSession->user->user_id;
      }
    }
  }

  /**
   * return a name value pair of all additional application parameters which we need to save
   * @return array
   */
  public function parameter_save()
  {
    $aParameters = array(
        'extension' => $this->extension,
        'user_id' => $this->user_id
    );
    return $aParameters;
  }

  public function execute()
  {
    $oService = new Voice();
    $template_path = $oService->template_path('transfer');
    $oService->application_execute($this, $template_path, 'template');
  }

}
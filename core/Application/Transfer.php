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

  /**
   * ******************************************** Default Application Values **
   */

  /**
   * If this application require any special dependency
   * @var integer
   */
  public static $defaultSetting = Application::REQUIRE_PROVIDER;

  public function get_user_id()
  {
    if (empty($this->user_id) || $this->user_id == '[extension:user_id]') {
      $oSession = Session::get_instance();
      if (isset($oSession->user)) {
        return $oSession->user->user_id;
      }
    } else {
      return $this->user_id;
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
    $this->user_id = $this->get_user_id();

    $oService = new Voice();
    $template_path = $oService->template_path('transfer');
    $oService->application_execute($this, $template_path, 'template');
  }

}

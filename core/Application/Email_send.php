<?php

namespace ICT\Core\Application;

/* * ***************************************************************
 * Copyright © 2015 ICT Innovations Pakistan All Rights Reserved   *
 * Developed By: Nasir Iqbal                                       *
 * Website : http://www.ictinnovations.com/                        *
 * Mail : nasir@ictinnovations.com                                 *
 * *************************************************************** */

use ICT\Core\Application;
use ICT\Core\Service\Email;
use ICT\Core\Spool;

class Email_send extends Application
{

  /** @var string */
  public $name = 'email_send';

  /**
   * @property-read string $type
   * @var string
   */
  protected $type = 'email_send';

  /**
   * This application initial application will start a new transmission
   * @var int weight
   */
  public $weight = Application::ORDER_INIT;

  /**
   * ******************************************** Default Application Values **
   */

  /**
   * Parameters required by this application along with default values
   * @var array 
   */
  public static $requiredParameter = array(
      'subject' => '[template:subject]',
      'body' => '[template:body]',
      'body_alt' => '[template:body_alt]',
      'attachment' => '[template:attachment]'
  );

  public function execute()
  {
    $oService = new Email();
    $template_path = $oService->template_path('email_send');
    $oService->application_execute($this, $template_path, 'template');
  }

  public function process()
  {
    return Spool::STATUS_COMPLETED;
  }

}
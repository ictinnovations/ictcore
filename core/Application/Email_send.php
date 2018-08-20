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
   * This application, is initial application will be executed at start of transmission
   * @var int weight
   */
  public $weight = Application::ORDER_INIT;

  /**
   * ************************************************ Application Parameters **
   */

  /**
   * Email address of sending party
   * @var string $source
   */
  public $source = '[transmission:source:email]';

  /**
   * Email address of remote party
   * @var int $destination
   */
  public $destination = '[transmission:destination:email]';

  /**
   * email subject
   * @var string $subject
   */
  public $subject = '[template:subject]';

  /**
   * email body
   * @var string $body
   */
  public $body = '[template:body]';

  /**
   * alternative email body
   * @var string $body_alt
   */
  public $body_alt = '[template:body_alt]';

  /**
   * file name of email attachment
   * @var string $attachment
   */
  public $attachment = '[template:attachment]';

  /**
   * return a name value pair of all aditional application parameters which we need to save
   * @return array
   */
  public function parameter_save()
  {
    $aParameters = array(
        'source' => $this->source,
        'destination' => $this->destination,
        'subject' => $this->subject,
        'body' => $this->body,
        'body_alt' => $this->body_alt,
        'attachment' => $this->attachment
    );
    return $aParameters;
  }

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

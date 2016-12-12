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
use ICT\Core\Token;

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
    $oProvider = $oService->get_route();
    $this->oSequence->oToken->add('provider', $oProvider);
    $output = $oService->application_template('email_send');
    $command = $this->oSequence->oToken->render_template($output, Token::KEEP_ORIGNAL); // keep provider related token intact
    // this application require gateway access to send an email
    $oService->application_execute('email_send', $command, $oProvider);
    return ''; // nothing to return
  }

  public function process()
  {
    return Spool::STATUS_COMPLETED;
  }

}
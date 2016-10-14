<?php
/* * ***************************************************************
 * Copyright © 2015 ICT Innovations Pakistan All Rights Reserved   *
 * Developed By: Nasir Iqbal                                       *
 * Website : http://www.ictinnovations.com/                        *
 * Mail : nasir@ictinnovations.com                                 *
 * *************************************************************** */

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
    $output = $oService->template_application('email_send');
    $command = $this->oSequence->oToken->token_replace($output, Token::KEEP_ORIGNAL); // keep provider related token intact
    // this application require gateway access to send an email
    return $oService->execute_application($command, FALSE);
  }

  public function process()
  {
    return Spool::STATUS_COMPLETED;
  }

}
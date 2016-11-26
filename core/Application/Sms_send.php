<?php
/* * ***************************************************************
 * Copyright © 2015 ICT Innovations Pakistan All Rights Reserved   *
 * Developed By: Nasir Iqbal                                       *
 * Website : http://www.ictinnovations.com/                        *
 * Mail : nasir@ictinnovations.com                                 *
 * *************************************************************** */

class Sms_send extends Application
{

  /** @var string */
  public $name = 'sms_send';

  /**
   * @property-read string $type
   * @var string
   */
  protected $type = 'sms_send';

  /**
   * ******************************************** Default Application Values **
   */

  /**
   * All possible results to use 
   * @var array 
   */
  public static $supportedResult = array(
      'result' => array('success', 'error'),
      'error' => array('') // empty message expected on success
  );

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
    $oService = new Sms();
    $output = $oService->template_application('sms_send');
    $command = $this->oSequence->oToken->render_variable($output, Token::KEEP_ORIGNAL); // keep provider related token intact
    // this application require gateway access to send an email
    return $oService->execute_application($command, true);
  }

  public function process()
  {
    return Spool::STATUS_COMPLETED;
  }

}
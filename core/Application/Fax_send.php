<?php
/* * ***************************************************************
 * Copyright © 2015 ICT Innovations Pakistan All Rights Reserved   *
 * Developed By: Nasir Iqbal                                       *
 * Website : http://www.ictinnovations.com/                        *
 * Mail : nasir@ictinnovations.com                                 *
 * *************************************************************** */

class Fax_send extends Application
{

  /** @var string */
  public $name = 'fax_send';

  /**
   * @property-read string $type
   * @var string
   */
  protected $type = 'fax_send';

  /**
   * ******************************************** Default Application Values **
   */

  /**
   * All possible results to use 
   * @var array 
   */
  public static $supportedResult = array(
      'result' => array('success', 'error'),
      'pages' => 0,
      'error' => '' // empty message expected on success
  );

  /**
   * Parameters required by this application along with default values
   * @var array 
   */
  public static $requiredParameter = array(
      'message' => '[document:file_name]',
      'header' => '[document:name]'
  );

  public function execute()
  {
    $oService = new Fax();
    return $oService->application_template('fax_send');
  }

  public function process()
  {
    if ($this->result['result'] == 'success') {
      // we delivered a fax, we need to save its pages
      $this->result_create($this->result['pages'], 'pages', Result::TYPE_INFO);
    } else {
      // fax delivery failed, we need to save the error message
      $this->result_create($this->result['error'], 'error', Result::TYPE_ERROR);
      $this->result['result'] = 'error';
    }

    // TODO return Spool::STATUS_CONNECTED;
    return Spool::STATUS_COMPLETED;
  }

}
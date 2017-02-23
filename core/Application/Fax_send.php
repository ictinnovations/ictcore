<?php

namespace ICT\Core\Application;

/* * ***************************************************************
 * Copyright © 2015 ICT Innovations Pakistan All Rights Reserved   *
 * Developed By: Nasir Iqbal                                       *
 * Website : http://www.ictinnovations.com/                        *
 * Mail : nasir@ictinnovations.com                                 *
 * *************************************************************** */

use ICT\Core\Application;
use ICT\Core\Result;
use ICT\Core\Service\Fax;
use ICT\Core\Spool;

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
   * ************************************************ Application Parameters **
   */

  /**
   * file name of fax document
   * @var string $message
   */
  public $message = '[document:file_name]';

  /**
   * title for email document
   * @var string $header
   */
  public $header = '[document:name]';

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
   * return a name value pair of all aditional application parameters which we need to save
   * @return array
   */
  public function parameter_save()
  {
    $aParameters = array(
        'message' => $this->message,
        'header' => $this->header
    );
    return $aParameters;
  }

  public function execute()
  {
    $oService = new Fax();
    $template_path = $oService->template_path('fax_send');
    $oService->application_execute($this, $template_path, 'template');
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
    return Spool::STATUS_DONE;
  }

}
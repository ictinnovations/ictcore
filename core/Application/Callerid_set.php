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

class Callerid_set extends Application
{

  /** @var string */
  public $name = 'callerid_set';

  /**
   * @property-read string $type
   * @var string
   */
  protected $type = 'callerid_set';

  /**
   * ************************************************ Application Parameters **
   */

  /**
   * new caller id
   * @var string $message
   */
  public $callerid = '[account:phone]';

  /**
   * ******************************************** Default Application Values **
   */

  /**
   * All possible results to use 
   * @var array 
   */
  public static $supportedResult = array(
      'result' => array('success')
  );

  /**
   * return a name value pair of all aditional application parameters which we need to save
   * @return array
   */
  public function parameter_save()
  {
    $aParameters = array(
        'callerid' => $this->callerid
    );
    return $aParameters;
  }

  public function execute()
  {
    $oService = new Voice();
    $template_path = $oService->template_path('callerid_set');
    $oService->application_execute($this, $template_path, 'template');
  }

}

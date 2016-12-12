<?php

namespace ICT\Core\Program;

/* * ***************************************************************
 * Copyright © 2015 ICT Innovations Pakistan All Rights Reserved   *
 * Developed By: Nasir Iqbal                                       *
 * Website : http://www.ictinnovations.com/                        *
 * Mail : nasir@ictinnovations.com                                 *
 * *************************************************************** */

use ICT\Core\Application\Email_send;
use ICT\Core\Message\Template;
use ICT\Core\Program;
use ICT\Core\Scheme;
use ICT\Core\Service\Email;
use ICT\Core\Transmission;

class Sendemail extends Program
{

  /** @var string */
  public $name = 'sendemail';

  /**
   * @property-read string $type
   * @var string 
   */
  protected $type = 'sendemail';

  /**
   * ************************************************ Default Program Values **
   */

  /**
   * Parameters required by this program along with default values
   * @var array 
   */
  public static $requiredParameter = array(
      'template_id' => '[template:template_id]'
  );

  /**
   * Function: data map
   * Needed to load objects based data using their corresponding IDs from given program data
   */
  protected function data_map($parameter_name, $parameter_value)
  {
    $dataMap = array();
    switch ($parameter_name) {
      case 'template_id':
        $dataMap['template'] = new Template($parameter_value);
        break;
      case 'template_file':
        $oTemplate = Template::construct_from_file($parameter_value);
        $oTemplate->save();
        $dataMap['template'] = $oTemplate;
        break;
      case 'template_array':
        $oTemplate = Template::construct_from_array($parameter_value);
        $oTemplate->save();
        $dataMap['template'] = $oTemplate;
        break;
    }
    return $dataMap;
  }

  /**
   * Function: scheme
   * Program scheme for primary transmission, application execution order and conditions
   */
  public function scheme()
  {
    $emailSend = new Email_send();
    $emailSend->data = array(
        'subject' => $this->aCache['template']->subject,
        'body' => $this->aCache['template']->body,
        'body_alt' => $this->aCache['template']->body_alt,
        'attachment' => $this->aCache['template']->attachment
    );

    $oScheme = new Scheme();
    $oScheme->add($emailSend);

    return $oScheme;
  }

  /**
   * Function: transmission_create
   * Creating transmission while using current program
   */
  public function transmission_create($contact_id, $account_id, $direction = Transmission::OUTBOUND)
  {
    $oTransmission = parent::transmission_create($contact_id, $account_id, $direction);
    $oTransmission->service_flag = Email::SERVICE_FLAG;
    return $oTransmission;
  }

}
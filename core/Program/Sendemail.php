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
      'template_id' => '[template_id]'
  );

  /**
   * Locate and load template
   * Use template_id or template_file or template_array from program data as reference
   * @return Template null or a valid template object
   */
  protected function resource_load_template()
  {
    if (isset($this->data['template_id']) && !empty($this->data['template_id'])) {
      $oTemplate = new Template($this->data['template_id']);
    } else if (isset($this->data['template_file']) && !empty($this->data['template_file'])) {
      $oTemplate = Template::construct_from_file($this->data['template_file']);
      $oTemplate->save();
    } else if (isset($this->data['template_array']) && !empty($this->data['template_array'])) {
      $oTemplate = Template::construct_from_array($this->data['template_array']);
      $oTemplate->save();
    }
    if (isset($oTemplate)) {
      // update template_id with new value, and remove all temporary parameters
      $this->data['template_id'] = $oTemplate->template_id;
      unset($this->data['template_file']);
      unset($this->data['template_array']);
      return $oTemplate;
    }
  }

  /**
   * Function: scheme
   * Program scheme for primary transmission, application execution order and conditions
   */
  public function scheme()
  {
    $emailSend = new Email_send();
    $emailSend->data = array(
        'subject' => $this->aResource['template']->subject,
        'body' => $this->aResource['template']->body,
        'body_alt' => $this->aResource['template']->body_alt,
        'attachment' => $this->aResource['template']->attachment
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
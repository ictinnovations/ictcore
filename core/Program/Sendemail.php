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
   * **************************************************** Program Parameters **
   */

  /**
   * template_id of template being used as message in this program
   * @var int $template_id
   */
  public $template_id = '[template:template_id]';

  /**
   * return a name value pair of all aditional program parameters which we need to save
   * @return array
   */
  public function parameter_save()
  {
    $aParameters = array(
        'template_id' => $this->template_id
    );
    return $aParameters;
  }

  /**
   * Locate and load template
   * Use template_id or template_file or template_array from program parameters as reference
   * @return Template null or a valid template object
   */
  protected function resource_load_template()
  {
    if (isset($this->template_id) && !empty($this->template_id)) {
      $oTemplate = new Template($this->template_id);
    } else if (isset($this->template_file) && !empty($this->template_file)) {
      $oTemplate = Template::construct_from_file($this->template_file);
      $oTemplate->save();
    } else if (isset($this->template_array) && !empty($this->template_array)) {
      $oTemplate = Template::construct_from_array($this->template_array);
      $oTemplate->save();
    }
    if (isset($oTemplate)) {
      // update template_id with new value, and remove all temporary parameters
      $this->template_id = $oTemplate->template_id;
      unset($this->template_file);
      unset($this->template_array);
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
    $emailSend->subject = $this->aResource['template']->subject;
    $emailSend->body = $this->aResource['template']->body;
    $emailSend->body_alt = $this->aResource['template']->body_alt;
    $emailSend->attachment = $this->aResource['template']->attachment;

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
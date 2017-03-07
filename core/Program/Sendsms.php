<?php

namespace ICT\Core\Program;

/* * ***************************************************************
 * Copyright © 2015 ICT Innovations Pakistan All Rights Reserved   *
 * Developed By: Nasir Iqbal                                       *
 * Website : http://www.ictinnovations.com/                        *
 * Mail : nasir@ictinnovations.com                                 *
 * *************************************************************** */

use ICT\Core\Application\Sms_send;
use ICT\Core\Message\Text;
use ICT\Core\Program;
use ICT\Core\Scheme;
use ICT\Core\Service\Sms;
use ICT\Core\Transmission;

class Sendsms extends Program
{

  /** @var string */
  public $name = 'sendsms';

  /**
   * @property-read string $type
   * @var string 
   */
  protected $type = 'sendsms';

  /**
   * **************************************************** Program Parameters **
   */

  /**
   * text_id of text being used as message in this program
   * @var int $text_id
   */
  public $text_id = '[text:text_id]';

  /**
   * return a name value pair of all aditional program parameters which we need to save
   * @return array
   */
  public function parameter_save()
  {
    $aParameters = array(
        'text_id' => $this->text_id
    );
    return $aParameters;
  }

  /**
   * Locate and load text
   * Use text_id or content or data from program parameters as reference
   * @return Text null or a valid text object
   */
  protected function resource_load_text()
  {
    if (isset($this->text_id) && !empty($this->text_id)) {
      $oText = new Text($this->text_id);
      return $oText;
    } else if (isset($this->message) || isset($this->content)) {
      $message = !empty($this->message) ? $this->message : $this->content;
      if (!empty($message)) {
        $oText = Text::construct_from_array(array('data' => $message));
        $oText->save();
         // update text_id with new value, and remove all temporary parameters
        $this->text_id = $oText->text_id;
        unset($this->message);
        unset($this->content);
        return $oText;
      }
    }
  }

  /**
   * Function: scheme
   * Program scheme for primary transmission, application execution order and conditions
   */
  public function scheme()
  {
    $smsSend = new Sms_send();
    $smsSend->message = $this->aResource['text']->data;
    $smsSend->class = $this->aResource['text']->class;
    $smsSend->encoding = $this->aResource['text']->encoding;
    $smsSend->charset = $this->aResource['text']->type;
    $smsSend->length = $this->aResource['text']->length;

    $oScheme = new Scheme($smsSend);

    return $oScheme;
  }

  /**
   * Function: transmission_create
   * Creating transmission while using current program
   */
  public function transmission_create($contact_id, $account_id, $direction = Transmission::OUTBOUND)
  {
    $oTransmission = parent::transmission_create($contact_id, $account_id, $direction);
    $oTransmission->service_flag = Sms::SERVICE_FLAG;
    return $oTransmission;
  }

}
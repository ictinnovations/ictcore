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
   * ************************************************ Default Program Values **
   */

  /**
   * Parameters required by this program along with default values
   * @var array 
   */
  public static $requiredParameter = array(
      'text_id' => '[text_id]'
  );

  /**
   * Locate and load text
   * Use text_id or content or data from program data as reference
   * @return Text null or a valid text object
   */
  protected function resource_load_text()
  {
    if (isset($this->data['text_id']) && !empty($this->data['text_id'])) {
      $oText = new Text($this->data['text_id']);
      return $oText;
    } else if (isset($this->data['data']) || isset($this->data['content'])) {
      $data = !empty($this->data['data']) ? $this->data['data'] : $this->data['content'];
      if (!empty($data)) {
        $oText = Text::construct_from_array(array('data' => $data));
        $oText->save();
         // update text_id with new value, and remove all temporary parameters
        $this->data['text_id'] = $oText->text_id;
        unset($this->data['data']);
        unset($this->data['content']);
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
    $smsSend->data = array(
        'data' => $this->aResource['text']->data
    );

    $oScheme = new Scheme();
    $oScheme->add($smsSend);

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
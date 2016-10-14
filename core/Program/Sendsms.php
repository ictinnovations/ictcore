<?php
/* * ***************************************************************
 * Copyright © 2015 ICT Innovations Pakistan All Rights Reserved   *
 * Developed By: Nasir Iqbal                                       *
 * Website : http://www.ictinnovations.com/                        *
 * Mail : nasir@ictinnovations.com                                 *
 * *************************************************************** */

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
      'text_id' => '[text:text_id]'
  );

  /**
   * Function: data map
   * Needed to load objects based data using their corresponding IDs from given program data
   */
  protected function data_map($parameter_name, $parameter_value)
  {
    $dataMap = array();
    switch ($parameter_name) {
      case 'text_id':
        $dataMap['text'] = new Text($parameter_value);
        break;
      case 'content':
      case 'data':
        $oText = Text::construct_from_array(array('data' => $parameter_value));
        $oText->save();
        $dataMap['text'] = $oText;
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
    $smsSend = new Sms_send();
    $smsSend->data = array(
        'data' => $this->aCache['text']->data
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
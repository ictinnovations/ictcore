<?php
/* * ***************************************************************
 * Copyright © 2015 ICT Innovations Pakistan All Rights Reserved   *
 * Developed By: Nasir Iqbal                                       *
 * Website : http://www.ictinnovations.com/                        *
 * Mail : nasir@ictinnovations.com                                 *
 * *************************************************************** */

class Receiveemail extends Program
{

  /** @var string */
  public $name = 'receiveemail';

  /**
   * @property-read string $type
   * @var string 
   */
  protected $type = 'receiveemail';

  /**
   * ************************************************ Default Program Values **
   */

  /**
   * Parameters required by this program along with default values
   * @var array 
   */
  public static $requiredParameter = array(
      'account_id' => '[account:account_id]'
  );

  /**
   * Function: data map
   * Needed to load objects based data using their corresponding IDs from given program data
   */
  protected function data_map($parameter_name, $parameter_value)
  {
    $dataMap = array();
    switch ($parameter_name) {
      case 'account_id':
        $dataMap['account'] = new Account($parameter_value);
        break;
      case 'email':
        $oAccount = Account::construct_from_array(array('email' => $parameter_value));
        $oAccount->save();
        $dataMap['account'] = $oAccount;
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
    $emailRecieve = new Email_receive();
    $emailRecieve->data = array(
        'source' => $this->aCache['account']->email,
        'filter_flag' => Dialplan::FILTER_COMMON
    );

    $oScheme = new Scheme();
    $oScheme->add($emailRecieve);

    return $oScheme;
  }

  /**
   * Function: transmission_create
   * Creating transmission while using current program
   */
  public function transmission_create($contact_id, $account_id, $direction = Transmission::INBOUND)
  {
    $oTransmission = parent::transmission_create($contact_id, $account_id, $direction);
    $oTransmission->service_flag = Email::SERVICE_FLAG;
    return $oTransmission;
  }

  /**
   * Event: Transmission completed
   * Will be fired when first / initial transmission is done
   * here we will decide either its was completed or failed
   */
  protected function transmission_done()
  {
    if ($this->result['result'] == 'error') {
      return Transmission::STATUS_FAILED;
    }

    // after processing further, we can confirm if current transmission was completed
    $result = 'error';
    $error = '';
    foreach ($this->oTransmission->aResult as $oResult) {
      switch ($oResult->type) {
        case Result::TYPE_APPLICATION:
          if ($oResult->name == 'email_receive' && $oResult->data == 'success') {
            $result = 'success';
          }
          break;
        case Result::TYPE_CONTACT:
          // no action needed
          break;
        case Result::TYPE_MESSAGE:
          // no action needed
          break;
      }
    }

    if ($result == 'success' && empty($error)) {
      return Transmission::STATUS_COMPLETED;
    } else {
      $this->result['result'] = 'error';
      $this->result['error'] = $error;
      return Transmission::STATUS_FAILED;
    }
  }

}
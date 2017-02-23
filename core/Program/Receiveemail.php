<?php

namespace ICT\Core\Program;

/* * ***************************************************************
 * Copyright © 2015 ICT Innovations Pakistan All Rights Reserved   *
 * Developed By: Nasir Iqbal                                       *
 * Website : http://www.ictinnovations.com/                        *
 * Mail : nasir@ictinnovations.com                                 *
 * *************************************************************** */

use ICT\Core\Account;
use ICT\Core\Application\Email_receive;
use ICT\Core\Exchange\Dialplan;
use ICT\Core\Program;
use ICT\Core\Result;
use ICT\Core\Scheme;
use ICT\Core\Service\Email;
use ICT\Core\Transmission;

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
   * **************************************************** Program Parameters **
   */

  /**
   * account_id of account associated with this program
   * @var int $account_id
   */
  public $account_id = '[transmission:account:account_id]';

  /**
   * return a name value pair of all aditional program parameters which we need to save
   * @return array
   */
  public function parameter_save()
  {
    $aParameters = array(
        'account_id' => $this->account_id
    );
    return $aParameters;
  }

  /**
   * Locate and load account
   * Use account_id or email address from program parameters as reference
   * @return Account null or a valid account object
   */
  protected function resource_load_account()
  {
    if (isset($this->account_id) && !empty($this->account_id)) {
      $oAccount = new Account($this->account_id);
      return $oAccount;
    } else if (isset($this->email) && !empty($this->email)) {
      $oAccount = Core::locate_account($this->email, 'email');
      if ($oAccount) {
        // update account_id with new value, and remove all temporary parameters
        $this->account_id = $oAccount->account_id;
        unset($this->email);
        return $oAccount;
      }
    }
  }

  /**
   * Function: scheme
   * Program scheme for primary transmission, application execution order and conditions
   */
  public function scheme()
  {
    $emailRecieve = new Email_receive();
    $emailRecieve->source = $this->aResource['account']->email;
    $emailRecieve->filter_flag = Dialplan::FILTER_COMMON;

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
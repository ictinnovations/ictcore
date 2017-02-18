<?php

namespace ICT\Core\Program;

/* * ***************************************************************
 * Copyright © 2015 ICT Innovations Pakistan All Rights Reserved   *
 * Developed By: Nasir Iqbal                                       *
 * Website : http://www.ictinnovations.com/                        *
 * Mail : nasir@ictinnovations.com                                 *
 * *************************************************************** */

use ICT\Core\Account;
use ICT\Core\Application\Connect;
use ICT\Core\Application\Disconnect;
use ICT\Core\Application\Fax_receive;
use ICT\Core\Application\Inbound;
use ICT\Core\Exchange\Dialplan;
use ICT\Core\Message\Document;
use ICT\Core\Program;
use ICT\Core\Result;
use ICT\Core\Scheme;
use ICT\Core\Service\Fax;
use ICT\Core\Transmission;

class Receivefax extends Program
{

  /** @var string */
  public $name = 'receivefax';

  /**
   * @property-read string $type
   * @var string 
   */
  protected $type = 'receivefax';

  /**
   * ************************************************ Default Program Values **
   */

  /**
   * Parameters required by this program along with default values
   * @var array 
   */
  public static $requiredParameter = array(
      'account_id' => '[transmission:account:account_id]'
  );

  /**
   * All possible results to use 
   * @var array 
   */
  public static $supportedResult = array(
      'result' => array('success', 'error'),
      'pages' => 0,
      'error' => ''
  );

  /**
   * Locate and load account
   * Use account_id or phone from program data as reference
   * @return Account null or a valid account object
   */
  protected function resource_load_account()
  {
    if (isset($this->data['account_id']) && !empty($this->data['account_id'])) {
      $oAccount = new Account($this->data['account_id']);
      return $oAccount;
    } else if (isset($this->data['phone']) && !empty($this->data['phone'])) {
      $oAccount = Core::locate_account($this->data['phone'], 'phone');
      if ($oAccount) {
        // update account_id with new value, and remove all temporary parameters
        $this->data['account_id'] = $oAccount->account_id;
        unset($this->data['phone']);
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
    $inboundCall = new Inbound();
    $inboundCall->data = array(
        'source' => $this->aResource['account']->phone,
        'filter_flag' => Dialplan::FILTER_COMMON
    );

    $answerCall = new Connect();

    $faxReceive = new Fax_receive();

    $hangupCall = new Disconnect();

    $oScheme = new Scheme();
    $oScheme->add($inboundCall);
    $oScheme->add($answerCall);
    $oScheme->add($faxReceive);
    $oScheme->add($hangupCall);

    return $oScheme;
  }

  /**
   * Function: transmission_create
   * Creating transmission while using current program
   */
  public function transmission_create($contact_id, $account_id, $direction = Transmission::INBOUND)
  {
    $oTransmission = parent::transmission_create($contact_id, $account_id, $direction);
    $oTransmission->service_flag = Fax::SERVICE_FLAG;
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
    $pages = 0;
    $error = '';
    foreach ($this->oTransmission->aResult as $oResult) {
      switch ($oResult->type) {
        case Result::TYPE_APPLICATION:
          if ($oResult->name == 'fax_receive' && $oResult->data == 'success') {
            $result = 'success';
          }
          break;
        case Result::TYPE_CONTACT:
          // no action needed
          break;
        case Result::TYPE_MESSAGE:
          $oDocument = new Document($oResult->data);
          if (!is_file($oDocument->file_name)) {
            $error = 'There is no or invalid fax file';
            break 2; // in case of error, also terminate foreach loop
          }
          break;
        case Result::TYPE_INFO:
          if ($oResult->name == 'pages') {
            $pages = $oResult->data;
          }
          break;
        case Result::TYPE_ERROR:
          $result = 'error';
          $error = $oResult->data;
          break 2; // in case of error, also terminate foreach loop
      }
    }

    if ($result == 'success' && empty($error) && $pages > 0) {
      $this->result['pages'] = $pages;
      return Transmission::STATUS_COMPLETED;
    } else {
      $this->result['result'] = 'error';
      $this->result['error'] = $error;
      return Transmission::STATUS_FAILED;
    }
  }

}
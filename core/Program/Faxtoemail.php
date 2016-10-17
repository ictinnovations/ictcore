<?php
/* * ***************************************************************
 * Copyright © 2015 ICT Innovations Pakistan All Rights Reserved   *
 * Developed By: Nasir Iqbal                                       *
 * Website : http://www.ictinnovations.com/                        *
 * Mail : nasir@ictinnovations.com                                 *
 * *************************************************************** */

class Faxtoemail extends Program
{

  /** @var string */
  public $name = 'faxtoemail';

  /**
   * @property-read string $type
   * @var string 
   */
  protected $type = 'faxtoemail';

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
   * All possible results to use 
   * @var array 
   */
  public static $supportedResult = array(
      'result' => array('success', 'error'),
      'pages' => 0,
      'error' => ''
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
      case 'phone':
        $oAccount = Account::construct_from_array(array('phone' => $parameter_value));
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
    $inboundCall = new Inbound();
    if (isset($this->aCache['account'])) {
      $inboundCall->data = array(
          'context' => 'external',
          'destination' => $this->aCache['account']->phone,
          'filter_flag' => (Dialplan::FILTER_COMMON | Dialplan::FILTER_ACCOUNT_DESTINATION)
      );
    } else {
      $inboundCall->data = array(
          'context' => 'external',
          'filter_flag' => (Dialplan::FILTER_COMMON | Dialplan::FILTER_ACCOUNT_DESTINATION)
      );
    }

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
    $oTransmission->direction = Transmission::INBOUND; // only inbound fax excepted on external context
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
          // If system already have current contact then this clause will not be called
          // so contact related code will be placed in following lines
          break;
        case Result::TYPE_MESSAGE:
          $oDocument = new Document($oResult->data);
          if (!is_file($oDocument->file_name)) {
            $error = 'There is no or invalid fax file';
            break 2; // in case of error, also terminate foreach loop
          }
          $this->oSequence->token_create($oDocument);
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

    // Update current contact to create email field
    $email = $this->oTransmission->oContact->phone_to_email();
    if (empty($email)) {
      $error = 'Invalid source fax number';
    } else {
      $this->oTransmission->oContact->save();
      $this->oSequence->token_create($this->oTransmission->oContact);
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

  /**
   * Event: Program completed
   * Will be fired when all is done, nothing else left to do for this program
   */
  public function program_completed($program_type, Program &$oProgram)
  {
    /*     * ******************************* Process Primary Program Results */
    if ($program_type == 'primary') {
      // check program status, to decide if we can continue with email notification or not
      if ($oProgram->result['result'] == 'success') {
        // send notification to user, about fax receipt
        $this->send_email_notification();
      } else {
        // primary transmission failed, we can't do anything
      }
    } else { // for all other associated programs
      switch ($oProgram->name) {
        /*         * *************************** Process notification emails */
        case 'faxtoemail_faxreceived':
          // Its is the end of program, we have nothing to do
          break;
      }
    }
  }

  /**
   * **************************************************************************
   * Send Email Notification
   * **************************************************************************
   */
  public function send_email_notification()
  {
    $this->oSequence->token_create($this->oTransmission->oContact);

    // Prepare token object for following transmissions
    $oToken = new Token();
    $oToken->add('fax', $this->oSequence->oToken->token);

    $oTemplate = Template::construct_from_file("Program/Faxtoemail/data/fax_received.tpl.php");
    // Now replace all program related tokens in loaded template, but remember to keep missing tokens
    // Note: in template there is a attachment token, (see: token_create in transmission_done for oDocument)
    $oTemplate->token_apply($oToken, Token::KEEP_ORIGNAL);
    $oTemplate->save();

    // prepare data for new program
    $programData = array(
        'name' => 'faxtoemail_faxreceived',
        'parent_id' => $this->program_id,
        'data' => array(
            'template_id' => $oTemplate->template_id
        )
    );

    // prepare data for new transmission
    $reply_from = conf_get('emailtofax:reply_account', 'default');
    $transmissionData = array(
        'contact_id' => $this->oTransmission->contact_id,
        // replace contact with company contact as per system configurations
        'account_id' => ($reply_from == 'company') ? Contact::COMPANY : $this->oTransmission->account_id,
        'direction' => Transmission::INBOUND
    );

    // Now we are ready to create new transmission for email
    $emailTransmission = Sendemail::transmission_instant($programData, $transmissionData);
    $emailTransmission->schedule(array('delay' => '60'));
    //$emailTransmission->send();
  }

}
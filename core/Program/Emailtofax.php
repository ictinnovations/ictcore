<?php
/* * ***************************************************************
 * Copyright © 2015 ICT Innovations Pakistan All Rights Reserved   *
 * Developed By: Nasir Iqbal                                       *
 * Website : http://www.ictinnovations.com/                        *
 * Mail : nasir@ictinnovations.com                                 *
 * *************************************************************** */

class Emailtofax extends Program
{

  /** @var string */
  public $name = 'emailtofax';

  /**
   * @property-read string $type
   * @var string 
   */
  protected $type = 'emailtofax';

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
    if (isset($this->aCache['account'])) {
      $emailRecieve->data = array(
          'context' => 'internal',
          'source' => $this->aCache['account']->email,
          'filter_flag' => (Dialplan::FILTER_COMMON | Dialplan::FILTER_ACCOUNT_SOURCE)
      );
    } else {
      $emailRecieve->data = array(
          'context' => 'internal',
          'filter_flag' => (Dialplan::FILTER_COMMON | Dialplan::FILTER_ACCOUNT_SOURCE)
      );
    }

    $oScheme = new Scheme();
    $oScheme->add($emailRecieve);

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
    $oTransmission->direction = Transmission::OUTBOUND; // only outbound email excepted on internal context
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
          // If system already have current contact then this clause will not be called
          // so contact related code will be placed in following lines
          break;
        case Result::TYPE_MESSAGE:
          $oTemplate = new Template($oResult->data);
          if (!is_file($oTemplate->attachment)) {
            $error = 'There is no attachment or invalid attachment';
            break 2; // in case of error, also terminate foreach loop
          }
          $this->oSequence->token_create($oTemplate);
          break;
      }
    }

    // Update current contact to create phone field
    $phone = $this->oTransmission->oContact->email_to_phone();
    if (empty($phone)) {
      $error = 'Invalid destination or fax number';
    } else {
      $this->oTransmission->oContact->save();
      $this->oSequence->token_create($this->oTransmission->oContact);
    }

    if ($result == 'success' && empty($error)) {
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
    /**
     * ************************************* Process Primary Program Results **
     */
    if ($program_type == 'primary') {
      // check program status, to decide if we can continue with fax or not
      if ($oProgram->result['result'] == 'success') {
        // send notification to user, about email receipt
        $this->send_email_notification('emailtofax_mailreceived', "Program/Emailtofax/data/email_accepted.tpl.php", 'request');
        // then send fax to destination address
        // use attachment from inbound email, (see: token_create in transmission_done for oTemplate)
        $attachment = $this->oSequence->oToken->token_replace('[template:attachment]');
        $this->send_fax($attachment);
      } else {
        // send notification to user, about email error
        $this->send_email_notification('emailtofax_mailreceived', "Program/Emailtofax/data/email_error.tpl.php", 'request');
      }
    } else { // for all other associated programs
      switch ($oProgram->name) {
        /**
         * ************************************* Process Fax program Results **
         */
        case 'emailtofax_sendfax':
          // check program status, to decide if we have to send success or failure notification
          if ($oProgram->result['result'] == 'success') {
            // Send email notification to user, about fax delivery
            $this->send_email_notification('emailtofax_faxsent', "Program/Emailtofax/data/fax_success.tpl.php", 'fax');
          } else {
            // send notification to user, about fax error
            $this->send_email_notification('emailtofax_faxsent', "Program/Emailtofax/data/fax_error.tpl.php", 'fax');
          }
          break;
        /**
         * ************************************* Process notification emails **
         */
        case 'emailtofax_mailreceived':
          // nothing to do
          break;
        case 'emailtofax_faxsent':
          // Its is the end of program, again nothing to do
          break;
      }
    }
  }

  /**
   * **************************************************************************
   * Send Email Notification
   * **************************************************************************
   */
  public function send_email_notification($notification_title, $template_file, $parent_alias = 'request')
  {
    $this->oSequence->token_create($this->oTransmission->oContact);

    // Prepare token object for following transmissions
    $oToken = new Token();
    $oToken->add($parent_alias, $this->oSequence->oToken->token);

    $oTemplate = Template::construct_from_file($template_file);
    // Now replace all program related tokens in loaded template, but remember to keep missing tokens
    $oTemplate->token_apply($oToken, Token::KEEP_ORIGNAL);
    $oTemplate->save();

    // prepare data for new program
    $programData = array(
        'name' => $notification_title,
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
    $emailTransmission->schedule(array('delay' => 5));
    //$emailTransmission->send();
  }

  /**
   * **************************************************************************
   * Send Fax
   * **************************************************************************
   */
  public function send_fax($fax_filepath)
  {
    // prepare data for new program
    $oDocument = Document();
    $oDocument->file_name = $fax_filepath;
    $oDocument->save();

    $programData = array(
        'name' => 'emailtofax_sendfax',
        'parent_id' => $this->program_id,
        'data' => array(
            'document_id' => $oDocument->document_id
        )
    );

    // prepare data for new transmission
    $transmissionData = array(
        'contact_id' => $this->oTransmission->contact_id,
        'account_id' => $this->oTransmission->account_id,
        'direction' => Transmission::OUTBOUND
    );

    // Now we are ready to launch new transmission for fax
    $faxTransmission = Sendfax::transmission_instant($programData, $transmissionData);
    $faxTransmission->schedule(array('delay' => 5));
    //$faxTransmission->send();
  }

}
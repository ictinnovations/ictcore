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
use ICT\Core\Conf;
use ICT\Core\Contact;
use ICT\Core\Core;
use ICT\Core\CoreException;
use ICT\Core\Exchange\Dialplan;
use ICT\Core\Message\Document;
use ICT\Core\Message\Template;
use ICT\Core\Program;
use ICT\Core\Request;
use ICT\Core\Result;
use ICT\Core\Scheme;
use ICT\Core\Service\Email;
use ICT\Core\Session;
use ICT\Core\Token;
use ICT\Core\Transmission;

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
    if (!Token::is_token($this->account_id) && !empty($this->account_id)) {
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
    $emailRecieve->context = 'internal';
    $emailRecieve->filter_flag = Dialplan::FILTER_COMMON;
    $emailRecieve->destination = null; // allow any destination
    if (isset($this->aResource['account'])) {
      $emailRecieve->source = $this->aResource['account']->email;
    }

    $oScheme = new Scheme($emailRecieve);

    return $oScheme;
  }

  public function authorize(Request $oRequest, Dialplan $oDialplan)
  {
    $aAuth = parent::authorize($oRequest, $oDialplan);
    // after valid authentication, if there is new contact
    if (isset($aAuth['contact']) && empty($aAuth['contact']->phone)) {
      // Update current contact to create phone field
      $phone = $aAuth['contact']->email_to_phone();
      if (empty($phone)) {
        throw new CoreException("404", "Invalid email format, unable to read destination fax number");
      }
    }
    return $aAuth;
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
          // so contact related code will be placed in upcoming lines
          break;
        case Result::TYPE_MESSAGE:
          $oTemplate = new Template($oResult->data);
          if (!is_file($oTemplate->attachment)) {
            $error = 'There is no attachment or invalid attachment';
            break 2; // in case of error, also terminate foreach loop
          }
          // save a refer
          $oSession = Session::get_instance();
          $oSession->template = $oTemplate;
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
        $this->send_email_notification('emailtofax_emailreceived');
        // then send fax to destination address
        // use attachment from inbound email, (see: token_create in transmission_done for oSession->template)
        $oSession = Session::get_instance();
        $oTemplate = $oSession->template;
        $attachment = $oTemplate->attachment;
        $this->send_fax($attachment);
      } else {
        // send notification to user, about email error
        $this->send_email_notification('emailtofax_emailrejected');
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
            $this->send_email_notification('emailtofax_faxsent');
          } else {
            // send notification to user, about fax error
            $this->send_email_notification('emailtofax_faxfailed');
          }
          break;
        /**
         * ************************************* Process notification emails **
         */
        case 'emailtofax_emailreceived':
        case 'emailtofax_emailrejected':
          // nothing to do
          break;
        case 'emailtofax_faxsent':
        case 'emailtofax_faxfailed':
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
  public function send_email_notification($notification_type)
  {
    switch ($notification_type) {
      case 'emailtofax_emailreceived':
        $parent_alias = 'request';
        $template_file = 'Program/Emailtofax/data/email_accepted.tpl.php';
        break;
      case 'emailtofax_emailrejected':
        $parent_alias = 'request';
        $template_file = 'Program/Emailtofax/data/email_error.tpl.php';
        break;
      case 'emailtofax_faxsent':
        $parent_alias = 'fax';
        $template_file = 'Program/Emailtofax/data/fax_success.tpl.php';
        break;
      case 'emailtofax_faxfailed':
      default:
        $parent_alias = 'fax';
        $template_file = 'Program/Emailtofax/data/fax_error.tpl.php';
        break;
    }

    // Prepare token object for following transmissions
    $currentToken = new Token(Token::SOURCE_ALL);
    $currentToken->add('program', $this);
    $oToken = new Token();
    $oToken->add($parent_alias, $currentToken->token); // put everything into new sub group to avoid token conflicts

    $oTemplate = Template::construct_from_file($template_file);
    // Now replace all program related tokens in loaded template, but remember to keep missing tokens
    $oTemplate->token_apply($oToken, Token::KEEP_ORIGNAL);
    $oTemplate->save();

    // prepare data for new program
    $programData = array(
        'name' => $notification_type,
        'parent_id' => $this->program_id,
        'template_id' => $oTemplate->template_id
    );

    // prepare data for new transmission
    $reply_from = Conf::get('emailtofax:reply_account', 'default');
    $transmissionData = array(
        'contact_id' => $this->oTransmission->contact_id,
        // replace contact with company contact as per system configurations
        'account_id' => ($reply_from == 'company') ? Contact::COMPANY : $this->oTransmission->account_id,
        'direction' => Transmission::INBOUND
    );

    // Now we are ready to create new transmission for email
    $emailTransmission = Sendemail::transmission_instant($programData, $transmissionData);
    $emailTransmission->task_create();
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
    $oDocument = new Document();
    $oDocument->file_name = $fax_filepath;
    $oDocument->save();

    $programData = array(
        'name' => 'emailtofax_sendfax',
        'parent_id' => $this->program_id,
        'document_id' => $oDocument->document_id
    );

    // prepare data for new transmission
    $transmissionData = array(
        'contact_id' => $this->oTransmission->contact_id,
        'account_id' => $this->oTransmission->account_id,
        'direction' => Transmission::OUTBOUND
    );

    // Now we are ready to launch new transmission for fax
    $faxTransmission = Sendfax::transmission_instant($programData, $transmissionData);
    $faxTransmission->task_create();
    //$faxTransmission->send();
  }

}

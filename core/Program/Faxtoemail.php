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
use ICT\Core\Service\Fax;
use ICT\Core\Session;
use ICT\Core\Token;
use ICT\Core\Transmission;

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
   * All possible results to use 
   * @var array 
   */
  public static $supportedResult = array(
      'result' => array('success', 'error'),
      'pages' => 0,
      'error' => ''
  );

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
   * Use account_id or phone from program parameters as reference
   * @return Account null or a valid account object
   */
  protected function resource_load_account()
  {
    if (!Token::is_token($this->account_id) && !empty($this->account_id)) {
      $oAccount = new Account($this->account_id);
      return $oAccount;
    } else if (isset($this->phone) && !empty($this->phone)) {
      $oAccount = Core::locate_account($this->phone, 'phone');
      if ($oAccount) {
        // update account_id with new value, and remove all temporary parameters
        $this->account_id = $oAccount->account_id;
        unset($this->phone);
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
    $inboundCall->context = 'external';
    $inboundCall->filter_flag = Dialplan::FILTER_COMMON;
    $inboundCall->source = null; // allow any source
    if (isset($this->aResource['account'])) {
      $inboundCall->destination = $this->aResource['account']->phone;
    }

    $answerCall = new Connect();

    $faxReceive = new Fax_receive();

    $hangupCall = new Disconnect();

    $oScheme = new Scheme($inboundCall);
    $oScheme->link($answerCall)->link($faxReceive)->link($hangupCall);

    return $oScheme;
  }

  public function authorize(Request $oRequest, Dialplan $oDialplan)
  {
    $aAuth = parent::authorize($oRequest, $oDialplan);
    // after valid authentication, if there is no email address in contact
    if (isset($aAuth['contact']) && empty($aAuth['contact']->email)) {
      // Update current contact to create email field
      $email = $aAuth['contact']->phone_to_email();
      if (empty($email)) {
        throw new CoreException("404", "Unable to create a from email address");
      }
    }
    return $aAuth;
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
          // save a reference in session, so it can be used later ( to launch email notification )
          $oSession = Session::get_instance();
          $oSession->document = $oDocument;
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
        // send notification to user, about fax receipt along with received fax document
        // read fax document from session
        $oSession = Session::get_instance();
        if (empty($oSession->transmission->oContact->email)) {
          $oSession->transmission->oContact->phone_to_email();
          $oSession->transmission->oContact->save();
        }
        $this->send_email_notification($oSession->document);
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
  public function send_email_notification(Document $oDocument)
  {
    // Prepare token object for following transmissions
    $currentToken = new Token(Token::SOURCE_ALL);
    $currentToken->add('program', $this);
    $currentToken->add('document', $oDocument); // document is required by following template
    $oToken = new Token();
    $oToken->add('fax', $currentToken->token); // put everything into new sub group to avoid token conflicts

    $oTemplate = Template::construct_from_file("Program/Faxtoemail/data/fax_received.tpl.php");
    // Now replace all program related tokens in loaded template, but remember to keep missing tokens
    // Note: in template there is a attachment token, (see: token_create in transmission_done for oDocument)
    $oTemplate->token_apply($oToken, Token::KEEP_ORIGNAL);
    $oTemplate->save();

    // prepare data for new program
    $programData = array(
        'name' => 'faxtoemail_faxreceived',
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

}

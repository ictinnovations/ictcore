<?php

namespace ICT\Core;

/* * ***************************************************************
 * Copyright © 2014 ICT Innovations Pakistan All Rights Reserved   *
 * Developed By: Nasir Iqbal                                       *
 * Website : http://www.ictinnovations.com/                        *
 * Mail : nasir@ictinnovations.com                                 *
 * *************************************************************** */

use Exception;
use ICT\Core\Exchange\Dialplan;

/* Bootstrap, load all required libraries and configurations */
require_once dirname(__FILE__) . "/lib/init.php";

class Core
{

  public static function send(Transmission $oTransmission)
  {
    Corelog::log('Executing transmission with id : ' . $oTransmission->transmission_id, Corelog::FLOW);

    // Starting a new sequence for current transmission
    $oSequence = new Sequence();
    if (!is_object($oTransmission->oSpool)) {
      $oTransmission->spool_create();
      $oSequence->oResponse->spool_id = $oTransmission->oSpool->spool_id;
    }
    Corelog::log('Attempting with spool_id : ' . $oTransmission->oSpool->spool_id, Corelog::FLOW);
    $oTransmission->oSpool->time_start = time();

    // Trigger program to handle further actions
    $oTransmission->status = Transmission::STATUS_PROCESSING;
    $oSequence->token_create($oTransmission);
    $oProgram = Program::load($oTransmission->program_id);
    $oProgram->_execute($oTransmission, $oSequence);

    // update all status just before dying
    self::wrapup($oTransmission);
  }

  public static function process(Request $oRequest)
  {
    Corelog::log('New request received to process status of application : ' . $oRequest->application_id, Corelog::FLOW);

    $spool_id = null;
    $transmission_id = null;
    $new_request = false;

    // Starting a new sequence for current request
    $oSequence = new Sequence($oRequest);

    // check if we have spool_id
    if ($oRequest->spool_id) {
      $spool_id = $oRequest->spool_id;
      // we have spool_id now use it to find transmission_id
      $listSpool = Spool::search(array('spool_id' => $spool_id));
      $aSpool = array_shift($listSpool);
      $transmission_id = $aSpool['transmission_id'];
    }

    // check either we a valid transmission or need to start a new one
    if ($transmission_id) {
      Corelog::log('Existing transmission requested with id : ' . $transmission_id, Corelog::FLOW);

      // Load transmission using transmission_id
      try {
        $oTransmission = new Transmission($transmission_id);
        $oTransmission->activate_owner();
      } catch (CoreException $ex) {
        throw new CoreException("500", "Unable to load transmission or", $ex);
      }

      // Load program
      try {
        $oProgram = Program::load($oTransmission->program_id);
      } catch (CoreException $ex) {
        throw new CoreException("500", "Unable to load program", $ex);
      }
    } else {
      $new_request = true; // this is new inbound call
      Corelog::log('No transmission found, searching for dialplan', Corelog::FLOW);

      try {
        $account_id = null; // Note: following call will update account_id
        $oProgram = self::locate_dialplan($oSequence, $account_id);
        Corelog::log('Dialplan found with id : ' . $oSequence->oDialplan->dialplan_id, Corelog::FLOW);
      } catch (Exception $ex) {
        throw new CoreException("500", "Unable to locate dialplan", $ex);
      }

      // determine call direction
      if ($oSequence->oDialplan->context == 'internal') {
        $direction = Transmission::OUTBOUND;
      } else {
        $direction = Transmission::INBOUND;
      }

      // for time being create transmissing by using company contact
      $oTransmission = $oProgram->transmission_create(Contact::COMPANY, $account_id, $direction);
      $oTransmission->activate_owner(); // Load permission
      // Finally update contact_id and status for newly created transmission
      // Note: we can't locate or create contact before activating transmission owner
      self::locate_contact($oTransmission, $oSequence);
      $oTransmission->status = Transmission::STATUS_INITIALIZING;
      $oTransmission->save(); // we must save transmission to generate transmission_id for new spool
    }

    // At this point we are excepting to have a valid program and transmission
    // So we are ready to start with response, but first prepare spool objects
    $oTransmission->spool_create($oSequence->oRequest->spool_id);

    // process available data using selected program to produce results
    $oSequence->token_create($oTransmission);
    $oProgram->_process($oTransmission, $oSequence);

    // update all status just before dying
    self::wrapup($oTransmission);

    // return our response
    return $oSequence->oResponse;
  }

  private static function locate_dialplan(Sequence &$oSequence, &$account_id)
  {
    try {
      // Note: dialplan lookup will also set account_id
      $oSequence->oDialplan = Dialplan::lookup($oSequence->oRequest, $account_id);
    } catch (CoreException $ex) {
      throw new CoreException("404", "No recipient found", $ex);
    }

    // Load concerned user credentials to permissions
    try {
      if (empty($account_id)) {
        throw new CoreException("500", "Dialplan unable to find owner account");
      }
      $oAccount = new Account($account_id); // just for test
      $account_id = $oAccount->account_id;
    } catch (Exception $ex) {
      throw new CoreException("500", "Unable to find assocated account", $ex);
    }

    try {
      $oProgram = Program::load($oSequence->oDialplan->program_id);
    } catch (CoreException $ex) {
      throw new CoreException("500", "Error with program, unable to proceed", $ex);
    }

    return $oProgram;
  }

  private static function locate_contact(Transmission &$oTransmission, Sequence &$oSequence)
  {
    // We also need to know about current contact and call direction
    // Both can be determined by examining current contaxt
    try {
      if ($oSequence->oDialplan->context == 'internal') {
        $contact = $oSequence->oRequest->destination;
      } else {
        $contact = $oSequence->oRequest->source;
      }
      // search fo existing contact
      $oGateway = Gateway::load($oSequence->oDialplan->gateway_flag);
      $contactField = $oGateway::CONTACT_FIELD;
      $contactFilter = array($contactField => $contact);
      $listContact = Contact::search($contactFilter);
      if ($listContact) {
        $aContact = array_shift($listContact);
        $oTransmission->contact_id = $aContact['contact_id'];
      } else {
        $oContact = new Contact();
        $oContact->$contactField = $contact;
        $oContact->save();
        $oTransmission->contact_id = $oContact->contact_id;
        $oTransmission->result_create($oTransmission->contact_id, 'contact_new', Result::TYPE_CONTACT, 'inbound');
      }
    } catch (CoreException $ex) {
      throw new CoreException("500", "Unable to locate contact", $ex);
    }
  }

  /* Closing
    save all status in transmission and spool etc ..
   */

  private static function wrapup(Transmission &$oTransmission)
  {
    foreach ($oTransmission->aResult as $oResult) {
      $oResult->save();
    }
    Corelog::log('Last spool status : ' . $oTransmission->oSpool->status, Corelog::FLOW);
    $oTransmission->oSpool->save();
    Corelog::log('Last transmission status : ' . $oTransmission->status, Corelog::FLOW);
    $oTransmission->save();
  }

}
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

  public static function statistic($aFilter = array())
  {
    $aWhere    = array();
    $where_str = '1=1';
    foreach ($aFilter as $search_field => $search_value) {
      switch($search_field) {
        case 'date_created':
        case 'since':
        case 'from':
          $aWhere[] = "t.date_created >= '$search_value'";
          break;
        case 'user_id':
        case 'created_by':
          $aWhere[] = "t.created_by = '$search_value'";
          break;
      }
    }
    if (!empty($aWhere)) {
      $where_str = implode(' AND ', $aWhere);
    }

    // table alias are being used to keep the filter even with JOINs
    $campaign_query     = "SELECT COUNT(t.campaign_id) FROM campaign t WHERE $where_str";
    $group_query        = "SELECT COUNT(t.contact_group_id) FROM contact_group t WHERE $where_str";
    $contact_query      = "SELECT COUNT(t.contact_id) FROM contact t WHERE $where_str";
    $transmission_query = "SELECT COUNT(t.transmission_id) FROM transmission WHERE t $where_str";
    $spool_query        = "SELECT COUNT(s.spool_id) FROM spool s JOIN account t ON s.account_id=t.account_id WHERE $where_str";

    $aStatistic = array(
        'campaign_total' => DB::query_result('campaign', $campaign_query),
        'campaign_active' => DB::query_result('campaign', "$campaign_query AND status='".Campaign::STATUS_RUNNING."'"),
        'group_total' => DB::query_result('contact_group', $group_query),
        'contact_total' => DB::query_result('contact', $contact_query),
        'transmission_total' => DB::query_result('transmission', $transmission_query),
        'transmission_inbound' => DB::query_result('transmission', "$transmission_query AND direction='".Transmission::INBOUND."'"),
        'transmission_outbound' => DB::query_result('transmission', "$transmission_query AND direction='".Transmission::OUTBOUND."'"),
        'transmission_active' => DB::query_result('transmission', "$transmission_query AND status='".Transmission::STATUS_PROCESSING."'"),
        'spool_total' => DB::query_result('spool', $spool_query),
        'spool_success' => DB::query_result('spool', "$spool_query AND status='".Spool::STATUS_COMPLETED."'"),
        'spool_failed' => DB::query_result('spool', "$spool_query AND status='".Spool::STATUS_FAILED."'"),
    );
    return $aStatistic;
  }

  /**
   * Initiate delivery / sending proces for a previously created transmission
   * @param Transmission $oTransmission
   */
  public static function send(Transmission $oTransmission)
  {
    Corelog::log('=================> transmission execution <=================', Corelog::FLOW);
    Corelog::log('Executing transmission with id : ' . $oTransmission->transmission_id, Corelog::FLOW);

    // Starting a new response for current transmission
    $oResponse = new Response();
    if (!is_object($oTransmission->oSpool)) {
      $oTransmission->spool_create();
      $oResponse->spool_id = $oTransmission->oSpool->spool_id;
    }
    Corelog::log('Attempting with spool_id : ' . $oTransmission->oSpool->spool_id, Corelog::FLOW);
    $oTransmission->oSpool->time_start = time();

    // Trigger program to handle further actions
    $oTransmission->status = Transmission::STATUS_PROCESSING;

    // load related program
    $oProgram = Program::load($oTransmission->program_id);

    // register transmission and response objects in session so they can be used by program if necessary
    $oSession = Session::get_instance();
    $oSession->program = $oProgram;
    $oSession->transmission = $oTransmission;
    $oSession->response = $oResponse;

    // Finally execute selected program
    $oSession->program->_execute($oSession->transmission);

    // update all status just before dying
    self::wrapup($oSession->transmission);
    Corelog::log('Transmission execution completed', Corelog::FLOW);

    return $oTransmission->oSpool->spool_id;
  }

  /**
   * Entry point or main function to process inbound request from gateways
   * @param Request $oRequest
   * @return Response
   * @throws CoreException
   */
  public static function process(Request $oRequest)
  {
    Corelog::log('=================> processing response <=================', Corelog::FLOW);
    Corelog::log('Processing status of application : ' . $oRequest->application_id, Corelog::FLOW);

    $spool_id = null;
    $transmission_id = null;
    $new_request = false;

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
        $listDialplan = Dialplan::lookup($oRequest);
        foreach ($listDialplan as $aDialplan) {
          Corelog::log('Trying with dialplan id : ' . $aDialplan['dialplan_id'], Corelog::FLOW);
          $oDialplan = new Dialplan($aDialplan['dialplan_id']);
          $oProgram = Program::load($aDialplan['program_id']);
          $aAuth = $oProgram->authorize($oRequest, $oDialplan);
          if (isset($aAuth['account'])) {
            $oAccount = $aAuth['account'];
            $oContact = $aAuth['contact'];
            Corelog::log('Successfully authenticated for program : ' . $oProgram->type, Corelog::FLOW);
            break;
          }
        }
        if (empty($oAccount)) {
          Corelog::log('Request: '.print_r($oRequest,true), Corelog::ERROR);
          throw new CoreException("404", "No recipient found");
        }
        if (empty($oContact)) {
          throw new CoreException("404", "Unauthorized contact");
        }
      } catch (Exception $ex) {
        throw new CoreException("404", "Unable to locate appropriate dialplan", $ex);
      }

      // determine call direction
      if ($oDialplan->context == 'internal') {
        $direction = Transmission::OUTBOUND;
      } else {
        $direction = Transmission::INBOUND;
      }

      // for time being create transmission by using company contact
      $oTransmission = $oProgram->transmission_create(Contact::COMPANY, $oAccount->account_id, $direction);
      $oTransmission->activate_owner(); // Load permission
      if (isset($oContact)) {
        if (empty($oContact->contact_id)) {
          // Finally update contact_id and status for newly created transmission
          // Note: we can't create and update contact before activating transmission owner
          $oContact->save();
          $oTransmission->result_create($oContact->contact_id, 'contact_new', Result::TYPE_CONTACT, 'inbound');
        }
        $oTransmission->contact_id = $oContact->contact_id;
      }
      $oTransmission->status = Transmission::STATUS_INITIALIZING;
      $oTransmission->save(); // we must save transmission to generate transmission_id for new spool
    }

    // At this point we are excepting to have a valid program and transmission
    // So we are ready to start with response, but first prepare spool objects
    $oTransmission->spool_create($oRequest->spool_id);
    $oResponse = new Response();
    $oResponse->spool_id = $oTransmission->oSpool->spool_id;

    // register program, transmission, request and response objects in session so they can be used by program if necessary
    $oSession = Session::get_instance();
    $oSession->program = $oProgram;
    $oSession->transmission = $oTransmission;
    $oSession->request = $oRequest;
    $oSession->response = $oResponse;

    // process available data using selected program to produce results
    $oSession->program->_process($oSession->transmission);

    // update all status just before dying
    self::wrapup($oSession->transmission);
    Corelog::log('Request processing completed', Corelog::FLOW);

    // return our response
    return $oSession->response;
  }

  /**
   * Locate existing account for given phone or email address
   * @param string $account phone number or email address
   * @param string $accountField i.e 'phone' or 'email'
   * @return boolean|Account
   */
  public static function locate_account($account, $accountField = 'phone')
  {
    // locate an existing account
    $accountFilter = array($accountField => $account);
    $listAccount = Account::search($accountFilter);
    if ($listAccount) {
      $aAccount = array_shift($listAccount);
      return Account::load($aAccount['account_id']);
    }
    return false; // no account found
  }

  /**
   * Locate existing contact or create new for given phone or email address
   * @param string $contact phone number or email address
   * @param string $contactField i.e phone or email
   * @return Contact
   */
  public static function locate_contact($contact, $contactField = 'phone')
  {
    // locate an existing contact or create it
    $contactFilter = array($contactField => $contact);
    $listContact = Contact::search($contactFilter);
    if ($listContact) {
      $aContact = array_shift($listContact);
      $oContact = new Contact($aContact['contact_id']);
    } else { // create a new contact
      $oContact = new Contact();
      $oContact->$contactField = $contact;
    }
    return $oContact;
  }

  /**
   * Closing
   * save all status in transmission and spool etc ..
   * @param Transmission $oTransmission
   */
  private static function wrapup(Transmission &$oTransmission)
  {
    foreach ($oTransmission->aResult as $oResult) {
      $oResult->save();
    }
    Corelog::log('Final spool status : ' . $oTransmission->oSpool->status, Corelog::FLOW);
    $oTransmission->oSpool->save();
    Corelog::log('Final transmission status : ' . $oTransmission->status, Corelog::FLOW);
    $oTransmission->last_run = time();
    $oTransmission->save();
    Corelog::log('-----------------> transaction ended <-----------------', Corelog::FLOW);
  }

}

<?php

namespace ICT\Core\Api;

/* * ***************************************************************
 * Copyright © 2017 ICT Innovations Pakistan All Rights Reserved   *
 * Developed By: Nasir Iqbal                                       *
 * Website : http://www.ictinnovations.com/                        *
 * Mail : nasir@ictinnovations.com                                 *
 * *************************************************************** */

use ICT\Core\Api;
use ICT\Core\Conf;
use ICT\Core\Core;
use ICT\Core\Gateway\Freeswitch;
use ICT\Core\Request;

class CoreApi extends Api
{

  /**
   * Provide System statistics
   *
   * @url GET /statistics
   */
  public function statistics($query = array())
  {
    $this->_authorize('statistic_read');
    $filter = (array)$query;
    return Core::statistic($filter);
  }

  /**
   * post results / response from gateway activity
   *
   * @url POST /responses
   */
  public function process($gateway_flag, $spool_id, $application_id, $data = array())
  {
    $this->_authorize('transmission_create');
    $this->_authorize('transmission_update');

    // now process the main request
    $oResponse = $this->process_response($spool_id, $application_id, $data, $gateway_flag);
    // and publish output
    if (!empty($oResponse->application_data)) {
      echo $oResponse->application_data;
    }

    // after all process data from additional app if there is any, we need to proecess it after main application
    // so it can use main application result to calculate next action while processing program
    // normally it will be used with last application to collect results of originate like applications
    if (isset($data['extra']) && is_array($data['extra'])) {
      foreach ($data['extra'] as $aApp) {
        // no need to collect any type of output
        $this->process_response($aApp['spool_id'], $aApp['application_id'], $aApp['application_data'], $aApp['gateway_flag']);
      }
    }
    exit();
  }

  function process_response($spool_id, $application_id, $application_data = array(), $gateway_flag = Freeswitch::GATEWAY_FLAG)
  {
    $oRequest = new Request();
    $oRequest->spool_id = $spool_id;
    $oRequest->application_id = $application_id;
    $oRequest->application_data = $application_data;
    $oRequest->gateway_flag = $gateway_flag;

    if (!empty($application_data['context'])) {
      $oRequest->context = $application_data['context'];
    }
    if (!empty($application_data['source'])) {
      if ($gateway_flag == Freeswitch::GATEWAY_FLAG) {
        $oRequest->source = preg_replace("/[^0-9]/", "", $application_data['source']);
      } else {
        $oRequest->source = $application_data['source'];
      }
    }
    if (!empty($application_data['destination'])) {
      if ($gateway_flag == Freeswitch::GATEWAY_FLAG) {
        $oRequest->destination = preg_replace("/[^0-9]/", "", $application_data['destination']);
      } else {
        $oRequest->destination = $application_data['destination'];
      }
    }

    return Core::process($oRequest);
  }

}

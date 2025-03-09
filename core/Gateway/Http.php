<?php

namespace ICT\Core\Gateway;

/* * ***************************************************************
 * Copyright © 2014 ICT Innovations Pakistan All Rights Reserved   *
 * Developed By: Nasir Iqbal                                       *
 * Website : http://www.ictinnovations.com/                        *
 * Mail : nasir@ictinnovations.com                                 *
 * *************************************************************** */

use ICT\Core\Corelog;
use ICT\Core\Gateway;
use ICT\Core\Provider;
use ICT\Core\Request;

class Http extends Gateway
{
  /** @const */
  const GATEWAY_FLAG = 16;
  const GATEWAY_TYPE = 'Http';
  const CONTACT_FIELD = 'phone';
  const CONTACT_ANONYMOUS = '0000000';


  function send($command, Provider $oProvider = NULL) {
    
    $data = json_decode($command, TRUE);
    $sms_from = $data['from'];
    $sms_to   = $data['to'];
    $sms_msg = $data['data'];

    $http_url = $oProvider->host;
    
    $ok       = false;
    $response = '';
    $length   = strlen($sms_msg);
    $pCount   = ceil($length / 160);
    

    // Check if the URL contains "from="
    if (strpos($http_url, 'from=') !== false) {
        $aURL = explode("from=", $http_url);
        $URL  = $aURL[0] . "from=" . urlencode($sms_from);
    } else {
        $aURL = explode("to=", $http_url);
        $URL  = $aURL[0] . "to=" . urlencode($sms_to);
    }

    $arr = explode("&",$aURL[1]);

    foreach ($arr as $key => $value) {
      $arr = explode("=",$value);
      if ($arr[1] != '') {
        switch($arr[0]) {
          case 'to':
          case 'dest':
            $URL .= "&" . $arr[0] . "=" . urlencode($sms_to);
          break;
          case 'text':
          case 'msg':
            $URL .=  "&" . $arr[0] . "=" . rawurlencode($sms_msg);
          break;
          case 'content':  
            $URL .=  "&" . $arr[0] . "=" . urlencode($sms_msg);
            break;
          case 'coding':
            $URL .=  "&" . $arr[0] . "=" . urlencode($arr[1]);
          break;
          case 'dlr-mask':
            $URL .=  "&" . $arr[0] . "=" . urlencode($arr[1]);
          break;
        }
      }
    }

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $URL);
    curl_setopt($ch, CURLOPT_HTTPGET, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $response = curl_exec($ch);

    $oRequest = new Request();

    if (self::isValidJson($response)) {
        $responseData = json_decode($response, true);
        if (isset($responseData['messages'][0]['accepted']) && $responseData['messages'][0]['accepted'] === true) {
            $ok = $pCount; // return sms count
            $oRequest->gateway_flag = Http::GATEWAY_FLAG;
            $oRequest->spool_id = $command['spool_id'];        
            $oRequest->application_id = $command['application_id'];
            $oRequest->application_data = array(
                'amount' => $pCount,
                'status' => 'completed',
                'response' => 'no-reponse',
                'result' => 'success',
                'error' => ''
            );
      }
    } else if (str_contains($response , 'Sent.') || str_contains($response , '0: Accepted for delivery') || str_contains($response , '3: Queued for later delivery')) {
      $oRequest->gateway_flag = Http::GATEWAY_FLAG;
      $oRequest->spool_id = $command['spool_id'];
      $oRequest->application_id = $command['application_id'];
      $oRequest->application_data = array(
          'amount' => $pCount,
          'status' => 'completed',
          'response' => 'no-reponse',
          'result' => 'success',
          'error' => ''
      );
    } else {
        $oRequest->gateway_flag = Http::GATEWAY_FLAG;
        $oRequest->spool_id = $command['spool_id'];
        $oRequest->application_id = $command['application_id'];
        $oRequest->application_data = array(
            'amount' => $pCount,
            'status' => 'failed',
            'response' => 'no-reponse',
            'result' => 'success',
            'error' => ''
        );
    }
    curl_close($ch);
    $oRequest->task_create();

    return $ok;
}

protected function isValidJson($string) {
    json_decode($string);
    return (json_last_error() == JSON_ERROR_NONE);
  }

}

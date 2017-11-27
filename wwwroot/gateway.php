<?php
/* * ***************************************************************
 * Copyright © 2014 ICT Innovations Pakistan All Rights Reserved   *
 * Developed By: Nasir Iqbal                                       *
 * Website : http://www.ictinnovations.com/                        *
 * Mail : nasir@ictinnovations.com                                 *
 * *************************************************************** */

use ICT\Core\Conf;
use ICT\Core\Core;
use ICT\Core\CoreException;
use ICT\Core\Gateway\Freeswitch;
use ICT\Core\Http;
use ICT\Core\Request;

// default include is /usr/ictcore/core
chdir(dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'core');

include_once "Core.php";

try {
  if (http_authenticate() != true) {
    throw new CoreException('401', 'Unknown authentication error');
  }
} catch (CoreException $e) {
  // send error
  echo "error code : " . $e->getCode() . "\n";
  echo "<br>\n";
  echo "description : " . $e->getMessage() . "\n";

  exit(); // terminate program execution
}

Http::get_instance(); // to prepare input cache
$spool_id = Http::request_get('spool_id', 0);
$gateway_flag = Http::request_get('gateway_flag', Freeswitch::GATEWAY_FLAG);
$application_id = Http::request_get('application_id', null);
$application_data = Http::request_get('application_data', array());
if (\ICT\Core\json_check($application_data)) {
  $application_data = json_decode($application_data, TRUE); // we need associated array
}

// now process the main request
$oResponse = process_response($spool_id, $application_id, $application_data, $gateway_flag);
// and publish output
echo $oResponse->application_data;

// after all process data from additional app if there is any, we need to proecess it after main application
// so it can use main application result to calculate next action while processing program
// normally it will be used with last application to collect results of originate like applications
if (isset($application_data['extra']) && is_array($application_data['extra'])) {
  foreach ($application_data['extra'] as $app_type => $aApp) {
    // no need to collect any type of output
    process_response($aApp['spool_id'], $aApp['application_id'], $aApp['application_data'], $aApp['gateway_flag']);
  }
}

exit();

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

function http_authenticate()
{
  $realm = Conf::get('company:name', 'ICTCore') . ' :: Gateway Hub';

  // select authentication method
  if (Http::request_get('username', null)) {
    $username = Http::request_get('username', null);
    $password = Http::request_get('password', null);
  } else {
    header("WWW-Authenticate: Basic realm=\"$realm\"");
    throw new CoreException('401', "You are not authorized to access this resource");
  }

  // authenticate using username and password method
  $user_ok = false;
  $pass_ok = false;
  if (Conf::get('gatewayhub:username', 'myuser') == $username) {
    $user_ok = true;
  }
  if (Conf::get('gatewayhub:password', 'plsChangeMe') == $password) {
    $pass_ok = true;
  }

  if ($user_ok && $pass_ok) {
    return TRUE;
  } else {
    header("WWW-Authenticate: Basic realm=\"$realm\"");
    throw new CoreException('401', "Invalid username or password");
  }
}
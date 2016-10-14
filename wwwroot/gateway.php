<?php
// default include is /usr/ictcore/core
chdir(dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'core');

include_once "core.php";
include_once "lib/http.php";

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

$spool_id = http_input_get('spool_id', 0);
$gateway_flag = http_input_get('gateway_flag', Freeswitch::GATEWAY_FLAG);
$application_id = http_input_get('application_id', null);
$application_data = http_input_get('application_data', array());
if (json_check($application_data)) {
  $application_data = json_decode($application_data, TRUE); // we need associated array
}

// now process the main request
$oResponse = process_response($spool_id, $application_id, $application_data, $gateway_flag);
// and publish output
echo json_encode($oResponse->application_data);

// after all process data from additional app if there is any, we need to proecess it after main application
// so it can use main application result to calculate next action while processing program
// normally it will be used with last application to collect results of originate like applications
if (isset($application_data['extra']) && is_array($application_data['extra'])) {
  foreach ($application_data['extra'] as $app_type => $aApp) {
    // no need to collect any type of output
    Core::process($aApp['spool_id'], $aApp['application_id'], $aApp['application_data'], $aApp['gateway_flag']);
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

  return Core::process($oRequest);
}

function http_authenticate()
{
  $realm = conf_get('company:name', 'ICTCore') . ' :: Gateway Hub';

  // select authentication method
  if (http_input_get('username', null)) {
    $username = http_input_get('username', null);
    $password = http_input_get('password', null);
  } else {
    header("WWW-Authenticate: Basic realm=\"$realm\"");
    throw new CoreException('401', "You are not authorized to access this resource");
  }

  // authenticate using username and password method
  $user_ok = false;
  $pass_ok = false;
  if (conf_get('gatewayhub:username', 'myuser') == $username) {
    $user_ok = true;
  }
  if (conf_get('gatewayhub:password', 'plsChangeMe') == $password) {
    $pass_ok = true;
  }

  if ($user_ok && $pass_ok) {
    return TRUE;
  } else {
    header("WWW-Authenticate: Basic realm=\"$realm\"");
    throw new CoreException('401', "Invalid username or password");
  }
}
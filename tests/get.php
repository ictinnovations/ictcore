<?php

require(__DIR__ . '/../vendor/nategood/httpful/bootstrap.php');

// STEP 1: configure API function to connect and authenticate with ICTBroadcast server
function ictcore_api($method) {
  $api_url  = 'http://core.voip.vision';
  $username = 'admin';
  $password = 'helloAdmin';
  $service_url = "$api_url/$method";
  echo $service_url."\n";

  $response = \Httpful\Request::get($service_url) // Build a PUT request...
      ->expectsJson()                             // we are receiving in json
      ->authenticateWith($username, $password)    // authenticate with basic auth...
      ->send(); 

  return $response->body;
}

$request = end($argv);
$result  = ictcore_api($request);
echo print_r($result, true)."\n";
exit(0);

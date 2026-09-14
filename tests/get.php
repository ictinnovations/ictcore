<?php

// Uses ext-curl directly. This used to pull in nategood/httpful, which has no
// PHP 8 release and was only ever needed by these two example scripts.

// STEP 1: configure API function to connect and authenticate with ICTCore server
function ictcore_api($method) {
  $api_url  = 'http://core.voip.vision';
  $username = 'admin';
  $password = 'helloAdmin';
  $service_url = "$api_url/$method";
  echo $service_url."\n";

  $ch = curl_init($service_url);
  curl_setopt_array($ch, array(
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER     => array('Accept: application/json'),
    CURLOPT_HTTPAUTH       => CURLAUTH_BASIC,
    CURLOPT_USERPWD        => "$username:$password"
  ));
  $body  = curl_exec($ch);
  $error = curl_error($ch);
  curl_close($ch);

  if ($body === false) {
    fwrite(STDERR, "request failed: $error\n");
    exit(1);
  }

  return json_decode($body);
}

$request = end($argv);
$result  = ictcore_api($request);
echo print_r($result, true)."\n";
exit(0);

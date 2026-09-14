<?php

// Uses ext-curl directly. This used to pull in nategood/httpful, which has no
// PHP 8 release and was only ever needed by these two example scripts.

// STEP 1: configure API function to connect and authenticate with ICTCore server
function ictcore_api($method, $arguments = array(), $files = array()) {
  $api_url  = 'http://core.voip.vision';
  $username = 'admin';
  $password = 'helloAdmin';
  $service_url = "$api_url/$method";
  echo $service_url."\n";

  $headers = array('Accept: application/json');

  if (empty($files)) {
    $payload   = json_encode($arguments);
    $headers[] = 'Content-Type: application/json';
  } else {
    // json is not supported with file upload, so send multipart instead and let
    // curl set the Content-Type with its own boundary.
    $payload = $arguments;
    foreach ($files as $field => $path) {
      $payload[$field] = new CURLFile($path);
    }
  }

  $ch = curl_init($service_url);
  curl_setopt_array($ch, array(
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => $payload,
    CURLOPT_HTTPHEADER     => $headers,
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

$arguments = array(
  'name'        => 'myTiff',
  'description' => 'nothing special'
);

$files = array(
  'file_name' => '/home/data/Desktop/ict-innovations/all_test-data/fax.pdf'
);

$result  = ictcore_api('documents', $arguments, $files);
echo print_r($result, true)."\n";
exit(0);

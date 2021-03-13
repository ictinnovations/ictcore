<?php

require(__DIR__ . '/../vendor/nategood/httpful/bootstrap.php');

// STEP 1: configure API function to connect and authenticate with ICTBroadcast server
function ictcore_api($method, $arguments = array(), $files = array()) {
  $api_url  = 'http://core.voip.vision';
  $username = 'admin';
  $password = 'helloAdmin';
  $service_url = "$api_url/$method";
  echo $service_url."\n";

  $request = \Httpful\Request::post($service_url); // Build a PUT request...
  $request->expectsJson();                             // and also receiving in json
  $request->sendsJson();
  $request->authenticateWith($username, $password);    // authenticate with basic auth...

  // json is not supported with file upload
  if (empty($files)) {
    $request->body(json_encode($arguments), Httpful\Mime::JSON);
  } else {
    $request->sendsType(Httpful\Mime::UPLOAD);
    $request->alwaysSerializePayload();
    $request->body($arguments); // no JSON
    $request->attach($files);
  }

  $response = $request->send();            // attach a body/payload...


  return $response->body;
}

$arguments = array(
  'name'        => 'myTiff',
  'description' => 'nothing special',
  'file_name'   => '@/home/data/Desktop/ict-innovations/all_test-data/fax.pdf'
);

$files = array(
  'file_name' => '/home/data/Desktop/ict-innovations/all_test-data/fax.pdf'
);

$result  = ictcore_api('documents', $arguments, $files);
echo print_r($result, true)."\n";
exit(0);

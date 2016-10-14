<?php
/* * ***************************************************************
 * Copyright © 2015 ICT Innovations Pakistan All Rights Reserved   *
 * Developed By: Nasir Iqbal                                       *
 * Website : http://www.ictinnovations.com/                        *
 * Mail : nasir@ictinnovations.com                                 *
 * *************************************************************** */

// initializing global variables
$http_input = array_merge($_GET, $_FILES, $_POST);
$http_output = array();

function http_input_get($name, $default = null)
{
  global $http_input;
  if (isset($http_input[$name])) {
    return $http_input[$name];
  }
  // check for : colon separated name
  return _get($http_input, $name, $default);
}

function http_input_set($name, $value)
{
  global $http_input;
  if (strpos($name, ':') === false) {
    $http_input[$name] = $value;
  } else {
    _set($http_input, $name, $value);
  }
}

function http_output_get($name, $default = null)
{
  global $http_output;
  if (isset($http_output[$name])) {
    return $http_output[$name];
  }
  // check for : colon separated name
  return _get($http_output, $name, $default);
}

function http_output_set($name, $value)
{
  global $http_output;
  if (strpos($name, ':') === false) {
    $http_output[$name] = $value;
  } else {
    _set($http_output, $name, $value);
  }
}

<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * @author nasir
 */
// TODO: check include path
//ini_set('include_path', ini_get('include_path'));

/** disabled
try {
  include '../core/Core.php';
} catch (Exception $ex) {
  // ignore all errors during loading
}
 * 
 */

$loader = require dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
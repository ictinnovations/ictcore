<?php
/* * ***************************************************************
 * Copyright © 2014 ICT Innovations Pakistan All Rights Reserved   *
 * Developed By: Nasir Iqbal                                       *
 * Website : http://www.ictinnovations.com/                        *
 * Mail : nasir@ictinnovations.com                                 *
 * *************************************************************** */

use ICT\Core\Core;
use ICT\Core\Gateway\Sendmail;
use ICT\Core\Request;

// remove sendmail and bin parent by .. / ..
chdir(dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'core');
include_once "Core.php";

$source_pdf = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'fax.pdf';
$test_pdf = '/tmp/fax_test.pdf';
copy($source_pdf, $test_pdf);

process_email('nasir@ictinnovations.com', '12132942943@localhost', 'test fax', 'empty', 'empty', $test_pdf);

function process_email($from, $to, $subject, $body, $body_alt = '', $attachment = '')
{
  $oRequest = new Request();
  $oRequest->gateway_flag = Sendmail::GATEWAY_FLAG;
  $oRequest->destination = $to;
  $oRequest->source = $from;
  $oRequest->context = 'internal'; // TODO replace context with domain

  $oRequest->application_id = 'email_receive';
  $oRequest->application_data = array(
      'subject' => $subject,
      'body' => $body,
      'body_alt' => $body_alt,
      'attachment' => $attachment,
      'status' => 'completed',
      'response' => '',
      'result' => 'success'
  );

  Core::process($oRequest);
}

<?php
/* * ***************************************************************
 * Copyright © 2014 ICT Innovations Pakistan All Rights Reserved   *
 * Developed By: Nasir Iqbal                                       *
 * Website : http://www.ictinnovations.com/                        *
 * Mail : nasir@ictinnovations.com                                 *
 * *************************************************************** */

use ICT\Core\Conf;
use ICT\Core\Core;
use ICT\Core\Corelog;
use ICT\Core\Gateway\Sendmail;
use ICT\Core\Request;



  "to": "nasir@ictinnovations.com",
  "from": "12132942943@localhost",
  "subject": "Fax delivery failed",
  "body": "<p>
Dear  , 
</p>

<p>
We have tried to send your fax to 12132942943 but unfortunately it failed.
</p>
Error: 
<p>
Please try again later.
</p>

<p>
Thanks<br/>
-----------------------<br />
The   Team
</p>",
  "body_alt": "Dear  , 

We have tried to send your fax to 12132942943 but unfortunately it failed.

Error: 

Please try again later.

Thanks
-----------------------
The   Team",
  "attachment": "",
  "spool_id": "727",
  "application_id": "326"



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

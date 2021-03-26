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

// remove sendmail and bin parent by .. / ..
chdir(dirname(dirname(dirname(__FILE__))) . DIRECTORY_SEPARATOR . 'core');
include_once "Core.php";

$host = Conf::get('sample_provider:user', 'localhost');
$port = Conf::get('sample_provider:port', '933');
$username = Conf::get('sample_provider:user', 'freeswitch');
$password = Conf::get('sample_provider:pass', '');
$imap_type = Conf::get('sample_provider:type', 'local');
$box_inbox = Conf::get('sample_provider:folder', 'INBOX');
$box_dump = Conf::get('sample_provider:folder_processed', 'processed');

$msg_status = "UNSEEN"; // new emails

if ($imap_type == 'local') {
  $conn = imap_open(Conf::get('sendmail:folder', '/var/spool/mail/ictcore'), "", "", NULL, 1)
          or die('Unable to read Mails: ' . imap_last_error());
} else if ($imap_type == 'imap') {
  $conn = imap_open("{" . "$host" . ":" . "$port" . "/imap}" . $box_inbox, $username, $password, 0, 0, NULL)
          or die('Unable to read Mails: ' . imap_last_error());
} else if ($imap_type == 'pop3') {
  $conn = imap_open("{" . "$host" . ":" . "$port" . "/pop3}" . $box_inbox, $username, $password, 0, 0, NULL)
          or die('Unable to read Mails: ' . imap_last_error());
}

$emails = imap_search($conn, $msg_status);

if (!empty($emails)) {
  foreach ($emails as $email_number) {
    // skip 0 index ( which indicate empty mail box)
    if (empty($email_number) || $email_number == 0) {
      continue;
    }

    //grab the overview and message
    $header = imap_headerinfo($conn, $email_number);

    //Because attachments can be problematic this logic will default to skipping the attachments    
    $message = imap_fetchbody($conn, $email_number, 1.2);
    $message_alt = imap_fetchbody($conn, $email_number, 1.1);
    $structure = imap_fetchstructure($conn, $email_number);
    $attachments = array();

    /* if any attachments found... */
    if (isset($structure->parts) && count($structure->parts)) {
      for ($i = 0; $i < count($structure->parts); $i++) {
        $attachments[$i] = array(
            'is_attachment' => false,
            'filename' => '',
            'name' => '',
            'attachment' => ''
        );

        if ($structure->parts[$i]->ifdparameters) {
          foreach ($structure->parts[$i]->dparameters as $object) {
            if (strtolower($object->attribute) == 'filename') {
              $attachments[$i]['is_attachment'] = true;
              $attachments[$i]['filename'] = $object->value;
            }
          }
        }

        if ($structure->parts[$i]->ifparameters) {
          foreach ($structure->parts[$i]->parameters as $object) {
            if (strtolower($object->attribute) == 'name') {
              $attachments[$i]['is_attachment'] = true;
              $attachments[$i]['name'] = $object->value;
            }
          }
        }

        if ($attachments[$i]['is_attachment']) {
          $attachments[$i]['attachment'] = imap_fetchbody($conn, $email_number, $i + 1);
          /* 4 = QUOTED-PRINTABLE encoding */
          if ($structure->parts[$i]->encoding == 3) {
            $attachments[$i]['attachment'] = base64_decode($attachments[$i]['attachment']);
            /* 3 = BASE64 encoding */
          } elseif ($structure->parts[$i]->encoding == 4) {
            $attachments[$i]['attachment'] = quoted_printable_decode($attachments[$i]['attachment']);
          }
        }
      } // foreach part
    } // if attachment

    $aAttachment = array();
    $filename = NULL;

    /* iterate through each attachment and save it */
    foreach ($attachments as $attachment) {
      if ($attachment['is_attachment'] == 1) {
        $filename = $attachment['name'];
        if (empty($filename)) {
          $filename = $attachment['filename'];
        }
        if (empty($filename)) {
          $filename = time() . ".dat";
        }
        /* prefix the email number to the filename in case two emails
         * have the attachment with the same file name.
         */
        global $path_cache;
        $filename = tempnam($path_cache, 'ictcore_') . $filename;
        $fp = fopen($filename, "w");
        fwrite($fp, $attachment['attachment']);
        fclose($fp);
        $aAttachment[] = $filename;
      }
    }

    //split the header array into variables
    $subject = $header->subject;
    $body = $message;
    $body_alt = $message_alt;
    $from = isset($header->from) ? $header->from[0] : $header->sender[0];
    $date = $header->date;
    $to = isset($header->to) ? $header->to[0] : $header->reply_to[0];
    $attachment = \ICT\Core\path_array_to_string($aAttachment);

    $status = imap_setflag_full($conn, $email_number, "\\Seen");
    try {
      imap_mail_move($conn, $email_number, "$box_inbox.$box_dump");
    } catch (Exception $ex) {
      Corelog::log($ex->getMessage(), Corelog::WARNING);
      Corelog::log('Unable to mark current mail as read', Corelog::WARNING);
    }
    process_email($from, $to, $subject, $body, $body_alt, $attachment);
  } // foreach end
}  // if statment end of new emails

imap_close($conn, CL_EXPUNGE);

function process_email($from, $to, $subject, $body, $body_alt = '', $attachment = '')
{
  $oRequest = new Request();
  $oRequest->gateway_flag = Sendmail::GATEWAY_FLAG;
  $oRequest->destination = $to->mailbox . "@" . $to->host;
  $oRequest->source = $from->mailbox . "@" . $from->host;
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

  try {
    Core::process($oRequest);
  } catch (Exception $ex) {
    Corelog::log("Skipping current email: ".$ex->getMessage(), Corelog::WARNING);
  }
}

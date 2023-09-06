<?php

namespace ICT\Core\Gateway;

/* * ***************************************************************
 * Copyright © 2014 ICT Innovations Pakistan All Rights Reserved   *
 * Developed By: Nasir Iqbal                                       *
 * Website : http://www.ictinnovations.com/                        *
 * Mail : nasir@ictinnovations.com                                 *
 * *************************************************************** */

use Exception;
use ICT\Core\Conf;
use ICT\Core\CoreException;
use ICT\Core\Corelog;
use ICT\Core\Gateway;
use ICT\Core\Provider;
use ICT\Core\Provider\Smtp;
use ICT\Core\Provider\Emailcmd;
use ICT\Core\Request;
use Swift_Attachment;
use Swift_Mailer;
use Swift_Message;
use Swift_SendmailTransport;
use Swift_SmtpTransport;

global $path_root;
require_once $path_root . '/vendor/swiftmailer/swiftmailer/lib/swift_required.php';

class Sendmail extends Gateway
{

  /** @const */
  const GATEWAY_FLAG = 4;
  const GATEWAY_TYPE = 'sendmail';
  const CONTACT_FIELD = 'email';
  const CONTACT_ANONYMOUS = 'unknown@localhost';

  /** @var boolean $conn */
  protected $conn = false;

  /** $var Provider $oProvider */
  protected $oProvider;

  public function __construct()
  {
    // no gateway, we have to connect directly with provider, see default_route
  }

  public static function default_route()
  {
    $type = Conf::get('sendmail:type', 'sendmail');
    switch ($type) {
      case 'smtp':
        $oProvider = new Smtp();
        $oProvider->host = Conf::get('sendmail:host', '127.0.0.1');
        $oProvider->port = Conf::get('sendmail:port', '25');
        $oProvider->encryption = Conf::get('sendmail:encryption', null);
        $oProvider->username = Conf::get('sendmail:user', '');
        $oProvider->password = Conf::get('sendmail:pass', '');
        return $oProvider;
      case 'sendmail':
        $oProvider = new Emailcmd();
        $oProvider->cli = Conf::get('sendmail:cli', '/usr/sbin/sendmail');
        return $oProvider;
    }
    return null;
  }

  protected function validate_email($email)
  {
    return preg_replace('/(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})/', '[\1]', $email);
  }

  protected function connect()
  {
    switch ($this->oProvider->type) {
      case 'smtp':
        try {
          $this->conn = Swift_SmtpTransport::newInstance($this->oProvider->host, $this->oProvider->port);
          if (!empty($this->oProvider->encryption)) {
            $this->conn->setEncryption($this->oProvider->encryption);
          }
          $this->conn->setUsername($this->oProvider->username);
          $this->conn->setPassword($this->oProvider->password);
        } catch (Exception $conn_error) {
          throw new CoreException("500", "smtp connection error", $conn_error);
        }
        break;
      case 'sendmail':
      default:
        try {
          $this->conn = Swift_SendmailTransport::newInstance($this->oProvider->cli);
        } catch (Exception $conn_error) {
          throw new CoreException("500", "sendmail connection error", $conn_error);
        }
        break;
    }
    Corelog::log("Sendmail connected successfully", Corelog::CRUD);
  }

  protected function dissconnect()
  {
    Corelog::log("Sendmail disconnect requested", Corelog::CRUD);
    return $this->conn->stop();
  }

  public function get()
  {
    if ($this->connect()) {
      // process
      $this->dissconnect();
      return TRUE;
    } else {
      return FALSE;
    }
  }

  public function send($command, Provider $oProvider = NULL)
  {
    // Convert json into data array
    $data = json_decode($command, TRUE);

    $mailMsg = Swift_Message::newInstance();

    // TODO, make it functional $headers = $mailMsg->getHeaders();
    // $headers->addIdHeader('spool_id', $data['spool_id']);

    try {
      $mailMsg->setTo($this->validate_email($data['to']));
      $mailMsg->setFrom($this->validate_email($data['from']));
      $mailMsg->setSubject($data['subject']);
      $mailMsg->setBody($data['body'], 'text/html');
      if (!empty($data['body_alt'])) {
        $mailMsg->addPart($data['body_alt'], 'text/plain');
      }
      // Optionally add attachments
      if (!empty($data['attachment'])) {
        $aAttachment = \ICT\Core\path_string_to_array($data['attachment']);
        foreach($aAttachment as $attachment) {
          if (is_file($attachment)) {
            $oAttachment = Swift_Attachment::fromPath($attachment);
            // $oAttachment->setFilename($data['file_title']);
            $mailMsg->attach($oAttachment);
          }
        }
      }
    } catch (Exception $msg_error) {
      throw new CoreException("500", "error while preparing email message", $msg_error);
    }

    // Connect and deliver email message
    $this->oProvider = $oProvider; // assignment required before connect, so it can consume it
    $this->connect();
    if ($this->conn) {
      try {
        $oMailer = Swift_Mailer::newInstance($this->conn);
        $oMailer->send($mailMsg);
      } catch (Exception $send_error) {
        throw new CoreException("500", "error while sending email", $send_error);
      }
    } else {
      echo "no handle";
    }
    $this->dissconnect();

    /*     * ******************************************************************
     * update that application has been completed
     * ********************************************************************* */
    $oRequest = new Request();
    $oRequest->gateway_flag = Sendmail::GATEWAY_FLAG;
    $oRequest->spool_id = $data['spool_id'];

    $oRequest->application_id = $data['application_id'];
    $oRequest->application_data = array(
        'amount' => 1,
        'amount_net' => 1,
        'status' => 'completed',
        'response' => '',
        'result' => 'success',
        'error' => ''
    );

    /* NOTE: 
     * under apache multithreading does not work
     * so we have to use scheduling
     */
    $oRequest->task_create();
    /*     * **************************************************** UPDATE END */
  }

  public static function template_dir()
  {
    $core_dir = parent::template_dir();
    return $core_dir . '/Gateway/Sendmail/templates';
  }

}

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
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport\SendmailTransport;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;
use Symfony\Component\Mime\Email;
use ICT\Core\Account\EAddress;

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
          // Swift took the encryption name as a string after construction.
          // Symfony decides it up front: true means implicit TLS on connect
          // (smtps, usually 465), null means plain connect then STARTTLS if the
          // server advertises it, which is what 'tls' meant to Swift.
          $tls = null;
          if (strtolower((string) $this->oProvider->encryption) === 'ssl') {
            $tls = true;
          }
          $this->conn = new EsmtpTransport($this->oProvider->host, (int) $this->oProvider->port, $tls);
          $this->conn->setUsername($this->oProvider->username);
          $this->conn->setPassword($this->oProvider->password);
        } catch (Exception $conn_error) {
          throw new CoreException("500", "smtp connection error", $conn_error);
        }
        break;
      case 'sendmail':
      default:
        try {
          $this->conn = new SendmailTransport($this->oProvider->cli);
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
    // Only the SMTP transport holds a socket open. SendmailTransport pipes to a
    // process per message and has no stop().
    if ($this->conn && method_exists($this->conn, 'stop')) {
      return $this->conn->stop();
    }
    return TRUE;
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

    $mailMsg = new Email();

    // TODO, make it functional $headers = $mailMsg->getHeaders();
    // $headers->addIdHeader('spool_id', $data['spool_id']);

    try {
      $mailMsg->to($this->validate_email($data['to']));
      $mailMsg->from($this->validate_email($data['from']));
      $mailMsg->subject($data['subject']);
      $mailMsg->html($data['body']);
      if (!empty($data['body_alt'])) {
        $mailMsg->text($data['body_alt']);
      }
      // Optionally add attachments
      if (!empty($data['attachment'])) {
        $aAttachment = \ICT\Core\path_string_to_array($data['attachment']);
        foreach($aAttachment as $attachment) {
          if (is_file($attachment)) {
            $mailMsg->attachFromPath($attachment);
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
        $oMailer = new Mailer($this->conn);
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

  public static function locate_account($account)
  {
    // in both external or internal email address will be same
    return EAddress::locate($account, static::CONTACT_FIELD);
  }
}

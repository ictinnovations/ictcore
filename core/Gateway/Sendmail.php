<?php
/* * ***************************************************************
 * Copyright © 2014 ICT Innovations Pakistan All Rights Reserved   *
 * Developed By: Nasir Iqbal                                       *
 * Website : http://www.ictinnovations.com/                        *
 * Mail : nasir@ictinnovations.com                                 *
 * *************************************************************** */
global $path_root;
require_once $path_root . '/vendor/swiftmailer/swiftmailer/lib/swift_required.php';

class Sendmail extends Gateway
{

  /** @const */
  const CONTACT_FIELD = 'email';
  const GATEWAY_FLAG = 4;

  /** @var boolean $conn */
  protected $conn = false;

  /** @var string $username */
  protected $username;

  /** @var string $password */
  protected $password;

  /** @var string $port */
  protected $port;

  /** @var string $host */
  protected $host;

  /** @var string $type */
  protected $type;

  /** @var string $cli */
  protected $cli;

  public function __construct()
  {
    $this->host = conf_get('sendmail:host', '127.0.0.1');
    $this->port = conf_get('sendmail:port', '25');
    $this->username = conf_get('sendmail:user', '');
    $this->password = conf_get('sendmail:pass', '');
    $this->type = conf_get('sendmail:type', 'sendmail');
    $this->cli = conf_get('sendmail:cli', '/usr/sbin/sendmail');
  }

  public static function capabilities()
  {
    return Email::SERVICE_FLAG;
  }

  public function is_supported($service)
  {
    if (($this->capabilities() & $service) == $service) {
      return TRUE;
    } else {
      return FALSE;
    }
  }

  protected function validate_email($email)
  {
    return preg_replace('/(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})/', '[\1]', $email);
  }

  protected function connect()
  {
    switch ($this->type) {
      case 'smtp':
        try {
          $this->conn = Swift_SmtpTransport::newInstance($this->host, $this->port);
          $this->conn->setUsername($this->username);
          $this->conn->setPassword($this->password);
        } catch (Exception $conn_error) {
          throw new CoreException("500", "smtp connection error", $conn_error);
        }
        break;
      case 'sendmail':
      default:
        try {
          $this->conn = Swift_SendmailTransport::newInstance($this->cli);
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

  public function send($command)
  {
    Corelog::log("Sendmail sending commands", Corelog::CRUD, $command);

    $mailMsg = Swift_Message::newInstance();

    // TODO, make it functional $headers = $mailMsg->getHeaders();
    // $headers->addIdHeader('spool_id', $command['spool_id']);

    try {
      $mailMsg->setTo($this->validate_email($command['to']));
      $mailMsg->setFrom($this->validate_email($command['from']));
      $mailMsg->setSubject($command['subject']);
      $mailMsg->setBody($command['body'], 'text/html');
      if (!empty($command['body_alt'])) {
        $mailMsg->addPart($command['body_alt'], 'text/plain');
      }
      if (!empty($command['attachment']) && is_file($command['attachment'])) {
        // Optionally add any attachments
        $attachment = Swift_Attachment::fromPath($command['attachment']);
        // $attachment->setFilename($command['file_title']);
        $mailMsg->attach($attachment);
      }
    } catch (Exception $msg_error) {
      throw new CoreException("500", "error while preparing email message", $msg_error);
    }

    // Connect and deliver email message
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
    $oRequest->spool_id = $command['spool_id'];

    $oRequest->application_id = $command['application_id'];
    $oRequest->application_data = array(
        'amount' => 1,
        'amount_net' => 1,
        'status' => 'completed',
        'response' => '',
        'result' => 'success',
        'error' => ''
    );

    // now process results in a separate thread
    include 'lib/CoreThread.php';
    $threadProcess = new CoreProcess();
    $threadProcess->wait()->run($oRequest);
    /*     * **************************************************** UPDATE END */
  }

  public static function template_application($application_name, $service_flag = Email::SERVICE_FLAG)
  {
    $template = '';
    switch ($application_name) {
      case 'email_send':
        $template = array(
            'to' => '[destination:email]',
            'from' => '[source:email]',
            'subject' => '[parameter:subject]',
            'body' => '[parameter:body]',
            'body_alt' => '[parameter:body_alt]',
            'attachment' => '[parameter:attachment]',
            'application_id' => '[application:application_id]',
            'spool_id' => '[spool:spool_id]',
                //'file_title' => '[message:name].[message:type]'
        );
        break;
      case 'log':
        // TODO: create a kannel.log file, and put log messages there
        break;
    }
    return $template;
  }

}
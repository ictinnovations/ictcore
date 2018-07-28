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
use ICT\Core\Corelog;
use ICT\Core\Gateway;
use ICT\Core\Provider;
use ICT\Core\Request;

class Kannel extends Gateway
{

  /** @const */
  const GATEWAY_FLAG = 2;
  const GATEWAY_TYPE = 'kannel';
  const CONTACT_FIELD = 'phone';
  const CONTACT_ANONYMOUS = '0000000';

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

  /** @var string $path */
  protected $path;

  public function __construct()
  {
    $this->username = Conf::get('kannel:user', 'myuser');
    $this->password = Conf::get('kannel:pass', 'mypass');
    $this->host = Conf::get('kannel:host', '127.0.0.1');
    $this->port = Conf::get('kannel:port', '13013');
    $this->path = Conf::get('kannel:path', '/cgi-bin/sendsms');
  }

  protected function connect()
  {

    $this->conn = fsockopen($this->host, $this->port);
    socket_set_blocking($this->conn, false);
    if ($this->conn) {
      Corelog::log("Kannel connected successfully", Corelog::CRUD);
      return $this->conn;
    } else {
      Corelog::log("Kannel connection failed", Corelog::ERROR);
      return false;
    }
  }

  protected function dissconnect()
  {
    Corelog::log("Kannel disconnect requested", Corelog::CRUD);
    return fclose($this->conn);
  }

  public function send($command, Provider $oProvider = NULL)
  {
    if (empty($oProvider)) {
      Corelog::log("Kannel sending commands", Corelog::CRUD, $command);
    } else {
      Corelog::log("Kannel sending commands via:".$oProvider->name, Corelog::CRUD, $command);
    }

    $queryString = array(
        'username' => $this->username,
        'password' => $this->password
    );

    // Convert json into data array
    $data = json_decode($command, TRUE);

    // check if we can update application status via delivery report, if so use it
    if (!empty($data['application_id']) && !empty($data['spool_id'])) {
      /* delivery report settings
        DLR-Mask
        1: Delivered to phone,
        2: Non-Delivered to Phone,
        4: Queued on SMSC,
        8: Delivered to SMSC,
        16: Non-Delivered to SMSC.
       */
      $spool_id = $data['spool_id'];
      $application_id = $data['application_id'];
      $dlrMask = 1 | 2 | 4 | 8 | 16;
      $dlrUser = Conf::get('gatewayhub:username', 'myuser');
      $dlrPass = Conf::get('gatewayhub:password', 'mypass');
      $dlrUrl  = Conf::get('gatewayhub:url', 'http://localhost/api/gateway.php'); // main url
      $dlrUrl .= "?username=$dlrUser&password=$dlrPass"; // authentication
      $dlrUrl .= "&spool_id=$spool_id&gateway_flag=" . Kannel::GATEWAY_FLAG; // target
      $dlrUrl .= "&application_id=$application_id&application_data[result]=%d&application_data[error]=%A"; // response with variables

      $queryString['dlr-mask'] = $dlrMask;
      $queryString['dlr-url'] = $dlrUrl;
    }

    foreach ($data as $variable => $value) {
      switch ($variable) {
        case 'dlr-url':
          $queryString['dlr-url'] = $value;
          break;
        case 'message':
        case 'data':
          $queryString['text'] = $value;
          break;
        default:
          $queryString[$variable] = $value;
          break;
      }
    }
    $URL = $this->path . '?' . http_build_query($queryString);

    $this->connect();

    if ($this->conn) {
      fputs($this->conn, "GET $URL HTTP/1.0\r\n\r\n");
      usleep(100); //allow time for response
      $response = "";
      try {
        while (!feof($this->conn)) {
          $response .= fgets($this->conn, 128);
        }
      } catch (Exception $ex) {
        Corelog::log($ex->getMessage(), Corelog::WARNING);
        Corelog::log('Unable to read kannel response', Corelog::WARNING);
      }

      return $response;
    } else {
      return false;
    }

    $this->dissconnect();


    /*     * ******************************************************************
     * update that application has been completed
     * ********************************************************************* */
    $oRequest = new Request();
    $oRequest->gateway_flag = Kannel::GATEWAY_FLAG;
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
    return $core_dir . '/Gateway/Kannel/templates';
  }

  private function config_filename($type, $name)
  {
    global $path_etc;
    switch ($type) {
      case 'did':
        return $path_etc . DIRECTORY_SEPARATOR . "kannel/provider/$name.xml";
      case 'smpp':
        return $path_etc . DIRECTORY_SEPARATOR . "kannel/provider/$name.xml";
    }
    return false;
  }

  public function config_save($type, $name, $data = '')
  {
    Corelog::log("Kannel saving config for type: $type, name: $name", Corelog::CRUD);
    $config_file = $this->config_filename($type, $name);
    file_put_contents($config_file, $data);
  }

  public function config_delete($type, $name)
  {
    Corelog::log("Kannel deleting config for type: $type, name: $name", Corelog::CRUD);
    $config_file = $this->config_filename($type, $name);
    unlink($config_file);
  }

  public function config_reload()
  {
    // TODO: develop reload method for kannel
    parent::config_reload();
  }

}

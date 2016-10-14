<?php
/* * ***************************************************************
 * Copyright © 2014 ICT Innovations Pakistan All Rights Reserved   *
 * Developed By: Nasir Iqbal                                       *
 * Website : http://www.ictinnovations.com/                        *
 * Mail : nasir@ictinnovations.com                                 *
 * *************************************************************** */

class Freeswitch extends Gateway
{

  /** @const */
  const CONTACT_FIELD = 'phone';
  const GATEWAY_FLAG = 8;

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

  public function __construct()
  {
    $this->host = conf_get('freeswitch:host', '127.0.0.1');
    $this->port = conf_get('freeswitch:port', '8021');
    $this->username = conf_get('freeswitch:user', 'user');
    $this->password = conf_get('freeswitch:pass', 'ClueCon');
  }

  public static function capabilities()
  {
    return (
            Voice::SERVICE_FLAG | Fax::SERVICE_FLAG
            );
  }

  public function is_supported($service)
  {
    if (($this->capabilities() & $service) == $service) {
      return TRUE;
    } else {
      return FALSE;
    }
  }

  protected function connect()
  {

    $this->conn = fsockopen($this->host, $this->port);
    socket_set_blocking($this->conn, false);
    if ($this->conn) {
      while (!feof($this->conn)) {
        $buffer = fgets($this->conn, 1024);
        usleep(100); //allow time for reponse
        if (trim($buffer) == "Content-Type: auth/request") {
          fputs($this->conn, "auth $this->password\n\n");
          break;
        }
      }
      Corelog::log("Freeswitch connected successfully", Corelog::CRUD);
      return $this->conn;
    } else {
      Corelog::log("Freeswitch connection failed", Corelog::ERROR);
      return false;
    }
  }

  protected function dissconnect()
  {
    Corelog::log("Freeswitch disconnect requested", Corelog::CRUD);
    return fclose($this->conn);
  }

  public function send($command)
  {
    Corelog::log("Freeswitch sending commands", Corelog::CRUD, $command);
    $this->connect();

    if ($this->conn) {
      // convert array based command into string
      $aVariable = array();
      foreach ($command['input'] as $var_name => $var_value) {
        $aVariable[] = "$var_name=$var_value";
      }
      $command_str = '';
      foreach ($command['batch'] as $aCommand) {
        $command_str .= $aCommand['name'] . ' {' . implode(',', $aVariable) . '}' . $aCommand['data'];
      }
      // TODO: work on $command['output']

      fputs($this->conn, $command_str . "\n\n");
      usleep(100); //allow time for response
      $response = "";
      $i = 0;
      $contentlength = 0;
      while (!feof($this->conn)) {
        $buffer = fgets($this->conn, 4096);
        if ($contentlength > 0) {
          $response .= $buffer;
        }

        if ($contentlength == 0) { //if contentlenght is already don't process again
          if (strlen(trim($buffer)) > 0) { //run only if buffer has content
            $temparray = explode(":", trim($buffer));
            if ($temparray[0] == "Content-Length") {
              $contentlength = trim($temparray[1]);
            }
          }
        }

        usleep(100); //allow time for reponse
        //optional because of script timeout //don't let while loop become endless
        if ($i > 10000) {
          break;
        }
        if ($contentlength > 0) { //is contentlength set
          //stop reading if all content has been read.
          if (strlen($response) >= $contentlength) {
            break;
          }
        }
        $i++;
      }
      $this->dissconnect();
      return $response;
    } else {
      $this->dissconnect();
      return false;
    }

    $this->dissconnect();
  }

  public static function template_application($application_name, $service_flag = Voice::SERVICE_FLAG)
  {
    switch ($application_name) {
      case 'inbound':
        // yet no template needed by inbound application
        break;
      case 'originate':
        $app_script = "/usr/ictcore/bin/freeswitch/application.lua";
        $end_script = "/usr/ictcore/bin/freeswitch/spool_failed.lua";
        $app_failed = "lua $end_script [spool:spool_id] [application:application_id] error";
        $template['input'] = array(
            'failure_causes' => 'NORMAL_CLEARING', // transfer on each failure except normal clearing
            'transfer_on_fail' => "'UNALLOCATED_NUMBER auto_cause xml ictcore_fail'",
            'api_hangup_hook' => "'$app_failed'", // queue are required for additional lua variables
            'session_in_hangup_hook' => 'true',
            'ignore_early_media' => 'true',
            'codec_string' => "'PCMU,PCMA'",
            'spool_status' => 'connected',
            'spool_id' => '[spool:spool_id]',
            'origination_caller_id_number' => '[source:phone]',
            'origination_caller_id_name' => '[source:phone]'
        );
        if (($service_flag & Fax::SERVICE_FLAG) == Fax::SERVICE_FLAG) {
          $template['input'] += array(
              'fax_enable_t38_request' => 'true',
              'fax_enable_t38' => 'true',
              'fax_verbose' => 'true',
              'fax_use_ecm' => 'true'
          );
        }
        $app_success = "lua($app_script [spool:spool_id] [application:application_id] success)";
        $template['batch'][1] = array(
            'name' => "bgapi originate",
            'data' => "sofia/gateway/[provider:name]/[provider:prefix][destination:phone] '&$app_success'"
        );
        $template['output'] = array(
            'status' => 'spool_status'
        );
        break;
      case 'connect':
        $template['input'] = array(
            'spool_id' => '[spool:spool_id]',
            'spool_status' => 'connected',
            'start_app_id' => '[application:application_id]'
        );
        $template['batch'][1] = array(
            'name' => "answer",
            'data' => '' // no data
        );
        $template['output'] = array(
            'status' => 'spool_status'
        );
        break;
      case 'log':
        $template['batch'][1] = array(
            'name' => "log",
            'data' => 'INFO [parameter:message]'
        );
        break;
      case 'disconnect':
        $template['batch'][1] = array(
            'name' => "hangup",
            'data' => '' // TODO, update it for cause codes
        );
        break;
      case 'voice_play':
        $template['batch'][1] = array(
            'name' => "playback",
            'data' => "[parameter:message]"
        );
        break;
      case 'fax_receive':
        $fax_file_tmp = tempnam('/tmp', 'fax_') . '.tif';
        $template['input'] = array(
            'fax_enable_t38_request' => 'true',
            'fax_enable_t38' => 'true',
            'fax_local_station_id' => 'ICTCore',
            'fax_file' => $fax_file_tmp,
            'application_result' => 'failed',
            'execute_on_fax_success' => 'set application_result=success'
        );
        $template['batch'][1] = array(
            'name' => "playback",
            'data' => "silence_stream://1000"
        );
        $template['batch'][4] = array(
            'name' => "rxfax",
            'data' => $fax_file_tmp
        );
        $template['output'] = array(
            'error' => 'fax_result_text',
            'pages' => 'fax_document_total_pages',
            'fax_file' => 'fax_file'
        );
        break;
      case 'fax_send':
        $template['input'] = array(
            'fax_enable_t38_request' => 'true',
            'fax_enable_t38' => 'true',
            'fax_local_station_id' => 'ICTCore',
            'fax_header' => '[parameter:header]',
            'fax_ident' => '[source:phone]',
            'application_result' => 'failed',
            'execute_on_fax_success' => 'set application_result=success'
        );
        $template['batch'][1] = array(
            'name' => "playback",
            'data' => "silence_stream://2000"
        );
        $template['batch'][4] = array(
            'name' => "txfax",
            'data' => "[parameter:message]"
        );
        $template['output'] = array(
            'error' => 'fax_result_text',
            'pages' => 'fax_document_transferred_pages'
        );
        break;
    }

    // insert missing parameters with their defaults
    $template += array(
        'application_id' => '[application:application_id]',
        'input' => array(),
        'output' => array(),
        'batch' => array()
    );
    $template['input'] += array(
        'application_result' => 'success'  // set the default result, will be read by output
    );
    $template['output'] += array(
        'result' => 'application_result', // mapping between application and gateway variables
        'call_id' => 'uuid'
    );

    return $template;
  }

  public function save_provider($name, $aSetting = array())
  {
    global $path_etc;
    Corelog::log("Freeswitch saving provide name: $name", Corelog::CRUD);

    if ($aSetting['register'] == true) {
      $aSetting['register'] = 'true';
    } else {
      $aSetting['register'] = 'false';
    }
    unset($aSetting['active']);
    $doc = new DOMDocument();
    $doc->formatOutput = true;
    $include_obj = $doc->createElement('include');
    $include_node = $doc->appendChild($include_obj);

    $gateway_obj = $doc->createElement('gateway');
    $gateway_node = $include_node->appendChild($gateway_obj);
    $gateway_node->setAttribute('name', $name);

    foreach ($aSetting as $param_name => $param_value) {
      $param_obj = $doc->createElement('param');
      $param_node = $gateway_node->appendChild($param_obj);
      $param_node->setAttribute('name', $param_name);
      $param_node->setAttribute('value', $param_value);
    }

    return $doc->save($path_etc . DIRECTORY_SEPARATOR . "freeswitch/sip_profiles/provider/$name.xml");
  }

  public function template_provider()
  {
    return array(
        'name' => '[provider:name]',
        'username' => '[provider:username]',
        'password' => '[provider:password]',
        'register' => '[provider:register]',
        'realm' => '[provider:host]',
        'proxy' => '[provider:host]:[provider:port]'
    );
  }

}
<?php

namespace ICT\Core\Program;

use ICT\Core\Application;

/* * *****************************************************************
 * Copyright © 2015 ICT Innovations Pakistan All Rights Reserved   *
 * Developed By: Nasir Iqbal                                       *
 * Website : http://www.ictinnovations.com/                        *
 * Mail : nasir@ictinnovations.com                                 *
 * *************************************************************** */

use ICT\Core\Account;
use ICT\Core\Message\Document;
use ICT\Core\Message\Recording;
use ICT\Core\Message\Template;
use ICT\Core\Message\Text;
use ICT\Core\Program;
use ICT\Core\Service\Voice;
use ICT\Core\Scheme;
use ICT\Core\Token;

class Ivr extends Program
{
  /**
   * ************************************************** Program related data **
   */
  /**
   * @property-read integer $program_id
   * @var integer 
  */

  /** @var string */
  public $name = 'ivr';

  /**
   * @property-read string $type
   * @var string 
  */
  protected $type = 'ivr';
  public $description = null;
  /**
   * @property-read string $ivr_scheme
   * @var  string 
  */
  public $ivr_scheme = null;
  //public $aResource = array();
  //fuction overriding program class

  /**
   * return a name value pair of all aditional program parameters which we need to save
   * @return array
   */
  public function parameter_save()
  {
    $aParameters = array(
      'account_id' => $this->account_id,
      'ivr_scheme' => $this->ivr_scheme,
      'description' => $this->description
    );
    return $aParameters;
  }

  protected function resource_load()
  {
    $aData = json_decode($this->ivr_scheme);

    foreach ($aData as $app_key => $application) {
      if (isset($application->resources) && is_object($application->resources)) {
        foreach ($application->resources as $resource_type => $resource_id) {
          $resource_name = 'resource_load_'.$resource_type;
          if (method_exists($this, $resource_name) && !empty($resource_id)) {
            $resource_key = $app_key . '_' . $resource_type;
            $this->aResource[$resource_key] = $this->$resource_name($resource_id);
          }
        }
      }
    }
  }

  /**
   * Locate and load recording
   * Use recording_id from application / ivr_scheme parameters as reference
   * @return Recording null or a valid recording object
   */
  protected function resource_load_recording($recording_id)
  {
    if (!empty($recording_id)) {
      $oRecording = new Recording($recording_id);
      return $oRecording;
    }
  }

  /**
   * Locate and load account
   * Use account_id from application / ivr_scheme parameters as reference
   * @return Account null or a valid account object
   */
  protected function resource_load_account($account_id)
  {
    if (!empty($account_id)) {
      $oAccount = Account::load($account_id);
      return $oAccount;
    }
  }

  protected function resource_load_extension($extension_id)
  {
    return $this->resource_load_account($extension_id);
  }

  protected function resource_load_did($did_id)
  {
    return $this->resource_load_account($did_id);
  }

  /**
   * Locate and load document
   * Use document_id or content or data from program parameters as reference
   * @return Document null or a valid document object
   */
  protected function resource_load_document($document_id)
  {
    if (!empty($document_id)) {
      $oDocument = new Document($document_id);
      return $oDocument;
    }
  }

  /**
   * Locate and load template
   * Use template_id from application / ivr_scheme parameters as reference
   * @return Template null or a valid template object
   */
  protected function resource_load_template($template_id)
  {
    if (!empty($template_id)) {
      $oTemplate = new Template($template_id);
      return $oTemplate;
    }
  }

  /**
   * Locate and load text
   * Use text_id from application / ivr_scheme parameters as reference
   * @return Text null or a valid text object
   */
  protected function resource_load_text($text_id)
  {
    if (!empty($text_id)) {
      $oText = new Text($text_id);
      return $oText;
    }
  }

  public function scheme()
  {
    $aData = json_decode($this->ivr_scheme);

    // starting with applications
    $aApps   = array();
    $atype   = array();
    $aAction = array();

    $resourceToken = new Token(Token::SOURCE_CUSTOM, $this->aResource);

    foreach ($aData as $app_key => $application) {
      if ($application->type == 'start') {
        $app_key = 'start';
        $type = 'originate'; // TODO: select between originate / answer depending on transmission direction
      } else {
        if ($application->type == 'hangup') { // ask fiza to change it
          $type = 'disconnect' ;
        } else {
          $type = $application->type;
        }
      }

      $aApps[$app_key] = Application::load($type);
      $this->application_set_data($app_key, $aApps[$app_key], $application->data, $resourceToken);

      if (is_array($application->in_nod)) {
        $aAction[] = $application->in_nod; 
      }
    }

    // locate the start app and initiate scheme
    // TODO: rename / confirm "start" index with GUI developer
    $mainScheme = new Scheme($aApps['start']);
    // following function will process all actions recursively
    $this->_scheme_add_action($mainScheme, 'start', $aApps, $aData);
    return $mainScheme;
  }

  private function _scheme_find_action($aActions, $app_index) 
  {
    $machingActions = array();
    /*Static $count=0;
    $count++;
    if($count>=5){
      return false;
    }*/
    foreach ($aActions as $key => $actions) {
      if($actions->type=='start' && $app_index=='start'){
         $source = $actions->app_index;
         $app_index = $actions->app_index;
      } else {
       $source = $actions->app_index;
      }

      foreach ($actions->out_nod as $out_key => $action) {
        if ($source == $app_index) {
           $machingActions[$out_key] = $action;
        }
      }
    }
    return $machingActions;
  }

  private function _scheme_add_action($oScheme, $app_index, $aApps, $aActions) 
  {
    $app_actions = $this->_scheme_find_action($aActions, $app_index);
    //echo "<pre>";print_r($app_actions);
    foreach($app_actions as $key => $action) {
      if ($action->pointer->linked_app_index) { 
        $aApps[$app_index]->processed = true;
        $target_app_index = $action->pointer->linked_app_index;
        $newScheme = $oScheme->node_create(array('result' => $action->node_type))->link($aApps[$target_app_index]);
        if ($aApps[$target_app_index]->processed != true) {
          $this->_scheme_add_action($newScheme, $target_app_index, $aApps, $aActions);
        }
      }
      //return $oScheme;
    }
    return $oScheme;
  }

  public function application_set_data($app_key, $oApplication, $data, $oToken)
  {
    // set application parameters from given data
    foreach ($data as $key => $value) {
      if ($oApplication->$key != 'type') {
        if (Token::is_token($value)) {
          // translate [resource:account:account_id] into [app2_account:account_id]
          // see $resource_key in @resource_load method
          $value = preg_replace('/\[resources:(\w+):([\w:]*)\]/', '['.$app_key.'_\1:\2]', $value);
          $value = $oToken->render_variable($value, Token::KEEP_ORIGNAL);
        }
        $oApplication->$key = $value;
      }
    }
  }

  /**
   * Function: transmission_create
   * Creating transmission while using current program
   */
  public function transmission_create($contact_id, $account_id, $direction = Transmission::OUTBOUND)
  {
    $oTransmission = parent::transmission_create($contact_id, $account_id, $direction);
    $oTransmission->service_flag = Voice::SERVICE_FLAG;
    return $oTransmission;
  }
}

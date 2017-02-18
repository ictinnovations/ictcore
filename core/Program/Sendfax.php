<?php

namespace ICT\Core\Program;

/* * ***************************************************************
 * Copyright © 2015 ICT Innovations Pakistan All Rights Reserved   *
 * Developed By: Nasir Iqbal                                       *
 * Website : http://www.ictinnovations.com/                        *
 * Mail : nasir@ictinnovations.com                                 *
 * *************************************************************** */

use ICT\Core\Application\Disconnect;
use ICT\Core\Application\Fax_send;
use ICT\Core\Application\Originate;
use ICT\Core\Message\Document;
use ICT\Core\Program;
use ICT\Core\Result;
use ICT\Core\Scheme;
use ICT\Core\Service\Fax;
use ICT\Core\Transmission;

class Sendfax extends Program
{

  /** @var string */
  public $name = 'sendfax';

  /**
   * @property-read string $type
   * @var string 
   */
  protected $type = 'sendfax';

  /**
   * ************************************************ Default Program Values **
   */

  /**
   * Parameters required by this program along with default values
   * @var array 
   */
  public static $requiredParameter = array(
      'document_id' => '[document_id]'
  );

  /**
   * Locate and load document
   * Use document_id or content or data from program data as reference
   * @return Document null or a valid document object
   */
  protected function resource_load_document()
  {
    if (isset($this->data['document_id']) && !empty($this->data['document_id'])) {
      $oDocument = new Document($this->data['document_id']);
      return $oDocument;
    } else if (isset($this->data['file_name']) || isset($this->data['document_file'])) {
      $file_name = !empty($this->data['file_name']) ? $this->data['file_name'] : $this->data['document_file'];
      if (!empty($file_name)) {
        $oDocument = Document::construct_from_array(array('file_name' => $file_name));
        $oDocument->save();
         // update document_id with new value, and remove all temporary parameters
        $this->data['document_id'] = $oDocument->document_id;
        unset($this->data['file_name']);
        unset($this->data['document_file']);
        return $oDocument;
      }
    }
  }

  /**
   * Function: scheme
   * Program scheme for primary transmission, application execution order and conditions
   */
  public function scheme()
  {
    $outboundCall = new Originate();

    $faxSend = new Fax_send();
    $faxSend->data = array(
        'message' => $this->aResource['document']->file_name,
        'header' => $this->aResource['document']->name
    );

    $hangupCall = new Disconnect();

    $oScheme = new Scheme();
    $oScheme->add($outboundCall);
    $oScheme->add($faxSend);
    $oScheme->add($hangupCall);

    return $oScheme;
  }

  /**
   * Function: transmission_create
   * Creating transmission while using current program
   */
  public function transmission_create($contact_id, $account_id, $direction = Transmission::OUTBOUND)
  {
    $oTransmission = parent::transmission_create($contact_id, $account_id, $direction);
    $oTransmission->service_flag = Fax::SERVICE_FLAG;
    return $oTransmission;
  }

  /**
   * Event: Transmission completed
   * Will be fired when first / initial transmission is done
   * here we will decide either its was completed or failed
   */
  protected function transmission_done()
  {
    if ($this->result['result'] == 'error') {
      return Transmission::STATUS_FAILED;
    }

    // after processing further, we can confirm if current transmission was completed
    $result = 'error';
    $pages = 0;
    $error = '';
    foreach ($this->oTransmission->aResult as $oResult) {
      switch ($oResult->type) {
        case Result::TYPE_APPLICATION:
          if ($oResult->name == 'fax_send' && $oResult->data == 'success') {
            $result = 'success';
          }
          break;
        case Result::TYPE_INFO:
          if ($oResult->name == 'pages') {
            $pages = $oResult->data;
          }
          break;
        case Result::TYPE_ERROR:
          $result = 'error';
          $error = $oResult->data;
          break 2; // in case of error, also terminate foreach loop
      }
    }

    if ($result == 'success' && empty($error) && $pages > 0) {
      $this->result['pages'] = $pages;
      return Transmission::STATUS_COMPLETED;
    } else {
      $this->result['result'] = 'error';
      $this->result['error'] = $error;
      return Transmission::STATUS_FAILED;
    }
  }

}
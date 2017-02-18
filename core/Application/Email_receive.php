<?php

namespace ICT\Core\Application;

/* * ***************************************************************
 * Copyright © 2015 ICT Innovations Pakistan All Rights Reserved   *
 * Developed By: Nasir Iqbal                                       *
 * Website : http://www.ictinnovations.com/                        *
 * Mail : nasir@ictinnovations.com                                 *
 * *************************************************************** */

use ICT\Core\Application;
use ICT\Core\Exchange\Dialplan;
use ICT\Core\Gateway\Sendmail;
use ICT\Core\Message\Template;
use ICT\Core\Program;
use ICT\Core\Result;
use ICT\Core\Spool;

class Email_receive extends Application
{

  /** @var string */
  public $name = 'email_receive';

  /**
   * @property-read string $type
   * @var string
   */
  protected $type = 'email_receive';

  /**
   * ******************************************** Default Application Values **
   */

  /**
   * Parameters required by this application along with default values
   * @var array 
   */
  public static $requiredParameter = array(
      'context' => 'external',
      'source' => '[contact:email]',
      'destination' => '[account:email]',
      'filter_flag' => Dialplan::FILTER_COMMON
  );

  public function deploy(Program &$oProgram)
  {
    $oDialplan = new Dialplan();
    $oDialplan->application_id = $this->type;
    $oDialplan->program_id = $oProgram->program_id;
    $oDialplan->context = $this->data['context'];
    $oDialplan->filter_flag = $this->data['filter_flag'];
    $oDialplan->gateway_flag = Sendmail::GATEWAY_FLAG;
    if (!empty($this->data['source'])) {
      $oDialplan->source = $this->data['source'];
    }
    if (!empty($this->data['destination'])) {
      $oDialplan->destination = $this->data['destination'];
    }

    return $oDialplan->save();
  }

  public function remove()
  {
    $filter = array('application_id' => $this->application_id);
    $listDialplan = Dialplan::search($filter);
    foreach ($listDialplan as $aDialplan) {
      $oDialplan = new Dialplan($aDialplan['dialplan_id']);
      $oDialplan->delete();
    }
  }

  public function execute()
  {
    // this application can't be executed in that way, so nothing to execute here
  }

  public function process()
  {
    // if we really have received an email
    if (!empty($this->result['subject']) || !empty($this->result['body'])) {
      // we received an email, we need to save it
      $file_name = 'email_' . $this->application_id . '_' . $this->oTransmission->oSpool->spool_id;
      $oTemplate = new Template();
      $oTemplate->name = $file_name;
      $oTemplate->description = 'email received while processing transmission: ' . $this->oTransmission->transmission_id;
      $oTemplate->subject = $this->result['subject'];
      $oTemplate->body = $this->result['body'];
      $oTemplate->body_alt = $this->result['body_alt'];
      $oTemplate->attachment = $this->result['attachment'];
      $oTemplate->save();

      // Save result
      $this->result_create($oTemplate->template_id, 'template', Result::TYPE_MESSAGE);
      $this->result['result'] = 'success';
    } else {
      $this->result['result'] = 'error';
    }

    return Spool::STATUS_COMPLETED;
  }

}
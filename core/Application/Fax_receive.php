<?php
/* * ***************************************************************
 * Copyright © 2015 ICT Innovations Pakistan All Rights Reserved   *
 * Developed By: Nasir Iqbal                                       *
 * Website : http://www.ictinnovations.com/                        *
 * Mail : nasir@ictinnovations.com                                 *
 * *************************************************************** */

class Fax_receive extends Application
{

  /** @var string */
  public $name = 'fax_receive';

  /**
   * @property-read string $type
   * @var string
   */
  protected $type = 'fax_receive';

  /**
   * ******************************************** Default Application Values **
   */

  /**
   * default condition
   * @var array 
   */
  public static $defaultCondition = array('result' => 'success');

  /**
   * All possible results to use 
   * @var array 
   */
  public static $supportedResult = array(
      'result' => array('success', 'error'),
      'file' => '/path/to/file',
      'pages' => 0,
      'error' => '' // empty message expected on success
  );

  public function execute()
  {
    $oService = new Fax();
    return $oService->application_template('fax_receive');
  }

  public function process()
  {
    // if we really have received a Fax
    if (isset($this->result['fax_file']) && file_exists($this->result['fax_file'])) {
      // we received a fax file, we need to save it
      $file_name = 'fax_' . $this->application_id . '_' . $this->oTransmission->oSpool->spool_id;
      $oDocument = new Document();
      $oDocument->name = $file_name;
      $oDocument->description = 'file received while processing transmission: ' . $this->oTransmission->transmission_id;
      $oDocument->file_name = $this->result['fax_file'];
      $oDocument->save();

      // Save result
      $this->result_create($oDocument->document_id, 'document', Result::TYPE_MESSAGE);
      $this->result_create($oDocument->pages, 'pages', Result::TYPE_INFO);
      $this->result['result'] = 'success';
    } else {
      // if no valid file found then change result to with error
      $this->result['result'] = 'error';
      $this->result_create('invalid fax', 'error', Result::TYPE_ERROR);
    }

    // TODO return Spool::STATUS_CONNECTED;
    return Spool::STATUS_COMPLETED;
  }

}
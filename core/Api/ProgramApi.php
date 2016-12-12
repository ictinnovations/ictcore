<?php

namespace ICT\Core\Api;

/* * ***************************************************************
 * Copyright © 2016 ICT Innovations Pakistan All Rights Reserved   *
 * Developed By: Nasir Iqbal                                       *
 * Website : http://www.ictinnovations.com/                        *
 * Mail : nasir@ictinnovations.com                                 *
 * *************************************************************** */

use ICT\Core\Api;
use ICT\Core\CoreException;
use ICT\Core\Program;

class ProgramApi extends Api
{

  /**
   * Create a new program
   *
   * @url POST /program/create
   * @url POST /program/create/$program_name
   */
  public function create($program_name = null, $data = array())
  {
    $this->_authorize('program_create');

    $oProgram = Program::load($program_name);
    $this->set($oProgram, $data);

    if ($oProgram->save()) {
      return $oProgram->program_id;
    } else {
      throw new CoreException(417, 'Program creation failed');
    }
  }

  /**
   * Initiate a new transmission for given program
   *
   * @url POST /program/$program_name/transmission
   */
  public function transmission($program_name, $data = array())
  {
    $this->_authorize('program_create');
    $this->_authorize('transmission_create');

    $aProgram = empty($data['program_data']) ? array() : $data['program_data'];
    $aTransmission = empty($data['transmission_data']) ? array() : $data['transmission_data'];
    if (empty($aTransmission)) {
      throw new CoreException(412, 'Transmission data is missing');
    }

    $oProgram = Program::load($program_name);
    $oTransmission = $oProgram->transmission_instant($aProgram, $aTransmission);

    if ($oTransmission->save()) {
      return $oTransmission->transmission_id;
    } else {
      throw new CoreException(417, 'Transmission creation failed');
    }
  }

  /**
   * List all available programs
   *
   * @url GET /program/list
   * @url POST /program/list
   * @url GET /program/list/$program_name
   * @url POST /program/list/$program_name
   */
  public function list_view($data = array(), $program_name = null)
  {
    $this->_authorize('program_list');
    if ($program_name) {
      $data['type'] = $program_name; // add program_name i.e class name as filter
    }
    return Program::search($data);
  }

  /**
   * Gets the program by id
   *
   * @url GET /program/$program_id
   */
  public function read($program_id)
  {
    $this->_authorize('program_read');

    $oProgram = Program::load($program_id);
    return $oProgram;
  }

  // can't update a program create new

  /**
   * Remove a program
   *
   * @url GET /program/$program_id/delete
   * @url DELETE /program/$program_id/delete
   */
  public function remove($program_id)
  {
    $this->_authorize('program_delete');

    $oProgram = Program::load($program_id);

    $result = $oProgram->delete();
    if ($result) {
      return $result;
    } else {
      throw new CoreException(417, 'Program delete failed');
    }
  }

}
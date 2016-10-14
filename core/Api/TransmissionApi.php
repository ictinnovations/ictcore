<?php
/* * ***************************************************************
 * Copyright © 2016 ICT Innovations Pakistan All Rights Reserved   *
 * Developed By: Nasir Iqbal                                       *
 * Website : http://www.ictinnovations.com/                        *
 * Mail : nasir@ictinnovations.com                                 *
 * *************************************************************** */

class TransmissionApi extends Api
{

  /**
   * Create a new custom transmission
   *
   * @url POST /transmission/create/$program_id
   */
  public function create($program_id, $data = array())
  {
    $this->_authorize('transmission_create');

    if (empty($data['contact_id'])) {
      throw new CoreException(412, 'contact_id is missing');
    } else {
      $contact_id = $data['contact_id'];
    }
    unset($data['contact_id']);

    if (empty($data['account_id'])) {
      $oAccount = new Account(Account::USER_DEFAULT);
      $account_id = $oAccount->account_id;
    } else {
      $account_id = $data['account_id'];
    }
    unset($data['account_id']);

    $direction = empty($data['direction']) ? Transmission::OUTBOUND : $data['direction'];
    unset($data['direction']);

    $oProgram = Program::load($program_id);
    $oTransmission = $oProgram->transmission_create($contact_id, $account_id, $direction);
    $this->set($oTransmission, $data);

    if ($oTransmission->save()) {
      return $oTransmission->transmission_id;
    } else {
      throw new CoreException(417, 'Transmission creation failed');
    }
  }

  /**
   * List all available transmissions
   *
   * @url GET /transmission/list
   * @url POST /transmission/list
   */
  public function list_view($data = array())
  {
    $this->_authorize('transmission_list');
    return Transmission::search($data);
  }

  /**
   * List all available calls
   *
   * @url GET /call/list
   * @url POST /call/list
   */
  public function call_list($data = array())
  {
    $data['service_flag'] = Voice::SERVICE_FLAG;
    return $this->list_view($data);
  }

  /**
   * List all available faxs
   *
   * @url GET /fax/list
   * @url POST /fax/list
   */
  public function fax_list($data = array())
  {
    $data['service_flag'] = Fax::SERVICE_FLAG;
    return $this->list_view($data);
  }

  /**
   * List all available SMS messages
   *
   * @url GET /sms/list
   * @url POST /sms/list
   */
  public function sms_list($data = array())
  {
    $data['service_flag'] = Sms::SERVICE_FLAG;
    return $this->list_view($data);
  }

  /**
   * List all available emails
   *
   * @url GET /email/list
   * @url POST /email/list
   */
  public function email_list($data = array())
  {
    $data['service_flag'] = Email::SERVICE_FLAG;
    return $this->list_view($data);
  }

  /**
   * Gets the transmission by id
   *
   * @url GET /transmission/$transmission_id
   * @url GET /call/$transmission_id
   * @url GET /fax/$transmission_id
   * @url GET /sms/$transmission_id
   * @url GET /email/$transmission_id
   */
  public function read($transmission_id)
  {
    $this->_authorize('transmission_read');

    $oTransmission = new Transmission($transmission_id);
    return $oTransmission;
  }

  /**
   * Update existing transmission
   *
   * no update needed
   */

  /**
   * Delete a transmission
   *
   * @url GET /transmission/$transmission_id/delete
   * @url DELETE /transmission/$transmission_id/delete
   * @url GET /call/$transmission_id/delete
   * @url DELETE /call/$transmission_id/delete
   * @url GET /fax/$transmission_id/delete
   * @url DELETE /fax/$transmission_id/delete
   * @url GET /sms/$transmission_id/delete
   * @url DELETE /sms/$transmission_id/delete
   * @url GET /email/$transmission_id/delete
   * @url DELETE /email/$transmission_id/delete
   */
  public function remove($transmission_id)
  {
    $this->_authorize('transmission_delete');

    $oTransmission = new Transmission($transmission_id);

    $result = $oTransmission->delete();
    if ($result) {
      return $result;
    } else {
      throw new CoreException(417, 'Transmission delete failed');
    }
  }

  /**
   * Send already saved transmission
   *
   * @url GET /transmission/$transmission_id/send
   * @url GET /call/$transmission_id/dial
   * @url GET /fax/$transmission_id/send
   * @url GET /sms/$transmission_id/send
   * @url GET /email/$transmission_id/send
   * @url GET /transmission/$transmission_id/retry
   * @url GET /call/$transmission_id/retry
   * @url GET /fax/$transmission_id/retry
   * @url GET /sms/$transmission_id/retry
   * @url GET /email/$transmission_id/retry
   */
  public function send($transmission_id)
  {
    $this->_authorize('transmission_send');

    $oTransmission = new Transmission($transmission_id);
    return $oTransmission->send();
  }

  /**
   * Schedule transmission 
   *
   * @url POST /transmission/$transmission_id/schedule
   * @url POST /call/$transmission_id/schedule
   * @url POST /fax/$transmission_id/schedule
   * @url POST /sms/$transmission_id/schedule
   * @url POST /email/$transmission_id/schedule
   */
  public function schedule($transmission_id, $data = array())
  {
    $this->_authorize('transmission_send');
    $this->_authorize('schedule_create');

    $oSchedule = new Schedule();
    $this->set($oSchedule, $data);

    $oTransmission = new Transmission($transmission_id);
    $oSchedule->type = 'transmission';
    $oSchedule->action = 'send';
    $oSchedule->data = $oTransmission->transmission_id;
    $oSchedule->account_id = $oTransmission->account_id;
    $oSchedule->save();

    return $oSchedule->schedule_id;
  }

  /**
   * Cancel transmission schedule
   *
   * @url GET /transmission/$transmission_id/schedule/cancel
   * @url GET /call/$transmission_id/schedule/cancel
   * @url GET /fax/$transmission_id/schedule/cancel
   * @url GET /sms/$transmission_id/schedule/cancel
   * @url GET /email/$transmission_id/schedule/cancel
   */
  public function schedule_cancel($transmission_id)
  {
    $this->_authorize('transmission_update');
    $this->_authorize('schedule_delete');

    $oTransmission = new Transmission($transmission_id);
    return $oTransmission->schedule_cancel();
  }

  /**
   * Create new transmission by cloning existing one
   *
   * @url GET /transmission/$transmission_id/clone
   * @url GET /call/$transmission_id/clone
   * @url GET /fax/$transmission_id/clone
   * @url GET /sms/$transmission_id/clone
   * @url GET /email/$transmission_id/clone
   */
  public function create_clone($transmission_id)
  {
    $this->_authorize('transmission_read');
    $this->_authorize('transmission_create');

    $oldTransmission = new Transmission($transmission_id);
    $newTransmission = clone $oldTransmission;
    if ($newTransmission->save()) {
      return $newTransmission->transmission_id;
    } else {
      throw new CoreException(417, 'Transmission cloning failed');
    }
  }

  /**
   * Get transmission status
   *
   * @url GET /transmission/$transmission_id/status
   * @url GET /call/$transmission_id/status
   * @url GET /fax/$transmission_id/status
   * @url GET /sms/$transmission_id/status
   * @url GET /email/$transmission_id/status
   */
  public function status($transmission_id)
  {
    $this->_authorize('transmission_read');

    $oTransmission = new Transmission($transmission_id);
    return $oTransmission->status;
  }

  /**
   * Get transmission details
   *
   * @url GET /transmission/$transmission_id/detail
   * @url GET /call/$transmission_id/detail
   * @url GET /fax/$transmission_id/detail
   * @url GET /sms/$transmission_id/detail
   * @url GET /email/$transmission_id/detail
   */
  public function detail($transmission_id)
  {
    $this->_authorize('transmission_read');
    $this->_authorize('spool_read');

    return Spool::search(array('transmission_id' => $transmission_id));
  }

  /**
   * Get transmission details
   *
   * @url GET /transmission/$transmission_id/result
   * @url GET /call/$transmission_id/result
   * @url GET /fax/$transmission_id/result
   * @url GET /sms/$transmission_id/result
   * @url GET /email/$transmission_id/result
   */
  public function result($transmission_id)
  {
    $this->_authorize('result_read');

    $aSpool = $this->detail($transmission_id);
    $spool = end($aSpool);
    return Result::search($spool['spool_id']);
  }

}
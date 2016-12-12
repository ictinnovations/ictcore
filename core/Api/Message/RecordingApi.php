<?php

namespace ICT\Core\Api\Message;

/* * ***************************************************************
 * Copyright © 2016 ICT Innovations Pakistan All Rights Reserved   *
 * Developed By: Nasir Iqbal                                       *
 * Website : http://www.ictinnovations.com/                        *
 * Mail : nasir@ictinnovations.com                                 *
 * *************************************************************** */

use ICT\Core\Api;
use ICT\Core\CoreException;
use ICT\Core\Corelog;
use ICT\Core\Message\Recording;

class RecordingApi extends Api
{

  /**
   * Create a new recording
   *
   * @url POST /recording/create
   * @url POST /message/recording/create
   */
  public function create($data = array())
  {
    $this->_authorize('recording_create');

    $oRecording = new Recording();
    global $_FILES, $_POST;
    if (!empty($_FILES)) {
      $file = array_shift(array_values($_FILES));
      $type = strtolower(end(explode('.', $file['name'])));
      $oRecording->type = $type;
      $oRecording->file_name = $file['tmp_name'];
      // WORK-AROUND as currently JSON is not possible with file uploads
      if (empty($data)) {
        $data = $_POST;
      }
    }
    $this->set($oRecording, $data);

    if ($oRecording->save()) {
      return $oRecording->recording_id;
    } else {
      throw new CoreException(417, 'Recording creation failed');
    }
  }

  /**
   * List all available recordings
   *
   * @url GET /recording/list
   * @url GET /message/recording/list
   * @url POST /recording/list
   * @url POST /message/recording/list
   */
  public function list_view($data = array())
  {
    $this->_authorize('recording_list');
    return Recording::search($data);
  }

  /**
   * Gets the recording by id
   *
   * @url GET /recording/$recording_id
   * @url GET /message/recording/$recording_id
   */
  public function read($recording_id)
  {
    $this->_authorize('recording_read');

    $oRecording = new Recording($recording_id);
    return $oRecording;
  }

  /**
   * Download recording by id
   *
   * @url GET /recording/$recording_id/download
   * @url GET /message/recording/$recording_id/download
   */
  public function download($recording_id)
  {
    $this->_authorize('recording_read');

    $oRecording = new Recording($recording_id);
    Corelog::log("Recording file / download requested :$oRecording->file_name", Corelog::CRUD);

    $quoted = sprintf('"%s"', addcslashes(basename($oRecording->file_name), '"\\'));
    $size = filesize($oRecording->file_name);

    header('Content-Description: File Transfer');
    header('Content-Type: audio/wav');
    header('Content-Disposition: attachment; filename=' . $quoted);
    header('Content-Transfer-Encoding: binary');
    header('Connection: Keep-Alive');
    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Pragma: public');
    header('Content-Length: ' . $size);

    readfile($oRecording->file_name);

    return true;
  }

  /**
   * Update existing recording
   *
   * @url POST /recording/$recording_id/update
   * @url POST /message/recording/$recording_id/update
   * @url PUT /recording/$recording_id/update
   * @url PUT /message/recording/$recording_id/update
   */
  public function update($recording_id, $data = array())
  {
    $this->_authorize('recording_update');

    $oRecording = new Recording($recording_id);
    global $_FILES, $_POST;
    if (!empty($_FILES)) {
      $file = array_shift(array_values($_FILES));
      $type = strtolower(end(explode('.', $file['name'])));
      $oRecording->type = $type;
      $oRecording->file_name = $file['tmp_name'];
      // WORK-AROUND as currently JSON is not possible with file uploads
      if (empty($data)) {
        $data = $_POST;
      }
    }
    $this->set($oRecording, $data);

    if ($oRecording->save()) {
      return $oRecording;
    } else {
      throw new CoreException(417, 'Recording update failed');
    }
  }

  /**
   * Create a new recording
   *
   * @url GET /recording/$recording_id/delete
   * @url GET /message/recording/$recording_id/delete
   * @url DELETE /recording/$recording_id/delete
   * @url DELETE /message/recording/$recording_id/delete
   */
  public function remove($recording_id)
  {
    $this->_authorize('recording_delete');

    $oRecording = new Recording($recording_id);

    $result = $oRecording->delete();
    if ($result) {
      return $result;
    } else {
      throw new CoreException(417, 'Recording delete failed');
    }
  }

}
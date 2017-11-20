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
   * @url POST /recordings
   * @url POST /messages/recordings
   */
  public function create($data = array())
  {
    $this->_authorize('recording_create');

    $oRecording = new Recording();
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
   * @url GET /recordings
   * @url GET /messages/recordings
   */
  public function list_view($query = array())
  {
    $this->_authorize('recording_list');
    return Recording::search((array)$query);
  }

  /**
   * Gets the recording by id
   *
   * @url GET /recordings/$recording_id
   * @url GET /messages/recordings/$recording_id
   */
  public function read($recording_id)
  {
    $this->_authorize('recording_read');

    $oRecording = new Recording($recording_id);
    return $oRecording;
  }

  /**
   * Upload recording file by id
   *
   * @url PUT /recordings/$recording_id/media
   * @url PUT /messages/recordings/$recording_id/media
   */
  public function upload($recording_id, $data = array())
  {
    $this->_authorize('recording_create');

    $oRecording = new Recording($recording_id);
    global $_FILES;
    if (!empty($_FILES)) {
      $file = array_shift(array_values($_FILES));
      $type = strtolower(end(explode('.', $file['name'])));
      $oRecording->type = $type;
      $oRecording->file_name = $file['tmp_name'];

      if ($oRecording->save()) {
        return $oRecording->recording_id;
      } else {
        throw new CoreException(417, 'Recording media upload failed');
      }
    } else {
      throw new CoreException(417, 'Recording media upload failed, no file uploaded');
    }
  }

  /**
   * Download recording by id
   *
   * @url GET /recordings/$recording_id/media
   * @url GET /messages/recordings/$recording_id/media
   */
  public function download($recording_id)
  {
    $this->_authorize('recording_read');

    $oRecording = new Recording($recording_id);
    Corelog::log("Recording media / download requested :$oRecording->file_name", Corelog::CRUD);

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
   * @url PUT /recordings/$recording_id
   * @url PUT /messages/recordings/$recording_id
   */
  public function update($recording_id, $data = array())
  {
    $this->_authorize('recording_update');

    $oRecording = new Recording($recording_id);
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
   * @url DELETE /recordings/$recording_id
   * @url DELETE /messages/recordings/$recording_id
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

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
use SplFileInfo;

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
    unset($data['file_name']);
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
   *
   * Upload multiple (one by one) recording files by id
   * @url POST /recordings/$recording_id/media
   * @url POST /messages/recordings/$recording_id/media
   */
  public function upload($recording_id, $data = null, $mime = 'audio/wav')
  {
    $this->_authorize('recording_create');

    $oRecording = new Recording($recording_id);
    if (!empty($data)) {
      if (in_array($mime, Recording::$media_supported)) {
        $extension = array_search($mime, Recording::$media_supported);
        $filename = tempnam('/tmp', 'recording') . ".$extension";
        file_put_contents($filename, $data);
        if ($this->get_request_method() == 'POST') { // if request is post then append, to support multiple files
          $filename = \ICT\Core\path_append($oRecording->file_name, $filename);
        }
        $oRecording->file_name = $filename;
        if ($oRecording->save()) {
          return $oRecording->recording_id;
        } else {
          throw new CoreException(417, 'Recording media upload failed');
        }
      } else {
        throw new CoreException(415, 'Recording media upload failed, invalid file type');
      }
    } else {
      throw new CoreException(411, 'Recording media upload failed, no file uploaded');
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
    if (file_exists($oRecording->file_name)) {
      $oFile = new SplFileInfo($oRecording->file_name);
      return $oFile;
    } else {
      throw new CoreException(404, 'Recording media not found');
    }
    return $oFile;
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
    unset($data['file_name']);
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

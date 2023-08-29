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
use ICT\Core\Message\Document;
use SplFileInfo;

class DocumentApi extends Api
{

  /**
   * Create a new document
   *
   * @url POST /documents
   * @url POST /messages/documents
   */ 
  public function create($data = array())
  {
    $this->_authorize('document_create');
    $oDocument = new Document();
    $oDocument->name = isset($data['name']) ? $data['name'] : null;
    $oDocument->file_name = isset($data['file_name']) ? $data['file_name'] : null;
    $oDocument->type = isset($data['type']) ? $data['type'] : null;
    $oDocument->description = isset($data['description']) ? $data['description'] : null;
    if ($oDocument->save()) {
      $document_id = $oDocument->document_id;
      return $document_id;
    } else {
      throw new CoreException(417, 'Document creation failed');
    }
  }
  /**
   * List all available documents
   *
   * @url GET /documents
   * @url GET /messages/documents
   */
  public function list_view($query = array())
  {
    $this->_authorize('document_list');
    return Document::search((array)$query);
  }

  /**
   * Gets the document by id
   *
   * @url GET /documents/$document_id
   * @url GET /messages/documents/$document_id
   */
  public function read($document_id)
  {
    $this->_authorize('document_read');
    $oDocument = new Document($document_id);
    return $oDocument;
  }

  /**
   * Upload document file by id
   *
   * @url PUT /documents/$document_id/media
   * @url PUT /messages/documents/$document_id/media
   *
   * Upload multiple (one by one) document files by id
   * @url POST /documents/$document_id/media
   * @url POST /messages/documents/$document_id/media
   */
  public function upload($document_id, $data = null, $mime = 'application/pdf')
  {
    $this->_authorize('document_create');
    $oDocument = new Document($document_id);
    if (!empty($data)) {
      if (in_array($mime, Document::$media_supported)) {
        $extension = array_search($mime, Document::$media_supported);
        $filename = tempnam('/tmp', 'document') . ".$extension";
        file_put_contents($filename, $data);
        if ($this->get_request_method() == 'POST') { // if request is post then append, to support multiple files
          $filename = \ICT\Core\path_append($oDocument->file_name, $filename);
        }
        $oDocument->file_name = $filename;
        if ($oDocument->save()) {
          return $oDocument->document_id;
        } else {
          throw new CoreException(417, 'Document media upload failed');
        }
      } else {
        throw new CoreException(415, 'Document media upload failed, invalid file type');
      }
    } else {
      throw new CoreException(411, 'Document media upload failed, no file uploaded');
    }
  }

  /**
   * Download document by id
   *
   * @url GET /documents/$document_id/media
   * @url GET /messages/documents/$document_id/media
   */
  public function download($document_id)
  {
    $this->_authorize('document_read');
    $oDocument = new Document($document_id);
    Corelog::log("Document media / download requested :$oDocument->file_name", Corelog::CRUD);
    $pdf_file = $oDocument->create_pdf($oDocument->file_name, 'tif');
    if (file_exists($pdf_file)) {
      $oFile = new SplFileInfo($pdf_file);
      return $oFile;
    } else {
      throw new CoreException(404, 'Document media not found');
    }
  }

/**
   * Update existing document
   *
   * @url PUT /documents/$document_id
   * @url PUT /messages/documents/$document_id
   */
  public function update($document_id, $data = array())
  {
      $this->_authorize('document_update');
      $oDocument = new Document($document_id); 
      unset($data['file_name']);
      foreach ($data as $key => $value) {
          $oDocument->$key = $value;
      }
      if ($oDocument->save()) {
          return $oDocument;
      } else {
          throw new CoreException(417, 'Document update failed');
      }
  }

  /**
   * Create a new document
   *
   * @url DELETE /documents/$document_id
   * @url DELETE /messages/documents/$document_id
   */
  public function remove($document_id)
  {
    $this->_authorize('document_delete');
    $oDocument = new Document($document_id);
    $result = $oDocument->delete();
    if ($result) {
      return $result;
    } else {
      throw new CoreException(417, 'Document delete failed');
    }
  }

}

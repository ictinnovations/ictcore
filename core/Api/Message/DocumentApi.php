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
    $this->set($oDocument, $data);

    if ($oDocument->save()) {
      return $oDocument->document_id;
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
   */
  public function upload($document_id, $data = array())
  {
    $this->_authorize('document_create');

    $oDocument = new Document($document_id);
    global $_FILES;
    if (!empty($_FILES)) {
      $file = array_shift(array_values($_FILES));
      $type = strtolower(end(explode('.', $file['name'])));
      $oDocument->type = $type;
      $oDocument->file_name = $file['tmp_name'];

      if ($oDocument->save()) {
        return $oDocument->document_id;
      } else {
        throw new CoreException(417, 'Document media upload failed');
      }
    } else {
      throw new CoreException(417, 'Document media upload failed, no file uploaded');
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

    $quoted = sprintf('"%s"', addcslashes(basename($pdf_file), '"\\'));
    $size = filesize($pdf_file);

    header('Content-Description: File Transfer');
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename=' . $quoted);
    header('Content-Transfer-Encoding: binary');
    header('Connection: Keep-Alive');
    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Pragma: public');
    header('Content-Length: ' . $size);

    readfile($pdf_file);

    return true;
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
    $this->set($oDocument, $data);

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

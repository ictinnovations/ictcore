<?php
/* * ***************************************************************
 * Copyright © 2016 ICT Innovations Pakistan All Rights Reserved   *
 * Developed By: Nasir Iqbal                                       *
 * Website : http://www.ictinnovations.com/                        *
 * Mail : nasir@ictinnovations.com                                 *
 * *************************************************************** */

class DocumentApi extends Api
{

  /**
   * Create a new document
   *
   * @url POST /document/create
   * @url POST /message/document/create
   */
  public function create($data = array())
  {
    $this->_authorize('document_create');

    $oDocument = new Document();
    global $_FILES, $_POST;
    if (!empty($_FILES)) {
      $file = array_shift(array_values($_FILES));
      $type = strtolower(end(explode('.', $file['name'])));
      $oDocument->type = $type;
      $oDocument->file_name = $file['tmp_name'];
      // WORK-AROUND as currently JSON is not possible with file uploads
      if (empty($data)) {
        $data = $_POST;
      }
    }
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
   * @url GET /document/list
   * @url GET /message/document/list
   * @url POST /document/list
   * @url POST /message/document/list
   */
  public function list_view($data = array())
  {
    $this->_authorize('document_list');
    return Document::search($data);
  }

  /**
   * Gets the document by id
   *
   * @url GET /document/$document_id
   * @url GET /message/document/$document_id
   */
  public function read($document_id)
  {
    $this->_authorize('document_read');

    $oDocument = new Document($document_id);
    return $oDocument;
  }

  /**
   * Download document by id
   *
   * @url GET /document/$document_id/download
   * @url GET /message/document/$document_id/download
   */
  public function download($document_id)
  {
    $this->_authorize('document_read');

    $oDocument = new Document($document_id);
    Corelog::log("Document file / download requested :$oDocument->file_name", Corelog::CRUD);
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
   * @url POST /document/$document_id/update
   * @url POST /message/document/$document_id/update
   * @url PUT /document/$document_id/update
   * @url PUT /message/document/$document_id/update
   */
  public function update($document_id, $data = array())
  {
    $this->_authorize('document_update');

    $oDocument = new Document($document_id);
    global $_FILES, $_POST;
    if (!empty($_FILES)) {
      $file = array_shift(array_values($_FILES));
      $type = strtolower(end(explode('.', $file['name'])));
      $oDocument->type = $type;
      $oDocument->file_name = $file['tmp_name'];
      // WORK-AROUND as currently JSON is not possible with file uploads
      if (empty($data)) {
        $data = $_POST;
      }
    }
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
   * @url GET /document/$document_id/delete
   * @url GET /message/document/$document_id/delete
   * @url DELETE /document/$document_id/delete
   * @url DELETE /message/document/$document_id/delete
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
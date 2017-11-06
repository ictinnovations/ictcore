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
use ICT\Core\Message\Template;

class TemplateApi extends Api
{

  /**
   * Create a new template
   *
   * @url POST /templates
   * @url POST /messages/templates
   */
  public function create($data = array())
  {
    $this->_authorize('template_create');

    $oTemplate = new Template();
    global $_FILES, $_POST;
    if (!empty($_FILES)) {
      $file = array_shift(array_values($_FILES));
      $type = strtolower(end(explode('.', $file['name'])));
      $oTemplate->type = $type;
      $oTemplate->attachment = $file['tmp_name'];
      // WORK-AROUND as currently JSON is not possible with file uploads
      if (empty($data)) {
        $data = $_POST;
      }
    }
    $this->set($oTemplate, $data);

    if ($oTemplate->save()) {
      return $oTemplate->template_id;
    } else {
      throw new CoreException(417, 'Template creation failed');
    }
  }

  /**
   * List all available templates
   *
   * @url GET /templates
   * @url GET /messages/templates
   */
  public function list_view($query = array())
  {
    $this->_authorize('template_list');
    return Template::search((array)$query);
  }

  /**
   * Gets the template by id
   *
   * @url GET /templates/$template_id
   * @url GET /messages/templates/$template_id
   */
  public function read($template_id)
  {
    $this->_authorize('template_read');

    $oTemplate = new Template($template_id);
    return $oTemplate;
  }

  /**
   * Download template by id
   *
   * @url GET /templates/$template_id/download
   * @url GET /messages/templates/$template_id/download
   */
  public function download($template_id)
  {
    $this->_authorize('template_read');

    $oTemplate = new Template($template_id);
    Corelog::log("Template file / download requested :$oTemplate->attachment", Corelog::CRUD);

    $quoted = sprintf('"%s"', addcslashes(basename($oTemplate->attachment), '"\\'));
    $size = filesize($oTemplate->attachment);

    header('Content-Description: File Transfer');
    header('Content-Type: audio/wav');
    header('Content-Disposition: attachment; filename=' . $quoted);
    header('Content-Transfer-Encoding: binary');
    header('Connection: Keep-Alive');
    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Pragma: public');
    header('Content-Length: ' . $size);

    readfile($oTemplate->attachment);

    return true;
  }

  /**
   * Update existing template
   *
   * @url PUT /templates/$template_id
   * @url PUT /messages/templates/$template_id
   */
  public function update($template_id, $data = array())
  {
    $this->_authorize('template_update');

    $oTemplate = new Template($template_id);
    global $_FILES, $_POST;
    if (!empty($_FILES)) {
      $file = array_shift(array_values($_FILES));
      $type = strtolower(end(explode('.', $file['name'])));
      $oTemplate->type = $type;
      $oTemplate->attachment = $file['tmp_name'];
      // WORK-AROUND as currently JSON is not possible with file uploads
      if (empty($data)) {
        $data = $_POST;
      }
    }
    $this->set($oTemplate, $data);

    if ($oTemplate->save()) {
      return $oTemplate;
    } else {
      throw new CoreException(417, 'Template update failed');
    }
  }

  /**
   * Create a new template
   *
   * @url DELETE /templates/$template_id
   * @url DELETE /messages/templates/$template_id
   */
  public function remove($template_id)
  {
    $this->_authorize('template_delete');

    $oTemplate = new Template($template_id);

    $result = $oTemplate->delete();
    if ($result) {
      return $result;
    } else {
      throw new CoreException(417, 'Template delete failed');
    }
  }

}
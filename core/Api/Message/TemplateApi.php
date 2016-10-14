<?php
/* * ***************************************************************
 * Copyright © 2016 ICT Innovations Pakistan All Rights Reserved   *
 * Developed By: Nasir Iqbal                                       *
 * Website : http://www.ictinnovations.com/                        *
 * Mail : nasir@ictinnovations.com                                 *
 * *************************************************************** */

class TemplateApi extends Api
{

  /**
   * Create a new template
   *
   * @url POST /template/create
   * @url POST /message/template/create
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
   * @url GET /template/list
   * @url GET /message/template/list
   * @url POST /template/list
   * @url POST /message/template/list
   */
  public function list_view($data = array())
  {
    $this->_authorize('template_list');
    return Template::search($data);
  }

  /**
   * Gets the template by id
   *
   * @url GET /template/$template_id
   * @url GET /message/template/$template_id
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
   * @url GET /template/$template_id/download
   * @url GET /message/template/$template_id/download
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
   * @url POST /template/$template_id/update
   * @url POST /message/template/$template_id/update
   * @url PUT /template/$template_id/update
   * @url PUT /message/template/$template_id/update
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
   * @url GET /template/$template_id/delete
   * @url GET /message/template/$template_id/delete
   * @url DELETE /template/$template_id/delete
   * @url DELETE /message/template/$template_id/delete
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
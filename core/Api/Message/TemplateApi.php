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
use SplFileInfo;

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
    unset($data['attachment']);
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
   * Upload template file by id
   *
   * @url PUT /templates/$template_id/media
   * @url PUT /messages/templates/$template_id/media
   */
  public function upload($template_id, $data = null, $mime = 'text/html')
  {
    $this->_authorize('template_create');

    $oTemplate = new Template($template_id);
    if (!empty($data)) {
      if (in_array($mime, Template::$media_supported)) {
        $extension = array_search($mime, Template::$media_supported);
        $filename = tempnam('/tmp', 'template') . ".$extension";
        file_put_contents($filename, $data);
        $oTemplate->attachment = $filename;
        if ($oTemplate->save()) {
          return $oTemplate->template_id;
        } else {
          throw new CoreException(417, 'Template media upload failed');
        }
      } else {
        throw new CoreException(415, 'Template media upload failed, invalid file type');
      }
    } else {
      throw new CoreException(411, 'Template media upload failed, no file uploaded');
    }
  }

  /**
   * Download template by id
   *
   * @url GET /templates/$template_id/media
   * @url GET /messages/templates/$template_id/media
   */
  public function download($template_id)
  {
    $this->_authorize('template_read');

    $oTemplate = new Template($template_id);
    Corelog::log("Template media / download requested :$oTemplate->attachment", Corelog::CRUD);
    if (file_exists($oTemplate->attachment)) {
      $oFile = new SplFileInfo($oTemplate->attachment);
      return $oFile;
    } else {
      throw new CoreException(404, 'Template file not found');
    }

    return $oFile;
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
    unset($data['attachment']);
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

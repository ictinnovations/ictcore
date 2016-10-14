<?php
/* * ***************************************************************
 * Copyright © 2016 ICT Innovations Pakistan All Rights Reserved   *
 * Developed By: Nasir Iqbal                                       *
 * Website : http://www.ictinnovations.com/                        *
 * Mail : nasir@ictinnovations.com                                 *
 * *************************************************************** */

class TextApi extends Api
{

  /**
   * Create a new text
   *
   * @url POST /text/create
   * @url POST /message/text/create
   */
  public function create($data = array())
  {
    $this->_authorize('text_create');

    $oText = new Text();
    $this->set($oText, $data);

    if ($oText->save()) {
      return $oText->text_id;
    } else {
      throw new CoreException(417, 'Text creation failed');
    }
  }

  /**
   * List all available texts
   *
   * @url GET /text/list
   * @url GET /message/text/list
   * @url POST /text/list
   * @url POST /message/text/list
   */
  public function list_view($data = array())
  {
    $this->_authorize('text_list');
    return Text::search($data);
  }

  /**
   * Gets the text by id
   *
   * @url GET /text/$text_id
   * @url GET /message/text/$text_id
   */
  public function read($text_id)
  {
    $this->_authorize('text_read');

    $oText = new Text($text_id);
    return $oText;
  }

  /**
   * Update existing text
   *
   * @url POST /text/$text_id/update
   * @url POST /message/text/$text_id/update
   * @url PUT /text/$text_id/update
   * @url PUT /message/text/$text_id/update
   */
  public function update($text_id, $data = array())
  {
    $this->_authorize('text_update');

    $oText = new Text($text_id);
    $this->set($oText, $data);

    if ($oText->save()) {
      return $oText;
    } else {
      throw new CoreException(417, 'Text update failed');
    }
  }

  /**
   * Create a new text
   *
   * @url GET /text/$text_id/delete
   * @url GET /message/text/$text_id/delete
   * @url DELETE /text/$text_id/delete
   * @url DELETE /message/text/$text_id/delete
   */
  public function remove($text_id)
  {
    $this->_authorize('text_delete');

    $oText = new Text($text_id);

    $result = $oText->delete();
    if ($result) {
      return $result;
    } else {
      throw new CoreException(417, 'Text delete failed');
    }
  }

}
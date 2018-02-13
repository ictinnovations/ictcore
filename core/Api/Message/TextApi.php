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
use ICT\Core\Message\Text;

class TextApi extends Api
{

  /**
   * Create a new text
   *
   * @url POST /texts
   * @url POST /messages/texts
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
   * @url GET /texts
   * @url GET /messages/texts
   */
  public function list_view($query = array())
  {
    $this->_authorize('text_list');
    return Text::search((array)$query);
  }

  /**
   * Gets the text by id
   *
   * @url GET /texts/$text_id
   * @url GET /messages/texts/$text_id
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
   * @url PUT /texts/$text_id
   * @url PUT /messages/texts/$text_id
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
   * @url DELETE /texts/$text_id
   * @url DELETE /messages/texts/$text_id
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

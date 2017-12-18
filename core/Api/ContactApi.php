<?php

namespace ICT\Core\Api;

/* * ***************************************************************
 * Copyright © 2016 ICT Innovations Pakistan All Rights Reserved   *
 * Developed By: Nasir Iqbal                                       *
 * Website : http://www.ictinnovations.com/                        *
 * Mail : nasir@ictinnovations.com                                 *
 * *************************************************************** */

use ICT\Core\Api;
use ICT\Core\Contact;
use ICT\Core\CoreException;

class ContactApi extends Api
{

  /**
   * Create a new contact
   *
   * @url POST /contacts
   */
  public function create($data = array())
  {
    $this->_authorize('contact_create');

    $oContact = new Contact();
    $this->set($oContact, $data);

    if ($oContact->save()) {
      return $oContact->contact_id;
    } else {
      throw new CoreException(417, 'Contact creation failed');
    }
  }

  /**
   * List all available contacts
   *
   * @url GET /contacts
   */
  public function list_view($query = array())
  {
    $this->_authorize('contact_list');
    return Contact::search((array)$query);
  }

  /**
   * Gets the contact by id
   *
   * @url GET /contacts/$contact_id
   */
  public function read($contact_id)
  {
    $this->_authorize('contact_read');

    $oContact = new Contact($contact_id);
    return $oContact;
  }

  /**
   * Update existing contact
   *
   * @url PUT /contacts/$contact_id
   */
  public function update($contact_id, $data = array())
  {
    $this->_authorize('contact_update');

    $oContact = new Contact($contact_id);
    $this->set($oContact, $data);

    if ($oContact->save()) {
      return $oContact;
    } else {
      throw new CoreException(417, 'Contact update failed');
    }
  }

  /**
   * Create a new contact
   *
   * @url DELETE /contacts/$contact_id
   */
  public function remove($contact_id)
  {
    $this->_authorize('contact_delete');

    $oContact = new Contact($contact_id);

    $result = $oContact->delete();
    if ($result) {
      return $result;
    } else {
      throw new CoreException(417, 'Contact delete failed');
    }
  }

  /**
   * Create a new contact group link
   * 
   * @url PUT /contacts/$contact_id/link/$group_id
   */
  public function join($contact_id, $group_id)
  {
    $this->_authorize('contact_create');
    $this->_authorize('group_create');

    $oContact = new Contact($contact_id);

    if ($oContact->link($group_id)) {
      return $oContact;
    } else {
      throw new CoreException(417, 'Contact and group join failed');
    }
  }

  /**
   * Delete an existing contact and group Link
   * 
   * @url DELETE /contacts/$contact_id/link/$group_id
   */
  public function leave($contact_id, $group_id)
  {
    $this->_authorize('contact_delete');
    $this->_authorize('group_delete');

    $oContact = new Contact($contact_id);

    $result = $oContact->link_delete($group_id);
    if ($result) {
      return $result;
    } else {
      throw new CoreException(417, 'Contact delete failed');
    }
  }

}
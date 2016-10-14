<?php
/* * ***************************************************************
 * Copyright © 2016 ICT Innovations Pakistan All Rights Reserved   *
 * Developed By: Nasir Iqbal                                       *
 * Website : http://www.ictinnovations.com/                        *
 * Mail : nasir@ictinnovations.com                                 *
 * *************************************************************** */

class ContactApi extends Api
{

  /**
   * Create a new contact
   *
   * @url POST /contact/create
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
   * @url GET /contact/list
   * @url POST /contact/list
   */
  public function list_view($data = array())
  {
    $this->_authorize('contact_list');
    return Contact::search($data);
  }

  /**
   * Gets the contact by id
   *
   * @url GET /contact/$contact_id
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
   * @url POST /contact/$contact_id/update
   * @url PUT /contact/$contact_id/update
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
   * @url GET /contact/$contact_id/delete
   * @url DELETE /contact/$contact_id/delete
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

}
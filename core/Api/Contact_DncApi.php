<?php

namespace ICT\Core\Api;
/* * ***************************************************************
 * Copyright © 2016 ICT Innovations Pakistan All Rights Reserved   *
 * Developed By: Nasir Iqbal                                       *
 * Website : http://www.ictinnovations.com/                        *
 * Mail : nasir@ictinnovations.com                                 *
 * *************************************************************** */
use ICT\Core\Api;
use ICT\Core\Contact_Dnc;
use ICT\Core\CoreException;
use ICT\Core\Contact_DncCli;
use SplFileInfo;

#[\AllowDynamicProperties]
class ContactDncApi extends Api
{
  /**
   * Create a new contact
   *
   * @url POST /contact_dncs
   */
  public function create($data = array())
  {
    $this->_authorize('contact_create');
    $oContact_Dnc = new Contact_Dnc();
    $this->set($oContact_Dnc, $data);
    if ($oContact_Dnc->save()) {
      return $oContact_Dnc->contact_dnc_id;
    } else {
      throw new CoreException(417, 'Contact_Dnc creation failed');
    }
  }
  /**
   * List all available contacts
   *
   * @url GET /contact_dncs
   */
  public function list_view($query = array())
  {
    $this->_authorize('contact_list');
    return Contact_Dnc::search((array)$query);
  }
  /**
   * Gets the contact by id
   *
   * @url GET /contact_dncs/$contact_dnc_id
   */
  public function read($contact_dnc_id)
  {
    $this->_authorize('contact_read');
    $oContact_Dnc = new Contact_Dnc($contact_dnc_id);
    return $oContact_Dnc;
  }
  /**
   * Update existing contact
   *
   * @url PUT /contact_dncs/$contact_dnc_id
   */
  public function update($contact_dnc_id, $data = array())
  {
    $this->_authorize('contact_update');
    $oContact_Dnc = new Contact_Dnc($contact_dnc_id);
    $this->set($oContact_Dnc, $data);
    if ($oContact_Dnc->save()) {
      return $oContact_Dnc;
    } else {
      throw new CoreException(417, 'Contact_Dnc update failed');
    }
  }
  /**
   * Create a new contact
   *
   * @url DELETE /contact_dncs/$contact_dnc_id
   */
  public function remove($contact_dnc_id)
  {
    $this->_authorize('contact_delete');
    $oContact_Dnc = new Contact_Dnc($contact_dnc_id);
    $result = $oContact_Dnc->delete();
    if ($result) {
      return $result;
    } else {
      throw new CoreException(417, 'Contact_Dnc delete failed');
    }
  }
  /**
   * Create a new contact group link
   *
   * @url PUT /contacts/$contact_dnc_id/link/$group_id
   */
  public function join($contact_dnc_id, $group_id)
  {
    $this->_authorize('contact_create');
    $this->_authorize('group_create');
    $oContact_Dnc = new Contact_Dnc($contact_dnc_id);
    if ($oContact_Dnc->link($group_id)) {
      return $oContact_Dnc;
    } else {
      throw new CoreException(417, 'Contact_Dnc and group join failed');
    }
  }
  /**
   * Delete an existing contact and group Link
   *
   * @url DELETE /contacts/$contact_dnc_id/link/$group_id
   */
  public function leave($contact_dnc_id, $group_id)
  {
    $this->_authorize('contact_delete');
    $this->_authorize('group_delete');
    $oContact_Dnc = new Contact_Dnc($contact_dnc_id);
    $result = $oContact_Dnc->link_delete($group_id);
    if ($result) {
      return $result;
    } else {
      throw new CoreException(417, 'Contact_Dnc delete failed');
    }
  }
  /**
   * Export Dnc
   *
   * @url GET /contact_dncs/contactdncs/csv
   */
  public function export_csv($query = array())
  {
    $filter = (array)$query;
    $listcontact = $this->list_view($filter);
    if ($listcontact) {
      $file_path = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'export_dnc'.'.csv';
      $handle = fopen($file_path, 'w');
      if (!$handle) {
        throw new CoreException(500, "Unable to open file");
      }
      foreach($listcontact as $aValue) {
        $contact_row ='"'.$aValue['first_name'].'","'.$aValue['last_name'].'","'.$aValue['phone'].'"'."\n";
        fwrite($handle, $contact_row);
      }
      fclose($handle);
      return new SplFileInfo($file_path);
    } else {
      throw new CoreException(404, "File not found");
    }
  }
/**
 * Import Dnc
 *
 * @url POST /contact_dncs/import/csv
 */
  public function import_csv( $data = array(), $mime = 'text/csv')
  {
    global $path_cache;
    $allowedTypes = array('csv' => 'text/csv', 'txt' => 'text/plain');
    $filename = $path_cache . DIRECTORY_SEPARATOR . 'dnc_' . 'import' . '.csv';
    file_put_contents($filename, $data);
    if ($mime && $allowedTypes) {
      if (!empty($data)) {
        $odnc = new \ICT\Core\Contact_DncCli();
        return $odnc ->import();
      } else {
        throw new CoreException(411, "Empty file");
      }
    } else {
      throw new CoreException(415, "Unsupported File Type");
    }
  }
}
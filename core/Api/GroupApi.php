<?php

namespace ICT\Core\Api;

/* * ***************************************************************
 * Copyright © 2017 ICT Innovations Pakistan All Rights Reserved   *
 * Developed By: Nasir Iqbal                                       *
 * Website : http://www.ictinnovations.com/                        *
 * Mail : nasir@ictinnovations.com                                 *
 * *************************************************************** */

use ICT\Core\Api;
use ICT\Core\CoreException;
use ICT\Core\Group;
use SplFileInfo;

class GroupApi extends Api
{
  /**
   * Create a new group
   *
   * @url POST /groups
   */
  public function create($data = array())
  {
    $this->_authorize('group_create');
    $oGroup = new Group();
    $this->set($oGroup, $data);
    if ($oGroup->save()) {
      return $oGroup->group_id;
    } else {
      throw new CoreException(417, 'group creation failed');
    }
  }
  /**
   * List all available groups
   *
   * @url GET /groups
   */
  public function list_view($query = array())
  {
    $this->_authorize('group_list');
    return Group::search((array)$query);
  }

  /**
   * List all available groups
   *
   * @url GET /groups/$group_id/contacts
   */
  public function contact_list_view($group_id, $query = array())
  {
    $this->_authorize('group_read');
    $this->_authorize('contact_list');
    $oGroup = new Group($group_id);
    return $oGroup->search_contact((array)$query);
  }

  /**
   * Gets the group by id
   *
   * @url GET /groups/$group_id
   */
  public function read($group_id)
  {
    $this->_authorize('group_read');
    $oGroup = new Group($group_id);
    return $oGroup;
  }

  /**
   * Update existing Group
   *
   * @url PUT /groups/$group_id
   */
  public function update($group_id, $data = array())
  {
    $this->_authorize('group_update');

    $oGroup = new Group($group_id);
    $this->set($oGroup, $data);

    if ($oGroup->save()) {
      return $oGroup;
    } else {
      throw new CoreException(417, 'Group update failed');
    }
  }

  /**
   * remove group
   *
   * @url DELETE /groups/$group_id
   */
  public function remove($group_id)
  {
    $this->_authorize('group_delete');
    $oGroup = new Group($group_id);
    $result = $oGroup->delete();
    if ($result) {
      return $result;
    } else {
      throw new CoreException(417, 'group delete failed');
    }
  }

  /**
   * Export Contact by group id
   *
   * @url GET /groups/$group_id/csv
   * 
   */
   public function export_csv($group_id, $query)
   {
     $oGroup = new Group($group_id);
     $listContact = $oGroup->search_contact((array)$query, true);

     $file_path = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'group_'.$group_id.'.csv';
     $handle = fopen($file_path, 'a');
     if (!$handle) {
       throw new CoreException(500, "Unable to open file");
     }
     foreach($listContact as $aValue) {
       $contact_row = $aValue['phone'].','.$aValue['first_name'].','.$aValue['last_name'].','.$aValue['email'].','.$aValue['address'].','.
                      $aValue['custom1'].','.$aValue['custom2'].','.$aValue['custom3'].','.$aValue['description']."\n";
       fwrite($handle, $contact_row);
     }
     fclose($handle);

     return new SplFileInfo($file_path);
   }

  /**
   * Import Contact by group id
   *
   * @url POST /groups/$group_id/csv
   */
  public function import_csv($group_id, $data = array(), $mime = 'text/csv')
  {
    global $path_root;
    $allowedTypes = array('csv' => 'text/csv');
    if (in_array($mime, $allowedTypes)) {
      if (!empty($data)) {
        $file_path = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'group_'.$group_id.'.csv';
        file_put_contents($file_path, $data);
        $contact_daemon = $path_root . DIRECTORY_SEPARATOR . 'bin' . DIRECTORY_SEPARATOR . 'contact';
        $output = array();
        exec("$contact_daemon '$file_path' $this->group_id start", $output);
        return $output[0];
      } else {
        throw new CoreException(411, "Empty file");
      }
    } else {
      throw new CoreException(415, "Unsupported File Type");
    }
  }

  /**
   * Provide Contact Sample
   *
   * @url GET /groups/sample/csv
   */
  public function sample_csv()
  {
    global $path_data;
    $sample_contact = $path_data . DIRECTORY_SEPARATOR . 'sample_contact.csv';
    if (file_exists($sample_contact)) {
      return SplFileInfo($sample_contact);
    } else {
      throw new CoreException(404, "File not found");
    }
  }

}
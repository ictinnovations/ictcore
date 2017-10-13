<?php
namespace ICT\Core\Api;
/* * ***************************************************************
 * Copyright © 2017 ICT Innovations Pakistan All Rights Reserved   *
 * Developed By: Nasir Iqbal                                       *
 * Website : http://www.ictinnovations.com/                        *
 * Mail : nasir@ictinnovations.com                                 *
 * *************************************************************** */
use ICT\Core\Api;
use ICT\Core\Group;
use ICT\Core\CoreException;
use ICT\Core\Corelog;
class GroupApi extends Api
{
  /**
   * Create a new group
   *
   * @url POST /group/create
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
   * @url GET /group/list
   * @url POST /group/list
   */
  public function list_view($data = array())
  {

    $this->_authorize('group_list');

    return Group::search($data);
  }

/**
   * List all available groupcontacts
   *
   * @url GET /group/$group_id/contact/list
   * @url POST /group/$group_id/contact/list
   */
  public function group_list_view($group_id)
  {
    //$this->_authorize('groupcontact_list');
    return Group::search_group_contact($group_id);
  }
  /**
   * Gets the group by id
   *
   * @url GET /group/$group_id
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
   * @url POST /group/$group_id/update
   * @url PUT /group/$group_id/update
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
   * Create a new group
   *
   * @url GET /group/$group_id/delete
   * @url DELETE /group/$group_id/delete
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
   * @url GET /group/$group_id/export/contact_csv
   * 
   */

   public function export_csv($group_id)
   {
      
     return Group::group_contact_export($group_id);

   }
/**
   * Import  Contact by group id
   *
   * @url POST /group/$group_id/import/contact_csv
   * 
   */
  public function import_csv($group_id,$data = array())
   {
      $oGroup = new Group();

      $test = explode('.',$data->file_name);

       global $_FILES, $_POST;


        $file = fopen($_FILES['file_contents']['tmp_name'], "r");

        $csv_array = array();

        $i=0;

        while (($getData = fgetcsv($file, 10000, ",")) !== FALSE)
        {

            if(!empty($getData[0]))
            {

             $csv_array[] = $getData;

            }

            $i++;

        }

         return Group::group_contact_import($group_id,$csv_array);
     }
     
   /**
   * Import  Contact Sample
   *
   * @url GET /group/import/contact_csv/sample
   * 
   */

   public function import_csv_sample()
   {
      
      $url = 'http://localhost/fileurl/contact.csv';

      header("Location:".$url);

   }


}
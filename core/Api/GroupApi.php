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
   * List all available groupcontacts
   *
   * @url GET /groups/$group_id/contact
   */
  public function group_list_view($group_id)
  {
      $oGroup = new Group($group_id);
      return $oGroup->search_contact();
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
   * @url GET /groups/$group_id/export/contact.csv
   * 
   */
   public function export_csv($group_id)
   {
      //header('Content-Disposition: attachment; filename=contact.csv');
     $oGroup = new Group($group_id);
     header('Content-Type: text/csv; charset=utf-8');  
     header('Content-Disposition: attachment; filename='.$oGroup->contact_export().'.csv'); 
     return ;
   }
/**
   * Import  Contact by group id
   *
   * @url POST /groups/$group_id/import/contact_csv
   * 
   */
  public function import_csv($group_id,$data = array())
   {
      $oGroup = new Group();
      global $_FILES, $_POST;
      $f_name = explode('.',$_FILES['file_contents']['name']);
       $chk_f_type = end($f_name );
      if($chk_f_type == 'csv' || $chk_f_type == 'CSV' || $chk_f_type == 'Csv'){
            $file = fopen($_FILES['file_contents']['tmp_name'], "r");
               return Group::contact_import($_FILES);
           }
         else{
          return "Upload Csv file";
         }
    }
   /**
   * Export  Contact Sample
   *
   * @url GET /group/export/contact_csv/sample
   * 
   */
   public function export_csv_sample()
   {
     // $url = 'http://localhost/fileurl/contact.csv';
     // header("Location:".$url);
      $oGroup = new Group();
      header("Content-Type: application/csv");
      return $oGroup->sample_link();
   }
     /**
   * Export  Contact Sample
   *
   * @url GET /download/$filename
   * 
   */
     public function download($filename)
     {
      echo $filename;
     }
}
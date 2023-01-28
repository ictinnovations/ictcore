<?php

namespace ICT\Core\Api;

/* * ***************************************************************
 * Copyright © 2016 ICT Innovations Pakistan All Rights Reserved   *
 * Developed By: Nasir Iqbal                                       *
 * Website : http://www.ictinnovations.com/                        *
 * Mail : nasir@ictinnovations.com                                 *
 * *************************************************************** */

use ICT\Core\Account;
use ICT\Core\Api;
use ICT\Core\CoreException;
use ICT\Core\User;
use ICT\Core\User\Permission;
use SplFileInfo;

class ExportCsvApi extends Api
{



  /**
   * Export Csv
   *
   * @url GET /export/users
   * 
   */
  public function export_list($query = array())
  {

    $this->_authorize('user_list');
    $oUser = User::search((array)$query);
    if ($oUser) {
      
      $file_path = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'users.csv';
      
      $handle = fopen($file_path, 'w');
      if (!$handle) {
        throw new CoreException(500, "Unable to open file");
      }
     
      foreach($oUser as $aValue) {
        $contact_row = '"'.$aValue['user_id'].'","'.$aValue['first_name'].'","'.$aValue['last_name'].'","'.$aValue['phone'].'","'.$aValue['email'].'",'.
                     '"'.$aValue['address'].'","'.$aValue['company'].'","'.$aValue['country_id'].'","'.$aValue['language_id'].'",'.
                     '"'.$aValue['timezone_id'].'"'."\n";
        fwrite($handle, $contact_row);
      }

      fclose($handle);
  
      return new SplFileInfo($file_path);
    } else {
      throw new CoreException(404, "User not found");
    }
  }


  /**
   * Export User by User id
   *
   * @url GET /export/user/$user_id
   * 
   */
  public function export_csv($user_id)
  {
    $this->_authorize('user_read');
    if ($user_id == 'sample') {
      return $this->sample_csv();
    }

    $oUser=array();
    
    $oUser = new User($user_id);
    
    
    if ($oUser) {
      
      $file_path = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'user'.$user_id.'.csv';
      
      $handle = fopen($file_path, 'w');
      if (!$handle) {
        throw new CoreException(500, "Unable to open file");
      }

      $oUser=(array)$oUser;
      $contact_row = '"'.$oUser['user_id'].'","'.$oUser['first_name'].'","'.$oUser['last_name'].'","'.$oUser['phone'].'","'.$oUser['email'].'",'.
                     '"'.$oUser['address'].'","'.$oUser['company'].'","'.$oUser['country_id'].'","'.$oUser['language_id'].'",'.
                     '"'.$oUser['timezone_id'].'"'."\n";
      fwrite($handle, $contact_row);
      
      fclose($handle);
  
      return new SplFileInfo($file_path);
    } else {
      throw new CoreException(404, "User not found");
    }
  }


  /**
   * Provide User Sample
   *
   * @url GET /export/users/sample
   */
  public function sample_csv()
  {
    global $path_data;
    $sample_contact = $path_data . DIRECTORY_SEPARATOR . 'users_sample.csv';
    if (file_exists($sample_contact)) {
      return new SplFileInfo($sample_contact);
    } else {
      throw new CoreException(404, "File not found");
    }
  }

  



  protected static function rest_include()
  {
    return 'Api/User';
  }

 
}

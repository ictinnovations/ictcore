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

class ImportCsvApi extends Api
{



 
  /**
   * Import Users
   *
   * @url POST /import/users
   * 
   */
  public function import_csv($data = array(), $mime = 'text/csv')
  {
    global $path_root, $path_cache;
    $newUsers=$errors=array();
    $allowedTypes = array('csv' => 'text/csv', 'txt' => 'text/plain');
    if (in_array($mime, $allowedTypes)) {
      if (!empty($data)) {
        $file_path = $path_cache . DIRECTORY_SEPARATOR . 'users.csv';
        file_put_contents($file_path, $data);
        if (file_exists($file_path)) { 
            $csvFile = fopen($file_path, 'r');
            
            // Skip the first line
            fgetcsv($csvFile);
            $line_no=0;
            while(($line = fgetcsv($csvFile)) !== FALSE){
              if($line[4]!=""){
                $line_no++;
                // Get row data

              $data=array(
                  'first_name'=>$line[1],
                  'last_name'=>$line[2],
                  'phone'=>$line[3],
                  'email'=>$line[4],
                  'address'=>$line[5],
                  'company'=>(int)$line[6],
                  'country_id'=>(int)$line[7],
                  'language_id'=>(int)$line[8],
                  'timezone_id'=>(int)$line[9]
                );
             
              $oUser = new User();
              $this->set($oUser, $data);

                if ($oUser->save()) {
                  array_push($newUsers,$oUser->user_id);
                } else {
                  array_push($errors, $line_no);
                }

              }
                
            }

            fclose($csvFile);

           if(empty($errors)){
                return "User Ids: ".json_encode($newUsers);
            }
            else{
              throw new CoreException(415, "Rocord(s) at following line(s) not inserted:".json_encode($errors));
            }
        }
        else{
          throw new CoreException(404, "File not found");
        }

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
   * @url GET /import/users/sample
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

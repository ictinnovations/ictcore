<?php
namespace ICT\Core;
/* * ***************************************************************
 * Copyright © 2014 ICT Innovations Pakistan All Rights Reserved   *
 * Developed By: Nasir Iqbal                                       *
 * Website : http://www.ictinnovations.com/                        *
 * Mail : nasir@ictinnovations.com                                 *
 * *************************************************************** */
use ICT\Core\Contact_Dnc;
use Exception;
use ICT\Core\Corelog;
class Contact_DncCli
{
  private  $csv_file = null;
  private $csv_columns = array(
    'first_name',
    'last_name',
    'phone'
  );
  public function __construct( )
  {
    global $path_cache;
    $filename = $path_cache . DIRECTORY_SEPARATOR . 'dnc_' . 'import' . '.csv';
    if (file_exists($filename)) {
      $this->csv_file = $filename;
     } else {
      throw new CoreException(404, 'File data  not found');
    }
  }
  /**
   * Parse CSV file to import Contact_Dncs
   */
  public function import()
  {
    $count = 0;
    /* parsing csv file */
    $csv_data = array_map("str_getcsv", file($this->csv_file, FILE_SKIP_EMPTY_LINES));
    if (empty($csv_data)) {
      throw new  CoreException(412, 'Invalid file');
    }
    // adding Contact_Dnc
    foreach ($csv_data as $csv_values) {
      try {
        if (count($csv_values) > 1) {
          $aContact_Dnc = array_combine( $this->csv_columns,$csv_values );
        } else {
          $aContact_Dnc = array_combine(array('first_name'), $csv_values);
        }
        $this->add_Contact_Dnc($aContact_Dnc);
        $count++;
      } catch (CoreException $e) {
        Corelog :: log('Unable to add Contact_Dnc. error: ' . $e->getMessage(), Corelog::WARNING, $csv_values);
      }
    }
    return $count;
  }
  /**
   * Build and save Contact_Dnc
   */
  private function add_Contact_Dnc($aContact_Dnc)
  {
    $oContact_Dnc = Contact_Dnc::construct_from_array($aContact_Dnc);
    $oContact_Dnc->save();
    return $oContact_Dnc;
  }
}
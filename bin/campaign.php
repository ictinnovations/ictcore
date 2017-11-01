#!/usr/bin/php  
<?php
namespace ICT\Core;

use \ICT\Core\Campaign;
use \ICT\Core\Transmission;
//use \ICT\Core\CoreException;
require dirname(__DIR__).'/vendor/autoload.php'; // composer
declare(ticks=1);
/*ini_set("log_errors", 1);
ini_set("error_log", "/tmp/php-errorwe.log");
error_log( "Hello, errors!" );*/
/* forking */
(new \Firehed\ProcessControl\Daemon)
 ->setPidFileLocation('/tmp/coreCampaign_td1.pid')
 //->setStdoutFileLocation(sys_get_temp_dir().'/campaign_td.log')
   // ->setStdErrFileLocation(sys_get_temp_dir().'/campaign_td.log')
 ->setProcessName('coreCampaign')
 ->autoRun();
 //parent close database conection that y i put here
 /* Campaing start */
 require_once dirname(__FILE__).'/../core/core.php';
 $campaign_id = $argv[1];  
 $oCampaign = new Campaign($campaign_id);
 $get_group_id = $oCampaign->group_id;
 $get_user_id = $oCampaign->created_by;
 do_login($get_user_id);

// Get tranmisions according to group
 $query = "SELECT c.transmission_id,c.program_id,c.contact_id ,c.status,cl.contact_id ,cl.group_id FROM transmission c INNER JOIN contact_link cl ON c.contact_id = cl.contact_id where cl.group_id=".$get_group_id." AND c.program_id=".$oCampaign->program_id." AND c.created_by=".$get_user_id." AND c.status !='processing' GROUP BY cl.contact_id";
 $result = mysql_query($query);
 $i=0;
if(mysql_num_rows($result)>0)
{
  // Transmissions loop
   while ($data = mysql_fetch_assoc($result)) 
    {
        sleep(2);
       try{
             $oTransmission = new Transmission($data['transmission_id']);
             $oTransmission->send() ;
             /* ini_set("log_errors", 1);
              ini_set("error_log", "/tmp/php-error10.log");
              error_log( "Hello, errors!" );*/
          }
          catch (Exception $e){
            ini_set("error_log", "/tmp/transmission_error.log");
            error_log( "Transmission not found:".$e->getMessage());
          }
        $i++;
    }
}
  else
  {
     // ini_set("log_errors", 1);
      ini_set("error_log", "/tmp/transmission_error.log");
      error_log( "Transmission not found" );
  }

?>

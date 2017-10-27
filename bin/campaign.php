#!/usr/bin/php  
<?php
namespace ICT\Core;
use ICT\Core\Api;
use \ICT\Core\Campaign;
use \ICT\Core\Transmission;
use ICT\Core\CoreException;
use ICT\Core\Corelog;
require dirname(__DIR__).'/vendor/autoload.php'; // composer
declare(ticks=1);
(new \Firehed\ProcessControl\Daemon)
 ->setUser('ictcore')
 ->setPidFileLocation('/tmp/coreCampaign_td1.pid')
 ->setProcessName('coreCampaign')
 ->autoRun();
 //parent close database conection that y i put here
 require_once dirname(__FILE__).'/../core/core.php';
$campaign_id = $argv[1];  
 $user = $argv[2];  
$oCampaign = new Campaign($campaign_id);
$get_group_id = $oCampaign->group_id;
$get_user_id = $oCampaign->created_by;
 do_login($get_user_id);
 $query = "SELECT c.transmission_id,c.program_id,c.contact_id ,c.status,cl.contact_link_id,cl.contact_id ,cl.group_id FROM transmission c INNER JOIN contact_link cl ON c.contact_id = cl.contact_id where cl.group_id=".$get_group_id." AND c.program_id=".$oCampaign->program_id." AND c.created_by=".$get_user_id." AND c.status !='processing' GROUP BY cl.contact_id";
 $result = mysql_query($query);
$i=0;
if(mysql_num_rows($result)>0)
{
  while ($data = mysql_fetch_assoc($result)) 
    {
         sleep(2);
         $oTransmission = new Transmission($data['transmission_id']);
         $oTransmission->send() ;
        $i++;
    }
}
  else
  {
    throw new CoreException(404, 'no transmission found');

  }
?>
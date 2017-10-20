#!/usr/bin/php  
<?php

namespace ICT\Core;

declare(ticks=1);

require  'core.php';

$campaign_id = $argv[1];  

$oCampaign = new Campaign($campaign_id);

$get_group_id = $oCampaign->group_id;

$campaign_id;

$query = "SELECT c.transmission_id,c.program_id,c.contact_id ,cl.contact_link_id,cl.contact_id ,cl.group_id FROM transmission c INNER JOIN contact_link cl ON c.contact_id = cl.contact_id where cl.group_id=".$get_group_id." AND c.program_id=".$oCampaign->program_id." GROUP BY cl.contact_id";

$result = mysql_query($query);

(new \Firehed\ProcessControl\Daemon)
->setUser('ictcore')
->setPidFileLocation('/tmp/coreCampaign.pid')
->setStdoutFileLocation(sys_get_temp_dir().'/coreCampaign.log')
->setStdErrFileLocation('/dev/null')
->setProcessName('coreCampaign')
->autoRun();
  while ($data = mysql_fetch_assoc($result)) 
  {
       sleep(5);
      $oTransmission = new Transmission($data['transmission_id']);
    
      if($oTransmission->status !='processing')
      {

        $oTransmission->send();
      }
      else
      {

         echo "contact_id: ".$data['contact_id'].' '.$oTransmission->status."<br>";

      }

  }

?>
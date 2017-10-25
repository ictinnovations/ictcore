#!/usr/bin/php  
<?php
namespace ICT\Core;
require_once dirname(__DIR__).'/vendor/autoload.php'; // composer
declare(ticks=1);
(new \Firehed\ProcessControl\Daemon)
 ->setUser('ictcore')
 ->setPidFileLocation('/tmp/coreCampaign_td1.pid')
 ->setProcessName('coreCampaign')
 ->autoRun();
require_once dirname(__FILE__).'/../core/core.php';
use \ICT\Core\Campaign;
use  \ICT\Core\Transmission;
$campaign_id = $argv[1];  
$user = $argv[2];  
$oCampaign = new Campaign(1);
$get_group_id = $oCampaign->group_id;
$get_user_id = $oCampaign->created_by;
 do_login($get_user_id);
 $query = "SELECT c.transmission_id,c.program_id,c.contact_id ,c.status,cl.contact_link_id,cl.contact_id ,cl.group_id FROM transmission c INNER JOIN contact_link cl ON c.contact_id = cl.contact_id where cl.group_id=".$get_group_id." AND c.program_id=".$oCampaign->program_id." AND c.created_by=".$get_user_id." AND c.status!='processing' GROUP BY cl.contact_id";
 $result = mysql_query($query);
   // file_put_contents("/tmp/error_chke11"," ============== ".print_r(get_declared_classes(),true)."=================e4===".dirname(__DIR__)."========00-===========".print_r(get_included_files(),true));
$i=0;
    //file_put_contents("/tmp/error_chke13"," ============== ".$data['transmission_id']."=========================".'hellorr'."======".$i."tesssting<br>");
while ($data = mysql_fetch_assoc($result)) 
  {
       sleep(1);
      // file_put_contents("/tmp/error_chke0","==".__NAMESPACE__."==".__DIR__."==".__FILE__."==".__CLASS__."==".__FUNCTION__);
       $oTransmission = new Transmission($data['transmission_id']);
       $oTransmission->send() ;
      // file_put_contents("/tmp/error_chke120"," ============== ".$data['transmission_id']."=========================".'hello'."======".$oTransmission->status."tessstingtry<br>");
      $i++;
  }
  
?>
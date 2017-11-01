#!/usr/bin/php  
<?php
namespace ICT\Core;

use \ICT\Core\Campaign;
use \ICT\Core\Transmission;
require dirname(__DIR__).'/vendor/autoload.php'; // composer
declare(ticks=1);
/* forking */
$group_id = $argv[2];
(new \Firehed\ProcessControl\Daemon)
 ->setPidFileLocation('/tmp/coreCampaign_td1.pid')
 //->setStdoutFileLocation(sys_get_temp_dir().'/campaign_td.log')
 // ->setStdErrFileLocation(sys_get_temp_dir().'/campaign_td.log')
 ->setProcessName('coreCampaign')
 ->autoRun();
 //parent close database conection that y i put here
 require_once dirname(__FILE__).'/../core/core.php';
 $file_tmpname = $argv[1];
 $file_tm = fopen($file_tmpname, "r");
 /* inserting import file contacts */
 while (($value = fgetcsv($file_tm, 10000, ",")) !== FALSE)
{
	if(!empty($value))
	{
	    //echo $getData[0];
	    $result_add = mysql_query("INSERT INTO contact(first_name,last_name,phone,email,address,custom1,custom2,custom3,description) value ('".$value[1]."','".$value[2]."','".$value[0]."','".$value[3]."','".$value[4]."','".$value[5]."','".$value[6]."','".$value[7]."','".$value[8]."')");
	    $result = mysql_insert_id();
	    $result_add = mysql_query("INSERT INTO contact_link(group_id,contact_id) value (".$group_id.",$result )");
	}
  $i++;
}

?>
